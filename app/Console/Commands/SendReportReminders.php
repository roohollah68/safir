<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use Carbon\Carbon;
use App\Notifications\ReportReminderNotification;

class SendReportReminders extends Command
{
    protected $signature = 'reports:remind';
    protected $description = 'Send reminders for report date';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        
        $projects = Project::whereDate('report_date', $tomorrow)
            ->whereNotNull('task_owner_id')
            ->get();

        foreach ($projects as $project) {
            $project->taskOwner->notify(new ReportReminderNotification($project));
        }

        $this->info('Reminders sent: '.$projects->count());
    }
}