@props([
    'type' => 'line',
    'labels' => [],
    'datasets' => [],
    'height' => 240,
    'currency' => false,
])

{{--
    wire:ignore keeps Livewire's DOM diffing away from the canvas Chart.js
    owns. Pass a wire:key on the parent (or this component) tied to the data
    you pass in, e.g. wire:key="spend-chart-{{ $range }}", so Livewire
    replaces the whole node (and Alpine re-inits, redrawing the chart) when
    the underlying numbers change, instead of trying to morph inside it.
--}}
<div wire:ignore {{ $attributes }}>
    <canvas
        x-data="trenaktChart({
            type: @js($type),
            labels: @js($labels),
            datasets: @js($datasets),
            currency: @js($currency),
        })"
        style="height: {{ $height }}px; width: 100%;"
    ></canvas>
</div>
