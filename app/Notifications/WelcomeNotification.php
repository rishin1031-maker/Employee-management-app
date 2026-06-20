<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function __construct(public Employee $employee) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title'   => 'Welcome to ' . config('app.name'),
            'message' => 'Your account has been created. Please change your password on first login.',
            'icon'    => 'fa-user-check',
            'color'   => 'green',
            'url'     => '/employee/profile',
        ];
    }
}