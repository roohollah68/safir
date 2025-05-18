<?php

namespace App\Listeners;

use App\Events\FixedCostEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Withdrawal;
use Hekmatinasser\Verta\Verta;
use Carbon\Carbon;

class HandleWithdrawal implements ShouldQueue {
    public function handle(FixedCostEvent $event) {
        $fixedCost = $event->fixedCost;
        $todayVerta = Verta::now();

        if ($fixedCost->due_day != $todayVerta->day) {
            return;
        }

        $currentPersianYearMonth = $todayVerta->format('Y-m');
        $hasWithdrawalThisMonth = Withdrawal::where('fixed_cost_id', $fixedCost->id)
            ->get()
            ->filter(function ($withdrawal) use ($currentPersianYearMonth) {
                $createdAtVerta = Verta::parse($withdrawal->created_at);
                return $createdAtVerta->format('Y-m') === $currentPersianYearMonth;
            })
            ->isNotEmpty();

        if (!$hasWithdrawalThisMonth) {
            Withdrawal::create([
                'user_id' => $fixedCost->user_id,
                'fixed_cost_id' => $fixedCost->id,
                'amount' => $fixedCost->amount,
                'expense' => $fixedCost->desc,
                'account_number' => $fixedCost->iban,
                'account_name' => $fixedCost->account_owner,
                'pay_method' => 'cash',
                'expense_type' => 'current',
                'expense_desc' => \Config::get('expense_type.current.' . $fixedCost->category),
                'bank_id' => $fixedCost->bank_id,
                'vat' => $fixedCost->vat,
                'official' => $fixedCost->official,
                'supplier_id' => $fixedCost->supplier_id,
                'location' => $fixedCost->location,
            ]);
        }
    }
}