<?php

namespace VCComponent\Laravel\Post\Traits;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use VCComponent\Laravel\Post\Entities\PostSchema;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Events\PostCreatedByAdminEvent;
use VCComponent\Laravel\Post\Events\PostDeletedEvent;
use VCComponent\Laravel\Post\Events\PostUpdatedByAdminEvent;
use VCComponent\Laravel\Post\Repositories\PostRepository;
use VCComponent\Laravel\Post\Transformers\PostSchemaTransformer;
use VCComponent\Laravel\Post\Transformers\PostTransformer;
use VCComponent\Laravel\Post\Validators\PostValidatorInterface;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;
use VCComponent\Laravel\Vicoders\Core\Exceptions\PermissionDeniedException;

trait PostAdminMethods
{
    public function __construct(PostRepository $repository, PostValidatorInterface $validator, Request $request)
    {
        $this->repository = $repository;
        $this->entity     = $repository->getEntity();
        $this->validator  = $validator;
        $this->type       = $this->getPostTypeFromRequest($request);

        if (config('post.auth_middleware.admin.middleware') !== '') {
            $this->middleware(
                config('post.auth_middleware.admin.middleware'),
                ['except' => config('post.auth_middleware.admin.except')]
            );
        }

        if (isset(config('post.transformers')['post'])) {
            $this->transformer = config('post.transformers.post');
        } else {
            $this->transformer = PostTransformer::class;
        }
    }

    public function getStatus($request, $query)
    {
        if ($request->has('status')) {
            $pattern = '/^\d$/';

            if (!preg_match($pattern, $request->status)) {
                throw new Exception('The input status is incorrect');
            }

            $query = $query->where('status', $request->status);
        }
        return $query;
    }

    public function fomatDate($date)
    {

        $fomatDate = Carbon::createFromFormat('Y-m-d', $date);

        return $fomatDate;
    }

    public function field($request)
    {
        if ($request->has('field')) {
            if ($request->field === 'updated') {
                $field = 'updated_at';
            } elseif ($request->field === 'published') {
                $field = 'published_date';
            } elseif ($request->field === 'created') {
                $field = 'created_at';
            }
            return $field;
        } else {
            throw new Exception('field requied');
        }
    }

    public function getFromDate($request, $query)
    {
        if ($request->has('from')) {

            $field     = $this->field($request);
            $form_date = $this->fomatDate($request->from);
            $query     = $query->whereDate($field, '>=', $form_date);
        }
        return $query;
    }

    public function getToDate($request, $query)
    {
        if ($request->has('to')) {
            $field   = $this->field($request);
            $to_date = $this->fomatDate($request->to);
            $query   = $query->whereDate($field, '<=', $to_date);
        }
        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->entity;
        $query = $this->getFromDate($request, $query);
        $query = $this->getToDate($request, $query);
        $query = $this->getStatus($request, $query);

        $query = $this->applyQueryScope($query, 'type', $this->type);
        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['title', 'description', 'content'], $request, ['postMetas' => ['value']]);
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

    function list(Request $request) {
        $query = $this->entity;

        $query = $this->getFromDate($request, $query);
        $query = $this->getToDate($request, $query);
        $query = $this->getStatus($request, $query);

        $query = $this->applyQueryScope($query, 'type', $this->type);
        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['title', 'description', 'content'], $request, ['postMetas' => ['value']]);
        $query = $this->applyOrderByFromRequest($query, $request);

