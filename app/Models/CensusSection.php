<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CensusSection extends Model
{
    use HasFactory;
    protected $table = 'census_sections';
    protected $fillable = [
        'census_module_id',
        'index_id',
        'section_id',
    ];

    public function censusModule()
    {
        return $this->belongsTo(CensusModule::class, 'census_module_id');
    }

    public function index()
    {
        return $this->belongsTo(Index::class, 'index_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /* relacion para poder relacionar con las preguntas del censo */
    public function censusQuestions()
    {
        return $this->hasMany(CensusQuestion::class, 'census_section_id');
    }

    /* relacion con la de assigments */
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'census_section_id');
    }

    public function institutions()
    {
        return $this->belongsToMany(Institution::class, 'assignments', 'census_section_id', 'institution_id');
    }
}