<div class="flex gap-4 items-center">
    <div> {{ $index }} </div>
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
    <div>
        <flux:dropdown position="top" align="start">
            <flux:button icon:trailing="ellipsis-horizontal"></flux:button>
            <flux:menu>
                <flux:navmenu.item href="{{$result['trackViewUrl']}}" icon="link" target="_blank">Visit</flux:navmenu.item>

                <flux:menu.submenu heading="Add as competitor" icon="plus">
                    @foreach($applications as $application)
                        <flux:navmenu.item wire:click="addAsCompetitor({{$application->id}}, {{$result['trackId']}}, {{$result['trackId']}})" icon="plus" target="_blank">{{ $application->name }}
                        </flux:navmenu.item>
                    @endforeach
                </flux:menu.submenu>
                </flux:menu>
        </flux:dropdown>
    </div>
</div>