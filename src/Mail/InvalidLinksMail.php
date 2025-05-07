<?php

namespace Martinoak\StatamicLinkChecker\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvalidLinksMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $errors;

    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $errors, string $subject)
    {
        $this->errors = $errors;
        $this->subject = $subject;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'link-checker::emails.invalid_links',
            with: ['errors' => $this->errors]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}
