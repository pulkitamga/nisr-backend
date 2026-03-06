<?php

namespace App\Traits;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use RuntimeException;

trait SettingsTrait
{
    use StorageTrait;
    public function setEnvironmentValue($envKey, $envValue): mixed
    {
        $envFile = app()->environmentFilePath();
        if (!is_file($envFile) || !is_writable($envFile)) {
            throw new RuntimeException('Environment file is missing or not writable.');
        }

        $currentContent = file_get_contents($envFile);
        if ($currentContent === false) {
            throw new RuntimeException('Unable to read environment file.');
        }

        $formattedValue = $this->formatEnvironmentValue($envValue);
        $line = "{$envKey}={$formattedValue}";
        $pattern = '/^' . preg_quote($envKey, '/') . '=.*/m';

        if (preg_match($pattern, $currentContent)) {
            $updatedContent = preg_replace($pattern, $line, $currentContent, 1);
        } else {
            $updatedContent = rtrim($currentContent) . PHP_EOL . $line . PHP_EOL;
        }

        if ($updatedContent === null || file_put_contents($envFile, $updatedContent, LOCK_EX) === false) {
            throw new RuntimeException('Unable to write environment file.');
        }

        return $envValue;
    }

    private function formatEnvironmentValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        $stringValue = (string)$value;
        if ($stringValue === '') {
            return '""';
        }

        if (preg_match('/^[A-Za-z0-9_\-.:\/]+$/', $stringValue)) {
            return $stringValue;
        }

        return '"' . str_replace('"', '\"', $stringValue) . '"';
    }

    public function getSettings($object, $type)
    {
        $config = null;
        foreach ($object as $setting) {
            if ($setting['type'] == $type) {
                $config = $this->storageDataProcessing($type,$setting);
            }
        }
        return $config;
    }
    private function storageDataProcessing($name,$value)
    {
        $arrayOfCompaniesValue = ['company_web_logo','company_mobile_logo','company_footer_logo','company_fav_icon','loader_gif'];
        if(in_array($name,$arrayOfCompaniesValue)){
            $imageData = json_decode($value->value,true) ?? ['image_name'=> $value['value'],'storage' => 'public'];
            $value['value'] = $this->storageLink('company',$imageData['image_name'],$imageData['storage']);
        }
        return $value;
    }
}
