<div>
    <div class="card max-w-full mb-4">
        <div class="p-1">
            <div class="sm:flex sm:items-start sm:justify-between">
                <ul class="flex w-full gap-2">
                    <li>
                        <input wire:model.live="showRedirects" id="showRedirects" type="checkbox" class="hidden peer" @checked($showRedirects)>
                        <label for="showRedirects" class="inline-flex items-center justify-between px-2 py-1  border-2 border-primary rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 peer-checked:border-secondary dark:peer-checked:border-secondary hover:text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700">
                            <div class="block">
                                <div class="w-full">Zobrazit přesměrování @if($showRedirects) <i class="fa-solid fa-check ml-2"></i> @else <i class="fa-solid fa-xmark ml-2"></i> @endif</div>
                            </div>
                        </label>
                    </li>
                    <li>
                        <input wire:model.live="mineOnly" id="mineOnly" type="checkbox" class="hidden peer" @checked($mineOnly)>
                        <label for="mineOnly" class="inline-flex items-center justify-between px-2 py-1  border-2 border-primary rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 peer-checked:border-secondary dark:peer-checked:border-secondary hover:text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700">
                            <div class="block">
                                <div class="w-full">Pouze mé odkazy @if($mineOnly) <i class="fa-solid fa-check ml-2"></i> @else <i class="fa-solid fa-xmark ml-2"></i> @endif</div>
                            </div>
                        </label>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card p-0">
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

