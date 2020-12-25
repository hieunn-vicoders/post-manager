<?php

namespace VCComponent\Laravel\Post\Traits;

use Illuminate\Http\Request;
use VCComponent\Laravel\Post\Events\PreviewEvent;
use VCComponent\Laravel\Post\Repositories\DraftableRepository;
use VCComponent\Laravel\Post\Transformers\DraftableTransformer;
use VCComponent\Laravel\Post\Validators\DraftableValidator;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;

trait DraftableAdminMethods
{
    public function __construct(DraftableRepository $repository, DraftableValidator $validator)
    {
        $this->repository  = $repository;
        $this->entity      = $repository->getEntity();
        $this->validator   = $validator;
        $this->transformer = DraftableTransformer::class;
    }
    public function index(Request $request)
    {
        $query = $this->entity;

        $query    = $this->applyConstraintsFromRequest($query, $request);
        $query    = $this->applySearchFromRequest($query, ['name'], $request);
        $query    = $this->applyOrderByFromRequest($query, $request);
        $per_page = $request->has('per_page') ? (int) $request->get('per_page') : 15;
        $drafts   = $query->paginate($per_page);

        return $this->response->paginator($drafts, new $this->transformer());
    }
    public function store(Request $request)
    {
        $this->validator->isValid($request, 'RULE_CREATE');
        $data  = $request->all();
        $draft = $this->repository->create($data);

        event(new PreviewEvent($draft));

        return $this->response->item($draft, new $this->transformer());
    }
    public function show(Request $request, $id)
    {
        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        $draft = $this->entity->where('id', $id)->first();

        return $this->response->item($draft, $transformer);
    }
    function list(Request $request) {
        $query = $this->entity;

        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['name'], $request);
        $query = $this->applyOrderByFromRequest($query, $request);

        $drafts = $query->get();

        return $this->response->collection($drafts, new $this->transformer());
    }

    public function update(Request $request, $id)
    {
        $this->validator->isValid($request, 'RULE_UPDATE');
        $data  = $request->all();
        $draft = $this->repository->update($data, $id);

        return $this->response->item($draft, new $this->transformer());
    }

    public function destroy($id)
    {
        $draft = $this->entity->find($id);
        if (!$draft) {
            throw new NotFoundException('draft');
        }

        $this->repository->delete($id);

        return $this->success();
    }
}
