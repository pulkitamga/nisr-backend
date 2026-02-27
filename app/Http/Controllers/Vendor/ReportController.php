<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function set_date(Request $request): RedirectResponse
    {
        $from = $request->input('from');
        $to = $request->input('to');

        session()->put('from_date', $from);
        session()->put('to_date', $to);

        $previousUrl = strtok(url()->previous(), '?');

        return redirect()
            ->to($previousUrl . '?' . http_build_query(['from_date' => $from, 'to_date' => $to]))
            ->with(['from' => $from, 'to' => $to]);
    }
}
