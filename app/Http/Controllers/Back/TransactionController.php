<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Resources\Datatable\Transaction\TransactionCollection;
use App\Models\SellerDeposit;
use App\Models\Transaction;
use App\Models\WalletHistory;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Transaction::class, 'transaction');
    }

    public function index()
    {
        return view('back.transactions.index');
    }

    public function apiIndex(Request $request)
    {
        $this->authorize('payments.transactions.index');

        $transactions = Transaction::filter($request);

        $transactions = datatable($request, $transactions);

        return new TransactionCollection($transactions);
    }

    public function show(Transaction $transaction)
    {
        return view('back.transactions.show', compact('transaction'))->render();
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return response('success');
    }

    public function multipleDestroy(Request $request)
    {
        $this->authorize('payments.transactions.delete');

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:transactions,id',
        ]);

        foreach ($request->ids as $id) {
            $transaction = Transaction::find($id);
            $this->destroy($transaction);
        }

        return response('success');
    }

    public function seller_deposits()
    {
        $histories = SellerDeposit::latest()->paginate(20);

        return view('back.transactions.seller_deposits', compact('histories'));
    }
}
