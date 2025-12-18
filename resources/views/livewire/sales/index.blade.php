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

            <div class="relative h-56 w-full" wire:ignore>
                <canvas id="dailySalesChart" class="!h-full !w-full"></canvas>
            </div>

            @php
                // Prepare labels and datasets for Chart.js without altering the component
                $labels = collect($dailyStacked)->pluck('date')->map(fn($d) => \Illuminate\Support\Carbon::parse($d)->format('m/d'))->values();
                $datasets = collect($topApps)->values()->map(function ($app, $idx) use ($dailyStacked) {
                    $data = collect($dailyStacked)->map(function ($day) use ($app) {
                        $seg = collect($day['segments'])->firstWhere('app', $app);
                        return $seg['value'] ?? 0;
                    })->values();
                    return [
                        'label' => $app,
                        'data' => $data,
                        'backgroundColor' => $idx,
                    ];
                })->values();
            @endphp

            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7"></script>
            <script>
                // Blade-provided data for the current render
                const __dailySalesLabels = @json($labels);
                const __dailySalesDatasetsRaw = @json($datasets);

                // Tailwind-ish palette approximations to match legend
                const __dailySalesPalette = [
                    'rgb(59 130 246)',   // blue-500
                    'rgb(16 185 129)',   // emerald-500
                    'rgb(245 158 11)',   // amber-500
                    'rgb(217 70 239)',   // fuchsia-500
                    'rgb(6 182 212)',    // cyan-500
                    'rgb(244 63 94)',    // rose-500
                ];

                // Expose an idempotent initializer so Livewire navigations can call it
                window.initDailySalesChart = function(labels = __dailySalesLabels, rawDatasets = __dailySalesDatasetsRaw) {
                    const canvas = document.getElementById('dailySalesChart');
                    if (!canvas || !window.Chart) { return; }

                    // Destroy any existing instance
                    if (window.__dailySalesChart) {
                        try { window.__dailySalesChart.destroy(); } catch (e) {}
                        window.__dailySalesChart = null;
                    }

                    const datasets = rawDatasets.map(d => ({
                        label: d.label,
                        data: d.data,
                        backgroundColor: __dailySalesPalette[d.backgroundColor % __dailySalesPalette.length],
                        borderWidth: 0,
                        barPercentage: 0.9,
                        categoryPercentage: 0.8,
                        borderSkipped: false,
                    }));

                    const chart = new Chart(canvas, {
                        type: 'bar',
                        data: { labels, datasets },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => {
                                            const v = context.raw ?? 0;
                                            return `${context.dataset.label}: ${Number(v).toFixed(2)} €`;
                                        },
                                        footer: (items) => {
                                            const sum = items.reduce((a, i) => a + (i.raw ?? 0), 0);
                                            return `Total: ${sum.toFixed(2)} €`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { stacked: true, grid: { display: false } },
                                y: {
                                    stacked: true,
                                    ticks: {
                                        callback: (v) => `${Number(v).toLocaleString()} €`
                                    }
                                }
                            }
                        }
                    });

                    window.__dailySalesChart = chart;
                }

                // Initialize now (first load) and after Livewire navigations
                document.addEventListener('DOMContentLoaded', () => {
                    window.initDailySalesChart();
                });

                document.addEventListener('livewire:navigated', () => {
                    window.initDailySalesChart();
                });
            </script>
        </div>
    @endisset

    <div class="mt-8">
        <table>
            @foreach($summary as $key => $value)
                <tr>
                    <td>{{ $key }} </td><td class="px-4 text-right"> {{ $value }} EUR </td>
                </tr>
            @endforeach
                <tr class="font-bold">
                    <td>Total </td><td class="px-4 text-right"> {{ $summary->sum() }} EUR </td>
                </tr>
        </table>
    </div>

    <flux:separator class="my-4" />

    <flux:button wire:click="toggleAll">{{ $this->showAll ? "Show less" : "Show more" }}</flux:button>

    <table class="w-full mt-4 text-sm">
        <tr>
            <thead>
                <th class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 text-left">Date</th>
                <th class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 text-left">Title</th>
                <th class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 text-left">Sku</th>
                <th class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 text-left">Version</th>
                <th class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 text-left">Device</th>
                <th class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 text-left">Product type identifier</th>
                <th class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 text-right">Units</th>
                <th class="px-2 py-1 bg-neutral-100 dark:bg-neutral-700 text-right">Proceeds</th>
            </thead>
        </tr>

        <tbody>
        @foreach(collect($sales) as $sale)
            <tr class="text-sm border-b border-b-gray-50 dark:border-b-gray-800">
                <td class="px-2 py-1">{{ $sale['Begin Date'] }}</td>
                <td class="px-2 py-1">{{ $sale['Title'] }}</td>
                <td class="px-2 py-1">{{ $sale['SKU'] }}</td>
                <td class="px-2 py-1">{{ $sale['Version'] }}</td>
                <td class="px-2 py-1">{{ $sale['Device'] }}</td>
                <td>
                    {{ \App\Services\AppStore\Helpers\AppStoreProductType::tryFrom($sale['Product Type Identifier'])?->description() ?? $sale['Product Type Identifier']}}
                </td>
                <td class="px-2 py-1 text-right">{{ $sale['Units'] }}</td>
                <td class="px-2 py-1 text-right font-bold">
                    {{ $sale['Developer Proceeds'] }} EUR
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
