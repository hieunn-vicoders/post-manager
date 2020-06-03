<?php

namespace VCComponent\Laravel\Post\Entities;

use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use VCComponent\Laravel\Category\Traits\HasCategoriesTrait;
use VCComponent\Laravel\MediaManager\HasMediaTrait;
use VCComponent\Laravel\Post\Contracts\PostManagement;
use VCComponent\Laravel\Post\Contracts\PostSchema;
use VCComponent\Laravel\Post\Traits\PostManagementTrait;
use VCComponent\Laravel\Post\Traits\PostQueryTrait;
use VCComponent\Laravel\Post\Traits\PostSchemaTrait;
use VCComponent\Laravel\Tag\Traits\HasTagsTraits;

class Post extends Model implements Transformable, PostSchema, PostManagement
{
    use TransformableTrait, PostSchemaTrait, PostManagementTrait, PostQueryTrait, Sluggable, SluggableScopeHelpers, SoftDeletes, HasTagsTraits, HasCategoriesTrait;

    const STATUS_PENDING   = 0;
    const STATUS_PUBLISHED = 1;

    protected $fillable = [
        'title',
        'description',
        'content',
        'type',
        'order',
        'status',
        'published_date',
        'author_id',
    ];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }
}
