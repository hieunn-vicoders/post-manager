<?php

namespace VCComponent\Laravel\Post\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VCComponent\Laravel\Post\Contracts\ViewDrafDetailControllerInterface;
use VCComponent\Laravel\Post\Repositories\DraftableRepository;
use VCComponent\Laravel\Post\Traits\Helpers;
use VCComponent\Laravel\Post\ViewModels\DrafDetail\DrafDetailViewModel;

class DrafDetailController extends Controller implements ViewDrafDetailControllerInterface
{
    use Helpers;

    protected $repository;
    protected $entity;

    public function __construct(DraftableRepository $repository)
    {
        $this->repository = $repository;
        $this->entity     = $repository->getEntity();

        if (isset(config('post.viewModels')['postDetail'])) {
            $this->ViewModel = config('post.viewModels.postDetail');
        } else {
            $this->ViewModel = DrafDetailViewModel::class;
        }
    }

    public function show($id, Request $request)
    {

        $type = $this->drafTypes($request);

        $post_preview = $this->entity->where([['draftable_id', '=', $id], ['draftable_type', '=', $type]])->firstOrFail();

        $view_model = new $this->ViewModel($post_preview);

        $custom_view_func_name = 'viewData' . ucwords($type);

        if (method_exists($this, $custom_view_func_name)) {
            $custom_view_data = $this->$custom_view_func_name($post_preview, $request);
        } else {
            $custom_view_data = $this->viewData($post_preview, $request);
        }

        $data = array_merge($custom_view_data, $view_model->toArray());

        $key = 'view' . ucwords($type);

        if (method_exists($this, $key)) {

            return view($this->$key(), $data);

        } else {

            return view($this->view(), $data);

        }
    }

    protected function view()
    {
        return 'post-manager::draft';
    }

    protected function viewData($post_preview, Request $request)
    {
        return [];
    }
}
