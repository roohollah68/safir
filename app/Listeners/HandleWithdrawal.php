<?php

namespace App\Listeners;

use App\Events\FixedCostEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Withdrawal;
use Hekmatinasser\Verta\Verta;

class HandleWithdrawal {
    public function handle(FixedCostEvent $event) {
        $fixedCost = $event->fixedCost;
        $today = Verta::now();
        $month = $today->format('Y-m');

        if ($fixedCost->due_day == $today->day && !Withdrawal::where('fixed_cost_id', $fixedCost->id)->exists()) {
            Withdrawal::create([
                'user_id' => $fixedCost->user_id,
                'fixed_cost_id' => $fixedCost->id,
                'amount' => $fixedCost->amount,
                'expense' => $fixedCost->desc,
                'account_number' => $fixedCost->iban,
                'account_name' => $fixedCost->account_owner,
                'pay_method' => 'cash',
                'expense_type' => 'current',
                'expense_desc' => $fixedCost->category,
                'bank_id' => $fixedCost->bank_id,
                'vat' => $fixedCost->vat,
                'official' => $fixedCost->official
            ]);
        }
    }
}