<?php

namespace App\Livewire\Sales;

use App\Services\AppStore\AppStoreConnect;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $connect = new AppStoreConnect(
            issuerId: config('services.app_store_connect.issuer_id'),
            keyId: config('services.app_store_connect.key_id'),
            privateKey: file_get_contents(config('services.app_store_connect.private_key')),
        );

        $sales = $connect->salesReports(
            vendorId: config('services.app_store_connect.vendor_id'),
//            date: now()->subDays(2)
        );

        $summary = collect($sales)->groupBy('SKU')->map(function($sales){
            return $sales->sum('Developer Proceeds');
        });

        return view('livewire.sales.index', [
            'sales' => $sales,
            'summary' => $summary
        ]);
    }
}
