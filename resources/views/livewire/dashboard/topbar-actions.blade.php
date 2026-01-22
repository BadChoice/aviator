<div class="flex items-center gap-2">
    <flux:button
        size="sm"
        variant="primary"
        icon="bolt"
        wire:click="runRankings"
        wire:loading.attr="disabled"
        wire:target="runRankings"
    >
        <span wire:loading.remove wire:target="runRankings">{{ __('Track rankings') }}</span>
        <span wire:loading wire:target="runRankings">{{ __('Starting...') }}</span>
    </flux:button>

    <flux:button
        size="sm"
        variant="outline"
        icon="arrow-path"
        wire:click="runSalesSync"
        wire:loading.attr="disabled"
        wire:target="runSalesSync"
    >
        <span wire:loading.remove wire:target="runSalesSync">{{ __('Sync yesterday sales') }}</span>
        <span wire:loading wire:target="runSalesSync">{{ __('Starting...') }}</span>
    </flux:button>
</div>