        $posts = $query->get();

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->collection($posts, $transformer);
    }

    public function allListPostAndType(Request $request)
    {
        $query = $this->entity;
        $query = $query->where('type', '!=', 'pages');

        $query = $this->getFromDate($request, $query);
        $query = $this->getToDate($request, $query);
        $query = $this->getStatus($request, $query);
        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['title', 'description', 'content'], $request, ['postMetas' => ['value']]);
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

    public function show(Request $request, $id)
    {

        if (config('post.auth_middleware.admin.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToShow($user, $id)) {
                throw new PermissionDeniedException();
            }
        }

        $post = $this->repository->findWhere(['id' => $id])->first();

        if (!$post) {
            throw new NotFoundException(($this->type) . ' entity');
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

        $user = null;
        if (config('post.auth_middleware.admin.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToCreate($user)) {
                throw new PermissionDeniedException();
            }
        }

        $data           = $this->filterPostRequestData($request, $this->entity, $this->type);
        // dd($data);
        $schema_rules   = $this->validator->getSchemaRules($this->entity, $this->type);
        $no_rule_fields = $this->validator->getNoRuleFields($this->entity, $this->type);

        $this->validator->isValid($data['default'], 'RULE_ADMIN_CREATE');
        $this->validator->isSchemaValid($data['schema'], $schema_rules);

        $data['default']['author_id'] = $user ? $user->id : null;

        $post       = $this->repository->create($data['default']);
        $post->type = $this->type;
        $post->save();

        if (count($no_rule_fields)) {
            foreach ($no_rule_fields as $key => $value) {
                $post->postMetas()->updateOrCreate([
                    'key'   => $key,
                    'value' => null,
                ], ['value' => '']);
            }
        }

        if (count($data['schema'])) {
            foreach ($data['schema'] as $key => $value) {
                $post->postMetas()->updateOrcreate([
                    'key' => $key,
                ], [
                    'value' => $value,
                ]);
            }
        }
        $data = $request->all();
        event(new PostCreatedByAdminEvent($post));

        return $this->response->item($post, new $this->transformer([],$data));
    }

    public function update(Request $request, $id)
    {
        if (config('post.auth_middleware.admin.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToUpdateItem($user, $id)) {
                throw new PermissionDeniedException();
            }
        }

        $post = $this->repository->findWhere(['id' => $id])->first();
        if (!$post) {
            throw new NotFoundException(Str::title($this->type) . ' entity');
        }

        $data         = $this->filterPostRequestData($request, $this->entity, $this->type);
        $schema_rules = $this->validator->getSchemaRules($this->entity, $this->type);

        $this->validator->isValid($data['default'], 'RULE_ADMIN_UPDATE');

        if (array_key_exists('schema', $data) && $schema_rules) {
            $this->validator->isSchemaValid($data['schema'], $schema_rules);
        }

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

        event(new PostUpdatedByAdminEvent($post));

        return $this->response->item($post, new $this->transformer);
    }

    public function destroy(Request $request, $id)
    {
        if (config('post.auth_middleware.admin.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToDelete($user, $id)) {
                throw new PermissionDeniedException();
            }
        }

        $post = $this->repository->findWhere(['id' => $id])->first();
        if (!$post) {
            throw new NotFoundException(Str::title($this->type));
        }

        $this->repository->delete($id);

        event(new PostDeletedEvent());

        return $this->success();
    }

    public function getType()
    {
        $postTypes = $this->entity->postTypes();
        return response()->json(['data' => array_diff($postTypes, ['pages'])]);
    }

    public function bulkUpdateStatus(Request $request)
    {
        if (config('post.auth_middleware.admin.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToUpdate($user)) {
                throw new PermissionDeniedException();
            }
        }

        $this->validator->isValid($request, 'BULK_UPDATE_STATUS');

        $this->repository->bulkUpdateStatus($request);

        return $this->success();
    }

    public function updateStatusItem(Request $request, $id)
    {
        if (config('post.auth_middleware.admin.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToUpdateItem($user, $id)) {
                throw new PermissionDeniedException();
            }
        }

        $post = $this->repository->findWhere(['id' => $id, 'type' => $this->type])->first();
        if (!$post) {
            throw new NotFoundException(($this->type) . ' entity');
        }

        $this->validator->isValid($request, 'UPDATE_STATUS_ITEM');

        $data         = $request->all();
        $post->status = $data['status'];
        $post->save();

        return $this->success();
    }

    public function bulkDelete(Request $request)
    {
        if (config('post.auth_middleware.admin.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToUpdate($user)) {
                throw new PermissionDeniedException();
            }
        }
        $this->validator->isValid($request, 'RULE_IDS');
        $ids   = $request->ids;
        $query = $this->entity;
        $query = $this->applyQueryScope($query, 'type', $this->type);
        $posts = $query->whereIn('id', $ids);
        if (count($request->ids) > $posts->get()->count()) {
            throw new NotFoundException("Post");
        }
        $posts->delete();
        return $this->success();
    }

    public function restore($id)
    {
        $query = $this->entity;
        $query = $this->applyQueryScope($query, 'type', $this->type);
        $post  = $query->where('id', $id)->get();
        if (count($post) > 0) {
            throw new NotFoundException('Post');
        }

        $this->repository->restore($id);

        $restore = $query->where('id', $id)->get();
        return $this->success();
    }

    public function bulkRestore(Request $request)
    {
        $this->validator->isValid($request, 'RULE_IDS');
        $ids   = $request->ids;
        $query = $this->entity;
        $posts = $query->onlyTrashed()->whereIn("id", $ids)->get();

        if (count($ids) > $posts->count()) {
            throw new NotFoundException("Post");
        }

        $post = $this->repository->bulkRestore($ids);

        $post = $this->entity->whereIn('id', $ids)->get();

        return $this->success();
    }

    public function getAllTrash()
    {
        $query = $this->entity;
        $query = $this->applyQueryScope($query, 'type', $this->type);
        $trash = $query->onlyTrashed()->get();

        return $this->response->collection($trash, new $this->transformer());
    }

    public function trash(Request $request)
    {
        $query = $this->entity;
        $query = $this->applyQueryScope($query, 'type', $this->type);
        $trash = $query->onlyTrashed();

        if ($trash->first() == null) {
            throw new NotFoundException("Post");
        }
        $per_page = $request->has('per_page') ? (int) $request->get('per_page') : 15;
        $post     = $trash->paginate($per_page);

        return $this->response->paginator($post, new $this->transformer());
    }

    public function deleteAllTrash()
    {
        $query = $this->entity;
        $query = $this->applyQueryScope($query, 'type', $this->type);

        $posts = $query->onlyTrashed()->forceDelete();
        return $this->success();
    }

    public function deleteTrash($id)
    {
        $post = $this->repository->deleteTrash($id);
        return $this->success();
    }

    public function bulkDeleteTrash(Request $request)
    {
        $this->validator->isValid($request, 'RULE_IDS');
        $ids   = $request->ids;
        $posts = $this->entity->onlyTrashed()->whereIn("id", $ids)->get();
        if (count($ids) > $posts->count()) {
            throw new NotFoundException("post");
        }
        $post = $this->repository->bulkDeleteTrash($ids);
        return $this->success();
    }

    public function forceDelete($id)
    {
        $query = $this->entity;
        $query = $this->applyQueryScope($query, 'type', $this->type);

        $post = $query->where('id', $id)->first();
        if (!$post) {
            throw new NotFoundException('Post');
        }

        $this->repository->forceDelete($id);

        return $this->success();
    }

    public function changeDate(Request $request, $id)
    {
        if (config('post.auth_middleware.admin.middleware') !== '') {
            $user = $this->getAuthenticatedUser();
            if (!$this->entity->ableToUpdateItem($user, $id)) {
                throw new PermissionDeniedException();
            }
        }

        $post = $this->repository->findWhere(['id' => $id, 'type' => $this->type])->first();
        if (!$post) {
            throw new NotFoundException(Str::title($this->type) . ' entity');
        }

        $this->validator->isValid($request, 'RULE_ADMIN_UPDATE_DATE');

        $data = $request->all();

        $data                 = Carbon::parse($request->published_date)->format('Y-m-d');
        $post->published_date = $data;
        $post->save();

        return $this->response->item($post, new $this->transformer);
    }

    public function getFieldMeta()
    {
        $data = PostSchema::ofPostType($this->type)->get();
        return $this->response->collection($data, new PostSchemaTransformer());
    }
    public function addMediaPost($id, $image_path)
    {
        $query = $this->entity;
        $query = $this->applyQueryScope($query, 'type', $this->type);

        $post = $query->where('id', $id)->first();
        if (!$post) {
            throw new NotFoundException('Post');
        }
        return $post->addMedia($image_path)->preservingOriginal()->toMediaCollection();
    }

    public function getMediaPost($id)
    {
        $query = $this->entity;
        $query = $this->applyQueryScope($query, 'type', $this->type);

        $post = $query->where('id', $id)->first();
        if (!$post) {
            throw new NotFoundException('Post');
        }
        return $post->getMedia();
    }
}
