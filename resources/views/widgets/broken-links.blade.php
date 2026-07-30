@php
    $t = fn ($key, $replace = []) => __('statamic-link-checker::messages.'.$key, $replace);
@endphp

<ui-panel heading="{{ $t('nav_title') }}">
    <ui-card>
        <div class="flex items-center justify-between gap-4">
            <div>
                @if (! $report_exists)
                    <p class="text-sm text-gray-500">{{ $t('no_scan') }}</p>
                @else
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold {{ $broken > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $broken }}</span>
                        <span class="text-sm text-gray-500">{{ $t('widget_broken_of', ['total' => $total]) }}</span>
                    </div>
                    @if ($scanned_at)
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $t('last_check', ['time' => \Illuminate\Support\Carbon::createFromTimestamp($scanned_at)->format('d.m.Y H:i')]) }}
                        </p>
                    @endif
                @endif
            </div>

            <ui-button size="sm" variant="{{ $broken > 0 ? 'primary' : 'default' }}" href="{{ $url }}" text="{{ $t('widget_open') }}"></ui-button>
        </div>
    </ui-card>
</ui-panel>
