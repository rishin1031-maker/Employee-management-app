<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContinuousSessionAutoCheckoutNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $limitLabel,
        public string $checkoutAt,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Auto checked out',
            'message' => "You were automatically checked out at {$this->checkoutAt} because your continuous "
                . "working session exceeded {$this->limitLabel} without a qualifying break.",
            'icon' => 'fa-right-from-bracket',
            'color' => 'red',
            'url' => '/employee/dashboard',
            'category' => 'attendance',
        ];
    }
}
