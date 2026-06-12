<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comments';
    protected $primaryKey = 'id';

    protected $fillable = [
        'comment',
        'published_at',
        'answer_id', 
    ];

    public function answer()
    {
        return $this->belongsTo(Answer::class, 'answer_id'); 
    }
}