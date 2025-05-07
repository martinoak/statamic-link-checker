<nav class="flex items-center justify-between border-t border-primary dark:border-gray-500 mt-4 px-4 sm:px-0">
    {{-- Previous Button --}}
    <div class="-mt-px flex w-0 flex-1">
        @if ($paginable->currentPage() > 1)
            <button wire:click="paginate({{ $paginable->currentPage() - 1 }})"
                    class="inline-flex items-center border-t-2 border-transparent pt-4 pr-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                <svg class="mr-3 size-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a.75.75 0 0 1-.75.75H4.66l2.1 1.95a.75.75 0 1 1-1.02 1.1l-3.5-3.25a.75.75 0 0 1 0-1.1l3.5-3.25a.75.75 0 1 1 1.02 1.1l-2.1 1.95h12.59A.75.75 0 0 1 18 10Z" clip-rule="evenodd" />
                </svg>
                Předchozí
            </button>
        @endif
    </div>

    <div class="hidden md:-mt-px md:flex">
        @for ($i = 1; $i <= $paginable->lastPage(); $i++)
            @if ($i > $paginable->currentPage() - 3 && $i < $paginable->currentPage() + 3)
                <button wire:click="paginate({{ $i }})"
                        class="inline-flex items-center border-t-2 {{ $i === $paginable->currentPage() ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} px-4 pt-4 text-sm font-medium"
                        aria-current="{{ $i === $paginable->currentPage() ? 'page' : false }}">
                    {{ $i }}
                </button>
            @elseif ($i === 1 || $i === $paginable->lastPage())
                <button wire:click="paginate({{ $i }})"
                        class="inline-flex items-center border-t-2 {{ $i === $paginable->currentPage() ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} px-4 pt-4 text-sm font-medium">
                    {{ $i }}
                </button>
            @elseif ($i === $paginable->currentPage() - 3 || $i === $paginable->currentPage() + 3)
                <span class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500">...</span>
            @endif
        @endfor
    </div>

    <div class="-mt-px flex w-0 flex-1 justify-end">
        @if ($paginable->currentPage() < $paginable->lastPage())
            <button wire:click="paginate({{ $paginable->currentPage() + 1 }})"
                    class="inline-flex items-center border-t-2 border-transparent pt-4 pl-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                Další
                <svg class="ml-3 size-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M2 10a.75.75 0 0 1 .75-.75h12.59l-2.1-1.95a.75.75 0 1 1 1.02-1.1l3.5 3.25a.75.75 0 0 1 0 1.1l-3.5 3.25a.75.75 0 1 1-1.02-1.1l2.1-1.95H2.75A.75.75 0 0 1 2 10Z" clip-rule="evenodd" />
                </svg>
            </button>
        @endif
    </div>
</nav>
