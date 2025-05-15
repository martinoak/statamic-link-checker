<div>
    <a href="{{ cp_route('link-checker.run') }}"
        wire:click="run"
            wire:loading.attr="disabled" wire:target="run"
            class="btn-primary ml-4 relative">
        <span wire:loading.remove wire:target="run">Re-run</span>
    </a>

    <!-- Full-screen loading overlay -->
    <div wire:loading.flex wire:target="run" class="fixed inset-0 bg-black/50 items-center justify-center z-[9999]" x-cloak>
        <div class="flex flex-col items-center bg-white dark:bg-zinc-800! p-8 rounded-lg shadow-lg">
            <svg class="animate-spin h-12 w-12 text-emerald-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <div class="text-xl font-medium">Probíhá kontrola odkazů...</div>
            <div class="text-sm text-gray-500 dark:text-gray-300 mt-2">Tato operace může trvat několik minut.</div>
        </div>
    </div>
</div>
