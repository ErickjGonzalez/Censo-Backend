<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'modules';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
    ];

    public function catalogs()
    {
        return $this->hasMany(Catalog::class, 'module_id');
    }

    public function censusModules()
    {
        return $this->hasMany(CensusModule::class, 'module_id');
    }

    public function censuses()
    {
        return $this->belongsToMany(Census::class, 'census_modules', 'module_id', 'census_id');
    }
}
