<?php
namespace VCComponent\Laravel\Post\Entities;

use Illuminate\Database\Eloquent\Model;

class PostBlock extends Model
{

    protected $fillable = [
        'post_id',
        'block',
    ];

    public function post()
    {
        return $this->beLongsTo(Post::class);
    }
}
