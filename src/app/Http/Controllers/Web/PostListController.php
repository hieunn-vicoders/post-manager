<?php
namespace VCComponent\Laravel\Post\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VCComponent\Laravel\Post\Contracts\ViewPostListControllerInterface;
use VCComponent\Laravel\Post\Pipes\ApplyConstraints;
use VCComponent\Laravel\Post\Pipes\ApplyOrderBy;
use VCComponent\Laravel\Post\Pipes\ApplySearch;
use VCComponent\Laravel\Post\Repositories\PostRepository;
use VCComponent\Laravel\Post\Traits\Helpers;
use VCComponent\Laravel\Post\ViewModels\PostList\PostListViewModel;

class PostListController extends Controller implements ViewPostListControllerInterface
{
    use Helpers;
    protected $repository;
    protected $entity;
    public function __construct(PostRepository $repository)
    {
        $this->repository = $repository;
        $this->entity     = $repository->getEntity();
        if (isset(config('post.viewModels')['postList'])) {
            $this->ViewModel = config('post.viewModels.postList');
        } else {
            $this->ViewModel = PostListViewModel::class;
        }
    }

    public function index(Request $request)
    {
        if (method_exists($this, 'beforeQuery')) {
            $this->beforeQuery($request);
        }
        $type  = $this->getTypePost($request);
        $pipes = $this->pipes();
        $posts = $this->repository->getWithPagination($pipes, $type);
        if (method_exists($this, 'afterQuery')) {
            $this->afterQuery($posts, $request);
        }
        $custom_view_func_name = 'viewData' . ucwords($type);
        if (method_exists($this, $custom_view_func_name)) {
            $custom_view_data = $this->$custom_view_func_name($posts, $request);
        } else {
            $custom_view_data = $this->viewData($posts, $request);
        }
        $view_model = new $this->ViewModel($posts);
        $data       = array_merge($custom_view_data, $view_model->toArray());
        if (method_exists($this, 'beforeView')) {
            $this->beforeView($data, $request);
        }
        $key = 'view' . ucwords($type);
        if (method_exists($this, $key)) {
            return view($this->$key(), $data);
        } else {
            return view($this->view(), $data);
        }
    }

    protected function pipes()
    {
        return [
            ApplyConstraints::class,
            ApplySearch::class,
            ApplyOrderBy::class,
        ];
    }

    protected function view()
    {
        return 'post-manager::post-list';
    }

    protected function viewData($posts, Request $request)
    {
        return [];
    }
}
