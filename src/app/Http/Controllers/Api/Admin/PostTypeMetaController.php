<?php

namespace VCComponent\Laravel\Post\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use VCComponent\Laravel\Post\Entities\PostTypeMeta;
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
    protected $postTypeMetaRepository;
    protected $postRepository;
    protected $validator;
    protected $transformer;
    protected $type;
    public function __construct(PostTypeMetaTransformer $transformer, PostTypeMetaRepository $postTypeMetaRepository, PostRepository $postRepository, Request $request)
    {
        $this->transformer            = $transformer;
        $this->postTypeMetaEntity     = $postTypeMetaRepository->getEntity();
        $this->postEntity             = $postRepository->getEntity();
        $this->postTypeMetaRepository = $postTypeMetaRepository;
        $this->postRepository         = $postRepository;
    }

    public function show($resource)
    {
        $postType    = $this->postTypeMetaRepository->findByField("type", $resource);
        $result      = [];
        foreach ($postType as $value) {
            $result[$value->key] = $value->value;
        }
        return response()->json(["data" => $result]);
    }

    public function updateOrCreate(Request $request, $resource)
    {
        $requestDatas = $request->all();
        $types        = $this->postEntity->postTypes();
        if (!in_array($resource, array_keys($types))) {
            throw new NotFoundException("post type: " . $resource . " in postTypes() function");
        }
        $checkPostTypeMeta = array_diff(array_keys($requestDatas), array_keys($types[$resource]["meta"]));
        if ($checkPostTypeMeta) {
            throw new NotFoundException("post type meta: " . implode(", ", $checkPostTypeMeta) . " in postTypes() function");
        }
        $result = [];
        foreach ($requestDatas as $key => $value) {
            $postTypeMeta = $this->postTypeMetaEntity::updateOrCreate(["key" => $key], ["type" => $resource, "value" => $value]);
            if ($postTypeMeta->save()) {
                $result[$key] = $value;
            }
        }
        return response()->json(["data" => $result]);
    }
}
