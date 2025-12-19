<div class="w-full rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 flex flex-col gap-4">
    <div class="flex items-center gap-3">
        @if($application->icon)
            <img src="{{ $application->icon }}" alt="{{ $application->name }}" class="rounded-xl h-12 w-12">
        @endif
        <a href="{{route('applications.show', $application)}}">
        <div class="text-lg font-semibold">{{ $application->name }}</div>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <div class="overflow-x-auto rounded-lg border border-neutral-200 dark:border-neutral-700">
                <table class="min-w-full text-sm">
                    <thead class="bg-neutral-50 dark:bg-neutral-800/50 text-neutral-600 dark:text-neutral-300">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium">Keyword</th>
                            <th class="px-3 py-2 text-left font-medium">Country</th>
                            <th class="px-3 py-2 text-left font-medium">Last Rank</th>
                            <th class="px-3 py-2 text-left font-medium">As of</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($latestRankings as $row)
                            <tr>
                                <td class="px-3 py-2">{{ $row['keyword'] }}</td>
                                <td class="px-3 py-2">{{ $row['country'] ?? '—' }}</td>
                                <td class="px-3 py-2">
                                    @if($row['position'] !== null)
                                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-0.5 text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/20 dark:text-green-300">#{{ $row['position'] }}</span>
                                    @else
                                        <span class="text-neutral-500">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-neutral-500">{{ $row['date'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-neutral-500">No rankings yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="md:col-span-1">
            <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-3">
                <div class="mb-2 text-sm font-medium">Revenue (last {{ $revenueSeries->count() }} days)</div>
                @php
                    $max = max(1, $revenueSeries->max('value'));
                @endphp
                <div class="flex items-end gap-1 h-28">
                    @foreach($revenueSeries as $point)
                        @php
                            $height = $max > 0 ? max(2, intval(($point['value'] / $max) * 100)) : 2;
                        @endphp
                        <div class="flex h-full flex-col items-center justify-end">
                            <div
                                class="group relative w-1.5 rounded-sm bg-blue-500/70 outline-none dark:bg-blue-400/80"
                                style="height: {{ $height }}%"
                                tabindex="0"
                                aria-label="{{ $point['date'] }} • {{ number_format($point['value'], 2) }}" €
                                title="{{ $point['date'] }} • {{ number_format($point['value'], 2) }}" €
                            >
                                <div class="pointer-events-none absolute -top-2 left-1/2 -translate-x-1/2 -translate-y-full whitespace-nowrap rounded bg-neutral-900 px-1.5 py-0.5 text-[10px] font-medium text-white opacity-0 transition-opacity group-hover:opacity-100 group-focus:opacity-100">
                                    {{ $point['date'] }} • {{ number_format($point['value'], 2) }} €
                                </div>
                            </div>
                            <span class="mt-1 text-[10px] text-neutral-500 md:hidden">{{ number_format($point['value'], 2) }} €</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-[10px] text-neutral-500">
                    <span>{{ $revenueSeries->first()['date'] ?? '' }}</span>
                    <span>{{ $revenueSeries->last()['date'] ?? '' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
