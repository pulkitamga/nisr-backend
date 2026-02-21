<?php

namespace App\Traits;

use Twilio\Rest\Client;

trait SmsGateway
{
    public static function send($receiver, $otp): string
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

    public static function twilio($receiver, $otp): string
    {
        $config = self::get_settings('twilio');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            $sid = $config['sid'];
            $token = $config['token'];
            try {
                $twilio = new Client($sid, $token);
                $twilio->messages->create($receiver, [
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
                curl_setopt($ch, CURLOPT_POSTFIELDS, "from=".$config['from']."&text=".$message."&to=".$receiver."&api_key=".$config['api_key']."&api_secret=".$config['api_secret']);

                $headers = [];
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
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://smsmisr.com/api/webapi/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query($payload),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                ],
            ]);

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
        $data = config_settings($name, 'sms_config');
        if (isset($data) && !is_null($data->live_values)) {
            return json_decode($data->live_values, true);
        }

        return null;
    }
}
