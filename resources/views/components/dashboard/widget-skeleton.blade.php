@props([
    'withSubtitle' => false,
    'skeletonColumns' => 4,
    'skeletonRows' => 4,
])

@php
    $skeletonColumns = max(1, min(8, (int) $skeletonColumns));
    $skeletonRows = max(1, min(8, (int) $skeletonRows));
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6']) }}>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-4 animate-pulse">
        <div class="space-y-2 flex-1">
            <div class="h-6 w-48 max-w-full rounded bg-zinc-200 dark:bg-zinc-700"></div>
            @if($withSubtitle)
                <div class="h-4 w-full max-w-lg rounded bg-zinc-200 dark:bg-zinc-700"></div>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <div class="h-9 w-36 rounded bg-zinc-200 dark:bg-zinc-700"></div>
            <div class="h-9 w-20 rounded bg-zinc-200 dark:bg-zinc-700"></div>
        </div>
    </div>

    <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden animate-pulse">
        <div class="h-11 bg-zinc-100 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700"></div>
        @for($i = 0; $i < $skeletonRows; $i++)
            <div class="flex gap-4 px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 last:border-b-0">
                @for($j = 0; $j < $skeletonColumns; $j++)
                    <div @class([
                        'h-4 flex-1 rounded bg-zinc-200 dark:bg-zinc-700',
                        'max-w-[5rem]' => $j === 0,
                    ])></div>
                @endfor
            </div>
        @endfor
    </div>
</div>
