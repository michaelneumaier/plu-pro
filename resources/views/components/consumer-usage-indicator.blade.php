@php
// Define colors for each tier
$barColors = [
'high' => 'bg-green-500',
'medium' => 'bg-yellow-500',
'low' => 'bg-red-500',
];

// Determine the number of bars based on the tier
$bars = match ($tier) {
'high' => 3,
'medium' => 2,
'low' => 1,
default => 0,
};
@endphp

<div class="flex items-end space-x-1" aria-label="{{ ucfirst($tier) ?? 'Unknown' }} Usage Tier"
    title="{{ ucfirst($tier) ?? 'Unknown' }} Usage Tier">
    @for ($i = 1; $i <= 3; $i++) @if ($i <=$bars) <div class="w-1 h-4 {{ $barColors[$tier] ?? 'bg-gray-300' }} rounded">
</div>
@else
<div class="w-1 h-2 bg-gray-300 rounded"></div>
@endif
@endfor
</div>