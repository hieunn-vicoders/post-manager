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

        $result = $this->whereIn("id", $request->ids)->update(['status' => $data['status']]);

        return $result;
    }

}
