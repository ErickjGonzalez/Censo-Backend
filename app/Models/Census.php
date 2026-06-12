<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Census extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'censuses';  
    protected $primaryKey = 'id';  

    protected $fillable = [
        'name',
        'description',
        'init_date',
        'deadline',
    ];

    protected $dates = [
        'init_date',
        'deadline',
        'deleted_at',
    ];

    public function censusModules()
    {
        return $this->hasMany(CensusModule::class, 'census_id');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'census_modules', 'census_id', 'module_id');
    }
}