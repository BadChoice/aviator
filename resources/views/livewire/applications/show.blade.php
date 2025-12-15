<div>
    <a href="{{ route('applications.index') }}" wire:navigate>
        <div class="flex items-center">
        <flux:icon name="chevron-left" variant="mini" class="text-neutral-500" />
        <div>Back</div>
        </div>
    </a>
    <div class="flex flex-col space-x-8 mt-4">
        <img src="{{ $application->icon }}" class="h-20 w-20 rounded-lg"/>
        <p class="text-neutral-500">{{ $application->name }}</p>
        <p class="text-neutral-500">{{ $application->appstore_id }}</p>
    </div>

    <div class="flex flex-col gap-1 mt-8">
        @forelse($groups as $group)
            <div class="flex items-center gap-4">
                <div class="flex text-sm w-52 items-center">
                    <div>{{ $group['keyword_name'] }}</div>
                    <div class="text-xs ml-2 rounded bg-neutral-200 px-2 py-1"> {{ $group['country'] }} </div>
                    @if($group['used'])
                        <flux:icon name="check" class="text-green-500 ml-2" variant="mini"  />
                    @endif
                </div>

                @if($group['latest_position'])
                <div class="font-bold text-sm">#{{ $group['latest_position'] ?? '--' }}</div>
                @endif

                <div class="grow max-w-[240px]">
                    <canvas
                        class="mini-chart h-10"
                        data-labels='@json($group['labels'])'
                        data-points='@json($group['data'])'
                    ></canvas>
                </div>
            </div>
        @empty
            <p class="text-neutral-500">No rankings yet.</p>
        @endforelse
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
        <script>
            function renderMiniCharts() {
                document.querySelectorAll('canvas.mini-chart').forEach((canvas) => {
                    if (canvas.dataset.chartInitialized === '1') { return; }
                    const labels = JSON.parse(canvas.getAttribute('data-labels') || '[]');
                    const points = JSON.parse(canvas.getAttribute('data-points') || '[]');

                    // Build a simple sparkline-like chart
                    new Chart(canvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: points,
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79,70,229,0.15)',
                                tension: 0.3,
                                borderWidth: 2,
                                pointRadius: 0,
                                spanGaps: true,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false }, tooltip: { enabled: true } },
                            scales: {
                                x: { display: false },
                                y: {
                                    display: false,
                                    reverse: true, // smaller ranking numbers are better
                                    suggestedMin: 1,
                                }
                            }
                        }
                    });

                    canvas.dataset.chartInitialized = '1';
                });
            }

            document.addEventListener('DOMContentLoaded', renderMiniCharts);
            document.addEventListener('livewire:navigated', renderMiniCharts);
        </script>
    @endonce
</div>
