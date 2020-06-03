<?php

namespace VCComponent\Laravel\Post\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VCComponent\Laravel\Post\Contracts\ViewPostDetailControllerInterface;
use VCComponent\Laravel\Post\Repositories\PostRepository;
use VCComponent\Laravel\Post\Traits\Helpers;
use VCComponent\Laravel\Post\ViewModels\PostDetail\PostDetailViewModel;

class PostDetailController extends Controller implements ViewPostDetailControllerInterface
{
    use Helpers;

    protected $repository;
    protected $entity;

    public function __construct(PostRepository $repository)
    {
        $this->repository = $repository;
        $this->entity     = $repository->getEntity();

        if (isset(config('post.viewModels')['postDetail'])) {
            $this->ViewModel = config('post.viewModels.postDetail');
        } else {
            $this->ViewModel = PostDetailViewModel::class;
        }
    }

    public function show($slug, Request $request)
    {
        if (method_exists($this, 'beforeQuery')) {
            $this->beforeQuery($request);
        }

        $type = $this->getTypePost($request);

        $post = $this->entity->where([['slug', '=', $slug], ['type', '=', $type]])->firstOrFail();

        if (method_exists($this, 'afterQuery')) {
            $this->afterQuery($post, $request);
        }

        $view_model = new $this->ViewModel($post);

        $custom_view_func_name = 'viewData' . ucwords($type);
        if (method_exists($this, $custom_view_func_name)) {
            $custom_view_data = $this->$custom_view_func_name($post, $request);
        } else {
            $custom_view_data = $this->viewData($post, $request);
        }

        $data = array_merge($custom_view_data, $view_model->toArray());

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

    protected function view()
    {
        return 'post-manager::post-detail';
    }

    protected function viewData($posts, Request $request)
    {
        return [];
    }
}
