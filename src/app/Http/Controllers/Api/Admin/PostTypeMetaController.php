<?php

namespace VCComponent\Laravel\Post\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use VCComponent\Laravel\Post\Repositories\PostRepository;
use VCComponent\Laravel\Post\Repositories\PostTypeMetaRepository;
use VCComponent\Laravel\Post\Traits\Helpers;
use VCComponent\Laravel\Post\Transformers\PostTypeMetaTransformer;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;

class PostTypeMetaController extends ApiController
{
    use Helpers;
    protected $postTypeMetaEntity;
    protected $postEntity;
    protected $validator;
    protected $transformer;
    protected $postTypeMetaRepository;
    protected $type;
    public function __construct(PostTypeMetaTransformer $transformer, PostTypeMetaRepository $postTypeMetaRepository, PostRepository $postRepository, Request $request)
    {
        $this->transformer            = $transformer;
        $this->postTypeMetaEntity     = $postTypeMetaRepository->getEntity();
        $this->postEntity             = $postRepository->getEntity();
        $this->postTypeMetaRepository = $postTypeMetaRepository;
    }
    // List With Paginate
    public function index(Request $request)
    {
        $per_page      = $request->has("per_page") ? $request->get("per_page") : 15;
        $postTypeMetas = $this->postTypeMetaEntity::paginate($per_page);
        $transformer   = $this->transformer;
        return $this->response->paginator($postTypeMetas, $transformer);
    }
    // List No Paginate
    function list() {
        $postTypeMetas = $this->postTypeMetaEntity::all();
        $transformer   = $this->transformer;
        return $this->response->collection($postTypeMetas, $transformer);
    }
    //Show By Id
    public function show($id)
    {
        $postTypeMeta = $this->postTypeMetaEntity::find($id);
        if (!$postTypeMeta) {
            throw new NotFoundException("Post type {id}: " . $id);
        }
        $transformer = $this->transformer;
        return $this->response->item($postTypeMeta, $transformer);
    }
    //Store
    public function store(Request $request, $resource)
    {
        $requestDatas = $request->all();
        /* Check post type có tồn tại trong postTypes() không */
        $types = $this->postEntity->postTypes();
        if (!in_array($resource, array_keys($types))) {
            throw new NotFoundException("post type: " . $resource . " in postTypes() function");
        }
        /* check post type meta có tồn tại trong postTypes() không */
        $checkPostTypeMeta = array_diff(array_keys($requestDatas), array_keys($types[$resource]["meta"]));
        if ($checkPostTypeMeta) {
            throw new NotFoundException("post type meta: " . implode(", ", $checkPostTypeMeta) . " in postTypes() function");
        }
        foreach ($requestDatas as $key => $value) {
            $postTypeMeta        = new $this->postTypeMetaEntity;
            $postTypeMeta->type  = $resource;
            $postTypeMeta->key   = $key;
            $postTypeMeta->value = $value;
            $postTypeMeta->save();
        }
        return $this->success();
    }
    //Update By ID
    public function update(Request $request, $resource, $id)
    {
        $postTypeMeta = $this->postTypeMetaRepository->findWhere(["id" => $id, "type" => $resource])->first();
        $requestDatas = $request->all();
        $types        = $this->postEntity->postTypes();
        /* Check post type  === postTypes() */
        if (!in_array($resource, array_keys($types))) {
            throw new NotFoundException("post type: " . $resource);
        }
        /* check $id, $type exists */
        if (!$postTypeMeta) {
            throw new NotFoundException("post type: " . $resource . " id " . $id);
        }
        /* Check post type meta === postTypes() */
        $checkPostTypeMeta = array_diff(array_keys($requestDatas), array_keys($types[$resource]["meta"]));
        if ($checkPostTypeMeta) {
            throw new NotFoundException("post type meta: " . implode(", ", $checkPostTypeMeta) . " in postTypes() function");
        }
        foreach ($requestDatas as $key => $value) {
            $postTypeMeta->type  = $resource;
            $postTypeMeta->key   = $key;
            $postTypeMeta->value = $value;
            $postTypeMeta->save();
        }
        $transformer = $this->transformer;
        return $this->response->item($postTypeMeta, $transformer);
    }
    //Destroy By ID
    public function destroy($id)
    {
        $postTypeMeta = $this->postTypeMetaEntity::find($id);
        if (!$postTypeMeta) {
            throw new NotFoundException("post type id:" . $id);
        } else {
            $postTypeMeta->delete();
        }
        return $this->success();
    }
}
