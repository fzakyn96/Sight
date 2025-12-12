<?php 
    namespace App\Service\Traits;

    use Exception;

    trait BPJSSecurityTrait 
    {
        protected function generateTimestamp()
        {
            date_default_timezone_set('UTC');
            $timestamp = (string) time();
            return $timestamp;
        }

        protected function generateSignature($consId, $secretKey, $timestamp)
        {
            try {
                $data = $consId . "&" . $timestamp;
                $signature = hash_hmac('sha256', $data, $secretKey, true);
                $encodeSignature = base64_encode($signature);
                return $encodeSignature;
            } catch (Exception $e) {
                throw new Exception("Error generating signature: " . $e->getMessage());
            }
        }

        protected function decryptData($key, $encryptedData): string|false
        {
            try {
                $method = 'AES-256-CBC';
                $keyHashed = hex2bin(hash('sha256', $key));
                $iv = substr($keyHashed, 0, 16);
                $decodedData = base64_decode($encryptedData);

                if ($decodedData === false) {
                    throw new Exception("Base64 Decode gagal.");
                }
                
                $decryptedData = openssl_decrypt($decodedData, $method, $keyHashed, OPENSSL_RAW_DATA, $iv);
                
                if ($decryptedData === false) {
                    return false;
                }

                return $decryptedData;
            } catch (Exception $e) {
                throw new Exception("Error decrypting data: " . $e->getMessage());
            }
        }

        protected function decompressData($decryptedData)
        {
            try {
                return \LZCompressor\LZString::decompressFromEncodedURIComponent($decryptedData);
            } catch (Exception $e) {
                throw new Exception("Error decompressing data: " . $e->getMessage());
            }
        }
    }
?>