<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;

class RequestDepositExport implements FromView
{
    public $users;
    public $request;

    public function __construct($histories, Request $request)
    {
        $this->histories   = $histories;
        $this->request = $request;
    }

    public function view(): View
    {
        return view('back.exports.requestDeposit', [
            'histories'     => $this->histories,
            'request'   => $this->request,
        ]);
    }
}
