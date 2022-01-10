<?php
namespace VCComponent\Laravel\Post\Entities;

use Illuminate\Database\Eloquent\Model;

class PostBlocks extends Model
{

    protected $fillable = [
        'post_id',
        'blocks',
    ];

    public function posts()
    {
        return $this->beLongsTo(Post::class);
    }
}
