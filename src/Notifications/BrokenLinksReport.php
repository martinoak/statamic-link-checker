<?php

namespace Martinoak\LinkChecker\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email alert about broken links (after scheduled run).
 */
class BrokenLinksReport extends Notification
{
    use Queueable;

    /**
     * @param  array<int, array>  $links  broken links to report
     * @param  array  $summary  run summary
     * @param  string  $cpUrl  link to CP tool
     * @param  bool  $onlyNew  newly broken links?
     */
    public function __construct(
        public array $links,
        public array $summary,
        public string $cpUrl,
        public bool $onlyNew = true,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $count = count($this->links);

        $mail = (new MailMessage)
            ->subject(__('statamic-link-checker::messages.mail_subject', ['count' => $count]))
            ->greeting(__('statamic-link-checker::messages.mail_greeting'))
            ->line($this->onlyNew
                ? __('statamic-link-checker::messages.mail_intro_new', ['count' => $count])
                : __('statamic-link-checker::messages.mail_intro_all', ['count' => $count]));

        foreach (array_slice($this->links, 0, 30) as $link) {
            $status = $link['status'] ?? ($link['error'] ?? $link['state'] ?? '');
            $mail->line('• ['.$status.'] '.$link['url']);
        }

        if ($count > 30) {
            $mail->line(__('statamic-link-checker::messages.mail_and_more', ['count' => $count - 30]));
        }

        return $mail->action(__('statamic-link-checker::messages.mail_action'), $this->cpUrl);
    }
}
