<?php

namespace VCComponent\Laravel\Post\Traits;

use Exception;
use Illuminate\Support\Str;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;

trait PostQueryTrait
{
    /**
     * Scope a query to only include posts of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get post collection by type
     *
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByType($type = 'posts')
    {
        return self::ofType($type)->get();
    }

    /**
     * Get post by type with pagination
     *
     * @param string $type
     * @param int $per_page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function getByTypeWithPagination($type = 'posts', $per_page = 15)
    {
        return self::ofType($type)->paginate($per_page);
    }

    /**
     * Get post by type and id
     *
     * @param string $type
     * @param int $id
     * @return self
     */
    public static function findByType($id, $type = 'posts')
    {
        try {
            return self::ofType($type)->where('id', $id)->firstOrFail();
        } catch (Exception $e) {
            throw new NotFoundException(Str::title($type));
        }
    }

    /**
     * Get post meta data
     *
     * @param string $key
     * @return string
     */
    public function getMetaField($key)
    {
        if (!$this->postMetas->count()) {
            throw new NotFoundException($key . ' field');
        }

        try {
            return $this->postMetas->where('key', $key)->first()->value;
        } catch (Exception $e) {
            throw new NotFoundException($key . ' field');
        }
    }

    /**
     * Scope a query to only include hot posts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsHot($query)
    {
        return $query->where('is_hot', 1);
    }

    /**
     * Scope a query to only include publisded posts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsPublished($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to sort posts by order column.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param string $order
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortByOrder($query, $order = 'desc')
    {
        return $query->orderBy('order', $order);
    }

    /**
     * Scope a query to sort posts by published_date column.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param string $order
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortByPublishedDate($query, $order = 'desc')
    {
        return $query->orderBy('published_date', $order);
    }

    /**
     * Scope a query to search posts of given key word. This function is also able to scope with categories, or tags.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param string $search
     * @param boolean $with_category
     * @param boolean $with_tag
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfSearching($query, $search, $with_category = false, $with_tag = false)
    {
        $query = $query->where(function ($q) use ($search) {
            $q->orWhere('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")->orWhere('content', 'like', "%{$search}%");
        });

        if ($with_category && method_exists($this, 'categories')) {
            $query->whereHas('categories', function ($q) use ($search) {
                $q->whereIn('name', 'like', "%{$search}%")->where('status', 1);
            });
        }

        if ($with_tag && method_exists($this, 'tags')) {
            $query->whereHas('tags', function ($q) use ($search) {
                $q->whereIn('name', 'like', "%{$search}%")->where('status', 1);
            });
        }

        return $query;
    }

    /**
     * Scope a query to include related posts. This function is also able to scope with categories, or tags.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param \VCComponent\Laravel\Post\Entities\Post $post
     * @param boolean $with_category
     * @param boolean $with_tag
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfRelatingTo($query, $post, $with_category = false, $with_tag = false)
    {
        if ($post) {
            $query = $query->where('id', '<>', $post->id);

            if ($with_category && count($post->categories)) {
                $query = $query->ofCategoriesBySlug($post->categories->pluck('slug')->toArray());
            }

            if ($with_tag && count($post->tags)) {
                $query = $query->ofTagsBySlug($post->tags->pluck('slug')->toArray());
            }
        }
        return $query;
    }
}
