<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdrawal extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'amount',
        'expense',
        'location',
        'user_desc',
        'user_file',
        'account_number',
        'account_name',
        'pay_method',
        'cheque_date',
        'cheque_id',
        'expense_type',
        'expense_desc',
        'counter_confirm',
        'official',
        'vat',
        'counter_desc',
        'bank',
        'manager_confirm',
        'manager_desc',
        'payment_confirm',
        'payment_desc',
        'payment_file',
        'payment_file2',
        'payment_file3',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function counter_status()
    {
        $action = "onclick='counter_form({$this->id})'";
        if($this->counter_confirm == -1)
            return "<span class='btn btn-danger' {$action}>رد شده</span>";
        elseif ($this->counter_confirm == 0)
            return "<span class='btn btn-info' {$action}>بررسی</span>";
        elseif ($this->counter_confirm == 1)
            return "<span class='btn btn-success' {$action}>تایید</span>";
        return '<span class="btn btn-warning">خطا</span>';
    }

    public function manager_status()
    {
        if($this->counter_confirm != 1)
            return '';
        $action = "onclick='manager_form({$this->id})'";
        if($this->manager_confirm == -1)
            return "<span class='btn btn-danger' {$action}>رد شده</span>";
        elseif ($this->manager_confirm == 0)
            return "<span class='btn btn-info' {$action}>بررسی</span>";
        elseif ($this->manager_confirm == 1)
            return "<span class='btn btn-success' {$action}>تایید</span>";
        return '<span class="btn btn-warning">خطا</span>';
    }

    public function payment_status()
    {
        if($this->manager_confirm != 1)
            return '';
        $action = "onclick='payment_form({$this->id})'";
        if($this->payment_confirm == -1)
            return "<span class='btn btn-danger' {$action}>رد شده</span>";
        elseif ($this->payment_confirm == 0)
            return "<span class='btn btn-info' {$action}>در حال انجام</span>";
        elseif ($this->payment_confirm == 1)
            return "<span class='btn btn-success' {$action}>تایید</span>";
        return '<span class="btn btn-warning">خطا</span>';
    }
}
