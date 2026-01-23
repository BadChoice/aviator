<?php

namespace App\Console\Commands;

use App\Services\AppleAds\AppleAds;
use Firebase\JWT\JWT;
use Illuminate\Console\Command;

class DebugAppleAdsJWT extends Command
{
    protected $signature = 'appleads:debug-jwt';
    protected $description = 'Debug Apple Ads JWT token generation';

    public function handle()
    {
        $this->info('Apple Ads JWT Debug Information');
        $this->newLine();

        $clientId = config('services.apple_ads.client_id');
        $teamId = config('services.apple_ads.team_id');
        $keyId = config('services.apple_ads.key_id');
        $orgId = config('services.apple_ads.org_id');
        $privateKeyPath = config('services.apple_ads.private_key');

        $this->line('Client ID: ' . $clientId);
        $this->line('Team ID: ' . $teamId);
        $this->line('Key ID: ' . $keyId);
        $this->line('Org ID: ' . $orgId);
        $this->line('Private Key Path: ' . $privateKeyPath);
        $this->newLine();

        if (!file_exists($privateKeyPath)) {
            $this->error('Private key file not found!');
            return 1;
        }

        $privateKey = file_get_contents($privateKeyPath);
        $this->line('Private Key loaded: ' . strlen($privateKey) . ' bytes');
        $this->line('First line: ' . explode("\n", $privateKey)[0]);
        $this->newLine();

        $now = time();
        $exp = $now + 600;

        $payload = [
            'iss' => $teamId,
            'aud' => 'https://appleid.apple.com',
            'sub' => $clientId,
            'iat' => $now,
            'exp' => $exp,
        ];

        $this->info('JWT Payload:');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT));
        $this->newLine();

        try {
            $jwt = JWT::encode($payload, $privateKey, 'ES256', $keyId);
            $this->info('Generated JWT (first 50 chars): ' . substr($jwt, 0, 50) . '...');
            $this->newLine();

            // Decode to verify
            $parts = explode('.', $jwt);
            $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/')), true);
            $this->info('JWT Header:');
            $this->line(json_encode($header, JSON_PRETTY_PRINT));

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to generate JWT: ' . $e->getMessage());
            return 1;
        }
    }
}
