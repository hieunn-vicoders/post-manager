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
    public function findByField($field, $value = null, $columns = ['*'], $type = 'posts');
    public function findWhere(array $where, $columns = ['*'], $type = 'posts');
    public function getPostsAll( $type = 'posts');

    public function getWithPagination($filters, $type);

    public function getRelatedPostsQuery($post_id, $post_type, $number, $order_by, $order, $is_hot,$status);
    public function getRelatedPostsQueryPaginate($post_id, $post_type, $number, $order_by, $order, $is_hot,$status);

    public function getPostsQuery($post_type, $category_id, $number,$order_by, $order,$is_hot, $status);
    public function getPostsQueryPaginate($post_type, $category_id, $number,$order_by, $order,$is_hot, $status);

    public function getSearchResultQuery($key_word,$number,$post_type,$category_id,$order_by,$order, $is_hot,$status);
    public function getSearchResultQueryPaginate($key_word,$number,$post_type,$category_id,$order_by,$order, $is_hot,$status);

    public function getPostByID( $post_id);
    public function getPostMedias( $post_id, $image_dimension);
    public function getPostUrl($post_id);
}
