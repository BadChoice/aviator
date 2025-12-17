<?php

namespace App\Services\AppStore;

use App\Services\AppStore\Helpers\SalesResponse;
use Carbon\CarbonInterface;
use Closure;
use DateTimeInterface;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

class AppStoreConnect
{
    private string $baseUrl;

    public static function make() : AppStoreConnect {
        return new AppStoreConnect(
            issuerId: config('services.app_store_connect.issuer_id'),
            keyId: config('services.app_store_connect.key_id'),
            privateKey: file_get_contents(config('services.app_store_connect.private_key')),
        );
    }

    public function __construct(
        public string $issuerId,
        public string $keyId,
        public string $privateKey,
    ) {
        $this->baseUrl = config('services.app_store_connect.base_url', 'https://api.appstoreconnect.apple.com');
    }


    public function call(string $url, ?array $params = null, $gziped = false){
        $response = Http::withToken($this->generateJWT())->withHeaders([
            'Accept' => 'application/a-gzip, application/json',
            'Accept-Encoding' => 'gzip, deflate, br',
        ])->withOptions([
                'decode_content' => false]
        )->get($url, $params);
        if ($response->successful()) {
            return $gziped ? gzdecode($response->body()) : $response->json();
        } else {
            return $response->body();
        }
    }

    public function getAppInfo($appId)
    {
        return $this->call("{$this->baseUrl}/apps/{$appId}");
    }

    public function reviews(string $appId, ?string $locale = null)
    {
        return $this->call("{$this->baseUrl}/v1/apps/{$appId}/customerReviews", [
            'filter[territory]' => $locale, // optional, e.g. 'ES', 'US'
            'sort' => '-createdDate',
            'limit' => 50,
        ]);
    }

    public function salesReports(string $vendorId, ?CarbonInterface $date = null){

        $tsv = $this->call("{$this->baseUrl}/v1/salesReports", [
            'filter[frequency]' => 'DAILY',
            'filter[reportDate]' => $date?->format('Y-m-d'),
            'filter[reportType]' => 'SALES',
            'filter[reportSubType]' => 'SUMMARY',
            'filter[vendorNumber]' => $vendorId,
        ], gziped:true);

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
