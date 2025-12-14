<div>
    <div class="flex gap-4">
        @foreach($applications as $application)
            <a href="{{route('applications.show', $application->id)}}" wire:navigate>
                <div class="flex flex-col space-x-8">
                    <img src="{{$application->icon}}" class="h-20 w-20 rounded-lg"/>
                    <p class="text-neutral-500">{{ $application->appstore_id }}</p>
                </div>
            </a>
        @endforeach
    </div>
</div>
