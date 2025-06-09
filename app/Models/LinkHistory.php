<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkHistory extends Model
{
    /** @use HasFactory<\Database\Factories\LinkHistoryFactory> */
    use HasFactory;

    protected $fillable  = [
        'link_id',
        'country_name',
        'ip_address',
        'user_agent',
        'browser',
        'os',
    ];

    public function link(){
        return $this->belongsTo(Link::class, 'link_id');
    }
}
