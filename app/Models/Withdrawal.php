<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdrawal extends Model
{
    use SoftDeletes;
    protected $guarded = [
        'id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
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
        elseif ($this->counter_confirm == 2)
            return "<span class='btn btn-secondary' {$action}>تعویق</span>";
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

    public function recipient_status()
    {
        if($this->payment_confirm != 1)
            return '';
        $action = "onclick='recipient_form({$this->id})'";
        if($this->recipient_confirm == -1)
            return "<span class='btn btn-danger' {$action}>رد شده</span>";
        elseif ($this->recipient_confirm == 0)
            return "<span class='btn btn-info' {$action}>بررسی</span>";
        elseif ($this->recipient_confirm == 1)
            return "<span class='btn btn-success' {$action}>تایید</span>";
        return '<span class="btn btn-warning">خطا</span>';
    }
}
