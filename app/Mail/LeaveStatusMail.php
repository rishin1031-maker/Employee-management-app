<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function envelope(): Envelope
    {
        $status = ucfirst($this->leaveRequest->status);
        return new Envelope(
            subject: "Your Leave Request has been {$status}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leave-status',
            with: ['leaveRequest' => $this->leaveRequest],
        );
    }
}