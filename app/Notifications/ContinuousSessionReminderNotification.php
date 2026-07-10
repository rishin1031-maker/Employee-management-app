<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContinuousSessionReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $limitLabel,
        public string $remainingLabel,
        public int $graceMinutes,
        public int $minBreakMinutes,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Break or check out soon',
            'message' => "You've been working continuously for nearly {$this->limitLabel}. "
                . "About {$this->remainingLabel} left before auto checkout"
                . ($this->graceMinutes > 0 ? " (plus {$this->graceMinutes}m grace)" : '')
                . ". Take a break of at least {$this->minBreakMinutes} minutes to reset, or check out.",
            'icon' => 'fa-clock',
            'color' => 'amber',
            'url' => '/employee/dashboard',
            'category' => 'attendance',
        ];
    }
}
