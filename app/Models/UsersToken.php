<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsersToken extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'users_tokens';
    protected $primaryKey = 'id';

    protected $fillable = [
        'email',
        'token',
        'type',        
        'expiration',
        'status',      
    ];

    
    protected $casts = [
        'expiration' => 'datetime',
        'status' => 'integer',
    ];
}

