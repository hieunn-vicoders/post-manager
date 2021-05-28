<?php

namespace VCComponent\Laravel\Post\Services;

trait PostQueryTrait
{
    public function relatedPostsQuery($post, $type, $value)
    {
        return $this->query->select('thumbnail', 'title', 'slug', 'description', 'id')->where('type', $type)
            ->where('id', '<>', $post->id)
            ->where('status', '1')
            ->latest()->limit($value)->get();
    }

    public function getLatestPostsQuery($type, $value, $perPage = false)
    {
        if ($perPage === true) {
            return $this->query->select('thumbnail', 'title', 'slug', 'description', 'id')->where('type', $type)
                ->where('status', '1')
                ->latest()->paginate($value);
        }
        return $this->query->select('thumbnail', 'title', 'slug', 'description', 'id')->where('type', $type)
            ->where('status', '1')
            ->latest()->limit($value)->get();
    }

    public function hotNewsQuery($type, $value)
    {
        return $this->query->where('type', $type)->whereHas('postMetas', function ($query) {
            $query->where('value', 1);
        })->latest()->limit($value)->get();
    }

    public function hotPostsQuery($post, $type, $value) {
        return $this->query->select('thumbnail', 'title', 'slug', 'description', 'id')->where('type', $type)
        ->where('id', '<>', $post->id)
        ->where('status', '1')
        ->where('is_hot', '1')
        ->latest()->limit($value)->get();
    }
}
