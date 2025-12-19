<div class="border rounded-xl p-4">
    <div class="flex">
        @foreach(range(1, $review['attributes']['rating']) as $x)
            <flux:icon name="star" variant="mini" />
        @endforeach
    </div>
    <div class="font-bold">{{ $review['attributes']['title'] }}</div>
    <div class="text-xs text-neutral-500">{{ \Carbon\Carbon::parse($review['attributes']['createdDate']) }}</div>
    <div class="text-xs text-neutral-500">{{ $review['attributes']['territory'] }}</div>
    <div class="text-sm my-4 text-neutral-800">{{ $review['attributes']['body'] }}</div>
</div>
