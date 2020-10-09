<?php
namespace VCComponent\Laravel\Post\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use VCComponent\Laravel\Post\Entities\PostTypeMeta;
use VCComponent\Laravel\Post\Repositories\PostTypeMetaRepository;

class PostTypeMetaRepositoryEloquent extends BaseRepository implements PostTypeMetaRepository
{

    public function model()
    {
        if (isset(config('post.models')['postTypeMeta'])) {
            return config('post.models.postTypeMeta');
        } else {
            return PostTypeMeta::class;
        }
    }
    public function getEntity()
    {
        return $this->model;
    }
}
