<?php

namespace App\Http\Controllers;

use App\Helpers\SimpleEncryption;

class EncryptedControllerLoader extends Controller
{
    private static $password = 'simple-password-123'; // MUST match your encryption password
    
    /**
     * Load and execute an encrypted controller
     */
    public static function load($controllerName, $method = 'index', $params = [])
    {
        $path = app_path("Http/Controllers/{$controllerName}.php");
        
        if (!file_exists($path)) {
            die("Controller not found: {$controllerName}.php");
        }
        
        try {
            // Read encrypted file
            $content = file_get_contents($path);
            
            // Decrypt using SimpleEncryption helper
            $decrypted = self::decrypt($content);
            
            // Execute the decrypted code
            eval('?>' . $decrypted);
            
            // Create instance and call method
            $fullControllerClass = "App\\Http\\Controllers\\{$controllerName}";
            $controller = new $fullControllerClass();
            return call_user_func_array([$controller, $method], $params);
            
        } catch (\Exception $e) {
            die("Error loading encrypted controller: " . $e->getMessage());
        }
    }
    
    /**
     * Decrypt the data (using SimpleEncryption logic)
     */
    private static function decrypt($data)
    {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        $key = hash('sha256', self::$password, true);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}