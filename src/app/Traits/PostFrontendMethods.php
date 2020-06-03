<?php

namespace VCComponent\Laravel\Post\Traits;

use Illuminate\Http\Request;
use VCComponent\Laravel\Post\Events\PostCreatedEvent;
use VCComponent\Laravel\Post\Events\PostDeletedEvent;
use VCComponent\Laravel\Post\Events\PostUpdatedEvent;
use VCComponent\Laravel\Post\Repositories\PostRepository;
use VCComponent\Laravel\Post\Transformers\PostTransformer;
use VCComponent\Laravel\Post\Validators\PostValidator;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;
use VCComponent\Laravel\Vicoders\Core\Exceptions\PermissionDeniedException;

trait PostFrontendMethods
{
    public function __construct(PostRepository $repository, PostValidator $validator, Request $request)
    {
        $this->repository = $repository;
        $this->entity     = $repository->getEntity();
        $this->validator  = $validator;
        $this->type       = $this->getPostTypeFromRequest($request);

        if (config('post.auth_middleware.frontend.middleware') !== '') {
            $this->middleware(
                config('post.auth_middleware.frontend.middleware'),
                ['except' => config('post.auth_middleware.frontend.except')]
            );
        }

        if (isset(config('post.transformers')['post'])) {
            $this->transformer = config('post.transformers.post');
        } else {
            $this->transformer = PostTransformer::class;
        }
    }

    public function index(Request $request)
    {
        $query = $this->entity;

        $query = $this->applyQueryScope($query, 'type', $this->type);
        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['title', 'description', 'content'], $request);
        $query = $this->applyOrderByFromRequest($query, $request);

        $per_page = $request->has('per_page') ? (int) $request->get('per_page') : 15;
        $posts    = $query->paginate($per_page);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->paginator($posts, $transformer);
    }

    function list(Request $request)
    {
        $query = $this->entity;

        $query = $this->applyQueryScope($query, 'type', $this->type);
        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['title', 'description', 'content'], $request);
        $query = $this->applyOrderByFromRequest($query, $request);

        $posts = $query->get();

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->collection($posts, $transformer);
    }

    public function show(Request $request, $id)
    {
        $post = $this->repository->findWhere(['id' => $id, 'type' => $this->type])->first();
        if (!$post) {
            throw new NotFoundException(title_case($this->type) . ' entity');
        }

        if (config('post.auth_middleware.frontend.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToShow($user, $id)) {
                throw new PermissionDeniedException();
            }
        }

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->item($post, $transformer);
    }

    public function store(Request $request)
    {
        if (config('post.auth_middleware.frontend.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToCreate($user)) {
                throw new PermissionDeniedException();
            }
        }

        $data           = $this->filterPostRequestData($request, $this->entity, $this->type);
        $schema_rules   = $this->validator->getSchemaRules($this->entity, $this->type);
        $no_rule_fields = $this->validator->getNoRuleFields($this->entity, $this->type);

        $this->validator->isValid($data['default'], 'RULE_ADMIN_CREATE');
        $this->validator->isSchemaValid($data['schema'], $schema_rules);

        $post       = $this->repository->create($data['default']);
        $post->type = $this->type;
        $post->save();

        if (count($data['schema'])) {
            foreach ($data['schema'] as $key => $value) {
                $post->postMetas()->create([
                    'key'   => $key,
                    'value' => $value,
                ]);
            }
        }

        if (count($no_rule_fields)) {
            foreach ($no_rule_fields as $key => $value) {
                $post->postMetas()->updateOrCreate([
                    'key'   => $key,
                    'value' => null,
                ], ['value' => '']);
            }
        }

        event(new PostCreatedEvent($post));

        return $this->response->item($post, new $this->transformer);
    }

    public function update(Request $request, $id)
    {
        $post = $this->repository->findWhere(['id' => $id, 'type' => $this->type])->first();
        if (!$post) {
            throw new NotFoundException(title_case($this->type) . ' entity');
        }

        if (config('post.auth_middleware.frontend.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToUpdateItem($user, $id)) {
                throw new PermissionDeniedException();
            }
        }

        $data         = $this->filterPostRequestData($request, $this->entity, $this->type);
        $schema_rules = $this->validator->getSchemaRules($this->entity, $this->type);

        $this->validator->isValid($data['default'], 'RULE_ADMIN_UPDATE');
        $this->validator->isSchemaValid($data['schema'], $schema_rules);

        $post = $this->repository->update($data['default'], $id);

        if ($request->has('status')) {
            $post->status = $request->get('status');
            $post->save();
        }

        if (count($data['schema'])) {
            foreach ($data['schema'] as $key => $value) {
                $post->postMetas()->updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        event(new PostUpdatedEvent($post));

        return $this->response->item($post, new $this->transformer);
    }

    public function destroy(Request $request, $id)
    {
        $post = $this->repository->findWhere(['id' => $id, 'type' => $this->type])->first();
        if (!$post) {
            throw new NotFoundException(title_case($this->type) . ' entity');
        }

        if (config('post.auth_middleware.frontend.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToDelete($user, $id)) {
                throw new PermissionDeniedException();
            }
        }

        $this->repository->delete($id);

        event(new PostDeletedEvent());

        return $this->success();
    }

    public function bulkUpdateStatus(Request $request)
    {
        if (config('post.auth_middleware.frontend.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToUpdate($user)) {
                throw new PermissionDeniedException();
            }
        }

        $data = $request->all();

        $posts = $this->entity->whereIn('id', $data['item_ids'])
            ->where('type', $this->type)
            ->get();

        if ($posts->count() == 0) {
            throw new NotFoundException(title_case($this->type) . ' entities');
        }

        $this->validator->isValid($request, 'BULK_UPDATE_STATUS');

        foreach ($posts as $post) {
            $post->status = $data['status'];
            $post->save();
        }

        return $this->success();
    }

    public function updateStatusItem(Request $request, $id)
    {
        $post = $this->repository->findWhere(['id' => $id, 'type' => $this->type])->first();
        if (!$post) {
            throw new NotFoundException(title_case($this->type) . ' entity');
        }

        if (config('post.auth_middleware.frontend.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToUpdateItem($user, $id)) {
                throw new PermissionDeniedException();
            }
        }

        $this->validator->isValid($request, 'UPDATE_STATUS_ITEM');

        $data         = $request->all();
        $post->status = $data['status'];
        $post->save();

        return $this->success();
    }
}
