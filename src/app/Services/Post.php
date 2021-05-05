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
    public function get_post_url($post_id) {
        if ($this->cache === true) {
            if (Cache::has('get_post_url') && Cache::get('get_post_url')->count() !== 0) {
                return Cache::get('get_post_url');
            }
            return Cache::remember('get_post_url', $this->timeCache, function () use ($post_id) {
                return $this->getPostUrlQuery($post_id);
            });
        }
        return $this->getPostUrlQuery($post_id);
    }
    public function get_posts($post_type, $category_slug = null, $number_post = 10, $order_by = 'id', $order = 'desc', $paginate = false)
    {
        if ($this->cache === true) {
            if (Cache::has('get_posts') && Cache::get('get_posts')->count() !== 0) {
                return Cache::get('get_posts');
            }
            return Cache::remember('get_posts', $this->timeCache, function () use ($post_type, $category_slug, $number_post,$order_by, $order, $paginate) {
                return $this->getPostsQuery($post_type, $category_slug, $number_post,$order_by, $order, $paginate);
            });
        }
        return $this->getPostsQuery($post_type, $category_slug, $number_post,$order_by, $order, $paginate);
    }
    public function get_related_posts($post_id, $post_type, $category_slug = null, $number_post = 4, $order_by = 'id', $order = 'desc')
    {
        if ($this->cache === true) {
            if (Cache::has('get_related_posts') && Cache::get('get_related_posts')->count() !== 0) {
                return Cache::get('get_related_posts');
            }
            return Cache::remember('get_related_posts', $this->timeCache, function () use ($post_id, $post_type, $category_slug, $number_post, $order_by, $order) {
                return $this->relatedPostsQuery($post_id, $post_type, $category_slug, $number_post, $order_by, $order);
            });
        }
        return $this->relatedPostsQuery($post_id, $post_type, $category_slug, $number_post, $order_by, $order);

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
