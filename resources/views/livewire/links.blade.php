<div>
    <div class="card max-w-full mb-4">
        <div class="p-1">
            <div class="sm:flex sm:items-start sm:justify-between">
                <ul class="flex w-full gap-2">
                    <li>
                        <input wire:model.live="showRedirects" id="showRedirects" type="checkbox" class="hidden peer" @checked($showRedirects)>
                        <label for="showRedirects" class="checkbox-wrapper">
                            <div class="block">
                                <div class="w-full">Zobrazit přesměrování @if($showRedirects) <i class="fa-solid fa-check ml-2"></i> @else <i class="fa-solid fa-xmark ml-2"></i> @endif</div>
                            </div>
                        </label>
                    </li>
                    <li>
                        <input wire:model.live="mineOnly" id="mineOnly" type="checkbox" class="hidden peer" @checked($mineOnly)>
                        <label for="mineOnly" class="checkbox-wrapper">
                            <div class="block">
                                <div class="w-full">Pouze mé odkazy @if($mineOnly) <i class="fa-solid fa-check ml-2"></i> @else <i class="fa-solid fa-xmark ml-2"></i> @endif</div>
                            </div>
                        </label>
                    </li>

                    <!-- Status Code Dropdown -->
                    <li>
                        <div x-data="{ open: false }" class="relative inline-block text-left">
                            <button type="button" x-on:click="open = !open" class="select-wrapper relative">
                                <span class="relative inline-block text-sm">
                                    Status kódy
                                    <svg class="inline-block ml-2 size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                    @php $statusCount = count(array_filter($statusCodesSelected)); @endphp
                                    @if($statusCount > 0)
                                        <span class="button-badge">{{ $statusCount }}</span>
                                    @endif
                                </span>
                            </button>

                            <div x-show="open" x-on:click.outside="open = false" class="dropdown" role="menu" x-cloak>
                                <div class="py-1" role="none">
                                    @foreach($statusCodes as $code)
                                        <div class="flex items-center p-3">
                                            <input id="status-{{ $code['value'] }}" type="checkbox" wire:model.live="statusCodesSelected.{{ $code['value'] }}" class="w-4 h-4 text-blue-600 bg-gray-100 rounded-sm dark:bg-gray-600 dark:border-gray-500">
                                            <label for="status-{{ $code['value'] }}" class="d-text">{{ $code['label'] }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- Link Type Dropdown -->
                    <li>
                        <div x-data="{ open: false }" class="relative inline-block text-left">
                            <button type="button" x-on:click="open = !open" class="select-wrapper relative">
                                <span class="relative inline-block text-sm">
                                    Typ odkazu
                                    <svg class="inline-block ml-2 size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                    @php $typeCount = count(array_filter($linkTypesSelected)); @endphp
                                    @if($typeCount > 0)
                                        <span class="button-badge">{{ $typeCount }}</span>
                                    @endif
                                </span>
                            </button>

                            <div x-show="open" x-on:click.outside="open = false" class="dropdown" role="menu" x-cloak>
                                <div class="py-1" role="none">
                                    @foreach($linkTypes as $type)
                                        <div class="flex items-center p-3">
                                            <input id="type-{{ $type['value'] }}" type="checkbox" wire:model.live="linkTypesSelected.{{ $type['value'] }}" class="w-4 h-4 text-blue-600 bg-gray-100 rounded-sm dark:bg-gray-600 dark:border-gray-500">
                                            <label for="type-{{ $type['value'] }}" class="d-text">{{ $type['label'] }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card p-0 relative">
        <!-- Table loading indicator -->
        <div wire:loading.flex wire:target="sort, paginate, toggleStatusCode, toggleLinkType, showRedirects, mineOnly" class="absolute inset-0 bg-white bg-opacity-75 dark:bg-zinc-800 dark:bg-opacity-75 z-10 items-center justify-center">
            <div class="flex items-center">
                <svg class="animate-spin h-8 w-8 text-emerald-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-emerald-500 font-medium">Aktualizace dat...</span>
            </div>
        </div>

        <table data-size="sm" tabindex="0" class="data-table">
            <thead>
            <tr>
                <th class="group rounded-none">
                    <span wire:click="sort('source')">Zdroj</span>
                    @if ($sortBy === 'source')
                        <span>@if($sortDirection === 'asc') ↑ @else ↓ @endif</span>
                    @endif
                </th>
                <th class="group rounded-none">
                    <span wire:click="sort('url')">URL</span>
                    @if ($sortBy === 'url')
                        <span>@if($sortDirection === 'asc') ↑ @else ↓ @endif</span>
                    @endif
                </th>
                <th class="group rounded-none text-center">
                    <span wire:click="sort('code')">Kód</span>
                    @if ($sortBy === 'code')
                        <span>@if($sortDirection === 'asc') ↑ @else ↓ @endif</span>
                    @endif
                </th>
                <th class="group rounded-none">
                    <span wire:click="sort('editor')">Editor</span>
                    @if ($sortBy === 'editor')
                        <span>@if($sortDirection === 'asc') ↑ @else ↓ @endif</span>
                    @endif
                </th>
            </tr>
            </thead>
            <tbody tabindex="0">
            @foreach ($links as $link)
                <tr class="outline-none" tabindex="0" wire:key="link-{{ $link->id }}">
                    <td>
                        @php $source = \Statamic\Facades\Entry::find(\Symfony\Component\Yaml\Yaml::parse(str_replace('---', '', file_get_contents($link->source)))['id']); @endphp
                        <a href="{{ $source->editUrl() }}">{{ $source->title }}</a>
                    </td>
                    <td>
                        <span class="font-mono text-xs">{{$link->url}}</span>
                    </td>
                    <td>
                        <p class="text-center cursor-pointer" v-tooltip="'{{ \Martinoak\StatamicLinkChecker\Http\Controllers\LinkCheckerController::getCodeMessage($link->code) }}'">{{$link->code}}</p>
                    </td>
                    <td>
                        <p>{{ \Statamic\Facades\User::find($link->editor)?->email() ?: 'nenalezen'}}</p>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @include('link-checker::components.pagination', ['paginable' => $links])
</div>

