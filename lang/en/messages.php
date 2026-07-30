<?php

return [

    'title' => 'Link Checker',
    'nav_title' => 'Link Checker',

    'panel_heading' => 'Link check',
    'panel_description' => 'Availability of links in your content — internal, external and statamic:// references. Error states (4XX/5XX) and unresolved references are highlighted.',

    'no_scan' => 'No check has run yet.',
    'no_scan_alert' => 'Links have not been checked yet. Run a check with the button above, or the command php artisan link-checker:scan.',
    'run_check' => 'Run check',
    'run_again' => 'Run again',
    'confirm_scan' => 'Run the link check? It may take a while depending on the amount of content.',
    'last_check' => 'Last check: :time',

    'filter_problems' => 'Problems only',
    'filter_all' => 'All',

    'toggle_internal' => 'Internal',
    'toggle_external' => 'External',
    'toggle_unresolved' => 'Unresolved',

    'col_status' => 'Status',
    'col_link' => 'Link',
    'col_type' => 'Type',
    'col_occurrences' => 'Occurrences',

    'stat_problematic' => ':count problematic',
    'stat_ok' => ':count OK',
    'stat_redirects' => ':count redirects',
    'stat_total' => ':count links total',
    'stat_occurrences' => ':count occurrences',

    'empty_errors' => 'No problematic links. 🎉',
    'empty_all' => 'No links found.',

    'state_error' => 'Error',
    'state_unresolved' => 'Unresolved',
    'state_redirect' => 'Redirect',
    'state_ok' => 'OK',
    'state_skipped' => 'Skipped',

    'type_external' => 'External',
    'type_internal' => 'Internal',
    'type_entry' => 'Entry',
    'type_asset' => 'Asset',
    'type_mailto' => 'E-mail',
    'type_tel' => 'Phone',
    'type_anchor' => 'Anchor',
    'type_unresolved' => 'Reference',
    'type_skipped' => 'Other',

    'field' => 'field: :name',
    'site' => 'site: :name',
    'more_occurrences' => '+ :count more occurrences',

    'prev' => '‹ Previous',
    'next' => 'Next ›',
    'showing' => 'Showing :from–:to of :total',

    'scan_done_errors' => 'Check complete — found :count problematic links out of :total.',
    'scan_done_ok' => 'Check complete — all :total links are OK.',

    'cmd_extracting' => 'Extracting links from content…',
    'cmd_checking' => 'Checking links…',
    'cmd_checked' => '  … checked :count links',
    'cmd_found_problems' => 'Found :count problematic links. See CP → Tools → Link Checker for details.',
    'cmd_no_broken' => 'No broken links. 🎉',

    'scan_queued' => 'Link check has been started.',
    'cmd_queued' => 'Link check dispatched to the queue.',
    'running' => 'A link check is currently running…',

    'toggle_ignored' => 'Ignored',
    'ignore_action' => 'Ignore',
    'unignore_action' => 'Un-ignore',
    'ignored' => 'Link added to the ignore list.',
    'unignored' => 'Link removed from the ignore list.',

    'anchor_missing' => 'anchor #:anchor not found',

    'widget_broken_of' => 'of :total links checked',
    'widget_open' => 'Open',

    'mail_subject' => 'Link checker: :count broken links',
    'mail_greeting' => 'Link check report',
    'mail_intro_new' => ':count newly broken links were detected:',
    'mail_intro_all' => ':count broken links were detected:',
    'mail_and_more' => '… and :count more.',
    'mail_action' => 'Open Link Checker',

    'sum_total' => 'Total',
    'sum_ok' => 'OK',
    'sum_error' => 'Errors',
    'sum_redirect' => 'Redirects',
    'sum_unresolved' => 'Unresolved',
    'sum_skipped' => 'Skipped',
    'sum_occurrences' => 'Occurrences',

];
