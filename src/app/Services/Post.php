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
    public function get_post_images($post_id, $image_dimension = '') {
        if ($this->cache === true) {
            if (Cache::has('get_post_images') && Cache::get('get_post_images')->count() !== 0) {
                return Cache::get('get_post_images');
            }
            return Cache::remember('get_post_images', $this->timeCache, function () use ($post_id, $image_dimension) {
                return $this->getPostImagesQuery($post_id, $image_dimension);
            });
        }
        return $this->getPostImagesQuery($post_id, $image_dimension);
    }
    public function get_post_by_id($post_id) {
        if ($this->cache === true) {
            if (Cache::has('get_post_by_id') && Cache::get('get_post_by_id')->count() !== 0) {
                return Cache::get('get_post_by_id');
            }
            return Cache::remember('get_post_by_id', $this->timeCache, function () use ($post_id) {
                return $this->getPostByIDQuery($post_id);
            });
        }
        return $this->getPostByIDQuery($post_id);
    }
    public function get_post_title($post_id) {
        if ($this->cache === true) {
            if (Cache::has('get_post_title') && Cache::get('get_post_title')->count() !== 0) {
                return Cache::get('get_post_title');
            }
            return Cache::remember('get_post_title', $this->timeCache, function () use ($post_id) {
                return $this->getPostTitleQuery($post_id);
            });
        }
        return $this->getPostTitleQuery($post_id);
    }
    public function get_post_content($post_id) {
        if ($this->cache === true) {
            if (Cache::has('get_post_content') && Cache::get('get_post_content')->count() !== 0) {
                return Cache::get('get_post_content');
            }
            return Cache::remember('get_post_content', $this->timeCache, function () use ($post_id) {
                return $this->getPostContentQuery($post_id);
            });
        }
        return $this->getPostContentQuery($post_id);
    }

    public function get_post_slug($post_id) {
        if ($this->cache === true) {
            if (Cache::has('get_post_slug') && Cache::get('get_post_slug')->count() !== 0) {
                return Cache::get('get_post_slug');
            }
            return Cache::remember('get_post_slug', $this->timeCache, function () use ($post_id) {
                return $this->getPostSlugQuery($post_id);
            });
        }
        return $this->getPostSlugQuery($post_id);
    }
    public function get_post_description($post_id) {
        if ($this->cache === true) {
            if (Cache::has('get_post_description') && Cache::get('get_post_description')->count() !== 0) {
                return Cache::get('get_post_description');
            }
            return Cache::remember('get_post_description', $this->timeCache, function () use ($post_id) {
                return $this->getPostDescriptionQuery($post_id);
            });
        }
        return $this->getPostDescriptionQuery($post_id);
    }
    public function get_post_type($post_id) {
        if ($this->cache === true) {
            if (Cache::has('get_post_type') && Cache::get('get_post_type')->count() !== 0) {
                return Cache::get('get_post_type');
            }
            return Cache::remember('get_post_type', $this->timeCache, function () use ($post_id) {
                return $this->getPostTypeQuery($post_id);
            });
        }
        return $this->getPostTypeQuery($post_id);
    }

    public function get_post_status($post_id) {
        if ($this->cache === true) {
            if (Cache::has('get_post_status') && Cache::get('get_post_status')->count() !== 0) {
                return Cache::get('get_post_status');
            }
            return Cache::remember('get_post_status', $this->timeCache, function () use ($post_id) {
                return $this->getPostStatusQuery($post_id);
            });
        }
        return $this->getPostStatusQuery($post_id);
    }
    public function has_post_featured($post_id) {
        if ($this->cache === true) {
            if (Cache::has('has_post_featured') && Cache::get('has_post_featured')->count() !== 0) {
                return Cache::get('has_post_featured');
            }
            return Cache::remember('has_post_featured', $this->timeCache, function () use ($post_id) {
                return $this->hasPostFeaturedQuery($post_id);
            });
        }
        return $this->hasPostFeaturedQuery($post_id);
    }
    public function get_post_datetime($post_id, $date_format="d-m-y") {
        if ($this->cache === true) {
            if (Cache::has('get_post_datetime') && Cache::get('get_post_datetime')->count() !== 0) {
                return Cache::get('get_post_datetime');
            }
            return Cache::remember('get_post_datetime', $this->timeCache, function () use ($post_id, $date_format) {
                return $this->getPostDateTimeQuery($post_id, $date_format);
            });
        }
        return $this->getPostDateTimeQuery($post_id, $date_format);
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
    public function get_posts_featured($is_hot, $post_type, $category_slug = null, $number_post = 10, $order_by = 'id', $order = 'desc', $paginate = false)
    {
        if ($this->cache === true) {
            if (Cache::has('get_posts_featured') && Cache::get('get_posts_featured')->count() !== 0) {
                return Cache::get('get_posts_featured');
            }
            return Cache::remember('get_posts_featured', $this->timeCache, function () use ($is_hot, $post_type, $category_slug, $number_post,$order_by, $order, $paginate) {
                return $this->getPostsFeaturedQuery($is_hot, $post_type, $category_slug, $number_post,$order_by, $order, $paginate);
            });
        }
        return $this->getPostsFeaturedQuery($is_hot, $post_type, $category_slug, $number_post,$order_by, $order, $paginate);
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
