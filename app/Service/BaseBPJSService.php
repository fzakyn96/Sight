<?php 
    namespace App\Service;

use App\Service\Traits\BPJSSecurityTrait;
use Exception;
use Illuminate\Support\Facades\Http;

    abstract class BaseBPJSService 
    {
        use BPJSSecurityTrait;

        protected $baseUrl;
        protected $serviceName;
        protected $consId;
        protected $secretKey;
        protected $userKey;

        protected $lastTimestamp;

        public function __construct(array $config)
        {
            $this->baseUrl = $config['base_url'] ?? '';
            $this->serviceName = $config['service_name'] ?? '';
            $this->consId = $config['cons_id'] ?? '';
            $this->secretKey = $config['secret_key'] ?? '';
            $this->userKey = $config['user_key'] ?? '';
        }

        protected function createHeaders()
        {
            $timestamp = $this->generateTimestamp();
            $signature = $this->generateSignature($this->consId, $this->secretKey, $timestamp);

            $this->lastTimestamp = $timestamp;

            return [
                'X-cons-id' => $this->consId,
                'X-timestamp' => $timestamp,
                'X-signature' => $signature,
                'user_key' => $this->userKey,
                'Content-Type' => 'application/json',
            ];
        }

        public function responseData(array $response): array
        {
            if (isset($response['response']) && is_string($response['response'])) {
                try {
                    $encryptedData = $response['response'];
                    $key = $this->consId . $this->secretKey . $this->lastTimestamp;
                    
                    $decryptedData = $this->decryptData($key, $encryptedData);
                    
                    if ($decryptedData === false) {
                        throw new \Exception("Dekripsi data BPJS gagal. Pastikan Secret Key dan Waktu Server (UTC) sudah benar.");
                    }
                    
                    $decompressedData = $this->decompressData($decryptedData);
                    if (empty($decompressedData)) {
                        throw new \Exception("Dekompresi data BPJS gagal.");
                    }

                    $result = json_decode($decompressedData, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception("JSON Decode gagal: " . json_last_error_msg());
                    }

                    return $result;
                    
                } catch (\Throwable $e) {
                    throw new \Exception("Gagal memproses respons terenkripsi: " . $e->getMessage());
                }
            }
            
            return $response; 
        }

        protected function getRequest(string $endpoint, array $query=[]): array
        {
            try {
                $url = $this->baseUrl . "/" . $this->serviceName . "/" . $endpoint;
                $response = Http::withHeaders($this->createHeaders())->get($url, $query);

                if ($response->failed()) {
                    throw new \Exception("HTTP request failed with status: " . $response->status());
                }

                return $response->json();
            } catch (\Throwable $e) {
                throw new Exception("Gagal terhubung ke BPJS: " . $e->getMessage());
            }
        }

        protected function postRequest(string $endpoint, array $query=[]): array
        {
            try {
                $url = $this->baseUrl . "/" . $this->serviceName . "/" . $endpoint;
                $response = Http::withHeaders($this->createHeaders())->post($url, $query);

                if ($response->failed()) {
                    throw new \Exception("HTTP request failed with status: " . $response->status());
                }

                return $response->json();
            } catch (\Throwable $e) {
                throw new Exception("Gagal terhubung ke BPJS: " . $e->getMessage());
            }
        }

        protected function putRequest(string $endpoint, array $query=[]): array
        {
            try {
                $url = $this->baseUrl . "/" . $this->serviceName . "/" . $endpoint;
                $response = Http::withHeaders($this->createHeaders())->put($url, $query);

                if ($response->failed()) {
                    throw new \Exception("HTTP request failed with status: " . $response->status());
                }

                return $response->json();
            } catch (\Throwable $e) {
                throw new Exception("Gagal terhubung ke BPJS: " . $e->getMessage());
            }
        }

        protected function deleteRequest(string $endpoint, array $query=[]): array
        {
            try {
                $url = $this->baseUrl . "/" . $this->serviceName . "/" . $endpoint;
                $response = Http::withHeaders($this->createHeaders())->delete($url, $query);

                if ($response->failed()) {
                    throw new \Exception("HTTP request failed with status: " . $response->status());
                }

                return $response->json();
            } catch (\Throwable $e) {
                throw new Exception("Gagal terhubung ke BPJS: " . $e->getMessage());
            }
        }
    }
?>