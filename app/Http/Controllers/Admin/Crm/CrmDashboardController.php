<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\Calculators\MessageCalculator;
use App\Utils\Calculators\LeadCalculator;
use App\Utils\Calculators\DealCalculator;
use App\Utils\Calculators\TicketCalculator;
use App\Utils\Calculators\WarrantyCalculator;
use App\Utils\Calculators\SlaActivityCalculator;
use App\Utils\Calculators\ServiceChartCalculator;
use App\Enums\ViewPaths\Admin\Crm;
use Illuminate\Support\Facades\View;

class CrmDashboardController extends Controller
{
    public function index(Request $request)
    {
        $statisticsType = $request->input('statistics_type', 'overall');

        // ---------- Calculators (unchanged) ----------
        $messageCalc   = new MessageCalculator($statisticsType);
        $leadCalc      = new LeadCalculator($statisticsType);
        $dealCalc      = new DealCalculator($statisticsType);
        $ticketCalc    = new TicketCalculator($statisticsType);
        $warrantyCalc  = new WarrantyCalculator($statisticsType);
        $slaCalc       = new SlaActivityCalculator($statisticsType);
        $chartCalc     = new ServiceChartCalculator($statisticsType);

        // ---------- Data fetching (unchanged) ----------
        $inboundMessages     = $messageCalc->inbound();
        $newMessages         = $messageCalc->newMessages();
        $convertedMessages   = $messageCalc->convertedMessages();
        $ignoredMessages     = $messageCalc->ignoredMessages();

        $totalLeads         = $leadCalc->totalLeads();
        $workingLeads       = $leadCalc->workingLeads();
        $qualifiedLeads     = $leadCalc->qualifiedLeads();
        $convertedLeads     = $leadCalc->convertedLeads();

        $openRetailDeals    = $dealCalc->openRetailDeals();
        $wonRetailDeals     = $dealCalc->wonRetailDeals();
        $lostRetailDeals    = $dealCalc->lostRetailDeals();
        $openWholesaleDeals = $dealCalc->openWholesaleDeals();
        $wonWholesaleDeals  = $dealCalc->wonWholesaleDeals();
        $lostWholesaleDeals = $dealCalc->lostWholesaleDeals();

        $supportTickets   = $ticketCalc->supportTickets();
        $complaintTickets = $ticketCalc->complaintTickets();
        $serviceTickets   = $ticketCalc->serviceTickets();
        $careerTickets    = $ticketCalc->careerTickets();
        $retailTickets    = $ticketCalc->retailTickets();
        $wholesaleTickets = $ticketCalc->wholesaleTickets();

        $warrantyClaims = $warrantyCalc->warrantyClaims();
        $claimsApproved = $warrantyCalc->claimsApproved();
        $claimsPending  = $warrantyCalc->claimsPending();
        $activeWarranty = $warrantyCalc->activeWarranty();

        $overdueSLAs      = $slaCalc->overdueSLAs();
        $pendingActivities = $slaCalc->pendingActivities();
        $voipCallsToday    = $slaCalc->voipCallsToday();

        $totalServices = $chartCalc->totalServices();
        $totalInvoice  = $chartCalc->totalInvoice();

        // Prepare data array
        $data = compact(
            'statisticsType',
            'inboundMessages',
            'newMessages',
            'convertedMessages',
            'ignoredMessages',
            'totalLeads',
            'workingLeads',
            'qualifiedLeads',
            'convertedLeads',
            'openRetailDeals',
            'wonRetailDeals',
            'lostRetailDeals',
            'openWholesaleDeals',
            'wonWholesaleDeals',
            'lostWholesaleDeals',
            'supportTickets',
            'complaintTickets',
            'serviceTickets',
            'careerTickets',
            'retailTickets',
            'wholesaleTickets',
            'warrantyClaims',
            'claimsApproved',
            'claimsPending',
            'activeWarranty',
            'overdueSLAs',
            'pendingActivities',
            'voipCallsToday',
            'totalServices',
            'totalInvoice'
        );

        // AJAX request - return JSON with raw data (and optional HTML)
        if ($request->ajax() || $request->wantsJson()) {
            // Optional: Render HTML for full section replace
            $html = [
                'messages' => View::make('admin-views.crm.dashboard.partials.message', $data)->render(),
                'leads'    => View::make('admin-views.crm.dashboard.partials.lead', $data)->render(),
                'deals'    => View::make('admin-views.crm.dashboard.partials.deal', $data)->render(),
                'tickets'  => View::make('admin-views.crm.dashboard.partials.tickets', [
                    'counts' => [
                        'Support' => $supportTickets,
                        'Complaint' => $complaintTickets,
                        'Service' => $serviceTickets,
                        'Career' => $careerTickets,
                        'Retail' => $retailTickets,
                        'Wholesale' => $wholesaleTickets
                    ],
                    'img' => 'tickets.png'
                ])->render(),
                'warranty' => View::make('admin-views.crm.dashboard.partials.warranty', [
                    'counts' => ['Claims' => $warrantyClaims, 'Approved' => $claimsApproved, 'Pending' => $claimsPending, 'Active' => $activeWarranty],
                    'img' => 'warranty.png'
                ])->render(),
                'sla'      => View::make('admin-views.crm.dashboard.partials.sla', [
                    'counts' => ['Overdue SLAs' => $overdueSLAs, 'Pending Activities' => $pendingActivities, 'VoIP Calls Today' => $voipCallsToday],
                    'img' => 'sla.png'
                ])->render(),
                'service'  => View::make('admin-views.crm.dashboard.partials.service_overview', [
                    'totalServices' => $totalServices,
                    'totalInvoice' => $totalInvoice
                ])->render(),
            ];

            return response()->json([
                'success' => true,
                'data'    => $data,  // Raw numbers for quick update
                'html'    => $html   // HTML for full replace (optional)
            ]);
        }

        // Normal full page
        return view(Crm::DASHBOARD_INDEX[VIEW], $data);
    }
}
