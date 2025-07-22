@props(['upc', 'pattern', 'size' => 'default'])

@php
$sizeClasses = match($size) {
    'sm' => 'h-6',
    'default' => 'h-10',
    'lg' => 'h-16', 
    'xl' => 'h-20',
    default => 'h-10'
};

$barWidth = match($size) {
    'sm' => '1px',
    'default' => '1.5px',
    'lg' => '2px',
    'xl' => '3px', 
    default => '1.5px'
};
@endphp

@if (!$upc)
<div class="flex items-center justify-center {{ $sizeClasses }} bg-gray-100 rounded border-2 border-dashed border-gray-300">
    <span class="text-xs text-gray-500">No barcode</span>
</div>
@else
<div class="flex items-center justify-center {{ $sizeClasses }} bg-white p-2 rounded border border-gray-200">
    <!-- Barcode bars -->
    <div class="flex items-end h-full">
        @foreach(str_split($pattern) as $bit)
        @if($bit === '1')
        <div class="bg-black" style="width: {{ $barWidth }}; height: 100%;"></div>
        @else
        <div class="bg-white" style="width: {{ $barWidth }}; height: 100%;"></div>
        @endif
        @endforeach
    </div>
</div>
@endif