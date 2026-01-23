<div>
    <div class="mb-6 flex justify-between items-start">
        <div>
            <flux:heading size="xl">{{ $campaign->name }}</flux:heading>
            <div class="mt-2 flex gap-3 items-center">
                <flux:badge :variant="$campaign->status === 'ENABLED' ? 'success' : 'warning'">
                    {{ $campaign->status }}
                </flux:badge>
                @if($campaign->last_synced_at)
                    <span class="text-sm text-zinc-500">
                        Last synced: {{ $campaign->last_synced_at->diffForHumans() }}
                    </span>
                @endif
            </div>
        </div>
        <flux:button wire:click="syncCampaign" icon="arrow-path">Sync Campaign</flux:button>
    </div>

    @if (session()->has('message'))
        <x-banner variant="success" class="mb-4">
            {{ session('message') }}
        </x-banner>
    @endif

    @if (session()->has('error'))
        <x-banner variant="danger" class="mb-4">
            {{ session('error') }}
        </x-banner>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <x-card>
            <flux:heading size="lg">Budget</flux:heading>
            <div class="mt-4 space-y-2">
                @if($campaign->daily_budget_amount)
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">Daily Budget</span>
                        <span class="font-semibold">{{ $campaign->currency }} {{ number_format($campaign->daily_budget_amount, 2) }}</span>
                    </div>
                @endif
                @if($campaign->budget_amount)
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">Total Budget</span>
                        <span class="font-semibold">{{ $campaign->currency }} {{ number_format($campaign->budget_amount, 2) }}</span>
                    </div>
                @endif
                @if($metrics && isset($metrics['localSpend']))
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">Spend ({{ $dateRange }}d)</span>
                        <span class="font-semibold text-red-600">{{ $campaign->currency }} {{ number_format($metrics['localSpend']['amount'], 2) }}</span>
                    </div>
                @endif
            </div>
        </x-card>

        <x-card>
            <flux:heading size="lg">Performance</flux:heading>
            <div class="mt-4 space-y-2">
                @if($metrics)
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">Impressions</span>
                        <span class="font-semibold">{{ number_format($metrics['impressions'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">Taps</span>
                        <span class="font-semibold">{{ number_format($metrics['taps'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">Installs</span>
                        <span class="font-semibold">{{ number_format($metrics['installs'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">Conversion Rate</span>
                        <span class="font-semibold">{{ number_format(($metrics['conversionRate'] ?? 0) * 100, 2) }}%</span>
                    </div>
                @else
                    <p class="text-zinc-500 text-sm">No metrics available</p>
                @endif
            </div>
        </x-card>

        <x-card>
            <flux:heading size="lg">Cost Metrics</flux:heading>
            <div class="mt-4 space-y-2">
                @if($metrics)
                    @if(isset($metrics['avgCPA']))
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">Avg CPA</span>
                            <span class="font-semibold">{{ $campaign->currency }} {{ number_format($metrics['avgCPA']['amount'], 2) }}</span>
                        </div>
                    @endif
                    @if(isset($metrics['avgCPT']))
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">Avg CPT</span>
                            <span class="font-semibold">{{ $campaign->currency }} {{ number_format($metrics['avgCPT']['amount'], 2) }}</span>
                        </div>
                    @endif
                    @if(isset($metrics['ttr']))
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">Tap-Through Rate</span>
                            <span class="font-semibold">{{ number_format($metrics['ttr'] * 100, 2) }}%</span>
                        </div>
                    @endif
                @else
                    <p class="text-zinc-500 text-sm">No metrics available</p>
                @endif
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <x-card>
                <flux:heading size="lg" class="mb-4">Ad Groups & Ads</flux:heading>
                @forelse($adGroups as $adGroup)
                    <div class="mb-4 p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <flux:heading size="md">{{ $adGroup['name'] }}</flux:heading>
                            <flux:badge size="sm" :variant="$adGroup['status'] === 'ENABLED' ? 'success' : 'warning'">
                                {{ $adGroup['status'] }}
                            </flux:badge>
                        </div>
                        @if(!empty($adGroup['ads']))
                            <div class="mt-3 space-y-2">
                                @foreach($adGroup['ads'] as $ad)
                                    <div class="pl-4 py-2 border-l-2 border-zinc-300 dark:border-zinc-600">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm">{{ $ad['name'] }}</span>
                                            <flux:badge size="xs" :variant="$ad['status'] === 'ENABLED' ? 'success' : 'warning'">
                                                {{ $ad['status'] }}
                                            </flux:badge>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-zinc-500 mt-2">No ads in this ad group</p>
                        @endif
                    </div>
                @empty
                    <p class="text-zinc-500">No ad groups found</p>
                @endforelse
            </x-card>
        </div>

        <div>
            <x-card>
                <flux:heading size="lg" class="mb-4">Recommendations</flux:heading>
                <div class="mb-4">
                    <flux:button
                        wire:click="runRecommendations"
                        icon="light-bulb"
                        :disabled="$loadingRecommendations"
                    >
                        {{ $loadingRecommendations ? 'Analyzing...' : 'Run Recommendations' }}
                    </flux:button>
                </div>

                @if(count($recommendations) > 0)
                    <div class="space-y-3">
                        @foreach($recommendations as $recommendation)
                            <div class="p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <flux:badge
                                                :variant="$recommendation['priority'] === 'high' ? 'danger' : ($recommendation['priority'] === 'medium' ? 'warning' : 'default')"
                                                size="sm"
                                            >
                                                {{ ucfirst($recommendation['priority']) }}
                                            </flux:badge>
                                            <flux:heading size="sm">{{ $recommendation['title'] }}</flux:heading>
                                        </div>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $recommendation['description'] }}</p>
                                        @if(isset($recommendation['expected_impact']))
                                            <p class="text-xs text-zinc-500 mt-1">
                                                <strong>Expected impact:</strong> {{ $recommendation['expected_impact'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <flux:button
                                        wire:click="applyRecommendation('{{ $recommendation['id'] }}')"
                                        size="sm"
                                        variant="primary"
                                    >
                                        Apply
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-zinc-500 text-sm">No recommendations yet. Click "Run Recommendations" to analyze this campaign.</p>
                @endif
            </x-card>

            @if(count($keywords) > 0)
                <x-card class="mt-6">
                    <flux:heading size="lg" class="mb-4">Keywords</flux:heading>
                    <div class="space-y-2">
                        @foreach($keywords as $keyword)
                            <div class="flex justify-between items-center p-2 border border-zinc-200 dark:border-zinc-700 rounded">
                                <span class="text-sm">{{ $keyword['text'] }}</span>
                                <div class="flex gap-2 items-center">
                                    <flux:badge size="xs" :variant="$keyword['status'] === 'ACTIVE' ? 'success' : 'default'">
                                        {{ $keyword['status'] }}
                                    </flux:badge>
                                    @if(isset($keyword['bidAmount']))
                                        <span class="text-xs text-zinc-500">
                                            Bid: {{ $campaign->currency }} {{ number_format($keyword['bidAmount']['amount'], 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>
    </div>
</div>
