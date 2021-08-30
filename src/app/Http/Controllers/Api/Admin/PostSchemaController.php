<?php

namespace VCComponent\Laravel\Post\Http\Controllers\Api\Admin;

use Exception;
use Illuminate\Http\Request;
use VCComponent\Laravel\Post\Repositories\PostSchemaRepository;
use VCComponent\Laravel\Post\Transformers\PostSchemaTransformer;
use VCComponent\Laravel\Post\Validators\PostSchemaValidator;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;
use VCComponent\Laravel\Post\Entities\PostMeta;
use VCComponent\Laravel\Post\Entities\PostSchemaType;
use VCComponent\Laravel\Post\Entities\PostSchemaRule;
use VCComponent\Laravel\Post\Entities\Post;

class PostSchemaController extends ApiController
{
    protected $repository;
    protected $validator;

    public function __construct(PostSchemaRepository $repository, PostSchemaValidator $validator)
    {
        $this->repository  = $repository;
        $this->entity      = $repository->getEntity();
        $this->validator   = $validator;
        $this->transformer = PostSchemaTransformer::class;

        if (config('product.auth_middleware.admin.middleware') !== '') {
            $this->middleware(
                config('product.auth_middleware.admin.middleware'),
                ['except' => config('product.auth_middleware.admin.except')]
            );
        }
        else {
            throw new Exception("Admin middleware configuration is required");
        }
    }

    public function index(Request $request )
    {
        $query = $this->entity;

        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['name'], $request);
        $query = $this->applyOrderByFromRequest($query, $request);

        $per_page   = $request->has('per_page') ? (int) $request->get('per_page') : 15;
        $schemas = $query->paginate($per_page);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->paginator($schemas, $transformer);
    }

    public function show($id, Request $request)
    {
        $schema = $this->repository->findById($id);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->item($schema, $transformer);
    }

    public function store(Request $request)
    {
        $post_schema   = config('post-schema');
        $post_schema = collect($post_schema)->map(function ($post) {
            $post_type = $post['posttype'];
            $has_value_items   = collect($post['items'])->map(function ($item)  use ($post_type) {
                if($item['slug'] != null) {
                    $post = Post::where('slug', $item['slug'])->first();

                    if($post) {
                        $post_id = $post->id;
                    }
                    else {
                        throw new Exception("slug '".$item['slug']."' không tồn tại");
                    }
                }
                else {
                    throw new Exception("slug không được bỏ trống");
                }
                $has_value_inputs   = collect($item['inputs'])->map(function ($input) use ($post_id, $post_type) {
                    $label = $input['label'];
                    $key = $input['key'];
                    $value = $input['value'] ? $input['value'] : "";
                    if ($input['type'] != null) {
                        $type = PostSchemaType::where('name', $input['type'])->first();
                        $type_id = $type->id;
                    } else {
                        $type_id = 1;
                    }
                    if ($input['rule'] != null) {
                        $rule = PostSchemaRule::where('name', $input['rule'])->first();
                        $rule_id = $rule->id;
                    } else {
                        $rule_id = 5;
                    }
                    $data = [
                        "name"           => $key,
                        "label"          => $label,
                        'schema_type_id' => $type_id,
                        'schema_rule_id' => $rule_id,
                        'post_type'      => $post_type,
                        'post_id'        => $post_id,
                        'value'          => $value
                    ];
                    $schema = $this->repository->updateOrCreate(["name" => $key], $data);

                    return $this->response->item($schema, new $this->transformer);
                    PostMeta::create(["post_id" => $post_id, "key" => $key]);
                });
                return $has_value_inputs;
            });
            return $has_value_items;
        });
        return $post_schema;
    }

    public function update(Request $request, $id)
    {
        $this->validator->isValid($request, 'RULE_ADMIN_UPDATE');

        $schema_updating = $this->repository->findById($id);

        $data = $request->all();

        PostMeta::where('key', $schema_updating->name)->update(['key' => $request->name]);
        $schema = $this->repository->update($data, $id);
        return $this->response->item($schema, new $this->transformer);
    }

    public function destroy($id)
    {
        $schema = $this->repository->findById($id);
        PostMeta::where('key', $schema->name)->delete();
        $schema->delete();

        return $this->success();
    }
}
