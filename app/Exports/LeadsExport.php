<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LeadsExport implements FromView
{
    protected $leads;

    public function __construct($leads)
    {
        $this->leads = $leads;
    }

    public function view(): View
    {
        return view('file-exports.leads', [
            'leads' => $this->leads
        ]);
    }
}
