<div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 dark:bg-zinc-800! dark:border-zinc-700">
  <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
    <div>
      <p class="text-sm text-gray-700 dark:text-zinc-300">
        Zobrazeno
        <span class="font-medium">{{ ($paginable->currentPage() - 1) * $paginable->perPage() + 1 }}</span>
        až
        <span class="font-medium">{{ min($paginable->currentPage() * $paginable->perPage(), $paginable->total()) }}</span>
        z
        <span class="font-medium">{{ $paginable->total() }}</span>
        výsledků
      </p>
    </div>
    <div>
      <nav class="isolate inline-flex -space-x-px rounded-md shadow-xs" aria-label="Pagination">
        {{-- Previous Page Link --}}
        @if ($paginable->currentPage() > 1)
          <button wire:click="paginate({{ $paginable->currentPage() - 1 }})" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-zinc-600 dark:hover:bg-zinc-700 dark:text-zinc-300">
            <span class="sr-only">Předchozí</span>
            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
              <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
          </button>
        @else
          <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-300 ring-1 ring-gray-300 ring-inset dark:ring-zinc-600 dark:text-zinc-600">
            <span class="sr-only">Předchozí</span>
            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
              <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
          </span>
        @endif

        {{-- Pagination Elements --}}
        @for ($i = 1; $i <= $paginable->lastPage(); $i++)
          @if ($i === $paginable->currentPage())
            {{-- Current Page --}}
            <span aria-current="page" class="relative z-10 inline-flex items-center bg-emerald-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 dark:bg-emerald-500">
              {{ $i }}
            </span>
          @elseif ($i === 1 || $i === $paginable->lastPage() || ($i >= $paginable->currentPage() - 1 && $i <= $paginable->currentPage() + 1))
            {{-- Show first page, last page, and pages around current page --}}
            <button wire:click="paginate({{ $i }})" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:text-white! dark:ring-zinc-600 dark:hover:bg-zinc-700">
              {{ $i }}
            </button>
          @elseif ($i === $paginable->currentPage() - 2 || $i === $paginable->currentPage() + 2)
            {{-- Ellipsis --}}
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset focus:outline-offset-0 dark:text-zinc-300 dark:ring-zinc-600">...</span>
          @endif
        @endfor

        {{-- Next Page Link --}}
        @if ($paginable->currentPage() < $paginable->lastPage())
          <button wire:click="paginate({{ $paginable->currentPage() + 1 }})" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-zinc-600 dark:hover:bg-zinc-700 dark:text-zinc-300">
            <span class="sr-only">Další</span>
            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
              <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
            </svg>
          </button>
        @else
          <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-300 ring-1 ring-gray-300 ring-inset dark:ring-zinc-600 dark:text-zinc-600">
            <span class="sr-only">Další</span>
            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
              <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
            </svg>
          </span>
        @endif
      </nav>
    </div>
  </div>
</div>
