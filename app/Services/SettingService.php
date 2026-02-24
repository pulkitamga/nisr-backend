<?php

namespace App\Services;

class SettingService
{
    public function getVacationData(string $type): string
    {
        $url = '';
        foreach (config('addon_admin_routes') as $routeArray) {
            foreach ($routeArray as $route) {
                if ($route['name'] === $type) {
                    $url = $route['url'];
                    break 2;
                }
            }
        }
        return $url;
    }

    public function getSMSModuleValidationData(object $request): array
    {
        collect(['status'])->each(fn($item, $key) => $request[$item] = $request->has($item) ? (int)$request[$item] : 0);
        $validation = [
            'gateway' => 'required|in:twilio,nexmo,sms_com_eg',
            'mode' => 'required|in:live,test'
        ];
        $additional_data = [];
        if ($request['gateway'] == 'twilio') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'sid' => 'required',
                'messaging_service_sid' => 'required',
                'token' => 'required',
                'from' => 'required',
                'otp_template' => 'required'
            ];
        } elseif ($request['gateway'] == 'nexmo') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'api_key' => 'required',
                'api_secret' => 'required',
                'token' => 'required',
                'from' => 'required',
                'otp_template' => 'required'
            ];
        } elseif ($request['gateway'] == 'sms_com_eg') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'username' => 'required',
                'password' => 'required',
                'sender' => 'required',
                'language' => 'required|in:1,2',
                'otp_template' => 'required',
            ];
        }

        return $request->validate(array_merge($validation, $additional_data));
    }

}
