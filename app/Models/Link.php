<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    /** @use HasFactory<\Database\Factories\LinkFactory> */
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'domain_id',
        'ip_address',
        'custom_name',
        'destination',
        'short_name',
        'available',
    ];

    public function link_histories(){
        return $this->hasMany(LinkHistory::class, 'link_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function domain(){
        return $this->belongsTo(Domain::class, 'domain_id');
    }
}
