<?php

namespace VCComponent\Laravel\Post\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface PostRepository.
 *
 * @package namespace VCComponent\Laravel\Post\Repositories;
 */
interface PostRepository extends RepositoryInterface
{
    public function getWithPagination($filters, $type);
}
