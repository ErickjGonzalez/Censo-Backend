<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Index extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'indexs';  
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
    ];
    public function censusModules()
    {
        return $this->hasMany(CensusModule::class, 'index_id');
    }

    public function censusSections()
    {
        return $this->hasMany(CensusSection::class, 'index_id');
    }

    public function censusQuestions()
    {
        return $this->hasMany(CensusQuestion::class, 'index_id');
    }    
}