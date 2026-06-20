<?php

namespace App\Jobs;

use App\Mail\LeaveStatusMail;
use App\Models\LeaveRequest;
use App\Notifications\LeaveStatusNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLeaveStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function handle(): void
    {
        $employee = $this->leaveRequest->employee;

        // Email to employee
        Mail::to($employee->email)
            ->send(new LeaveStatusMail($this->leaveRequest));

        // In-app notification to employee
        $employee->notify(new LeaveStatusNotification($this->leaveRequest));
    }
}