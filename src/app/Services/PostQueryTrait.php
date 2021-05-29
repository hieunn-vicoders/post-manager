<?php

namespace VCComponent\Laravel\Post\Services;
trait PostQueryTrait
{
    public function getPostImagesQuery($post_id, $image_dimension)
    {
        $posts = $this->query->select()->where('id', $post_id)->first();
        $post=[];
        $count = 0;
        foreach ($posts->getMedia() as $item) {
            $post[$count] = $item->getUrl($image_dimension);
            $count++;
        }

        return $post;
    }
    public function getPostByIDQuery($post_id)
    {
        $post = $this->query->select()->where('id', $post_id)->first();
        return $post;
    }
    public function getPostTitleQuery($post_id)
    {
        $post = $this->query->select()->where('id', $post_id)->first();
        return $post->title;
    }

    public function getPostContentQuery($post_id)
    {
        $post = $this->query->select()->where('id', $post_id)->first();
        return $post->content;
    }

    public function getPostSlugQuery($post_id)
    {
        $post = $this->query->select()->where('id', $post_id)->first();
        return $post->slug;
    }

    public function getPostDescriptionQuery($post_id)
    {
        $post = $this->query->select()->where('id', $post_id)->first();
        return $post->description;
    }

    public function getPostTypeQuery($post_id)
    {
        $post = $this->query->select()->where('id', $post_id)->first();
        return $post->type;
    }

    public function getPostStatusQuery($post_id)
    {
        $post_status = $this->query->select()->where('id', $post_id)->first();
        return $post_status->status;
    }
    public function hasPostFeaturedQuery($post_id)
    {
        $post_hot = $this->query->select()->where('id', $post_id)->where('is_hot', '1')->first();
        $post_hot != null ? $hot = 'true': $hot = 'false';

        return $hot;
    }
    public function getPostDateTimeQuery($post_id, $date_format)
    {
        $post_date = $this->query->select('published_date')->where('id', $post_id)->first();
        return date($date_format, strtotime($post_date->published_date));
    }

    public function getPostUrlQuery($post_id)
    {
        $post_query = $this->query->select('type', 'slug')->where('id', $post_id)->get();
        return '/'.$post_query[0]->type.'/'.$post_query[0]->slug;
    }
    public function getPostsFeaturedQuery($is_hot, $post_type, $category_slug, $number_post, $order_by, $order, $paginate) {
        $is_hot == 1 ? $hot = 'true' : $hot = 'false';
        if ($paginate === true) {
            return $this->query->select()->where('type', $post_type)
                ->where('is_hot', $hot)
                ->where('status', '1')
                ->orderBy($order_by, $order)
                ->whereHas('categories', function ($q) use ($category_slug) {
                    $q->where('slug', $category_slug);
                })->paginate($number_post);
        }
        else {
            return $this->query->select()->where('type', $post_type)
                ->where('is_hot', $hot)
                ->where('status', '1')
                ->orderBy($order_by, $order)
                ->limit($number_post)
                ->whereHas('categories', function ($q) use ($category_slug) {
                    $q->where('slug', $category_slug);
                })->get();
        }

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
        else {
            return $this->query->select()->where('type', $post_type)
                ->where('status', '1')
                ->orderBy($order_by, $order)
                ->limit($number_post)
                ->whereHas('categories', function ($q) use ($category_slug) {
                    $q->where('slug', $category_slug);
                })->get();
        }

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
