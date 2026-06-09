<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class NewslettersExport implements FromView, ShouldAutoSize, WithTitle
{
    public $subscribers;
    public $type;

    public function __construct($subscribers, $type)
    {
        $this->subscribers = $subscribers;
        $this->type = $type;
    }

    public function view(): View
    {
        return view('back.exports.newsletters', [
            'subscribers' => $this->subscribers,
            'type' => $this->type,
        ]);
    }

    public function title(): string
    {
        $title = 'newsletters';

        switch ($this->type) {
            case 'email':
                $title = 'emails';
                break;
            case 'mobile':
                $title = 'mobiles';
                break;
            case 'active':
                $title = 'active_subscribers';
                break;
            case 'inactive':
                $title = 'inactive_subscribers';
                break;
        }

        return $title . '_' . now()->format('Y-m-d');
    }
}
