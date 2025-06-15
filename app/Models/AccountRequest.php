<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountRequest extends Model
{
    /** @use HasFactory<\Database\Factories\AccountRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'expired',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
