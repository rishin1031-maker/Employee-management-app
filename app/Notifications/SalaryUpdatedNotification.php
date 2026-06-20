<?php

namespace App\Notifications;

use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SalaryUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Employee $employee,
        public Salary $salary
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title'   => 'Salary Updated',
            'message' => 'Your salary has been updated. New net salary: ₹'
                       . number_format($this->salary->net_salary, 2)
                       . ', effective ' . $this->salary->effective_from->format('d M Y') . '.',
            'icon'    => 'fa-money-bill',
            'color'   => 'blue',
            'url'     => '/employee/profile',
        ];
    }
}