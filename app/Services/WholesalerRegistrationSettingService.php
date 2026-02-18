<?php

namespace App\Services;

use App\Traits\FileManagerTrait;

class WholesalerRegistrationSettingService
{
    use FileManagerTrait;
    public function getHeaderAndSellWithUsUpdateData(object $request, $image): array
    {

        $defaultLang = getDefaultLanguage() ?? 'en';
        $defaultLangIndex = array_search($defaultLang, $request->lang);
        return [
            'title' => $request->title[$defaultLangIndex][$defaultLang] ?? '',
            'sub_title' => $request->sub_title[$defaultLangIndex][$defaultLang] ?? '',
            'image' => $this->getImageDataProcess(request: $request, image: $image, requestImageName: 'image'),
        ];
    }
    public function getBusinessProcessUpdateData($request): array
    {
        if (is_array($request)) {
            $title = $request['title'] ?? null;
            $sub_title = $request['sub_title'] ?? null;
        } else {
            $title = $request->title ?? null;
            $sub_title = $request->sub_title ?? null;
        }

        return [
            'title' => $title,
            'sub_title' => $sub_title,
        ];
    }

    public function getBusinessProcessStepUpdateData(object $request, $businessProcessStep, string $defaultLang = 'en'): array
    {
        $array = [];
        for ($index = 1; $index <= 3; $index++) {
            $image = (isset($businessProcessStep[$index - 1]) ? $businessProcessStep[$index - 1]?->image : null);

            // Extract default language string for title and description
            $title = $request['section_' . $index . '_title'];
            if (is_array($title) && isset($title[$defaultLang])) {
                $title = $title[$defaultLang];
            } elseif (is_object($title) && isset($title->$defaultLang)) {
                $title = $title->$defaultLang;
            }

            $description = $request['section_' . $index . '_description'];
            if (is_array($description) && isset($description[$defaultLang])) {
                $description = $description[$defaultLang];
            } elseif (is_object($description) && isset($description->$defaultLang)) {
                $description = $description->$defaultLang;
            }

            $array[] = [
                'title' => $title,
                'description' => $description,
                'image' => $this->getImageDataProcess(request: $request, image: $image, requestImageName: 'section_' . $index . '_image'),
            ];
        }
        return $array;
    }

    protected function getImageDataProcess($request, $image, $requestImageName): array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $imageData = is_string($image) ? $image : $image?->image_name;
        if ($imageData) {
            $imageName = $request->file($requestImageName) ? $this->update(dir: 'vendor-registration-setting/', oldImage: $imageData, format: 'webp', image: $request->file($requestImageName)) : $imageData;
            $storage = $request->file($requestImageName) ? $storage : ($image?->storage ?? $storage);
        } else {
            $imageName = $request->file($requestImageName) ? $this->upload(dir: 'vendor-registration-setting/', format: 'webp', image: $request->file($requestImageName)) : null;
        }
        return [
            'image_name' => $imageName,
            'storage' =>  $storage
        ];
    }

    public function getVendorRegistrationReasonData(object $request): array
    {
        $defaultLangIndex = array_search(config('app.locale'), $request->lang);
        return [
            'title' => $request['title'][$defaultLangIndex],
            'description' => $request['description'][$defaultLangIndex],
            'priority' => $request['priority'],
            'status' => $request->get('status', 0),
        ];
    }
    public function getDownloadVendorAppUpdateData(object $request, $image): array
    {
        return [
            'title' => $request['title'],
            'sub_title' => $request['sub_title'],
            'image' => $this->getImageDataProcess(request: $request, image: $image, requestImageName: 'image'),
            'download_google_app' => $request['download_google_app'],
            'download_google_app_status' => $request->get('download_google_app_status', 0),
            'download_apple_app' => $request['download_apple_app'],
            'download_apple_app_status' => $request->get('download_apple_app_status', 0),
        ];
    }
}
