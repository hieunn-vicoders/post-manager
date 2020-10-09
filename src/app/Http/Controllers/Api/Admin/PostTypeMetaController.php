<?php

namespace VCComponent\Laravel\Post\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use VCComponent\Laravel\Post\Repositories\PostTypeMetaRepository;
use VCComponent\Laravel\Post\Traits\Helpers;
use VCComponent\Laravel\Post\Transformers\PostTypeMetaTransformer;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;

class PostTypeMetaController extends ApiController
{
    use Helpers;
    protected $entity;
    protected $validator;
    protected $transformer;
    protected $repository;

    public function __construct(PostTypeMetaTransformer $transformer, PostTypeMetaRepository $repository)
    {
        $this->transformer = $transformer;
        $this->entity      = $repository->getEntity();
        $this->repository  = $repository;
    }
    // List With Paginate
    public function index(Request $request)
    {
        $per_page      = $request->has("per_page") ? $request->get("per_page") : 15;
        $postTypeMetas = $this->entity::paginate($per_page);
        $transformer   = $this->transformer;
        return $this->response->paginator($postTypeMetas, $transformer);
    }
    // List No Paginate
    function list() {
        $postTypeMetas = $this->entity::all();
        $transformer   = $this->transformer;
        return $this->response->collection($postTypeMetas, $transformer);
    }
    //Show By Id
    public function show($id)
    {
        $postTypeMeta = $this->entity::find($id);
        if (!$postTypeMeta) {
            throw new NotFoundException("Post type {id}: " . $id);
        }
        $transformer = $this->transformer;
        return $this->response->item($postTypeMeta, $transformer);
    }
    //Store
    public function store(Request $request)
    {
        $requestDatas = $request->all();
        $postTypeMeta = new $this->entity;
        foreach ($requestDatas as $key => $value) {
            $postTypeMeta->$key = $value;
        }
        $types = $this->entity->postTypes();
        if (!in_array($requestDatas["type"], array_keys($types))) {
            throw new NotFoundException("post type: " . $requestDatas["type"] . " in postTypes() function");
        }
        $postTypeMeta->save();
        $transformer = $this->transformer;
        return $this->response->item($postTypeMeta, $transformer);
    }
    //Update By Id
    public function update(Request $request, $id)
    {
        $user         = $this->getAuthenticatedUser();
        $postTypeMeta = $this->entity::find($id);
        $requestDatas = $request->all();
        if (!$postTypeMeta) {
            throw new NotFoundException("Post type {id}: " . $id);
        }
        foreach ($requestDatas as $key => $value) {
            $postTypeMeta->$key = $value;
        }
        $types = $this->entity->postTypes();
        if (!in_array($requestDatas["type"], array_keys($types))) {
            throw new NotFoundException("Post type : " . $requestDatas["type"] . " in postTypes() function");
        }
        $postTypeMeta->save();
        $transformer = $this->transformer;
        return $this->response->item($postTypeMeta, $transformer);
    }
    //Destroy By Id
    public function destroy($id)
    {
        $postTypeMeta = $this->entity::find($id);
        if (!$postTypeMeta) {
            throw new NotFoundException("post type id:" . $id . " in database");
        } else {
            $postTypeMeta->delete();
        }
        return $this->success();
    }
}
