<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainPassword
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tài khoản doanh nghiệp đã được phê duyệt'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.company-approved',
            with: [
                'user' => $this->user,
                'plainPassword' => $this->plainPassword,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}