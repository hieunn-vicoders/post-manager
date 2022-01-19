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
    public function findByField($field, $value = null, $type = 'posts')
    {
        try {
            return $this->model->ofType($type)->where($field, '=', $value)->get();
        } catch (Exception $e) {
            throw new NotFoundException('post not found');
        }
    }

    /**
     * Find data by multiple fields
     *
     * @param string $type
     * @param int $id
     * @return self
     */
    public function findByWhere(array $where, $type = 'posts', $number = 10, $order_by = 'order', $order = 'asc') {
        try {
            $query = $this->model->ofType($type)
            ->where($where)
            ->orderBy($order_by,$order);
            if ($number > 0) {
                return $query->limit($number)->get();
            }
            return $query->get();
        } catch (Exception $e) {
            throw new NotFoundException($e);
        }
    }
    public function findByWherePaginate(array $where, $type = 'posts', $number = 10, $order_by = 'order', $order = 'asc') {
         return $this->model->ofType($type)
         ->where($where)
         ->orderBy($order_by,$order)
         ->paginate($number);
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

    public function getPostByID($post_id) {
        return $this->model->where('id', $post_id)->first();
    }
    public function getPostMedias( $post_id, $image_dimension='') {
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
        $post = $this->model->where('id', $post_id)->first();
        return '/'.$post->type.'/'.$post->slug;
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
            ->orderBy($order_by,$order);
        if($number > 0) {
            return $query->limit($number)->get($columns);
        }
        return $query->get($columns);

    }
    public function getRelatedPostsPaginate($post_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']) {
        $query = $this->getEntity()
            ->where('id', '<>', $post_id)
            ->where($where)
            ->orderBy($order_by,$order);
        return $query->paginate($number);

    }
    public function getPostsWithCategory($category_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']) {
        $query = $this->getEntity()->where($where)
            ->orderBy($order_by,$order);
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
            ->orderBy($order_by,$order);
            $query = $query->whereHas('categories', function ($q) use ($category_id) {
                $q->where('categories.id', $category_id); });
        return $query->paginate($number);
    }
    public function getSearchResult($key_word, array $list_field = ['title'],array $where, $category_id = 0,$number = 10,$order_by = 'order', $order = 'asc', $columns = ['*']) {
        $query = $this->getEntity()->where(function($q) use($list_field , $key_word) {
            foreach ($list_field  as $field)
                $q->orWhere($field, 'like', "%{$key_word}%");
        });
        $query->where($where)
            ->orderBy($order_by,$order);
            if ($category_id > 0) {
                $query = $query->whereHas('categories', function ($q) use ($category_id) {
                    $q->where('categories.id', $category_id); });
            }

        if($number > 0) {
            return $query->limit($number)->get($columns);
        }
        return $query->get($columns);
    }
    public function getSearchResultPaginate($key_word, array $list_field  = ['title'], array $where, $category_id = 0, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']) {
        $query = $this->getEntity()->where(function($q) use($list_field , $key_word) {
            foreach ($list_field  as $field)
                $q->orWhere($field, 'like', "%{$key_word}%");
        });
        $query->select($columns)->where($where)
            ->orderBy($order_by,$order);
            if ($category_id > 0) {
                $query = $query->whereHas('categories', function ($q) use ($category_id) {
                    $q->where('categories.id', $category_id); });
            }
        return $query->paginate($number);
    }

    public function getListHotPosts($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')
            ->with('categories')
            ->with('tags')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListRelatedPosts($post, $numbert_of_posts = null)
    {
        $query = $this->getEntity()->ofType($post->type)
            ->with('postMetas')
            ->with('categories')
            ->with('tags')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListOfSearchingPosts($search, $numbert_of_posts = null, $type = 'posts', $absolute_search = false)
    {
        if (!$absolute_search) {
            $search = '%' . $search . '%';
        }
        
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', $search)
                ->orWhere('description', 'like', $search)
                ->orWhere('content', 'like', $search)
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', $search);
                })
                ->orWhereHas('tags', function ($q) use ($search) {
                    $q->where('name', 'like', $search);
                });
            })
            ->with('postMetas')
            ->with('categories')
            ->with('tags')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListPaginatedHotPosts($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')
            ->with('categories')
            ->with('tags')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginate($per_page);
    }

    public function getListPaginatedRelatedPosts($post, $per_page = 15)
    {
        $query = $this->getEntity()->ofType($post->type)
            ->with('postMetas')
            ->with('categories')
            ->with('tags')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginate($per_page);
    }

    public function getListPaginatedOfSearchingPosts($search, $per_page = 15, $type = 'posts', $absolute_search = false)
    {
        if (!$absolute_search) {
            $search = '%' . $search . '%';
        }

        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', $search)
                ->orWhere('description', 'like', $search)
                ->orWhere('content', 'like', $search)
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', $search);
                })
                ->orWhereHas('tags', function ($q) use ($search) {
                    $q->where('name', 'like', $search);
                });
            })
            ->with('postMetas')
            ->with('categories')
            ->with('tags')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginate($per_page);
    }

    public function getListHotTranslatablePosts($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')
            ->with('categories')
            ->with('tags')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListRelatedTranslatablePosts($post, $numbert_of_posts = null)
    {
        $query = $this->getEntity()->ofType($post->type)->with('languages')
            ->with('postMetas')
            ->with('categories')
            ->with('tags')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListOfSearchingTranslatablePosts($search, $numbert_of_posts = null, $type = 'posts', $absolute_search = false)
    {
        if (!$absolute_search) {
            $search = '%' . $search . '%';
        }

        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', $search)
                ->orWhere('description', 'like', $search)
                ->orWhere('content', 'like', $search)
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', $search);
                })
                ->orWhereHas('tags', function ($q) use ($search) {
                    $q->where('name', 'like', $search);
                });
            })
            ->with('postMetas')
            ->with('categories')
            ->with('tags')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListPaginatedHotTranslatablePosts($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')
            ->with('categories')
            ->with('tags')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginate($per_page);
    }

    public function getListPaginatedRelatedTranslatablePosts($post, $per_page = 15)
    {
        $query = $this->getEntity()->ofType($post->type)->with('languages')
            ->with('postMetas')
            ->with('categories')
            ->with('tags')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginate($per_page);
    }

    public function getListPaginatedOfSearchingTranslatablePosts($search, $per_page = 15, $type = 'posts', $absolute_search = false)
    {
        if (!$absolute_search) {
            $search = '%' . $search . '%';
        }

        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', $search)
                ->orWhere('description', 'like', $search)
                ->orWhere('content', 'like', $search)
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', $search);
                })
                ->orWhereHas('tags', function ($q) use ($search) {
                    $q->where('name', 'like', $search);
                });
            })
            ->with('postMetas')
            ->with('categories')
            ->with('tags')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginate($per_page);
    }
}
