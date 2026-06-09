<?php

namespace App\Http\Controllers\Back;

use App\Events\WalletAmountDecreased;
use App\Events\WalletAmountIncreased;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:users.wallet');
    }

    public function show(Wallet $wallet)
    {
        $histories = $wallet->histories()->latest()->paginate(20);
        return view('back.wallets.show', compact('wallet', 'histories'));
    }

    public function create(Wallet $wallet)
    {
        return view('back.wallets.create', compact('wallet'));
    }

    public function store(Wallet $wallet, Request $request)
    {
        if ($request->status=='canceled' and $request->backMoney) {
            $data = $request->validate([
                'type' => 'required|in:deposit,withdraw',
                'amount' => 'required|numeric',
                'description' => 'nullable'
            ]);
            $data['order_id'] =$request->order_id;
            $data['orderCanceled'] =1;
        }else{
            $data = $request->validate([
                'type' => 'required|in:deposit,withdraw',
                'amount' => 'required|numeric|max:100000000',
                'description' => 'nullable'
            ]);
        }


        $data['source'] = 'admin';
        $data['status'] = 'success';

        if ($data['type'] == 'withdraw') {
            $request->validate([
                'amount' => 'numeric|max:' . $wallet->balance
            ]);
        }


        DB::transaction(function () use ($wallet, $data) {
            $wallet->histories()->create($data);

            if ($data['type'] == 'deposit') {
                $wallet->update([
                    'balance' => $wallet->balance + $data['amount']
                ]);
                if (@$data['order_id']){
                    $wallet->update([
                        'order_id' => $data['order_id']
                    ]);
                }

                event(new WalletAmountIncreased($wallet));
            } else {
                $wallet->update([
                    'balance' => $wallet->balance - $data['amount']
                ]);

                event(new WalletAmountDecreased($wallet));
            }
        });

        if ($request->status!='canceled' and !$request->backMoney) {
            session()->put('toast-success', 'تراکنش با موفقیت ایجاد شد.');
        }
        return response('success');
    }

    public function history(WalletHistory $history)
    {
        return view('back.wallets.history', compact('history'));
    }

    public function status_pay(Request $request,WalletHistory $history)
    {
        if ($request->status_pay){
            if ($request->status_pay=="unpay-refund" and $history->status_pay!="unpay-refund"){
                $new_history=new WalletHistory();
                $new_history->wallet_id=$history->wallet_id;
                $new_history->type='deposit';
                $new_history->order_id=$history->order_id;
                $new_history->source='seller';
                $new_history->status='success';
                $new_history->amount=$history->amount;
                $new_history->description='بازگشت وجه';
                $new_history->amount=$history->amount;
                $new_history->save();

                $wallet=Wallet::find($history->wallet_id);
                $amount=$wallet->balance+$history->amount;
                $wallet->balance=$amount;
                $wallet->save();
            }
            $history->status_pay=$request->status_pay;
            $history->save();

            if($history->status_pay=="waiting"){
                $status_pay='<div class="badge badge-warning">در انتظار پرداخت</div>';
            }elseif($history->status_pay=="pay"){
                $status_pay='<div class="badge badge-success">پرداخت شده</div>';
            }elseif($history->status_pay=="unpay"){
                $status_pay='<div class="badge badge-danger">پرداخت نشده</div>';
            }elseif($history->status_pay=="unpay-refund"){
                $status_pay='<div class="badge badge-danger">پرداخت نشده و مبلغ عودت داده شده</div>';
            }

            return response([
                'id'=>$history->id,
                'status_pay'=>$status_pay
            ]);
        }
        if ($request->trackingId){
             $request->validate([
                'trackingId' => 'required|unique:wallet_histories,trackingId,'.$history->id,
            ],[
                'trackingId.unique'=>'شماره پیگیری قبلا ثبت شده است'
             ]);
            $history->trackingId=$request->trackingId;
            $history->save();
        }

    }

}
