<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'questions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'instructions',
        'commentaries', 
        'question_structure',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function censusQuestions()
    {
        return $this->hasMany(CensusQuestion::class, 'question_id');
    }

    public function censusSections()
    {
        return $this->belongsToMany(CensusSection::class, 'census_questions', 'question_id', 'census_section_id');
    }
}

