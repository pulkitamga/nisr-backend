<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SerialTransferHistory;
use App\Models\Branch;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

class WarrantyTransferController extends Controller
{
    public function list(Request $request)
    {

        $dataLimit = getWebConfig('pagination_limit') ?? 10;
        $query = SerialTransferHistory::with(['fromBranch', 'toBranch', 'distributor', 'stockTransfer', 'wholesaleDelivery'])
            ->latest();


        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('serial_number', 'like', "%{$search}%");
        }
        if ($request->filled('from_branch')) {
            $query->where('from_branch_id', $request->from_branch);
        }

        if ($request->filled('to_branch')) {
            $query->where('to_branch_id', $request->to_branch);
        }

        if ($request->filled('transfer_type')) {
            $query->where('transfer_type', $request->transfer_type);
        }

        // DATE FILTER (from & to)
        if ($request->filled('from') && $request->filled('to')) {
            $from = $request->from . ' 00:00:00';
            $to   = $request->to . ' 23:59:59';

            $query->whereBetween('transferred_at', [$from, $to]);
        }


        $transactions = $query->paginate($dataLimit);

        $branches = Branch::pluck('branch_name', 'id');
        $types = [
            'branch_to_branch' => 'Branch → Branch',
            'branch_to_wholesale' => 'Branch → Wholesaler',
        ];

        return view('admin-views.warranty.serial-transaction.list', compact('transactions', 'branches', 'types'));
    }

    public function historyModal($serial)
    {
        $history = SerialTransferHistory::with(['fromBranch', 'toBranch', 'distributor', 'stockTransfer', 'wholesaleDelivery'])
            ->where('serial_number', $serial)
            ->latest()
            ->get();

        return view('admin-views.warranty.serial-transaction._history-modal', compact('history', 'serial'))->render();
    }
}
