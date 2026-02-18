<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CareerCard;
use App\Models\CareerJob;
use App\Models\CareerSection;
use App\Models\CareerBenefits;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Traits\CommonTrait;
use App\Traits\PaginatorTrait;

class CareerController extends Controller
{


    use PaginatorTrait;
    use CommonTrait;

    public function __construct(
        private readonly ProductRepositoryInterface     $productRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,

    ) {}

    protected $modelMap = [
        'current_openings' => CareerJob::class,
        'why_join_us' => CareerCard::class,
        'perks' => CareerBenefits::class,
        'hero' => CareerSection::class,
    ];


    public function index($section = 'current_openings')
    {
        if (!array_key_exists($section, $this->modelMap)) {
            $section = 'current_openings';
        }

        $model = $this->modelMap[$section];
        $data = $model::all();
        $items = $model::latest()->paginate(10);

        return view('admin-views.content-management.career.index', compact('section', 'items'));
    }

    public function pages($section)
    {
        if (!array_key_exists($section, $this->modelMap)) {
            $section = 'current_openings';
        }

        $model = $this->modelMap[$section];
        $data = $model::all();
        $items = $model::latest()->paginate(10);

        return view('admin-views.content-management.career.index', compact('section', 'items', 'data'));
    }

    public function create(Request $request, $section = 'current_openings')
    {
        $section = $request->get('section', 'current_openings'); // default: current_openings

        $viewPath = "admin-views.content-management.career.sections.create." . $section;

        return view($viewPath, compact('section'));
    }

    public function store(Request $request, $section)
    {


        $modelMap = [
            'current_openings' => CareerJob::class,
            'why_join_us' => CareerCard::class,
            'perks' => CareerBenefits::class,
            'hero' => CareerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return back()->withErrors(['Invalid section']);
        }

        $modelClass = $modelMap[$section];
        $defaultLangIndex = array_search(config('app.locale'), $request->lang);

        $data = $request->except('_token', 'lang',);

        $data['title'] = $request->title[$defaultLangIndex] ?? null;
        $data['location'] = $request->location[$defaultLangIndex] ?? null;
        $data['skills'] = $request->skills[$defaultLangIndex] ?? null;

        if ($request->has('job_description')) {
            $data['job_description'] = $request->job_description[$defaultLangIndex] ?? null;
        }
        if ($request->has('description')) {
            $data['description'] = $request->description[$defaultLangIndex] ?? null;
        }
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('career', 'public');
        }

        if ($request->has('buttonText')) {
            $data['button_text'] = $request->buttonText[$defaultLangIndex] ?? null;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('career', 'public');
            $data['image'] = $path;
        }

        $model = new $modelClass;
        $model->fill($data);
        $model->save();

        $this->translationRepo->add($request, $modelClass, $model->id);

        return redirect()->route('admin.content-management.career.pages', ['section' => $section])
            ->with('success', translate('Data added successfully.'));
    }


    public function destroy($section, $id)
    {
        // Map each section to its corresponding model
        $modelMap = [
            'current_openings' => CareerJob::class,
            'why_join_us' => CareerCard::class,
            'perks' => CareerBenefits::class,
            'hero' => CareerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return redirect()->route('admin.content-management.career.pages')
                ->withErrors(['Invalid section']);
        }

        $model = $modelMap[$section]::findOrFail($id);
        $model->delete();

        return redirect()->route('admin.content-management.career.pages', ['section' => $section])
            ->with('success', 'Item deleted successfully.');
    }

    // Edit an item in the selected section
    public function edit($section, $id)
    {
        // Map each section to its corresponding model
        $modelMap = [
            'current_openings' => CareerJob::class,
            'why_join_us' => CareerCard::class,
            'perks' => CareerBenefits::class,
            'hero' => CareerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return redirect()->route('admin.content-management.career.index')
                ->withErrors(['Invalid section']);
        }

        // Get the model instance for the selected section
        $job = $modelMap[$section]::with('translations')->findOrFail($id);
        if (!$job) {
            return redirect()->route('admin.content-management.career.index')
                ->withErrors(['Invalid data or job not found.']);
        }

        return view("admin-views.content-management.career.sections.edit.$section", compact('job', 'section'));
    }

    public function update(Request $request, $section, $id)
    {


        $modelMap = [
            'current_openings' => CareerJob::class,
            'why_join_us' => CareerCard::class,
            'perks' => CareerBenefits::class,
            'hero' => CareerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return back()->withErrors(['Invalid section']);
        }

        $modelClass = $modelMap[$section];
        $model = $modelClass::findOrFail($id);

        $defaultLangIndex = array_search('en', $request->input('lang', []));

        $data = $request->except('_token', 'lang', 'name', 'title', 'description', 'job_description', 'section');

        if ($defaultLangIndex !== false) {
            if ($request->has('name')) {
                $data['name'] = $request->input('name')[$defaultLangIndex];
            }
            if ($request->has('title')) {
                $data['title'] = $request->input('title')[$defaultLangIndex];
            }
            if ($request->has('description')) {
                $data['description'] = $request->input('description')[$defaultLangIndex];
            }
            if ($request->has('job_description')) {
                $data['job_description'] = $request->input('job_description')[$defaultLangIndex]; // store full HTML
            }
        }


        if ($request->has('skills')) {
            $data['skills'] = $request->skills[$defaultLangIndex];
        }
        if ($request->has('location')) {
            $data['location'] = $request->location[$defaultLangIndex];
        }
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('career', 'public');
        }

        $model->fill($data);
        $model->save();

        $this->translationRepo->update($request, $modelClass, $id);
        return redirect()->route('admin.content-management.career.pages', ['section' => $section])
            ->with('success', 'Data updated successfully.');
    }

   public function toggleStatus(Request $request)
{
    $request->validate([
        'id' => 'required|integer',
        'section' => 'required|string|in:current_openings,why_join_us,perks,hero',
    ]);

    if (!isset($this->modelMap[$request->section])) {
        return response()->json(['message' => 'Invalid section.'], 400);
    }

    $modelClass = $this->modelMap[$request->section];
    $item = $modelClass::find($request->id);

    if (!$item) {
        return response()->json(['message' => 'Item not found.'], 404);
    }

    // Toggle status
    $item->is_active = !$item->is_active;
    $item->save();

    $statusText = $item->is_active ? 'activated' : 'deactivated';

    return response()->json([
        'message' => ucfirst(str_replace('_', ' ', $request->section)) . " {$statusText} successfully!",
        'is_active' => $item->is_active
    ]);
}
}
