<?php

namespace VCComponent\Laravel\Post\Repositories;

use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\App;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Repositories\PostRepository;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;
use Exception;
use Illuminate\Support\Str;
/**
 * Class PostRepositoryEloquent.
 *
 * @package namespace VCComponent\Laravel\Post\Repositories;
 */
class PostRepositoryEloquent extends BaseRepository implements PostRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        if (isset(config('post.models')['post'])) {
            return config('post.models.post');
        } else {
            return Post::class;
        }
    }

    public function getEntity()
    {
        return $this->model;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Find data by a fields
     *
     * @param string $type
     * @param int $id
     * @return self
     */
    public function findByField($field, $value = null, $columns = ['*'], $type = 'posts')
    {
        try {
            return $this->model->ofType($type)->where($field, '=', $value)->get($columns);
        } catch (Exception $e) {
            throw new NotFoundException($e);
        }


    }

    /**
     * Find data by multiple fields
     *
     * @param string $type
     * @param int $id
     * @return self
     */
    public function findWhere(array $where, $columns = ['*'], $type = 'posts') {
        try {
            return $this->model->ofType($type)->where($where)->get($columns);
        } catch (Exception $e) {
            throw new NotFoundException($e);
        }
    }

    public function getPostsAll( $type = 'posts') {
        try {
            return $this->model->ofType($type)->get();
        } catch (Exception $e) {
            throw new NotFoundException($e);
        }
    }

    public function getWithPagination($filters, $type)
    {
        $request = App::make(Request::class);
        $query   = $this->getEntity();

        $items = App::make(Pipeline::class)
            ->send($query)
            ->through($filters)
            ->then(function ($content) use ($request, $type) {
                $content  = $content->where('type', $type);
                $per_page = $request->has('per_page') ? (int) $request->get('per_page') : 15;
                $posts    = $content->paginate($per_page);
                return $posts;
            });

        return $items;
    }

    public function getPostByID( $post_id) {
        return $this->model->where('id', $post_id)->first();
    }
    public function getPostMedias( $post_id, $image_dimension) {
        $post = $this->model->where('id', $post_id)->first();
        $images=[];
        $count = 0;
        foreach ($post->getMedia() as $item) {
            $images[$count] = $item->getUrl($image_dimension);
            $count++;
        }

        return $images;
    }
    public function getPostUrl($post_id){
        $post_query = $this->model->where('id', $post_id)->first();
        return '/'.$post_query->type.'/'.$post_query->slug;
    }

    public function restore($id)
    {

        $post = $this->model->where('id', $id)->restore();
    }

    public function bulkRestore($ids)
    {

        $post = $this->model->whereIn("id", $ids)->restore();
    }
    public function deleteTrash($id)
    {

        $post = $this->model->where("id", $id)->forceDelete();
    }

    public function forceDelete($id)
    {

        $post = $this->model->where("id", $id)->forceDelete();
    }

    public function bulkDeleteTrash($ids)
    {

        $post = $this->model->whereIn('id', $ids)->forceDelete();
    }

    public function bulkUpdateStatus($request)
    {

        $data  = $request->all();
        $posts = $this->findWhereIn("id", $request->ids);

        if (count($request->ids) > $posts->count()) {
            throw new NotFoundException("Post");
        }

        $result = $this->model->whereIn("id", $request->ids)->update(['status' => $data['status']]);

        return $result;
    }

    public function getRelatedPosts($post_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']) {
        $query = $this->getEntity()
            ->where('id', '<>', $post_id)
            ->where($where)
            ->orderBy($order_by,$order)
            ->with('languages');
        if($number > 0) {
            return $query->limit($number)->get($columns);
        }
        return $query->get($columns);

    }
    public function getRelatedPostsPaginate($post_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']) {
        $query = $this->getEntity()
            ->where('id', '<>', $post_id)
            ->where($where)
            ->orderBy($order_by,$order)
            ->with('languages');
        return $query->paginate($number);

    }
    public function getPostsWithCategory($category_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']) {
        $query = $this->getEntity()->where($where)
            ->orderBy($order_by,$order)
            ->with('languages');
            $query = $query->whereHas('categories', function ($q) use ($category_id) {
                $q->where('categories.id', $category_id); });
        if($number > 0) {
            return $query->limit($number)->get($columns);
        }
        return $query->get($columns);
    }
    public function getPostsWithCategoryPaginate($category_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']) {
        $query = $this->getEntity()->select($columns)
            ->where($where)
            ->orderBy($order_by,$order)
            ->with('languages');
            $query = $query->whereHas('categories', function ($q) use ($category_id) {
                $q->where('categories.id', $category_id); });
        return $query->paginate($number);
    }
    public function getSearchResult($key_word,array $list_field = ['title'],array $where, $category_id = 0,$number = 10,$order_by = 'order', $order = 'asc', $columns = ['*']) {
        $query = $this->getEntity()->where(function($q) use($list_field , $key_word) {
            foreach ($list_field  as $field)
                $q->orWhere($field, 'like', "%{$key_word}%");
        });
        $query->where($where)
            ->orderBy($order_by,$order)
            ->with('languages');
            if ($category_id > 0) {
                $query = $query->whereHas('categories', function ($q) use ($category_id) {
                    $q->where('categories.id', $category_id); });
            }

        if($number > 0) {
            return $query->limit($number)->get($columns);
        }
        return $query->get($columns);
    }
    public function getSearchResultPaginate($key_word, array $list_field  = ['title'], array $where, $category_id = 0,$number = 10,$order_by = 'order', $order = 'asc', $columns = ['*']) {
        $query = $this->getEntity()->where(function($q) use($list_field , $key_word) {
            foreach ($list_field  as $field)
                $q->orWhere($field, 'like', "%{$key_word}%");
        });
        $query->select($columns)->where($where)
            ->orderBy($order_by,$order)
            ->with('languages');
            if ($category_id > 0) {
                $query = $query->whereHas('categories', function ($q) use ($category_id) {
                    $q->where('categories.id', $category_id); });
            }
        return $query->paginate($number);

    }
}
