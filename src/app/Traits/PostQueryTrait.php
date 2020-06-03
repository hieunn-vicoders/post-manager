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
            return $this->postMetas()->where('key', $key)->first()->value;
        } catch (Exception $e) {
            throw new NotFoundException($key . ' field');
        }
    }
}
