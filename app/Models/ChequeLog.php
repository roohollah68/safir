<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChequeLog extends Model
{
    protected $guarded = [];

    const UPDATED_AT = null;
    
    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}