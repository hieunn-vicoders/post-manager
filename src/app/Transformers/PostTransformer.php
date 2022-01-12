<?php

namespace VCComponent\Laravel\Post\Transformers;

use League\Fractal\TransformerAbstract;
use VCComponent\Laravel\Category\Transformers\CategoryTransformer;
use VCComponent\Laravel\Comment\Transformers\CommentCountTransformer;
use VCComponent\Laravel\Comment\Transformers\CommentTransformer;
use VCComponent\Laravel\MediaManager\Transformers\MediaTransformer;
use VCComponent\Laravel\Tag\Transformers\TagTransformer;
use VCComponent\Laravel\Post\Transformers\PostMetaTransformer;
use VCComponent\Laravel\Post\Transformers\PostBlocksTransformer;

class PostTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'comments',
        'comment_count',
        'tags',
        'media',
        'categories',
        'postMetas',
        'postBlocks'
    ];

    public function __construct($includes = ['postBlocks'])
    {
        $this->setDefaultIncludes($includes);
    }

    public function transform($model)
    {
        $transform = [
            'id'             => (int) $model->id,
            'title'          => $model->title,
            'slug'           => $model->slug,
            'description'    => $model->description,
            'content'        => $model->content,
            'type'           => $model->type,
            'author_id'      => $model->author_id,
            'thumbnail'      => $model->thumbnail,
            'is_hot'         => $model->is_hot,
            'order'          => (int) $model->order,
            'status'         => (int) $model->status,
            'published_date' => $model->published_date,
            'editor_type'    => (int) $model->editor_type,
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

    public function includePostMetas($model)
    {
        return $this->collection($model->postMetas, new PostMetaTransformer());
    }
    public function includePostBlocks($model)
    {
        if ($model->postBlocks) {
            return $this->item($model->postBlocks, new PostBlocksTransformer());
        }
    }
}
