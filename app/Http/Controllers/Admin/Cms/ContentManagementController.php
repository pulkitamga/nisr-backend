<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log; 

class ContentManagementController extends Controller
{
    protected $baseViewPath = 'admin-views.content-management';

    public function edit($slug)
    {
        // Correct folder structure using slash instead of dot
        $filePath = resource_path("views/admin-views/content-management/{$slug}/index.blade.php");
    
    
        if (!File::exists($filePath)) {
            abort(404, "View file not found for slug: {$slug}");
        }
    
        $content = File::get($filePath);
    
        return view("admin-views.content-management.{$slug}.edit", compact('slug', 'content'));
    }
    

    public function update(Request $request, $slug)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $filePath = resource_path("views/admin-views/content-management/{$slug}/edit.blade.php");

        if (!File::exists($filePath)) {
            abort(404, "File not found for update.");
        }

        File::put($filePath, $request->content);

        return redirect()->route('admin.content-management.edit', $slug)->with('success', 'Page updated successfully!');
    }

    public function showHomeEdit()
    {
        return view('admin-views.content-management.home.edit');
    }
}
