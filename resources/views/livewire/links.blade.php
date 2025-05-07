<div>
    <div class="card max-w-full mb-4">
        <div class="p-2">
            <div class="sm:flex sm:items-start sm:justify-between">

            </div>
        </div>
    </div>

    <div class="card p-0">
        <table data-size="sm" tabindex="0" class="data-table">
            <thead>
            <tr>
                <th class="group rounded-none">
                    <span wire:click="sort('source')">Zdroj</span>
                </th>
                <th class="group rounded-none">
                    <span wire:click="sort('url')">URL</span>
                </th>
                <th class="group rounded-none text-center">
                    <span wire:click="sort('code')">Kód</span>
                </th>
                <th class="group rounded-none">
                    <span wire:click="sort('editor')">Editor</span>
                </th>
            </tr>
            </thead>
            <tbody tabindex="0">
            @foreach ($links as $link)
                <tr class="outline-none" tabindex="0" wire:key="link-{{ $link->id }}">
                    <td>
<!--                         @php $source = \Statamic\Facades\Entry::find(\Symfony\Component\Yaml\Yaml::parse(str_replace('---', '', file_get_contents($link->source)))['id']); @endphp
                        <a href="{{ $source->editUrl() }}">{{ $source->title }}</a> -->
                        TODO
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
