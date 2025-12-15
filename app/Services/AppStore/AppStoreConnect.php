<?php

namespace App\Services\AppStore;

use App\Services\AppStore\Helpers\SalesResponse;
use Closure;
use DateTimeInterface;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

class AppStoreConnect
{
    private string $baseUrl;

    public function __construct(
        public string $issuerId,
        public string $keyId,
        public string $privateKey,
    ) {
        $this->baseUrl = config('services.app_store_connect.base_url', 'https://api.appstoreconnect.apple.com');
    }


    public function call(string $url, ?array $params = null){
        $response = Http::withToken($this->generateJWT())->withHeaders([
            'Accept' => 'application/a-gzip, application/json',
            'Accept-Encoding' => 'gzip, deflate, br',
        ])->withOptions([
                'decode_content' => false]
        )->get($url, $params);
        if ($response->successful()) {
            return gzdecode($response->body());
        } else {
            return $response->body();
        }
    }
    public function getAppInfo($appId)
    {
        return $this->call("{$this->baseUrl}/apps/{$appId}");
    }

    public function salesReports(string $vendorId){
        $now = date('Y-m-d');
        $lastMonth = date('Y-m-d', strtotime('-30 days'));

        $tsv = $this->call("{$this->baseUrl}/v1/salesReports", [
            'filter[frequency]' => 'DAILY',
//            'filter[reportDate]' => "{$lastMonth},{$now}",
            'filter[reportType]' => 'SALES',
            'filter[reportSubType]' => 'SUMMARY',
            'filter[vendorNumber]' => $vendorId,
        ]);

        return SalesResponse::tsvToArray($tsv);
    }

    //--------------------------------------------
    //MARK: - Authentication
    //--------------------------------------------
    private function generateJWT() : string
    {
        $now = time();
        $exp = $now + 900; // Token valid for 15 minutes, tokens for more than 20 minutes are not valid

        $payload = [
            'iss' => $this->issuerId,
            'iat' => $now,
            'exp' => $exp,
            'aud' => 'appstoreconnect-v1',
        ];

        return JWT::encode($payload, $this->privateKey, 'ES256', $this->keyId);
    }
}
