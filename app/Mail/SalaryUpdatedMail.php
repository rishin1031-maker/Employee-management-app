<?php

namespace App\Mail;

use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SalaryUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Employee $employee,
        public Salary $salary
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Salary Has Been Updated',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.salary-updated',
            with: [
                'employee' => $this->employee,
                'salary'   => $this->salary,
            ]
        );
    }
}