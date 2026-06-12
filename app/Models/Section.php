<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sections';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'instructions',
        'is_extra',
    ];

    public function censusSections()
    {
        return $this->hasMany(CensusSection::class, 'section_id');
    }

    public function censusModules()
    {
        return $this->belongsToMany(CensusModule::class, 'census_sections', 'section_id', 'census_module_id');
    }

}

