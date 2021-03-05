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
    public function getRelatedPostsQuery($post_id, $post_type, $number, $pagination, $order_by, $order, $is_hot,$status) {
        $query = $this->query->where('type', $post_type)
            ->where('id', '<>', $post_id)
            ->orderBy($order_by,$order)
            ->where('is_hot',$is_hot)
            ->where('status', $status)
            ->with('languages');
        if( $pagination === true) {
            return $query->paginate($number);
        }
        return $query->limit($number)->get();

    }
    public function getPostsQuery($post_type, $category_id, $number, $pagination,$order_by, $order,$is_hot, $status) {
        $query = $this->query->where('type', $post_type)
            ->orderBy($order_by,$order)
            ->where('is_hot',$is_hot)
            ->where('status', $status)
            ->with('languages');
        if ($category_id != '') {
                $query = $query->whereHas('categories', function ($q) use ($category_id) {
                    $q->where('categories.id', $category_id); });
            }
        if( $pagination === true) {
            return  $query->paginate($number);
        }
        return  $query->limit($number)->get();
    }

    public function getSearchResultQuery($key_word,$number,$post_type,$category_id,$pagination,$order_by,$order, $is_hot,$status) {

        $query = $this->query->orderBy($order_by,$order)
            ->where('is_hot',$is_hot)
            ->where('status', $status)
            ->with('languages');
            if ($post_type != '') {
                $query = $query->where('type', $post_type);
            }
            if ($category_id != '') {
                $query = $query->whereHas('categories', function ($q) use ($category_id) {
                    $q->where('categories.id', $category_id); });
            }
            $query->where('title', 'like', "%{$key_word}%")->orWhere('description', "%{$key_word}%");
        if( $pagination === true) {
            return $query->paginate($number);
        }
        return  $query->limit($number)->get();
    }
}
