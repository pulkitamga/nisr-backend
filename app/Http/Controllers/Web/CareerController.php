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
        $validated = $request->validate([
            'job_id'        => 'required|exists:career_jobs,id',
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => 'required|email|max:150',
            'phone'         => 'required|string|max:20',
            'gender'        => 'required|in:Male,Female',
            'country'       => 'required|string|max:100',
            'state'         => 'required|string|max:100',
            'city'          => 'required|string|max:100',
            'area'          => 'nullable|string|max:150',
            'notice_period' => 'nullable|string|max:50',
            'last_ctc'      => 'nullable|numeric',
            'resume'        => 'required|mimes:pdf,doc,docx|max:2048',
        ], [], $this->careerValidationAttributes());

        $applicationData = [
            'job_id'        => $validated['job_id'],
            'first_name'    => $validated['first_name'],
            'last_name'     => $validated['last_name'],
            'email'         => $validated['email'],
            'phone'         => $validated['phone'],
            'gender'        => $validated['gender'],
            'country'       => $validated['country'],
            'state'         => $validated['state'],
            'city'          => $validated['city'],
            'area'          => $this->normalizeOptionalCareerField($validated['area'] ?? null),
            'notice_period' => $this->normalizeOptionalCareerField($validated['notice_period'] ?? null),
            'last_ctc'      => $this->normalizeOptionalCareerField($validated['last_ctc'] ?? null),
        ];

        $resumePath = null;

        try {
            if ($request->hasFile('resume')) {
                $resumePath = $request->file('resume')->store('career-resumes', 'local');
            }

            DB::transaction(function () use ($applicationData, $resumePath) {
                careerApplies::create([
                    'job_id'        => $applicationData['job_id'],
                    'first_name'    => $applicationData['first_name'],
                    'last_name'     => $applicationData['last_name'],
                    'email'         => $applicationData['email'],
                    'phone'         => $applicationData['phone'],
                    'gender'        => $applicationData['gender'],
                    'country'       => $applicationData['country'],
                    'state'         => $applicationData['state'],
                    'city'          => $applicationData['city'],
                    'area'          => $applicationData['area'],
                    'notice_period' => $applicationData['notice_period'],
                    'last_ctc'      => $applicationData['last_ctc'],
                    'resume'        => $resumePath,
                ]);

                InboxMessage::create([
                    'subject'      => 'Career Application - ' . $applicationData['first_name'] . ' ' . $applicationData['last_name'],
                    'body'         => 'New career application submitted',
                    'sender_name'  => $applicationData['first_name'] . ' ' . $applicationData['last_name'],
                    'sender_email' => $applicationData['email'],
                    'sender_phone' => $applicationData['phone'],
                    'pipeline'     => 'form',
                    'message_type' => 'career',
                    'status'       => 'new',
                    'priority'     => 'medium',
                    'details'      => [
                        'job_id'        => $applicationData['job_id'],
                        'gender'        => $applicationData['gender'],
                        'country'       => $applicationData['country'],
                        'state'         => $applicationData['state'],
                        'city'          => $applicationData['city'],
                        'area'          => $applicationData['area'],
                        'notice_period' => $applicationData['notice_period'],
                        'last_ctc'      => $applicationData['last_ctc'],
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

    private function normalizeOptionalCareerField(mixed $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function careerValidationAttributes(): array
    {
        return [
            'job_id' => $this->translatedValidationAttribute('job_id', 'job'),
            'first_name' => $this->translatedValidationAttribute('first_name', 'first name'),
            'last_name' => $this->translatedValidationAttribute('last_name', 'last name'),
            'email' => $this->translatedValidationAttribute('email', 'email address'),
            'phone' => $this->translatedValidationAttribute('phone', 'phone number'),
            'gender' => $this->translatedValidationAttribute('gender', 'gender'),
            'country' => $this->translatedValidationAttribute('country', 'country'),
            'state' => $this->translatedValidationAttribute('state', 'state'),
            'city' => $this->translatedValidationAttribute('city', 'city'),
            'area' => $this->translatedValidationAttribute('area', 'area'),
            'notice_period' => $this->translatedValidationAttribute('notice_period', 'notice period'),
            'last_ctc' => $this->translatedValidationAttribute('last_ctc', 'last salary'),
            'resume' => $this->translatedValidationAttribute('resume', 'resume / CV'),
        ];
    }

    private function translatedValidationAttribute(string $key, string $fallback): string
    {
        $translated = trans("validation.attributes.$key");

        return $translated === "validation.attributes.$key" ? $fallback : $translated;
    }
}
