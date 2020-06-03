<?php

namespace VCComponent\Laravel\Post\Repositories;

use VCComponent\Laravel\Post\Repositories\DraftableRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use VCComponent\Laravel\Post\Entities\Draftable;

/**
 * Class AccountantRepositoryEloquent.
 */
class DraftableRepositoryEloquent extends BaseRepository implements DraftableRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Draftable::class;
    }

    public function getEntity()
    {
        return $this->model;
    }



    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
