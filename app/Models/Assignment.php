<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $table = 'assignments';
    protected $primaryKey = 'id';

    protected $fillable = [
        'institution_id', 
        'census_section_id', 
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id'); 
    }

    /* relacion con las secciones del censo */
    public function censusSection()
    {
        return $this->belongsTo(CensusSection::class, 'census_section_id'); 
    }
}