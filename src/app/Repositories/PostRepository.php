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
    public function getPostByID( $post_id);
    public function getPostMedias( $post_id, $image_dimension);
    public function getPostUrl($post_id);

    public function getRelatedPosts($post_id, array $where, $number = 10, $order_by = 'order', $order = 'asc',  $columns = ['*']);
    public function getRelatedPostsPaginate($post_id, array $where, $number = 10, $order_by = 'order', $order = 'asc',  $columns = ['*']);
    public function getPostsWithCategory($category_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']);
    public function getPostsWithCategoryPaginate($category_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']);
    public function getSearchResult($key_word,array $list_field  = ['title'], array $where, $category_id = 0,$number = 10,$order_by = 'order', $order = 'asc', $columns = ['*']);
    public function getSearchResultPaginate($key_word, array $list_field  = ['title'], array $where, $category_id = 0,$number = 10,$order_by = 'order', $order = 'asc', $columns = ['*']);
}
