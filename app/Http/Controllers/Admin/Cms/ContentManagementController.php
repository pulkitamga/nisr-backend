<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Traits\AuthorizesCmsSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ContentManagementController extends Controller
{
    use AuthorizesCmsSection;

    protected $baseViewPath = 'admin-views.content-management';
    private string $contentBasePath;

    public function __construct()
    {
        $this->middleware($this->cmsPermissionMiddleware('cms_section.read'))->only(['edit', 'showHomeEdit']);
        $this->middleware($this->cmsPermissionMiddleware('cms_section.update'))->only(['update']);
        $this->contentBasePath = resource_path('views/admin-views/content-management');
    }

    private function resolveContentFilePath(string $slug, string $fileName): string
    {
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $slug)) {
            abort(404);
        }

        $baseDirectory = realpath($this->contentBasePath);

        if ($baseDirectory === false) {
            abort(404);
        }

        $sectionDirectory = realpath($baseDirectory . DIRECTORY_SEPARATOR . $slug);

        if (
            $sectionDirectory === false
            || !str_starts_with($sectionDirectory, $baseDirectory)
        ) {
            abort(404);
        }

        return $sectionDirectory . DIRECTORY_SEPARATOR . $fileName;
    }

    public function edit($slug)
    {
        $filePath = $this->resolveContentFilePath($slug, 'index.blade.php');

        if (!File::exists($filePath)) {
            abort(404);
        }

        $content = File::get($filePath);

        return view("admin-views.content-management.{$slug}.edit", compact('slug', 'content'));
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $filePath = $this->resolveContentFilePath($slug, 'edit.blade.php');

        if (!File::exists($filePath)) {
            abort(404);
        }

        File::put($filePath, $request->content);

        return redirect()->route('admin.content-management.edit', $slug)->with('success', 'Page updated successfully!');
    }

    public function showHomeEdit()
    {
        return view('admin-views.content-management.home.edit');
    }
}
