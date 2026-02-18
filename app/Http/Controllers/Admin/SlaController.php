<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaPolicy;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class SlaController extends Controller
{
    public function index(Request $request)
    {
        $query = SlaPolicy::query();
        if ($request->filled('searchValue')) {
            $search = $request->searchValue;
            $query->where('entity_type', 'like', "%{$search}%");
        }

        if ($request->filled('entity_type') && $request->entity_type != 'all') {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('priority') && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('active_status') && $request->active_status != 'all') {
            $isActive = $request->active_status == 'active' ? 1 : 0;
            $query->where('is_active', $isActive);
        }

        $policies = $query->latest()->paginate(20);
        $entityTypes = [
            'inbox_message' => 'Inbox Message',
            'lead' => 'Lead',
            'retail_deal' => 'Retail Deal',
            'wholesale_deal' => 'Wholesale Deal',
            'warranty_claim' => 'Warranty Claim',
            'complaint_ticket' => 'Complaint Ticket',
            'service_ticket' => 'Service Ticket',
            'career_ticket' => 'Career Ticket',
            'support_ticket' => 'Support Ticket',
            'retail_ticket' => 'Retail Ticket',
            'wholesale_ticket' => 'Wholesale Ticket',
        ];

        return view('admin-views.crm.sla.index', compact('policies', 'entityTypes'));
    }


    public function create()
    {
        return view('admin-views.crm.sla.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|in:inbox_message,lead,retail_deal,wholesale_deal,warranty_claim,complaint_ticket,service_ticket,career_ticket,support_ticket,retail_ticket,wholesale_ticket',
            'priority' => 'required|in:low,medium,high,urgent',
            'response_time_minutes' => 'required|integer|min:1',
            'resolution_time_minutes' => 'required|integer|min:1',
        ]);

        SlaPolicy::create($request->all());
        Toastr::success('SLA Policy created successfully');
        return redirect()->route('admin.sla.index');
    }

    public function edit($id)
    {
        $policy = SlaPolicy::findOrFail($id);
        return view('admin-views.crm.sla.edit', compact('policy'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'entity_type' => 'required|in:inbox_message,lead,retail_deal,wholesale_deal,warranty_claim,complaint_ticket,service_ticket,career_ticket,support_ticket,retail_ticket,wholesale_ticket',
            'priority' => 'required|in:low,medium,high,urgent',
            'response_time_minutes' => 'required|integer|min:1',
            'resolution_time_minutes' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $policy = SlaPolicy::findOrFail($id);
        $policy->update($request->all());
        Toastr::success('SLA Policy updated successfully');
        return redirect()->route('admin.sla.index');
    }

    public function destroy($id)
    {
        SlaPolicy::findOrFail($id)->delete();
        Toastr::success('SLA Policy deleted successfully');
        return redirect()->back();
    }


    public function status(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sla_policies,id',
            'status' => 'required|boolean',
        ]);

        $sla_policy = SlaPolicy::findOrFail($request->id);
        $sla_policy->is_active = $request->status;
        $sla_policy->save();

        return response()->json(['message' => 'Status updated successfully']);
    }
}
