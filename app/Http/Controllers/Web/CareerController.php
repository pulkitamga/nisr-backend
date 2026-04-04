<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CareerJob;
use App\Models\CareerSection;
use App\Contracts\Repositories\RobotsMetaContentRepositoryInterface;
use App\Models\careerApplies;
use App\Models\InboxMessage;
use Brian2694\Toastr\Facades\Toastr;
use App\Services\SlaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;




class CareerController extends Controller
{

    public function __construct(
        private readonly RobotsMetaContentRepositoryInterface $robotsMetaContentRepo,
        private SlaService                                         $slaService,

    ) {}

    public function show($slug)
    {
        $job = CareerJob::where('id', $slug)
            ->where('is_active', 1)
            ->firstOrFail();
        $careerSection = CareerSection::where('is_active', 1)->get();

        return view('default.web-views.pages.career-detail', compact('job', 'careerSection'));
    }


    public function careerStore(Request $request)
    {
        $request->validate([
            'job_id'        => 'required|exists:career_jobs,id',
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => 'required|email|max:150',
            'phone'         => 'required|string|max:20',
            'gender'        => 'required|in:male,female,other',
            'country'       => 'required|string|max:100',
            'state'         => 'required|string|max:100',
            'city'          => 'required|string|max:100',
            'area'          => 'nullable|string|max:150',
            'notice_period' => 'nullable|string|max:50',
            'last_ctc'      => 'nullable|numeric',
            'resume'        => 'required|mimes:pdf,doc,docx|max:2048',
        ]);

        $resumePath = null;

        try {
            if ($request->hasFile('resume')) {
                $resumePath = $request->file('resume')->store('career-resumes', 'local');
            }

            DB::transaction(function () use ($request, $resumePath) {
                careerApplies::create([
                    'job_id'        => $request->job_id,
                    'first_name'    => $request->first_name,
                    'last_name'     => $request->last_name,
                    'email'         => $request->email,
                    'phone'         => $request->phone,
                    'gender'        => $request->gender,
                    'country'       => $request->country,
                    'state'         => $request->state,
                    'city'          => $request->city,
                    'area'          => $request->area,
                    'notice_period' => $request->notice_period,
                    'last_ctc'      => $request->last_ctc,
                    'resume'        => $resumePath,
                ]);

                InboxMessage::create([
                    'subject'      => 'Career Application - ' . $request->first_name . ' ' . $request->last_name,
                    'body'         => 'New career application submitted',
                    'sender_name'  => $request->first_name . ' ' . $request->last_name,
                    'sender_email' => $request->email,
                    'sender_phone' => $request->phone,
                    'pipeline'     => 'form',
                    'message_type' => 'career',
                    'status'       => 'new',
                    'priority'     => 'medium',
                    'details'      => [
                        'job_id'        => $request->job_id,
                        'gender'        => $request->gender,
                        'country'       => $request->country,
                        'state'         => $request->state,
                        'city'          => $request->city,
                        'area'          => $request->area,
                        'notice_period' => $request->notice_period,
                        'last_ctc'      => $request->last_ctc,
                        'resume'        => $resumePath,
                    ]
                ]);
            });
        } catch (Throwable) {
            if ($resumePath) {
                Storage::disk('local')->delete($resumePath);
            }
            return back()
                ->withInput()
                ->withErrors(['career' => translate('something_went_wrong')]);
        }

     
        
        Toastr::success(translate('career_applied_success'));

        return redirect()->back();
    }
}
