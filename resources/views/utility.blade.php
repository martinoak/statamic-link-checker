@extends('statamic::layout')
@section('title', __('statamic-link-checker::messages.title'))
@section('wrapper_class', 'max-w-full')

@php
    /**
     * @var bool        $report_exists
     * @var int|null    $scanned_at
     * @var array|null  $summary
     * @var array       $links
     * @var string      $filter
     * @var array       $types
     * @var array       $type_counts
     * @var int         $current_page
     * @var int         $per_page
     * @var int         $total
     * @var int         $last_page
     * @var string      $scan_url
     * @var string      $base_url
     */
    $t = fn ($key, $replace = []) => __('statamic-link-checker::messages.'.$key, $replace);

    $buildUrl = function (array $overrides = []) use ($base_url, $filter, $current_page, $types) {
        $params = array_merge([
            'filter' => $filter,
            'page' => $current_page,
            'types' => implode(',', $types),
        ], $overrides);
        $params = array_filter($params, fn ($v) => $v !== '' && $v !== null);

        return $base_url.'?'.http_build_query($params);
    };

    $typeLabels = [
        'external' => $t('type_external'),
        'internal' => $t('type_internal'),
        'entry' => $t('type_entry'),
        'asset' => $t('type_asset'),
        'mailto' => $t('type_mailto'),
        'tel' => $t('type_tel'),
        'anchor' => $t('type_anchor'),
        'unresolved' => $t('type_unresolved'),
        'skipped' => $t('type_skipped'),
    ];

    $categories = ['internal' => $t('toggle_internal'), 'external' => $t('toggle_external'), 'unresolved' => $t('toggle_unresolved')];
    $problematic = $summary ? (($summary['error'] ?? 0) + ($summary['unresolved'] ?? 0)) : 0;
@endphp

