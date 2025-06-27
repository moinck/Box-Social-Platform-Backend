<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserTemplateSendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    public $type;
    /**
     * Create a new message instance.
     */
    public function __construct($mailData, $type)
    {
        $this->mailData = $mailData;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->type == 'store' ? 'New Template Created' : 'Template Updated',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'content.email.send-user-template',
            with: [
                'data' => $this->mailData,
                'type' => $this->type,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $templlateName = $this->mailData['template']->template_name ?? "Template";
        return [
            Attachment::fromPath($this->mailData['template']->template_image)
                ->as($templlateName)
                ->withMime('image/jpeg'),
        ];
    }
}
