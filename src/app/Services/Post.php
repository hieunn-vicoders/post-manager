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
       public function getRelatedPosts($post_id, $post_type, $number, $pagination = false, $order_by="id", $order="desc", $is_hot = 0, $status = 1)
    {
        if ($this->cache === true) {
            if (Cache::has('getRelatedPosts') && Cache::get('getRelatedPosts')->count() !== 0) {
                return Cache::get('getRelatedPosts');
            }
            return Cache::remember('getRelatedPosts', $this->timeCache, function () use ($post_id, $post_type, $number, $pagination, $order_by, $order, $is_hot, $status) {
                return $this->getRelatedPostsQuery($post_id, $post_type, $number, $pagination, $order_by, $order, $is_hot, $status);
            });
        }
        return $this->getRelatedPostsQuery($post_id, $post_type, $number, $pagination, $order_by, $order, $is_hot, $status);
    }

     public function getPosts($post_type,$category_id, $number, $pagination = false, $order_by="id", $order="desc", $is_hot = 0, $status = 1)
    {
        if ($this->cache === true) {
            if (Cache::has('getPosts') && Cache::get('getPosts')->count() !== 0) {
                return Cache::get('getPosts');
            }
            return Cache::remember('getPosts', $this->timeCache, function () use ($post_type, $category_id, $number, $pagination,$order_by, $order,$is_hot, $status) {
                return $this->getPostsQuery($post_type, $category_id, $number, $pagination,$order_by, $order,$is_hot, $status);
            });
        }
        return $this->getPostsQuery($post_type, $category_id, $number, $pagination,$order_by, $order,$is_hot, $status);
    }

     public function getSearchResult($key_word,$number,$post_type,$category_id,$pagination= "false",$order_by="id", $order="desc", $is_hot = false, $status =1)
    {
        if ($this->cache === true) {
            if (Cache::has('getSearchResult') && Cache::get('getSearchResult')->count() !== 0) {
                return Cache::get('getSearchResult');
            }
            return Cache::remember('getSearchResult', $this->timeCache, function () use ($key_word,$number,$post_type,$category_id,$pagination,$order_by,$order, $is_hot,$status) {
                return $this->getSearchResultQuery($key_word,$number,$post_type,$category_id,$pagination,$order_by,$order, $is_hot,$status);
            });
        }
        return $this->getSearchResultQuery($key_word,$number,$post_type,$category_id,$pagination,$order_by,$order, $is_hot,$status);
    }

}
