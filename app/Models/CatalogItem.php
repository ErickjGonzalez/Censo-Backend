<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'catalog_items';  
    protected $primaryKey = 'id';  

    protected $fillable = [
        'value',
        'label',
        'catalog_id', 
    ];

    /**
     * Relación con Catalog
     */
    public function catalog()
    {
        return $this->belongsTo(Catalog::class); 
    }
}