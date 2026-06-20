<?php

namespace App\Jobs;

use App\Mail\WelcomeEmployeeMail;
use App\Models\Employee;
use App\Notifications\WelcomeNotification;
use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Employee $employee,
        public string $plainPassword
    ) {}

    public function handle(): void
    {
        // Send welcome email to employee
        Mail::to($this->employee->email)
            ->send(new WelcomeEmployeeMail($this->employee, $this->plainPassword));

        // In-app notification to employee
        $this->employee->notify(new WelcomeNotification($this->employee));
    }
}