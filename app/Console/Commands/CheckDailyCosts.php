<?php

namespace App\Console\Commands;
use App\Models\FixedCost;
use Illuminate\Console\Command;

class CheckDailyCosts extends Command {
    protected $signature = 'costs:check';
    protected $description = 'Check daily fixed costs';

    public function handle() {
        $today = Verta::now();
        FixedCost::where('due_day', $today->day)->each(function ($fixedCost) {
            event(new \App\Events\FixedCostEvent($fixedCost));
        });
    }
}
