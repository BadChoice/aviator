<div>
    @php
        $palette = [
            'bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-fuchsia-500', 'bg-cyan-500', 'bg-rose-500',
        ];
    @endphp

    @isset($dailyStacked)
        <div class="mb-6 rounded-xl border border-neutral-200 dark:border-neutral-700 p-4" data-testid="daily-sales-stacked">
            <div class="mb-3 flex items-center justify-between">
                <div class="text-sm font-medium">Daily Sales (USD, last {{ $daysWindow }} days)</div>
                <div class="flex items-center gap-3">
                    @foreach($topApps as $idx => $app)
                        <div class="flex items-center gap-1 text-xs text-neutral-700 dark:text-neutral-300">
                            <span class="inline-block h-2 w-2 rounded-sm {{ $palette[$idx % count($palette)] }}"></span>
                            <span class="truncate max-w-40" title="{{ $app }}">{{ $app }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-end gap-1 overflow-x-auto py-2 h-36">
                @foreach($dailyStacked as $day)
                    @php
                        $barHeight = $maxTotal > 0 ? max(2, intval(($day['total'] / $maxTotal) * 100)) : 2;
                    @endphp
                    <div class="flex h-full flex-col items-center justify-end min-w-4">
                        <div class="group relative w-2 rounded-sm bg-neutral-200/50 dark:bg-neutral-700/50 flex flex-col justify-end" style="height: {{ $barHeight }}%" tabindex="0" aria-label="{{ $day['date'] }} • ${{ number_format($day['total'], 2) }}" title="{{ $day['date'] }} • ${{ number_format($day['total'], 2) }}">
                            @php $runningTotal = max(1, $day['total']); @endphp
                            @foreach($day['segments'] as $segIdx => $seg)
                                @php
                                    $segHeight = $runningTotal > 0 ? intval(($seg['value'] / $runningTotal) * 100) : 0;
                                @endphp
                                <div class="{{ $palette[$segIdx % count($palette)] }} w-full" style="height: {{ $segHeight }}%">
                                    <span class="sr-only">{{ $seg['app'] }}: ${{ number_format($seg['value'], 2) }}</span>
                                </div>
                            @endforeach
                            <div class="pointer-events-none absolute -top-2 left-1/2 -translate-x-1/2 -translate-y-full whitespace-nowrap rounded bg-neutral-900 px-1.5 py-0.5 text-[10px] font-medium text-white opacity-0 transition-opacity group-hover:opacity-100 group-focus:opacity-100">
                                {{ $day['date'] }} • ${{ number_format($day['total'], 2) }}
                            </div>
                        </div>
                        <span class="mt-1 text-[10px] text-neutral-500">{{ \Illuminate\Support\Carbon::parse($day['date'])->format('m/d') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endisset
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
