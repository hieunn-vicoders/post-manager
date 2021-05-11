<?php

namespace VCComponent\Laravel\Post\Entities;

use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use VCComponent\Laravel\Category\Traits\HasCategoriesTrait;
use VCComponent\Laravel\Post\Contracts\PostManagement;
use VCComponent\Laravel\Post\Contracts\PostSchema;
use VCComponent\Laravel\Post\Traits\PostManagementTrait;
use VCComponent\Laravel\Post\Traits\PostQueryTrait;
use VCComponent\Laravel\Post\Traits\PostSchemaTrait;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use VCComponent\Laravel\Post\Contracts\PostUtilitiesInterface;
use VCComponent\Laravel\Post\Traits\PostUtilitiesTrait;
//use VCComponent\Laravel\MediaManager\HasMediaTrait;
class Post extends Model implements HasMedia, Transformable, PostSchema, PostManagement, PostUtilitiesInterface
{
    use TransformableTrait, PostSchemaTrait, PostManagementTrait, PostQueryTrait, Sluggable, SluggableScopeHelpers, SoftDeletes, HasCategoriesTrait, HasMediaTrait, PostUtilitiesTrait;

    const STATUS_PENDING   = 0;
    const STATUS_PUBLISHED = 1;

    const HOT = 1;

    protected $fillable = [
        'title',
        'description',
        'content',
        'type',
        'order',
        'status',
        'published_date',
        'author_id',
        'thumbnail',
        'is_hot',
        'slug',
    ];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    public function schema()
    {
        return [
            'alt_image' => [
                'type' => 'string',
                'rule' => [],
            ],
            'images_url' => [
              'type' => 'json',
              'rule' => []
            ]
        ];
    }
    public function registerMediaConversions(Media $media = null)
    {
        $media_dimension = DB::table('media_dimensions')->where('model', 'post')->get();
        foreach ($media_dimension as $item) {
            $this->addMediaConversion($item->name)
            ->width($item->width)
            ->height($item->height)
            ->sharpen(10);
        }
    }
    public function getLimitDescription($limit = 30)
    {
        return Str::limit($this->description, $limit);
    }

    public function getLimitedName($limit = 10)
    {
        return Str::limit($this->name, $limit);
    }

    public function scopeHot($query)
    {
        return $query->where('is_hot', self::HOT);
    }
    public function categories()
    {
        return $this->morphToMany(Category::class, 'categoryable')->with('languages');
    }
}
