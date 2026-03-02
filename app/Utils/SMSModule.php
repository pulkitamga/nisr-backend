<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;
use Modules\Gateways\Traits\SmsGateway;

class SMSModule
{
    public static function sendCentralizedSMS($phone, $token)
    {
        $paymentPublishedStatus = config('get_payment_publish_status') ?? 0;
        return $paymentPublishedStatus == 1 ? SmsGateway::send($phone, $token) : SMSModule::send($phone, $token);
    }

    public static function send($receiver, $otp)
    {
        $config = self::get_settings('twilio');
        if (isset($config) && $config['status'] == 1) {
            return self::twilio($receiver, $otp);
        }

        $config = self::get_settings('nexmo');
        if (isset($config) && $config['status'] == 1) {
            return self::nexmo($receiver, $otp);
        }

        $config = self::get_settings('sms_com_eg');
        if (isset($config) && $config['status'] == 1) {
            return self::sms_com_eg($receiver, $otp);
        }

        return 'not_found';
    }

    public static function sendTextMessage($receiver, string $message): string
    {
        $config = self::get_settings('twilio');
        if (isset($config) && $config['status'] == 1) {
            return self::twilioMessage($receiver, $message);
        }

        $config = self::get_settings('nexmo');
        if (isset($config) && $config['status'] == 1) {
            return self::nexmoMessage($receiver, $message);
        }

        $config = self::get_settings('sms_com_eg');
        if (isset($config) && $config['status'] == 1) {
            return self::smsComEgMessage($receiver, $message);
        }

        return 'not_found';
    }

    public static function twilio($receiver, $otp)
    {
        $config = self::get_settings('twilio');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            $sid = $config['sid'];
            $token = $config['token'];
            try {
                $twilio = new Client($sid, $token);
                $twilio->messages
                    ->create($receiver, // to
                        array(
                            "messagingServiceSid" => $config['messaging_service_sid'],
                            "body" => $message
                        )
                    );
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }
        }
        return $response;
    }

    private static function twilioMessage($receiver, string $message): string
    {
        $config = self::get_settings('twilio');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $sid = $config['sid'];
            $token = $config['token'];
            try {
                $twilio = new Client($sid, $token);
                $twilio->messages
                    ->create($receiver, [
                        'messagingServiceSid' => $config['messaging_service_sid'],
                        'body' => $message,
                    ]);
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function nexmo($receiver, $otp): string
    {
        $config = self::get_settings('nexmo');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            try {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://rest.nexmo.com/sms/json');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "from=" . $config['from'] . "&text=" . $message . "&to=" . $receiver . "&api_key=" . $config['api_key'] . "&api_secret=" . $config['api_secret']);

                $headers = array();
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                curl_close($ch);
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }
        }
        return $response;
    }

    private static function nexmoMessage($receiver, string $message): string
    {
        $config = self::get_settings('nexmo');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            try {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://rest.nexmo.com/sms/json');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "from=" . $config['from'] . "&text=" . urlencode($message) . "&to=" . $receiver . "&api_key=" . $config['api_key'] . "&api_secret=" . $config['api_secret']);

                $headers = array();
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                curl_exec($ch);
                if (curl_errno($ch)) {
                    $response = 'error';
                } else {
                    $response = 'success';
                }
                curl_close($ch);
            } catch (\Exception $exception) {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function sms_com_eg($receiver, $otp): string
    {
        $config = self::get_settings('sms_com_eg');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $receiver = str_replace("+", "", $receiver);
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            $payload = [
                'username' => $config['username'],
                'password' => $config['password'],
                'sender' => $config['sender'],
                'mobile' => $receiver,
                'language' => $config['language'] ?? '2',
                'message' => $message,
            ];

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://smsmisr.com/api/webapi/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query($payload),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded',
                ),
            ));

            curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if (!$err) {
                $response = 'success';
            } else {
                $response = 'error';
            }
        }
        return $response;
    }

    private static function smsComEgMessage($receiver, string $message): string
    {
        $config = self::get_settings('sms_com_eg');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $receiver = str_replace("+", "", $receiver);
            $payload = [
                'username' => $config['username'],
                'password' => $config['password'],
                'sender' => $config['sender'],
                'mobile' => $receiver,
                'language' => $config['language'] ?? '2',
                'message' => $message,
            ];

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://smsmisr.com/api/webapi/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query($payload),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded',
                ),
            ));

            curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if (!$err) {
                $response = 'success';
            } else {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function get_settings($name)
    {
        try {
            $config = DB::table('addon_settings')->where('key_name', $name)
                ->where('settings_type', 'sms_config')->first();
        } catch (\Exception $exception) {
            return null;
        }

        return (isset($config)) ? json_decode($config->live_values, true) : null;
    }
}
