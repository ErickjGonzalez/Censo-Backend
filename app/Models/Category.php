<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    //
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['name'];

    public function questions()
    {
        return $this->hasMany(Question::class, 'category_id', 'id');
    }
}
