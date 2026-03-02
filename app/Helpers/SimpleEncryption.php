<?php

namespace App\Helpers;

class SimpleEncryption
{
    private static $cipher = 'AES-256-CBC';
    
    /**
     * Encrypt a file
     */
    public static function encryptFile($sourcePath, $destPath, $password)
    {
        if (!file_exists($sourcePath)) {
            throw new \Exception("Source file not found");
        }
        
        // Read the file
        $data = file_get_contents($sourcePath);
        
        // Generate encryption key from password
        $key = hash('sha256', $password, true);
        
        // Generate random initialization vector
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        // Encrypt the data
        $encrypted = openssl_encrypt($data, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);
        
        // Combine IV and encrypted data
        $result = base64_encode($iv . $encrypted);
        
        // Save encrypted file
        file_put_contents($destPath, $result);
        
        return true;
    }
    
    /**
     * Decrypt a file
     */
    public static function decryptFile($sourcePath, $destPath, $password)
    {
        if (!file_exists($sourcePath)) {
            throw new \Exception("Source file not found");
        }
        
        // Read encrypted file
        $data = file_get_contents($sourcePath);
        $data = base64_decode($data);
        
        // Get IV length
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        
        // Extract IV and encrypted data
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        // Generate key from password
        $key = hash('sha256', $password, true);
        
        // Decrypt the data
        $decrypted = openssl_decrypt($encrypted, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);
        
        // Save decrypted file
        file_put_contents($destPath, $decrypted);
        
        return true;
    }
}