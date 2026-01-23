<div>
    <flux:heading size="xl" class="mb-6">Apple Ads Campaigns</flux:heading>

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

    <div class="mb-6 flex gap-4 items-end">
        <flux:select wire:model.live="statusFilter" label="Status" class="w-48">
            <option value="all">All Statuses</option>
            <option value="ENABLED">Enabled</option>
            <option value="PAUSED">Paused</option>
        </flux:select>

        @if($applications->count() > 1)
            <flux:select wire:model.live="applicationFilter" label="Application" class="w-64">
                <option value="">All Applications</option>
                @foreach($applications as $app)
                    <option value="{{ $app->id }}">{{ $app->name }}</option>
                @endforeach
            </flux:select>
        @endif

        <flux:button wire:click="syncAll" icon="arrow-path">Sync All</flux:button>
    </div>

    <div class="grid gap-4">
        @forelse($campaigns as $campaign)
            <x-card>
                <a href="{{ route('apple-ads.campaigns.show', $campaign->id) }}" wire:navigate class="block">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <flux:heading size="lg">{{ $campaign->name }}</flux:heading>
                            <div class="mt-2 flex gap-4 text-sm text-zinc-600 dark:text-zinc-400">
                                <span>
                                    <flux:badge :variant="$campaign->status === 'ENABLED' ? 'success' : 'warning'" size="sm">
                                        {{ $campaign->status }}
                                    </flux:badge>
                                </span>
                                @if($campaign->country_or_region)
                                    <span>{{ $campaign->country_or_region }}</span>
                                @endif
                                <span>{{ $campaign->currency }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($campaign->daily_budget_amount)
                                <div class="text-sm text-zinc-600 dark:text-zinc-400">Daily Budget</div>
                                <div class="text-lg font-semibold">{{ number_format($campaign->daily_budget_amount, 2) }}</div>
                            @endif
                            @if($campaign->budget_amount)
                                <div class="text-xs text-zinc-500 dark:text-zinc-500 mt-1">
                                    Total: {{ number_format($campaign->budget_amount, 2) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between items-center text-xs text-zinc-500 dark:text-zinc-500">
                        <span>{{ $campaign->ads->count() }} ads</span>
                        @if($campaign->last_synced_at)
                            <span>Last synced: {{ $campaign->last_synced_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </a>
            </x-card>
        @empty
            <x-card>
                <div class="text-center py-8 text-zinc-600 dark:text-zinc-400">
                    <flux:icon.megaphone class="w-12 h-12 mx-auto mb-3 text-zinc-400" />
                    <p>No campaigns found.</p>
                    <flux:button wire:click="syncAll" variant="primary" class="mt-4">Sync Campaigns</flux:button>
                </div>
            </x-card>
        @endforelse
    </div>
</div>
