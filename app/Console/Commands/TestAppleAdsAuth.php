<?php

namespace App\Console\Commands;

use App\Services\AppleAds\AppleAds;
use Illuminate\Console\Command;

class TestAppleAdsAuth extends Command
{
    protected $signature = 'appleads:test-auth';
    protected $description = 'Test Apple Ads API authentication';

    public function handle()
    {
        $this->info('Testing Apple Ads API Authentication...');
        $this->newLine();

        // Check configuration
        $this->info('Checking configuration:');
        $this->line('Client ID: ' . (config('services.apple_ads.client_id') ? '✓ Set' : '✗ Missing'));
        $this->line('Team ID: ' . (config('services.apple_ads.team_id') ? '✓ Set' : '✗ Missing'));
        $this->line('Key ID: ' . (config('services.apple_ads.key_id') ? '✓ Set' : '✗ Missing'));
        $this->line('Org ID: ' . (config('services.apple_ads.org_id') ? '✓ Set' : '✗ Missing'));

        $keyPath = config('services.apple_ads.private_key');
        $keyExists = file_exists($keyPath);
        $this->line('Private Key: ' . ($keyExists ? '✓ Found at ' . $keyPath : '✗ Not found at ' . $keyPath));

        if (!$keyExists) {
            $this->error('Private key file not found. Please check your APPLE_ADS_PRIVATE_KEY environment variable.');
            return 1;
        }

        $this->newLine();
        $baseUrl = config('services.apple_ads.base_url');
        $this->info('Base URL: ' . $baseUrl);
        $this->info('Attempting API call to: ' . $baseUrl . '/campaigns');
        $this->newLine();

        try {
            $appleAds = AppleAds::make();
            $campaigns = $appleAds->getCampaigns();

            $this->info('✓ Authentication successful!');
            $this->line('Found ' . count($campaigns['data'] ?? []) . ' campaigns');

            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Authentication failed');
            $this->error('Error: ' . $e->getMessage());

            $this->newLine();
            $this->warn('Troubleshooting tips:');
            $this->line('1. Verify your credentials in the .env file match your Apple Search Ads account');
            $this->line('2. Ensure the private key (.p8 file) is in the correct location: resources/appleads/');
            $this->line('3. Check that your Client ID, Team ID, and Key ID are from Apple Search Ads (not App Store Connect)');
            $this->line('4. Verify the Org ID matches your Apple Search Ads organization');
            $this->line('5. Make sure your API credentials have the correct permissions in Apple Search Ads');

            return 1;
        }
    }
}
