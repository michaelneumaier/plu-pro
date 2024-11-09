<div wire:poll.1s>
    <div class="grid grid-cols-10 gap-4 mb-4">
        @for($i = 1; $i <= 50; $i++) <button wire:click="startDownload({{ $i }})" wire:loading.attr="disabled"
            @class([ 'px-4 py-2 rounded text-sm' , 'bg-blue-500 hover:bg-blue-600 text-white'=> !$isProcessing &&
            !isset($completedChunks[$i]),
            'bg-green-500' => isset($completedChunks[$i]),
            'bg-yellow-500' => $isProcessing && $currentChunk === $i,
            'bg-gray-400 cursor-not-allowed' => $isProcessing && $currentChunk !== $i
            ])
            @disabled($isProcessing)
            >
            <span wire:loading.remove>
                {{ $i }}
                ({{ ($i-1) * $this->chunkSize + 1 }}-{{ min($i * $this->chunkSize, $totalPLUs) }})
            </span>
            <span wire:loading>Loading...</span>
            </button>
            @endfor
    </div>

    @if($isProcessing)
    <div class="mt-4">
        <div class="mb-4">
            <div class="text-sm text-gray-600 mb-1">Overall Progress:</div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500"
                    style="width: {{ $this->totalProgress }}%">
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="text-sm text-gray-600 mb-1">Current Chunk Progress:</div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-green-600 h-2.5 rounded-full transition-all duration-500"
                    style="width: {{ $this->progress }}%">
                </div>
            </div>
        </div>

        <div class="text-sm text-gray-600">
            Processing Chunk: {{ $currentChunk }} of 50<br>
            Current PLU: {{ $currentPLU }}<br>
            Current Chunk: {{ $successCount }} successful, {{ $failureCount }} failed<br>
            Total: {{ $totalSuccess }} successful, {{ $totalFailure }} failed
        </div>
    </div>
    @endif

    @if (session()->has('message'))
    <div class="mt-4 text-green-500">
        {{ session('message') }}
    </div>
    @endif
</div>