@props(['id', 'config', 'height' => 300])

<canvas
    id="{{ $id }}"
    data-trend-chart="{{ json_encode($config) }}"
    height="{{ $height }}"
></canvas>
