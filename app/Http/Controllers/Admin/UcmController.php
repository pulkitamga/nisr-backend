<?php

namespace App\Http\Controllers\Admin;

use App\Services\UcmApiService;
use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CrmCall;
use Illuminate\Http\Request;

class UcmController extends Controller
{
    public function calls()
    {
        $ucm = new UcmApiService();
        $active = $ucm->getActiveCalls();
        $employeeExtension = auth('admin')->user()->extension ?? '1001';

        $calls = collect($active)->map(function ($call) use ($employeeExtension) {
            $caller = $call['caller_id'] ?? '';
            $callee = $call['callee_id'] ?? '';
            $callId = $call['call_id'] ?? '';

            $contact = User::where('phone', $caller)
                ->orWhere('phone', $callee)
                ->first();
            return [
                'call_id' => $callId,
                'caller' => $caller,
                'callee' => $callee,
                'contact' => $contact ? ['name' => $contact->name] : null,
                'is_mine' => $callee === $employeeExtension,
            ];
        })->values();

        return response()->json($calls);
    }

    public function accept(Request $request)
    {
        return response()->json(['status' => 'accepted']);
    }
    public function end(Request $request)
    {
        $call = CrmCall::where('call_id', $request->call_id)->first();
        if ($call) {
            $call->update(['status' => 'completed', 'call_duration' => now()->diffInSeconds($call->call_date)]);
        }
        return response()->json(['status' => 'ended']);
    }
}