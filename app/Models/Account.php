<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'accounts';

    protected $fillable = [
        'id', 'party_type', 'party_id', 'debit', 'credit', 'comments', 'bank_id', 'cheque', 'discount'
    ];

    protected $with  = [ 'bank' ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function partyable()
    {
        return $this->morphTo();
    }

    public function bank()
    {
        return $this->belongsTo( Bank::class );
    }
}
