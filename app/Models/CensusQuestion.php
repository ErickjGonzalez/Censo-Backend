<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CensusQuestion extends Model
{
    //
    use HasFactory;
    protected $table = 'census_questions';
    protected $fillable = [
        'census_section_id',
        'index_id',
        'question_id',
    ];
    public function censusSection()
    {
        return $this->belongsTo(CensusSection::class, 'census_section_id');
    }
    public function index()
    {
        return $this->belongsTo(Index::class, 'index_id');
    }
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'census_question_id');
    }
}