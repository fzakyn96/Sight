<?php 
    namespace App\Service;

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

            return [
                'X-cons-id' => $this->consId,
                'X-timestamp' => $timestamp,
                'X-signature' => $signature,
                'user_key' => $this->userKey,
                'Content-Type' => 'application/json',
            ];
        }

        protected function responseData(array $response): array
        {
            if (isset($response['response']) && is_string($response['response'])) {
                $encryptedData = $response['response'];
                $decryptedData = $this->decryptData($encryptedData, $this->secretKey);
                $decompressedData = $this->decompressData($decryptedData);
                return json_decode($decompressedData, true);
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