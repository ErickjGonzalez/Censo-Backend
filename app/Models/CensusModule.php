<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CensusModule extends Model
{
    //
    use HasFactory;
    protected $table = 'census_modules';
    protected $fillable = [
        'census_id',
        'index_id',
        'module_id',
    ];

    public function census()
    {
        return $this->belongsTo(Census::class, 'census_id');
    }

    public function index()
    {
        return $this->belongsTo(Index::class, 'index_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function censusSections()
    {
        return $this->hasMany(CensusSection::class, 'census_module_id');
    }
}
