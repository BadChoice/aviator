<div>
    <table class="w-full">
        <tr>
            <thead>
                <th>Date</th>
                <th>Title</th>
                <th>Sku</th>
                <th>Version</th>
                <th>Device</th>
                <th>Product type identifier</th>
                <th>Units</th>
                <th>Proceeds</th>
                <th>Customer Price</th>
            </thead>
        </tr>

        <tbody>
        @foreach(collect($sales)->sortBy('SKU') as $sale)
            <tr>
                <td>{{ $sale['Begin Date'] }}</td>
                <td>{{ $sale['Title'] }}</td>
                <td>{{ $sale['SKU'] }}</td>
                <td>{{ $sale['Version'] }}</td>
                <td>{{ $sale['Device'] }}</td>
                <td>
                    {{ \App\Services\AppStore\Helpers\AppStoreProductType::tryFrom($sale['Product Type Identifier'])?->description() ?? $sale['Product Type Identifier']}}
                </td>
                <td>{{ $sale['Units'] }}</td>
                <td>
                    {{ $sale['Developer Proceeds'] }}
                    {{ $sale['Currency of Proceeds'] }}
                </td>
                <td>
                    {{ $sale['Customer Price'] }}
                    {{ $sale['Customer Currency'] }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-8">
        <table>
            @foreach($summary as $key => $value)
                <tr>
                    <td>{{ $key }} </td><td class="px-4"> {{ $value }} USD </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
