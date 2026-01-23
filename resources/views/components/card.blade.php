@props([
    'padding' => true,
])

@php
    $paddingClass = $padding ? 'p-6' : '';
@endphp

<div {{ $attributes->merge(['class' => "bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-sm {$paddingClass}"]) }}>
    {{ $slot }}
</div>
