<div>

    <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
        <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
            <div class="flex flex-col gap-3">
                @foreach($json['results'] as $result)
                    <a href="{{$result['trackViewUrl']}}" target="_blank">
                    <div class="flex gap-4 items-center">
                        <div>
                            {{ $loop->index + 1 }}
                        </div>
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
            </div>
        </main>
    </div>

</div>
