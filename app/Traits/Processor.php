<?php

namespace App\Traits;

use App\Models\Setting;
use App\Models\PaymentRequest;
use App\Support\Payments\PaymentHookRegistry;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;

trait  Processor
{
    public function response_formatter($constant, $content = null, $errors = []): array
    {
        $constant = (array)$constant;
        $constant['content'] = $content;
        $constant['errors'] = $errors;
        return $constant;
    }

    public function error_processor($validator): array
    {
        $errors = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            $errors[] = ['error_code' => $index, 'message' => self::translate($error[0])];
        }
        return $errors;
    }

    public function translate($key)
    {
        return function_exists('translate') ? \translate($key) : $key;
    }

    public function payment_config($key, $settings_type): object|null
    {
        try {
            $config = DB::table('addon_settings')->where('key_name', $key)
                ->where('settings_type', $settings_type)->first();
        } catch (Exception $exception) {
            return new Setting();
        }

        return (isset($config)) ? $config : null;
    }

    public function file_uploader(string $dir, string $format, $image = null, $old_image = null)
    {
        if ($image == null) return $old_image ?? 'def.png';

        if (isset($old_image)) Storage::disk('public')->delete($dir . $old_image);

        $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
        Storage::disk('public')->put($dir . $imageName, file_get_contents($image));

        return $imageName;
    }

    public function payment_response($payment_info, $payment_flag): Application|JsonResponse|Redirector|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $getNewUser = (int)0;
        $additionalData = json_decode($payment_info->additional_data, true);

        if (isset($additionalData['new_customer_id']) && isset($additionalData['is_guest_in_order'])) {
            $getNewUser = (int)(($additionalData['new_customer_id'] != 0 && $additionalData['is_guest_in_order'] != 1) ? 1 : 0);
        }

        $token_string = 'payment_method=' . $payment_info->payment_method . '&&transaction_reference=' . $payment_info->transaction_id;
        if (in_array($payment_info->payment_platform, ['web', 'app']) && $payment_info['external_redirect_link'] != null) {
            return redirect($payment_info['external_redirect_link'] . '?flag=' . $payment_flag . '&&token=' . base64_encode($token_string) . '&&new_user=' . $getNewUser);
        }
        return redirect()->route('payment-' . $payment_flag, ['token' => base64_encode($token_string), 'new_user' => $getNewUser]);
    }

    protected function executePaymentHook(?string $hook, ?PaymentRequest $paymentRequest): bool
    {
        if (!$paymentRequest) {
            return false;
        }

        return PaymentHookRegistry::dispatch($hook, $paymentRequest);
    }
}
