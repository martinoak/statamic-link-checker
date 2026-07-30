<?php

return [

    'title' => 'Kontrola odkazů',
    'nav_title' => 'Kontrola odkazů',

    'panel_heading' => 'Kontrola odkazů',
    'panel_description' => 'Dostupnost odkazů v obsahu – interní, externí i statamic:// reference. Zvýrazněny jsou chybové stavy (4XX/5XX) a nerozřešené reference.',

    'no_scan' => 'Zatím neproběhla žádná kontrola.',
    'no_scan_alert' => 'Odkazy zatím nebyly zkontrolovány. Spusťte kontrolu tlačítkem výše, nebo příkazem php artisan link-checker:scan.',
    'run_check' => 'Spustit kontrolu',
    'run_again' => 'Spustit znovu',
    'confirm_scan' => 'Spustit kontrolu odkazů? Podle objemu obsahu může chvíli trvat.',
    'last_check' => 'Poslední kontrola: :time',

    'filter_problems' => 'Jen problémy',
    'filter_all' => 'Vše',

    'toggle_internal' => 'Interní',
    'toggle_external' => 'Externí',
    'toggle_unresolved' => 'Nerozřešené',

    'col_status' => 'Status',
    'col_link' => 'Odkaz',
    'col_type' => 'Typ',
    'col_occurrences' => 'Výskyty',

    'stat_problematic' => ':count problematických',
    'stat_ok' => ':count OK',
    'stat_redirects' => ':count přesměrování',
    'stat_total' => ':count odkazů celkem',
    'stat_occurrences' => ':count výskytů',

    'empty_errors' => 'Žádné problematické odkazy. 🎉',
    'empty_all' => 'Žádné odkazy nenalezeny.',

    'state_error' => 'Chyba',
    'state_unresolved' => 'Nerozřešeno',
    'state_redirect' => 'Přesměrování',
    'state_ok' => 'OK',
    'state_skipped' => 'Přeskočeno',

    'type_external' => 'Externí',
    'type_internal' => 'Interní',
    'type_entry' => 'Entry',
    'type_asset' => 'Asset',
    'type_mailto' => 'E-mail',
    'type_tel' => 'Telefon',
    'type_anchor' => 'Kotva',
    'type_unresolved' => 'Reference',
    'type_skipped' => 'Ostatní',

    'field' => 'pole: :name',
    'site' => 'web: :name',
    'more_occurrences' => '+ :count dalších výskytů',

    'prev' => '‹ Předchozí',
    'next' => 'Další ›',
    'showing' => 'Zobrazeno :from–:to z :total',

    'scan_done_errors' => 'Kontrola dokončena – nalezeno :count problematických odkazů z :total.',
    'scan_done_ok' => 'Kontrola dokončena – všech :total odkazů je v pořádku.',

    'cmd_extracting' => 'Extrahuji odkazy z obsahu…',
    'cmd_checking' => 'Kontroluji odkazy…',
    'cmd_checked' => '  … zkontrolováno :count odkazů',
    'cmd_found_problems' => 'Nalezeno :count problematických odkazů. Detaily v CP → Nástroje → Kontrola odkazů.',
    'cmd_no_broken' => 'Žádné rozbité odkazy. 🎉',

    'scan_queued' => 'Kontrola odkazů byla spuštěna.',
    'cmd_queued' => 'Kontrola odkazů odeslána do fronty.',
    'running' => 'Právě probíhá kontrola odkazů…',

    'toggle_ignored' => 'Ignorované',
    'ignore_action' => 'Ignorovat',
    'unignore_action' => 'Zrušit ignoraci',
    'ignored' => 'Odkaz přidán mezi ignorované.',
    'unignored' => 'Odkaz odebrán z ignorovaných.',

    'anchor_missing' => 'kotva #:anchor nenalezena',

    'widget_broken_of' => 'z :total kontrolovaných odkazů',
    'widget_open' => 'Otevřít',

    'mail_subject' => 'Link checker: :count rozbitých odkazů',
    'mail_greeting' => 'Report kontroly odkazů',
    'mail_intro_new' => 'Bylo zjištěno :count nově rozbitých odkazů:',
    'mail_intro_all' => 'Bylo zjištěno :count rozbitých odkazů:',
    'mail_and_more' => '… a dalších :count.',
    'mail_action' => 'Otevřít Link Checker',

    'sum_total' => 'Celkem',
    'sum_ok' => 'OK',
    'sum_error' => 'Chyby',
    'sum_redirect' => 'Přesměrování',
    'sum_unresolved' => 'Nerozřešeno',
    'sum_skipped' => 'Přeskočeno',
    'sum_occurrences' => 'Výskytů',

];
