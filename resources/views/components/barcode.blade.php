@props(['upc', 'pattern'])

@if (!$upc)
<p class="text-red-500">Error</p>
@else
<div class="flex items-end h-full">
    <!-- Barcode -->
    <div class="flex items-end h-full">
        @foreach(str_split($pattern) as $bit)
        @if($bit === '1')
        <div class="bg-black" style="width: 1px; height: 100%;"></div>
        @else
        <div class="bg-white" style="width: 1px; height: 100%;"></div>
        @endif
        @endforeach
    </div>
</div>
@endif