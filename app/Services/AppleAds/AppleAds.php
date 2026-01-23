<?php

namespace App\Services\AppleAds;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

class AppleAds
{
    private string $baseUrl;

    public static function make(): AppleAds
    {
        return new AppleAds(
            clientId: config('services.apple_ads.client_id'),
            teamId: config('services.apple_ads.team_id'),
            keyId: config('services.apple_ads.key_id'),
            privateKey: file_get_contents(config('services.apple_ads.private_key')),
            orgId: config('services.apple_ads.org_id'),
        );
    }

    public function __construct(
        public string $clientId,
        public string $teamId,
        public string $keyId,
        public string $privateKey,
        public string $orgId,
    ) {
        $this->baseUrl = config('services.apple_ads.base_url', 'https://api.searchads.apple.com/api/v4');
    }

    public function call(string $method, string $url, ?array $params = null, ?array $data = null)
    {
        $request = Http::withToken($this->generateJWT())
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->generateJWT(),
                //'X-AP-Context' => 'orgId=' . $this->orgId,
                'Content-Type' => 'application/json',
            ]);

        $response = match (strtoupper($method)) {
            'GET' => $request->get($url, $params),
            'POST' => $request->post($url, $data),
            'PUT' => $request->put($url, $data),
            'DELETE' => $request->delete($url),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception("Apple Ads API Error: " . $response->body());
        }
    }

    public function getCampaigns(?array $params = null)
    {
        return $this->call('GET', "{$this->baseUrl}/campaigns", $params);
    }

    public function getCampaign(string $id)
    {
        return $this->call('GET', "{$this->baseUrl}/campaigns/{$id}");
    }

    public function getAdGroups(string $campaignId, ?array $params = null)
    {
        return $this->call('GET', "{$this->baseUrl}/campaigns/{$campaignId}/adgroups", $params);
    }

    public function getAds(string $campaignId, string $adGroupId, ?array $params = null)
    {
        return $this->call('GET', "{$this->baseUrl}/campaigns/{$campaignId}/adgroups/{$adGroupId}/ads", $params);
    }

    public function getCampaignMetrics(string $campaignId, array $params)
    {
        return $this->call('POST', "{$this->baseUrl}/reports/campaigns/{$campaignId}", data: $params);
    }

    public function getKeywords(string $campaignId, string $adGroupId, ?array $params = null)
    {
        return $this->call('GET', "{$this->baseUrl}/campaigns/{$campaignId}/adgroups/{$adGroupId}/targetingkeywords", $params);
    }

    public function updateCampaign(string $id, array $data)
    {
        return $this->call('PUT', "{$this->baseUrl}/campaigns/{$id}", data: $data);
    }

    public function updateKeywordBid(string $campaignId, string $adGroupId, string $keywordId, array $data)
    {
        return $this->call('PUT', "{$this->baseUrl}/campaigns/{$campaignId}/adgroups/{$adGroupId}/targetingkeywords/{$keywordId}", data: $data);
    }

    private function generateJWT(): string
    {
        $now = time();
        $exp = $now + 900; // Token valid for 15 minutes

        $payload = [
            'sub' => $this->clientId,
            'aud' => 'https://appleid.apple.com',
            'iat' => $now,
            'exp' => $exp,
            'iss' => $this->teamId,
        ];

        return JWT::encode($payload, $this->privateKey, 'ES256', $this->keyId);
    }
}
