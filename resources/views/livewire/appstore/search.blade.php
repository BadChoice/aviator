<div>

    <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
        <main class="flex flex-col max-w-[335px] w-full lg:max-w-4xl">
            <div class="flex">
                <flux:input wire:model="search" type="text" placeholder="Search App Store" class="input input-bordered w-full mb-6 lg:mb-0 lg:me-6" />
                <flux:select wire:model="country" placeholder="Country" class="w-10">
                    <flux:select.option value="US">US</flux:select.option>
                    <flux:select.option value="ES">ES</flux:select.option>
                </flux:select>
                <flux:button variant="primary" wire:click="onSearchPressed">Search</flux:button>
            </div>
            <div class="flex flex-col gap-3 mt-4">
                @if ($results)
                    @foreach($results['results'] as $result)
                        <a href="{{$result['trackViewUrl']}}" target="_blank">
                        <div class="flex gap-4 items-center">
                            <div> {{ $loop->index + 1 }} </div>
                            <img src="{{$result['artworkUrl512']}}" class="w-16 h-16 rounded-xl" alt="icon"/>
    {{--                        {{ $result['trackId']}}--}}
                            <div>
                                <div>
                                    <div>
                                        {{ $result['trackName']}} -
                                        {{ $result['primaryGenreName']}}
                                    </div>
                                    <div class="flex gap-4 text-sm">
                                        <div>{{ $result['formattedPrice'] ?? "FREE"}}</div>
                                        <div>
                                            {{ number_format($result['averageUserRating'], 2)}}
                                            ({{ $result['userRatingCount']}})
                                        </div>
                                    </div>
                                </div>
                                <div class="text-gray-500 text-sm">{{ $result['artistName']}}</div>
                            </div>
                        </div>
                        </a>
                    @endforeach
                @else
                    No results
                @endif
            </div>
        </main>
    </div>

</div>
