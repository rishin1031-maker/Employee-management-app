<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $status = $this->leaveRequest->status;
        return [
            'title'   => 'Leave Request ' . ucfirst($status),
            'message' => 'Your ' . $this->leaveRequest->type . ' leave request ('
                       . $this->leaveRequest->from_date->format('d M')
                       . ' – ' . $this->leaveRequest->to_date->format('d M Y')
                       . ') has been ' . $status . '.',
            'icon'    => $status === 'approved' ? 'fa-circle-check' : 'fa-circle-xmark',
            'color'   => $status === 'approved' ? 'green' : 'red',
            'url'     => '/employee/leave',
        ];
    }
}