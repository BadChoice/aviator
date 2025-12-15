<div>
    <div class="flex flex-col gap-4">
        @foreach($applications as $application)
            <a href="{{route('applications.show', $application->id)}}" wire:navigate>
                <div class="flex flex-col space-x-8">
                    <img src="{{$application->icon}}" class="h-20 w-20 rounded-lg"/>
                    <p class="text-neutral-500">{{ $application->name }}</p>
                    <div class="flex gap-1">
                    @foreach($application->keywords as $keyword)
                        <div class="bg-neutral-200 rounded-lg px-1 py-1 text-xs text-center">
                        {{ $keyword->name }}
                        {{ $keyword->last_ranking_position ?? "--" }}
                        </div>
                    @endforeach
                    </div>
                    <p class="text-neutral-500">{{ $application->appstore_id }}</p>
                </div>
            </a>
        @endforeach
    </div>
</div>