@section('content')
    <ui-heading size="2xl" class="mt-6 mb-6">{{ $t('title') }}</ui-heading>

    @if (session('success'))
        <ui-alert variant="success" text="{{ session('success') }}" class="mb-4" />
    @elseif (session('error'))
        <ui-alert variant="error" text="{{ session('error') }}" class="mb-4" />
    @endif

    @if ($running)
        <ui-alert text="{{ $t('running') }}" class="mb-4" />
    @endif

    <ui-panel heading="{{ $t('panel_heading') }}" description="{{ $t('panel_description') }}">
        <ui-card>
            {{-- Toolbar: summary + actions --}}
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($report_exists && $summary)
                        <ui-badge color="{{ $problematic > 0 ? 'red' : 'green' }}" size="lg" text="{{ $t('stat_problematic', ['count' => $problematic]) }}"></ui-badge>
                        <ui-badge color="green" size="lg" text="{{ $t('stat_ok', ['count' => $summary['ok'] ?? 0]) }}"></ui-badge>
                        <ui-badge color="yellow" size="lg" text="{{ $t('stat_redirects', ['count' => $summary['redirect'] ?? 0]) }}"></ui-badge>
                        <ui-badge color="default" size="lg" text="{{ $t('stat_total', ['count' => $summary['total'] ?? 0]) }}"></ui-badge>
                        <ui-badge color="default" size="lg" text="{{ $t('stat_occurrences', ['count' => $summary['occurrences'] ?? 0]) }}"></ui-badge>
                    @else
                        <span class="text-sm text-gray-500">{{ $t('no_scan') }}</span>
                    @endif
                </div>

                <form method="POST" action="{{ $scan_url }}" class="inline">
                    @csrf
                    <ui-button
                        variant="primary"
                        icon="refresh"
                        text="{{ $report_exists ? $t('run_again') : $t('run_check') }}"
                        onclick="if (confirm({{ \Illuminate\Support\Js::from($t('confirm_scan')) }})) { this.closest('form').submit(); } return false;"
                    ></ui-button>
                </form>
            </div>

            {{-- Filters --}}
            @if ($report_exists)
                <div class="flex flex-wrap items-center gap-4 mb-4">
                    <div class="flex items-center gap-2">
                        <ui-button size="sm" href="{{ $buildUrl(['filter' => 'errors', 'types' => '', 'page' => 1]) }}"
                                   variant="{{ $filter === 'errors' ? 'primary' : 'default' }}" text="{{ $t('filter_problems') }}"></ui-button>
                        <ui-button size="sm" href="{{ $buildUrl(['filter' => 'all', 'types' => '', 'page' => 1]) }}"
                                   variant="{{ $filter === 'all' ? 'primary' : 'default' }}" text="{{ $t('filter_all') }}"></ui-button>
                        <ui-button size="sm" href="{{ $buildUrl(['filter' => 'ignored', 'types' => '', 'page' => 1]) }}"
                                   variant="{{ $filter === 'ignored' ? 'primary' : 'default' }}" text="{{ $t('toggle_ignored') }} ({{ $ignored_count }})"></ui-button>
                    </div>

                    @if ($filter !== 'ignored')
                        <div class="flex items-center gap-2">
                            @foreach ($categories as $cat => $label)
                                @php
                                    $active = in_array($cat, $types, true);
                                    $newSet = $active ? array_values(array_diff($types, [$cat])) : array_merge($types, [$cat]);
                                    $count = $type_counts[$cat] ?? 0;
                                @endphp
                                <ui-button size="sm"
                                           href="{{ $buildUrl(['types' => implode(',', $newSet), 'page' => 1]) }}"
                                           variant="{{ $active ? 'primary' : 'default' }}"
                                           text="{{ $label }} ({{ $count }})"></ui-button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @if ($report_exists && $scanned_at)
                <p class="text-xs text-gray-500 mb-4">
                    {{ $t('last_check', ['time' => \Illuminate\Support\Carbon::createFromTimestamp($scanned_at)->tz(config('app.timezone'))->format('d.m.Y H:i')]) }}
                </p>
            @endif

            {{-- Content --}}
            @if (! $report_exists)
                <ui-alert text="{{ $t('no_scan_alert') }}" />
            @elseif (empty($links))
                <ui-alert variant="success" text="{{ $filter === 'errors' ? $t('empty_errors') : $t('empty_all') }}" />
            @else
                <ui-table>
                    <ui-table-columns>
                        <ui-table-column>{{ $t('col_status') }}</ui-table-column>
                        <ui-table-column>{{ $t('col_link') }}</ui-table-column>
                        <ui-table-column>{{ $t('col_type') }}</ui-table-column>
                        <ui-table-column>{{ $t('col_occurrences') }}</ui-table-column>
                    </ui-table-columns>
                    <ui-table-rows>
                        @foreach ($links ?? [] as $link)
                            @php
                                $badge = match ($link['state']) {
                                    'error' => ['red', $link['status'] ?: $t('state_error')],
                                    'unresolved' => ['orange', $t('state_unresolved')],
                                    'redirect' => ['yellow', $link['status'] ?: $t('state_redirect')],
                                    'ok' => ['green', $link['status'] ?: $t('state_ok')],
                                    default => ['default', $t('state_skipped')],
                                };
                                $occ = $link['occurrences'] ?? [];
                            @endphp
                            <ui-table-row>
                                <ui-table-cell>
                                    <ui-badge color="{{ $badge[0] }}" text="{{ $badge[1] }}"></ui-badge>
                                </ui-table-cell>
                                <ui-table-cell>
                                    <div class="break-all text-xs font-mono">
                                        @if (str_starts_with((string) $link['url'], 'http'))
                                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ $link['url'] }}</a>
                                        @else
                                            {{ $link['url'] }}
                                        @endif
                                    </div>
                                    @if (! empty($link['final_url']) && $link['final_url'] !== $link['url'])
                                        <div class="text-xs text-gray-400 mt-1">→ {{ $link['final_url'] }}</div>
                                    @endif
                                    <form method="POST" action="{{ $filter === 'ignored' ? $unignore_url : $ignore_url }}" class="mt-1">
                                        {!! csrf_field() !!}
                                        <input type="hidden" name="url" value="{{ $link['url'] }}">
                                        <ui-button size="sm" variant="default"
                                                   text="{{ $filter === 'ignored' ? $t('unignore_action') : $t('ignore_action') }}"
                                                   onclick="this.closest('form').submit(); return false;"></ui-button>
                                    </form>
                                </ui-table-cell>
                                <ui-table-cell>
                                    <ui-badge color="default" size="sm" text="{{ $typeLabels[$link['type']] ?? $link['type'] }}"></ui-badge>
                                </ui-table-cell>
                                <ui-table-cell>
                                    @foreach (array_slice($occ, 0, 3) as $o)
                                        <div class="text-sm">
                                            @if (! empty($o['edit_url']))
                                                <a href="{{ $o['edit_url'] }}" class="text-blue-600 hover:underline">{{ $o['title'] ?: $o['entry_id'] }}</a>
                                            @else
                                                {{ $o['title'] ?: $o['entry_id'] }}
                                            @endif
                                            <span class="text-xs text-gray-400">
                                                @if (! empty($o['collection'])) · {{ $o['collection'] }}@endif
                                                @if (! empty($o['site'])) · {{ $t('site', ['name' => $o['site']]) }}@endif
                                                @if (! empty($o['field'])) · {{ $t('field', ['name' => $o['field']]) }}@endif
                                            </span>
                                        </div>
                                    @endforeach
                                    @if (count($occ) > 3)
                                        <div class="text-xs text-gray-400">{{ $t('more_occurrences', ['count' => count($occ) - 3]) }}</div>
                                    @endif
                                </ui-table-cell>
                            </ui-table-row>
                        @endforeach
                    </ui-table-rows>
                </ui-table>

                {{-- Pagination --}}
                @if ($last_page > 1)
                    <div class="flex flex-wrap items-center gap-2 mt-4">
                        @if ($current_page > 1)
                            <ui-button size="sm" variant="default" href="{{ $buildUrl(['page' => $current_page - 1]) }}" text="{{ $t('prev') }}"></ui-button>
                        @endif

                        @foreach (range(1, $last_page) as $p)
                            @if ($p == 1 || $p == $last_page || abs($p - $current_page) <= 2)
                                <ui-button size="sm" variant="{{ $p == $current_page ? 'primary' : 'default' }}" href="{{ $buildUrl(['page' => $p]) }}" text="{{ $p }}"></ui-button>
                            @elseif (abs($p - $current_page) == 3)
                                <span class="text-gray-400">…</span>
                            @endif
                        @endforeach

                        @if ($current_page < $last_page)
                            <ui-button size="sm" variant="default" href="{{ $buildUrl(['page' => $current_page + 1]) }}" text="{{ $t('next') }}"></ui-button>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        {{ $t('showing', ['from' => ($current_page - 1) * $per_page + 1, 'to' => min($current_page * $per_page, $total), 'total' => $total]) }}
                    </p>
                @endif
            @endif

        </ui-card>
    </ui-panel>

@stop
