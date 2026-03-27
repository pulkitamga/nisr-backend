<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Enums\ViewPaths\Admin\FileManager;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\FileManagerUploadRequest;
use App\Services\FileManagerService;
use App\Services\FileManagerPathGuard;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileManagerController extends BaseController
{

    public function __construct(
        private readonly FileManagerService $fileManagerService,
        private readonly FileManagerPathGuard $fileManagerPathGuard,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getFoldersView($request);
    }

    public function getFoldersView(Request $request,$folderPath = "cHVibGlj"): View
    {
        $storageConnectionType = getWebConfig(name: 'storage_connection_type');
        $storage = $request['storage'] === 's3' && $storageConnectionType === 's3' ? 's3' : 'public';
        $disk = $this->fileManagerPathGuard->resolveDisk($storage, $storageConnectionType);
        $resolvedPath = $this->fileManagerPathGuard->resolveEncodedPath($folderPath, $storage, $storageConnectionType);
        $displayPath = $this->fileManagerPathGuard->getDisplayPath($resolvedPath, $disk);
        $folderPath = base64_encode($displayPath);
        $storageConnectionType = getWebConfig(name: 'storage_connection_type');

        if ($disk === 's3') {
            $directory = $resolvedPath === '' ? '' : $resolvedPath . '/';
            $s3 = Storage::disk('s3');
            $file = $directory === '' ? [] : $s3->allFiles($directory);
            $directories = $s3->allDirectories($directory);
        } else {
            $file = Storage::disk('local')->files($resolvedPath);
            $directories = Storage::disk('local')->directories($resolvedPath);
        }

        $folders = $this->fileManagerService->formatFileAndFolders(files: $directories, type: 'folder');
        $files = $this->fileManagerService->formatFileAndFolders(files: $file, type: 'file');
        $data = array_merge($folders, $files);
        $currentFolder = $displayPath === '' ? [] : explode('/', $displayPath);
        $previousFolder = count($currentFolder) > 1
            ? implode('/', array_slice($currentFolder, 0, -1))
            : ($disk === 'local' ? 'public' : '');

        return view(FileManager::VIEW[VIEW], compact('data', 'folderPath', 'currentFolder', 'previousFolder','storage','storageConnectionType'));
    }

    public function upload(FileManagerUploadRequest $request, FileManagerService $fileManagerService): RedirectResponse
    {
        if (env('APP_MODE') == 'demo') {
            Toastr::info(translate('This_option_is_disabled_for_demo'));
            return back();
        }

        $storageConnectionType = getWebConfig(name: 'storage_connection_type');
        $storage = $request['storage'] === 's3' && $storageConnectionType === 's3' ? 's3' : 'public';
        $resolvedPath = $this->fileManagerPathGuard->resolvePlainPath($request->input('path'), $storage, $storageConnectionType);
        $request->merge([
            'storage' => $storage,
            'path' => $resolvedPath,
        ]);

        $fileManagerService->uploadImages(request: $request);
        Toastr::success(translate('image_uploaded_successfully'));
        return back()->with('success', translate('image_uploaded_successfully'));
    }

    public function download(Request $request , $fileName): StreamedResponse
    {
        $storageConnectionType = getWebConfig(name: 'storage_connection_type');
        $storage = $request['storage'] === 's3' && $storageConnectionType === 's3' ? 's3' : 'public';
        $disk = $this->fileManagerPathGuard->resolveDisk($storage, $storageConnectionType);
        $resolvedPath = $this->fileManagerPathGuard->resolveEncodedPath($fileName, $storage, $storageConnectionType);

        return Storage::disk($disk)->download($resolvedPath);
    }
}
