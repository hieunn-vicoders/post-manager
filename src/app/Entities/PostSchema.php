<?php

namespace VCComponent\Laravel\Post\Entities;

use Illuminate\Database\Eloquent\Model;

class PostSchema extends Model
{
    protected $fillable = [
        'name',
        'label',
        'schema_type_id',
        'schema_rule_id',
        'post_type',
        'post_id',
        'value'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function schemaType()
    {
        return $this->beLongsTo(PostSchemaType::class);
    }

    public function schemaRule()
    {
        return $this->beLongsTo(PostSchemaRule::class);
    }

    public function scopeOfPostType($query, $post_type)
    {
        return $query->where('post_type', $post_type);
    }
}
