<div>

    <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
        <main class="flex flex-col max-w-[335px] w-full lg:max-w-4xl">
            <div class="flex items-center gap-3">
                <flux:input wire:model="search" type="text" placeholder="Search App Store" class="input input-bordered w-full mb-6 lg:mb-0 lg:me-6" />
                <flux:select wire:model="country" size="sm">
                    <flux:select.option value="US">US</flux:select.option>
                    <flux:select.option value="ES">ES</flux:select.option>
                </flux:select>
                <flux:button variant="primary" wire:click="onSearchPressed">Search</flux:button>
            </div>
            <div class="flex flex-col gap-3 mt-4">
                <div wire:loading>
                    <flux:skeleton.group animate="shimmer" class="flex items-center gap-4">
                        <flux:skeleton class="size-10 rounded-full" />
                        <div class="flex-1">
                            <flux:skeleton.line />
                            <flux:skeleton.line class="w-1/2" />
                        </div>
                    </flux:skeleton.group>
                </div>
                @if ($results)
                    @foreach($results['results'] as $result)
                        <x-applications.search-result :result="$result" :index="$loop->index + 1" :applications="$applications" />
                    @endforeach
                @else
                    No results
                @endif
            </div>
        </main>
    </div>

</div>
