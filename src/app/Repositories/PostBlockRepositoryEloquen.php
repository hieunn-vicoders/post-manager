<?php

namespace VCComponent\Laravel\Post\Repositories;

use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use VCComponent\Laravel\Post\Entities\PostBlock;

class PostBlockRepositoryEloquent extends BaseRepository implements PostBlockRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PostBlock::class;
    }

    public function getEntity()
    {
        return $this->model;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function findById($id)
    {
        $block = $this->model->find($id);
        if (!$block) {
            throw new \Exception('Không tìm thấy thuộc tính !', 1);
        }
        return $block;
    }
}
