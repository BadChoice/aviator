<div>

    <div class="mb-4">
        @foreach($sales as $sale)
            <div class="flex gap-2 text-sm">
                <div class="w-24">{{ $sale->begin_date->format('Y-m-d') }} </div>
                <div class="w-80">{{ $sale->sku }} </div>
                <div class="w-20 text-right">{{ $sale->normalized_proceeds }} €</div>
            </div>
        @endforeach

        <div class="flex gap-2 text-sm font-bold mt-2">
            <div class="w-24"></div>
            <div class="w-80"> Total </div>
            <div class="w-20 text-right">{{ $sales->sum('normalized_proceeds') }} €</div>
        </div>

    </div>

    <flux:separator />

    @foreach($applications as $application)

        <div class="my-4">

            <div class="flex gap-4 mt-4">
                <img src="{{ $application->icon }}" class="h-20 w-20 rounded-lg"/>
                {{ $application->name }}
            </div>

            <div class="my-4">
                @forelse($application->appStoreReviews(from: $fromDate) as $review)
                    <x-applications.review :review="$review" />
                @empty
                    No new reviews
                @endforelse
            </div>



            <div class="flex flex-col gap-2 text-sm my-2">
                @forelse($application->lastRankings($fromDate) as $ranking)
                    <div class="flex gap-2 items-center">
                        <div>{{ $ranking->created_at->format('Y-m-d') }}</div>
                        <div class="text-xs bg-neutral-200 text-gray-500 py-1 px-2 rounded">{{ $ranking->country }}</div>
                        <div class="w-80">{{ $ranking->keyword->name }}</div>
                        <div class="text-right w-20">{{ $ranking->position }}</div>
                    </div>
                @empty
                    App not ranked
                @endforelse
            </div>

        </div>
        <flux:separator />
    @endforeach

</div>
