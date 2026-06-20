<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveAppliedNotification extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title'   => 'New Leave Request',
            'message' => $this->leaveRequest->employee->name . ' applied for '
                       . $this->leaveRequest->days . ' day(s) of '
                       . $this->leaveRequest->type . ' leave.',
            'icon'    => 'fa-calendar-xmark',
            'color'   => 'yellow',
            'url'     => '/admin/leave/' . $this->leaveRequest->id,
        ];
    }
}