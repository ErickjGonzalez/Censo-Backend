<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'institutions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'geocode',
        'municipality',
        'typeinst',
        'lat',      
        'lon',      
    ];

    protected $casts = [
        'lat' => 'float',   
        'lon' => 'float',   
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'institution_id'); 
    }

    /* Relacion en la tabla asigments */
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'institution_id');
    }
}


