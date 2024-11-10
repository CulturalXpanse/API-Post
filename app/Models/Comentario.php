<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    use HasFactory;

    protected $table = 'comentarios';

    protected $fillable = [
        'post_id',
        'user_id',
        'contenido',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
