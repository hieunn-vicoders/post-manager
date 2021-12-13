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

    public function getPostWithMetas($id)
    {
        return $this->getEntity()->where('id', $id)->with('postMetas')->first();
    }

    public function getPostWithCategories($id)
    {
        return $this->getEntity()->where('id', $id)->with('categories')->first();
    }

    public function getPostWithComments($id)
    {
        return $this->getEntity()->where('id', $id)->with('comments')->first();
    }

    public function getPostWithTags($id)
    {
        return $this->getEntity()->where('id', $id)->with('tags')->first();
    }

    public function getPostWithMetasCategories($id)
    {
        return $this->getEntity()->where('id', $id)->with('postMetas')->with('categories')->first();
    }

    public function getPostWithMetasComments($id)
    {
        return $this->getEntity()->where('id', $id)->with('postMetas')->with('comments')->first();
    }

    public function getPostWithMetasTags($id)
    {
        return $this->getEntity()->where('id', $id)->with('postMetas')->with('tags')->first();
    }

    public function getPostWithCategoriesComments($id)
    {
        return $this->getEntity()->where('id', $id)->with('categories')->with('comments')->first();
    }

    public function getPostWithCategoriesTags($id)
    {
        return $this->getEntity()->where('id', $id)->with('categories')->with('Tags')->first();
    }

    public function getPostWithCommentsTags($id)
    {
        return $this->getEntity()->where('id', $id)->with('comments')->with('tags')->first();
    }

    public function getPostWithAll($id)
    {
        return $this->getEntity()->where('id', $id)->with('postMetas')->with('categories')->with('comments')->with('tags')->first();
    }

    public function getListPostsHasCategories(array $categories_constraints = [], $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->whereHas('categories', function ($q) use ($categories_constraints) {
                return $q->where($categories_constraints);
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListPostsHasTags(array $tags_constraints = [], $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->whereHas('tags', function ($q) use ($tags_constraints) {
                return $q->where($tags_constraints);
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListPostsWithMetas($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListPostsWithCategories($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListPostsWithMetasCategories($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListHotPosts($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListHotPostsHasCategories(array $categories_constraints = [] ,$numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->whereHas('categories', function ($q) use ($categories_constraints) {
                return $q->where($categories_constraints);
            })
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListHotPostsHasTags(array $tags_constraints, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->whereHas('categories', function ($q) use ($tags_constraints) {
                return $q->where($tags_constraints);
            })
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListHotPostsWithMetas($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListHotPostsWithCategories($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('categories')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListHotPostsWithMetasCategories($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')->with('categories')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListRelatedPosts($post, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListRelatedPostsWithMetas($post, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListRelatedPostsWithCategories($post, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('categories')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListRelatedPostsWithMetasCategories($post, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')->with('categories')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListOfSearchingPosts($search, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListOfSearchingPostsHasCategoriesTags($search, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%')
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListOfSearchingPostsWithMetas($search, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
            })
            ->with('postMetas')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListOfSearchingPostsWithCategories($search, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
            })
            ->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListOfSearchingPostsHasCategoriesTagsWithMetas($search, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%')
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            })
            ->with('postMetas')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListOfSearchingPostsHasCategoriesTagsWithMetasCategories($search, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%')
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            })
            ->with('postMetas')->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListPaginatedPostsHasCategories(array $categories_constraints = [], $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->whereHas('categories', function ($q) use ($categories_constraints) {
                return $q->where($categories_constraints);
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedPostsHasTags(array $tags_constaints = [], $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->whereHas('tags', function ($q) use ($tags_constaints) {
                return $q->where($tags_constaints);
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedPostsWithMetas($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedPostsWithCategories($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedPostsWithMetasCategories($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedHotPosts($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedHotPostsHasCategories(array $categories_constraints = [] ,$per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->whereHas('categories', function ($q) use ($categories_constraints) {
                return $q->where($categories_constraints);
            })
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedHotPostsHasTags(array $tags_constraints = [], $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->whereHas('categories', function ($q) use ($tags_constraints) {
                return $q->where($tags_constraints);
            })
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedHotPostsWithMetas($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedHotPostsWithCategories($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('categories')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->get($per_page);
    }

    public function getListPaginatedHotPostsWithMetasCategories($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')->with('categories')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedRelatedPosts($post, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedRelatedPostsWithMetas($post, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedRelatedPostsWithCategories($post, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('categories')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedRelatedPostsWithMetasCategories($post, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->with('postMetas')->with('categories')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedOfSearchingPosts($search, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedOfSearchingPostsHasCategoriesTags($search, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%')
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedOfSearchingPostsWithMetas($search, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
            })
            ->with('postMetas')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedOfSearchingPostsWithCategories($search, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
            })
            ->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedOfSearchingPostsHasCategoriesTagsWithMetas($search, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%')
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            })
            ->with('postMetas')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedOfSearchingPostsHasCategoriesTagsWithMetasCategories($search, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%')
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            })
            ->with('postMetas')->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListTranslatablePosts($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListPaginatedTranslatablePosts($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getPostTranslatableWithMetas($id)
    {
        return $this->getEntity()->with('languages')->where('id', $id)->with('postMetas')->first();
    }

    public function getPostTranslatableWithCategories($id)
    {
        return $this->getEntity()->with('languages')->where('id', $id)->with('categories')->first();
    }

    public function getPostTranslatableWithComments($id)
    {
        return $this->getEntity()->with('languages')->where('id', $id)->with('comments')->first();
    }

    public function getPostTranslatableWithTags($id)
    {
        return $this->getEntity()->with('languages')->where('id', $id)->with('tags')->first();
    }

    public function getPostTranslatableWithMetasCategories($id)
    {
        return $this->getEntity()->with('languages')->where('id', $id)->with('postMetas')->with('categories')->first();
    }

    public function getPostTranslatableWithMetasComments($id)
    {
        return $this->getEntity()->with('languages')->where('id', $id)->with('postMetas')->with('comments')->first();
    }

    public function getPostTranslatableWithMetasTags($id)
    {
        return $this->getEntity()->with('languages')->where('id', $id)->with('postMetas')->with('tags')->first();
    }

    public function getPostTranslatableWithCategoriesComments($id)
    {
        return $this->getEntity()->with('languages')->where('id', $id)->with('categories')->with('comments')->first();
    }

    public function getPostTranslatableWithCategoriesTags($id)
    {
        return $this->getEntity()->with('languages')->where('id', $id)->with('categories')->with('Tags')->first();
    }

    public function getPostTranslatableWithCommentsTags($id)
    {
        return $this->getEntity()->with('languages')->where('id', $id)->with('comments')->with('tags')->first();
    }

    public function getPostTranslatableWithAll($id)
    {
        return $this->getEntity()->with('languages')->where('id', $id)->with('postMetas')->with('categories')->with('comments')->with('tags')->first();
    }

    public function getListTranslatablePostsHasCategories(array $categories_constraints = [], $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->whereHas('categories', function ($q) use ($categories_constraints) {
                return $q->where($categories_constraints);
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatablePostsHasTags(array $tags_constraints = [], $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->whereHas('tags', function ($q) use ($tags_constraints) {
                return $q->where($tags_constraints);
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatablePostsWithMetas($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatablePostsWithCategories($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatablePostsWithMetasCategories($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableHotPosts($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableHotPostsHasCategories(array $categories_constraints = [] ,$numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->whereHas('categories', function ($q) use ($categories_constraints) {
                return $q->where($categories_constraints);
            })
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableHotPostsHasTags(array $tags_constraints, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->whereHas('categories', function ($q) use ($tags_constraints) {
                return $q->where($tags_constraints);
            })
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableHotPostsWithMetas($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableHotPostsWithCategories($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('categories')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableHotPostsWithMetasCategories($numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')->with('categories')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableRelatedPosts($post, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableRelatedPostsWithMetas($post, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableRelatedPostsWithCategories($post, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('categories')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableRelatedPostsWithMetasCategories($post, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')->with('categories')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableOfSearchingPosts($search, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableOfSearchingPostsHasCategoriesTags($search, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%')
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableOfSearchingPostsWithMetas($search, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
            })
            ->with('postMetas')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableOfSearchingPostsWithCategories($search, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
            })
            ->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableOfSearchingPostsHasCategoriesTagsWithMetas($search, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%')
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            })
            ->with('postMetas')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListTranslatableOfSearchingPostsHasCategoriesTagsWithMetasCategories($search, $numbert_of_posts = null, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%')
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            })
            ->with('postMetas')->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        $query = $numbert_of_posts ? $query->limit($numbert_of_posts) : $query;
        return $query->get();
    }

    public function getListPaginatedTranslatablePostsHasCategories(array $categories_constraints = [], $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->whereHas('categories', function ($q) use ($categories_constraints) {
                return $q->where($categories_constraints);
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatablePostsHasTags(array $tags_constaints = [], $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->whereHas('tags', function ($q) use ($tags_constaints) {
                return $q->where($tags_constaints);
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatablePostsWithMetas($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatablePostsWithCategories($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatablePostsWithMetasCategories($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableHotPosts($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableHotPostsHasCategories(array $categories_constraints = [] ,$per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->whereHas('categories', function ($q) use ($categories_constraints) {
                return $q->where($categories_constraints);
            })
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableHotPostsHasTags(array $tags_constraints = [], $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->whereHas('categories', function ($q) use ($tags_constraints) {
                return $q->where($tags_constraints);
            })
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableHotPostsWithMetas($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableHotPostsWithCategories($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('categories')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->get($per_page);
    }

    public function getListPaginatedTranslatableHotPostsWithMetasCategories($per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')->with('categories')
            ->where('is_hot', 1)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableRelatedPosts($post, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableRelatedPostsWithMetas($post, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableRelatedPostsWithCategories($post, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('categories')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableRelatedPostsWithMetasCategories($post, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->with('postMetas')->with('categories')
            ->where('id', '<>', $post->id)
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableOfSearchingPosts($search, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableOfSearchingPostsHasCategoriesTags($search, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%')
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            })
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableOfSearchingPostsWithMetas($search, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
            })
            ->with('postMetas')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableOfSearchingPostsWithCategories($search, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%');
            })
            ->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableOfSearchingPostsHasCategoriesTagsWithMetas($search, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%')
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            })
            ->with('postMetas')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }

    public function getListPaginatedTranslatableOfSearchingPostsHasCategoriesTagsWithMetasCategories($search, $per_page = 15, $type = 'posts')
    {
        $query = $this->getEntity()->ofType($type)->with('languages')
            ->where(function ($where_query) use ($search) {
                $where_query
                ->orWhere('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('content', 'like', '%'.$search.'%')
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                })
                ->orWhereHas('categories', function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%');
                });
            })
            ->with('postMetas')->with('categories')
            ->where('status', 1)
            ->orderBy('order', 'asc')
            ->latest();
        return $query->paginated($per_page);
    }
}
