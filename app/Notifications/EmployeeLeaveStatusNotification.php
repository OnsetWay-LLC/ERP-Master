<?php

namespace App\Notifications;

use App\Models\EmployeeLeave;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeLeaveStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly EmployeeLeave $leave
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->leave->status === 'approved'
            ? 'Leave Request Approved'
            : 'Leave Request Rejected';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.employee_leave_status', [
                'employee' => $this->leave->employee,
                'leave' => $this->leave,
            ]);
    }
}