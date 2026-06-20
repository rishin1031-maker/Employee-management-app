<?php

namespace App\Jobs;

use App\Mail\LeaveAppliedMail;
use App\Models\Admin;
use App\Models\LeaveRequest;
use App\Notifications\LeaveAppliedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLeaveAppliedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function handle(): void
    {
        $admins = Admin::all();

        foreach ($admins as $admin) {
            // Email to each admin
            Mail::to($admin->email)
                ->send(new LeaveAppliedMail($this->leaveRequest));

            // In-app notification to admin
            $admin->notify(new LeaveAppliedNotification($this->leaveRequest));
        }
    }
}