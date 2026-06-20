<?php

namespace App\Jobs;

use App\Mail\SalaryUpdatedMail;
use App\Models\Employee;
use App\Models\Salary;
use App\Notifications\SalaryUpdatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSalaryUpdatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Employee $employee,
        public Salary $salary
    ) {}

    public function handle(): void
    {
        // Email to employee
        Mail::to($this->employee->email)
            ->send(new SalaryUpdatedMail($this->employee, $this->salary));

        // In-app notification to employee
        $this->employee->notify(new SalaryUpdatedNotification($this->employee, $this->salary));
    }
}