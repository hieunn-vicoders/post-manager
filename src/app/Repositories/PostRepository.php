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
    public function findByField($field, $value = null, $type = 'posts');
    public function findByWhere(array $where, $type = 'posts');
    public function getPostsAll($type = 'posts');
    public function getWithPagination($filters, $type);
    public function getPostByID($post_id);
    public function getPostMedias($post_id, $image_dimension= '');
    public function getPostUrl($post_id);
}
