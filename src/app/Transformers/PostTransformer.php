<?php

namespace VCComponent\Laravel\Post\Transformers;

use League\Fractal\TransformerAbstract;
use VCComponent\Laravel\Category\Transformers\CategoryTransformer;
use VCComponent\Laravel\Comment\Transformers\CommentCountTransformer;
use VCComponent\Laravel\Comment\Transformers\CommentTransformer;
use VCComponent\Laravel\MediaManager\Transformers\MediaTransformer;
use VCComponent\Laravel\Tag\Transformers\TagTransformer;

class PostTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'comments',
        'comment_count',
        'tags',
        'media',
        'categories',
    ];

    public function __construct($includes = [])
    {
        $this->setDefaultIncludes($includes);
    }

    public function transform($model)
    {
        $author_name = $this->getNameAuthor($model);

        $transform = [
            'id'             => (int) $model->id,
            'title'          => $model->title,
            'slug'           => $model->slug,
            'description'    => $model->description,
            'content'        => $model->content,
            'type'           => $model->type,
            'author'         => $author_name,
            'thumbnail'      => $model->thumbnail,
            'order'          => (int) $model->order,
            'status'         => (int) $model->status,
            'published_date' => $model->published_date,
        ];

        if ($model->postMetas->count()) {
            foreach ($model->postMetas as $item) {
                $transform[$item['key']] = $item['value'];
            }
        }

        $transform['timestamps'] = [
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];

        return $transform;
    }

    public function includeCommentCount($model)
    {
        return $this->collection($model->commentCount, new CommentCountTransformer());
    }

    public function includeTags($model)
    {
        if ($model->tags) {
            return $this->collection($model->tags, new TagTransformer());
        }
    }

    public function includeMedia($model)
    {
        if ($model->media) {
            return $this->collection($model->media, new MediaTransformer());
        }
    }

    public function includeCategories($model)
    {
        if ($model->categories) {
            return $this->collection($model->categories, new CategoryTransformer());
        }
    }

    public function includeComments($model)
    {
        return $this->collection($model->comments, new CommentTransformer());
    }

    protected function getNameAuthor($model)
    {
        $author = $model->user;
        $name   = null;
        if ($author != null) {
            if ($author->first_name != null && $author->last_name != null) {
                $name = $author->first_name . ' ' . $author->last_name;
            } else if ($author->first_name != null || $author->last_name != null) {
                $name = $author->first_name ? $author->first_name : $author->last_name;
            } else {
                $name = $author->username;
            }
        }
        return $name;
    }
}
