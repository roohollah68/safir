<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Project;

class ReportReminderNotification extends Notification
{
    use Queueable;

    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'پروژه "' . $this->project->title . '" نیاز به ثبت گزارش تا فردا (' 
                       . verta($this->project->report_date)->formatJalaliDate() 
                       . ') دارد.',
            'project_id' => $this->project->id,
            'due_date' => $this->project->report_date
        ];
    }
}