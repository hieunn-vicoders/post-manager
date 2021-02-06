<?php

namespace VCComponent\Laravel\Post\Entities;

use Illuminate\Database\Eloquent\Model;
use VCComponent\Laravel\Post\Entities\PostSchema;

class PostSchemaType extends Model
{
    protected $fillable = [
        'id',
        'name',
    ];

    public function schema()
    {
        return $this->hasMany(PostSchema::class, 'schema_type_id');
    }
}
