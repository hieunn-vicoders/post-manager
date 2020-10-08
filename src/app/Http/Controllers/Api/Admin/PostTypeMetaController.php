<?php

namespace VCComponent\Laravel\Post\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use VCComponent\Laravel\Post\Traits\Helpers;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;
use VCComponent\Laravel\Post\Entities\PostTypeMeta;
use VCComponent\Laravel\Post\Transformers\PostTypeMetaTransformer;
use VCComponent\Laravel\Post\Repositories\PostTypeMetaRepository;
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
        $this->entity = $repository->getEntity();
        $this->repository = $repository;
    }
    // List With Paginate
    public function index(Request $request)
    {
        $per_page = $request->has("per_page") ? $request->get("per_page") : 15;
        $postTypeMetas = $this->entity::paginate($per_page);
        $transformer = $this->transformer;
        return $this->response->paginator($postTypeMetas, $transformer);
    }
    // List No Paginate
    function list()
    {
        $postTypeMetas = $this->entity::all();
        $transformer  = $this->transformer;
        return $this->response->collection($postTypeMetas, $transformer);
    }
    //Show By Id
    public function show($id)
    {
        $postTypeMeta = $this->entity::find($id);
        $transformer  = $this->transformer;
        $data = $postTypeMeta->postTypes();
        return $this->response->item($postTypeMeta, $transformer);
    }
    //Store
    public function store(Request $request)
    {
        $postTypeMeta = new $this->entity;
        $postTypeMeta->type = $request->type;
        $postTypeMeta->key = $request->key;
        $postTypeMeta->value = $request->value;
        $postTypes = $postTypeMeta->postTypes();
        $fieldsArrs = array_keys($postTypes['post-type']['meta']);
        foreach($fieldsArrs as $field){
            if($field === $postTypeMeta->key ){
                $postTypeMeta->save();
            }
        }
        $transformer  = $this->transformer;
        return $this->response->item($postTypeMeta, $transformer);
    }
    //Update By Id
    public function update(Request $request, $id)
    {
        $postTypeMeta = $this->entity::find($id);
        $postTypeMeta->type = $request->type;
        $postTypeMeta->key = $request->key;
        $postTypeMeta->value = $request->value;
        $postTypes = $postTypeMeta->postTypes();
        $fieldsArrs = array_keys($postTypes['post-type']['meta']);
        foreach($fieldsArrs as $field){
            if($postTypeMeta->key === $field ){
                $postTypeMeta->save();
            }
        }
        $transformer  = $this->transformer;
        return $this->response->item($postTypeMeta, $transformer);
    }
    //Destroy By Id
    public function destroy(Request $request, $id)
    {
        $postTypeMeta = $this->entity::find($id);
        $postTypeMeta->delete();
        return $this->success();
    }
}
