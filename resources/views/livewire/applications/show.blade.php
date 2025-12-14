<div>
    <a href="{{route('applications.index')}}" wire:navigate>Back</a>
    <div class="flex flex-col space-x-8">
        <img src="{{$application->icon}}" class="h-20 w-20 rounded-lg"/>
        <p class="text-neutral-500">{{ $application->appstore_id }}</p>
    </div>


    <div class="flex flex-col gap-2 mt-8">
        @foreach($application->rankings as $ranking)
            <div class="flex gap-2">
                <div>{{ $ranking->date }}</div>
                <div class="bg-neutral-200 rounded-lg px-1 py-1 text-xs text-center">
                {{ $ranking->keyword->name }} ({{$ranking->country}})
                </div>
                @if ($ranking->position)
                <div class="font-bold">
                    #{{ $ranking->position ?? "--"}}
                </div>
                @else

                @endif
                <div>
{{--                {{ $ranking->average_rating ?? "--"}}--}}
{{--                ({{ $ranking->rating_count ?? "--"}})--}}
                </div>
            </div>
        @endforeach
    </div>
</div>
