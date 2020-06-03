<?php

namespace VCComponent\Laravel\Post\Services;

use Illuminate\Support\Str;
use VCComponent\Laravel\Post\Entities\Post as BaseModel;

class Post
{

    public $query;

    public function __construct()
    {
        $this->query = new BaseModel;
    }

    public function relatedPosts($post, $type)
    {
        $this->query = $this->query->whereType($type)
            ->where('id', '<>', $post->id)
            ->latest();

        return $this;
    }

    public function getLatestPosts($type)
    {
        $this->query = $this->query->whereType($type)
            ->latest();

        return $this;
    }

    public function toSql()
    {
        return $this->query->toSql();
    }

    public function get()
    {
        return $this->query->get();
    }

    public function paginate($perPage)
    {
        return $this->query->paginate($perPage);
    }

    public function limit($value)
    {
        $this->query = $this->query->limit($value);

        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        return $this->query->orderBy($column, $direction);
    }

    public function with($relations)
    {
        $this->query->with($relations);

        return $this;
    }

    public function first()
    {
        return $this->query->first();
    }

}
