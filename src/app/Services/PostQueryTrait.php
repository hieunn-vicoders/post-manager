<?php

namespace VCComponent\Laravel\Post\Services;
trait PostQueryTrait
{
    public function getPostUrlQuery($post_id)
    {
        $post_query = $this->query->select('type', 'slug')->where('id', $post_id)->get();
        return '/'.$post_query[0]->type.'/'.$post_query[0]->slug;
    }
    public function getPostsQuery($post_type, $category_slug, $number_post, $order_by, $order, $paginate) {
        if ($paginate === true) {
            return $this->query->select()->where('type', $post_type)
                ->where('status', '1')
                ->orderBy($order_by, $order)
                ->whereHas('categories', function ($q) use ($category_slug) {
                    $q->where('slug', $category_slug);
                })->paginate($number_post);
        }
        return $this->query->select()->where('type', $post_type)
            ->where('status', '1')
            ->orderBy($order_by, $order)
            ->limit($number_post)
            ->whereHas('categories', function ($q) use ($category_slug) {
                $q->where('slug', $category_slug);
            })->get();

    }
    public function relatedPostsQuery($post_id, $post_type, $category_slug, $number_post, $order_by, $order)
    {

        if ($category_slug != null) {

            $related_post = $this->query->select()->where('type', $post_type)
            ->where('id', '<>', $post_id)
            ->where('status', '1')
            ->orderBy($order_by, $order)
            ->limit($number_post)
            ->whereHas('categories', function ($q) use ($category_slug) {
                $q->where('slug', $category_slug);
            })->get();
        }
        else {
            $related_post = $this->query->select()->where('type', $post_type)
            ->where('id', '<>', $post_id)
            ->where('status', '1')
            ->orderBy($order_by, $order)
            ->limit($number_post)->get();
        }

        return $related_post;
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
