<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Catalog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'catalogs';  
    protected $primaryKey = 'id';  

    protected $fillable = [
        'name',
        'slug',
        'module_id', 
    ];

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id'); 
    }

    public function items()
    {
        return $this->hasMany(CatalogItem::class); 
    }
}