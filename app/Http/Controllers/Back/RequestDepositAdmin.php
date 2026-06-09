<?php

namespace App\Http\Controllers\Back;

use App\Exports\RequestDepositExport;
use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Filter;
use App\Models\Product;
use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RequestDepositAdmin extends Controller
{
    public function __construct()
    {
        $this->middleware('can:request_deposit');
    }

    public function index(Request $request)
    {
        $items=WalletHistory::where(['withdraw'=>1,'type'=>'withdraw'])->filter($request)->paginate(15);

        return view('back.requestDeposit.index', compact('items'));
    }
    public function history(WalletHistory $history)
    {
        return view('back.requestDeposit.history', compact('history'));
    }
    public function export(Request $request)
    {
        $this->authorize('request_deposit');
        if ($request->source_type=="users"){
            $history=WalletHistory::where(['withdraw'=>1,'type'=>'withdraw','source'=>'user'])->get();
        }elseif ($request->source_type=="sellers"){
            $history=WalletHistory::where(['withdraw'=>1,'type'=>'withdraw','source'=>'seller'])->get();
        }else{
            $history=WalletHistory::where(['withdraw'=>1,'type'=>'withdraw'])->get();
        }

        switch ($request->export_type) {
            case 'excel': {
                return $this->exportExcel($history, $request);
                break;
            }
            default: {
                return $this->exportPrint($history, $request);
            }
        }
    }
    private function exportExcel($history, Request $request)
    {
        return Excel::download(new RequestDepositExport($history, $request), 'request-deposit.xlsx');
    }


}
