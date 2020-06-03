<?php
namespace VCComponent\Laravel\Post\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use VCComponent\Laravel\Post\Entities\Post;

class PostMeta extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'post_meta';

    protected $fillable = [
        'key',
        'value',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
