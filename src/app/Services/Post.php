<?php

namespace VCComponent\Laravel\Post\Services;

use VCComponent\Laravel\Post\Entities\Post as BaseModel;
use VCComponent\Laravel\Post\Services\PostQueryTrait;
use Illuminate\Support\Facades\Cache;

class Post
{
    use PostQueryTrait;

    public $query;
    protected $cache        = false;
    protected $cacheMinutes = 60;

    public function __construct()
    {
        if (isset(config('post.models')['post'])) {
            $model       = config('post.models.post');
            $this->query = new $model;
        } else {
            $this->query = new BaseModel;
        }

        if (config('post.cache')['enabled'] === true) {
            $this->cache     = true;
            $this->timeCache = config('post.cache')['minutes'] ? config('post.cache')['minutes'] * 60 : $this->cacheMinutes * 60;
        }
    }

    public function relatedPosts($post, $type, $value = 5)
    {
        if ($this->cache === true) {
            if (Cache::has('relatedPosts') && Cache::get('relatedPosts')->count() !== 0) {
                return Cache::get('relatedPosts');
            }
            return Cache::remember('relatedPosts', $this->timeCache, function () use ($post, $type, $value) {
                return $this->relatedPostsQuery($post, $type, $value);
            });
        }
        return $this->relatedPostsQuery($post, $type, $value);

    }

    public function getLatestPosts($type, $value = 5, $perPage = false)
    {
        if ($this->cache === true) {
            if (Cache::has('getLatestPosts') && Cache::get('getLatestPosts')->count() !== 0) {
                return Cache::get('getLatestPosts');
            }
            return Cache::remember('getLatestPosts', $this->timeCache, function () use ($type, $value, $perPage) {
                return $this->getLatestPostsQuery($type, $value, $perPage);
            });
        }
        return $this->getLatestPostsQuery($type, $value, $perPage);
    }

    public function hotNews($type, $value = 5)
    {
        if ($this->cache === true) {
            if (Cache::has('hotNews') && Cache::get('hotNews')->count() !== 0) {
                return Cache::get('hotNews');
            }
            return Cache::remember('hotNews', $this->timeCache, function () use ($type, $value) {
                return $this->hotNewsQuery($type, $value);
            });
        }
        return $this->hotNewsQuery($type, $value);
    }
}
