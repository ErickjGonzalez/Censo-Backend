<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccessRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'access_request'; 
    protected $primaryKey = 'id';

    protected $fillable = [
        'email',
        'attemps',    
        'date',
        'ip_adress',   
        'valid',
    ];

    protected $casts = [
        'date'  => 'datetime',
        'valid' => 'integer', 
    ];
}


