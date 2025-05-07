<div class="paginator">
    <nav>
        <div class="-mt-px flex w-0 flex-1">
            @if ($paginable->currentPage() > 1)
                <button wire:click="paginate({{ $paginable->currentPage() - 1 }})" class="pag-item pr-1">
                    <i class="fa-regular fa-arrow-left mr-2"></i>
                    Předchozí
                </button>
            @endif
        </div>
        <div class="hidden md:-mt-px md:flex">
            @for ($i = 1; $i <= $paginable->lastPage(); $i++)
                @if ($i > $paginable->currentPage() - 3 && $i < $paginable->currentPage() + 3)
                    <a wire:click="paginate({{ $i }})" class="pag-item{{ $i === $paginable->currentPage() ? '-active' : '' }} px-4">{{ $i }}</a>
                @elseif ($i === 1 || $i === $paginable->lastPage())
                    <a wire:click="paginate({{ $i }})" class="pag-item{{ $i === $paginable->currentPage() ? '-active' : '' }} px-4">{{ $i }}</a>
                @elseif ($i === $paginable->currentPage() - 3 || $i === $paginable->currentPage() + 3)
                    <span class="pag-item px-4 pt-4 text-sm font-medium text-gray-500">...</span>
                @endif
            @endfor
        </div>
        <div class="-mt-px flex w-0 flex-1 justify-end">
            @if ($paginable->currentPage() < $paginable->lastPage())
                <button wire:click="paginate({{ $paginable->currentPage() + 1 }})" class="pag-item pl-1">
                    Další
                    <i class="fa-regular fa-arrow-right ml-2"></i>
                </button>
            @endif
        </div>
    </nav>
</div>
