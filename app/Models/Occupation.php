<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Occupation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'occupations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'occupation_id'); 
    }

}     