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
    public function findByWhere(array $where, $type = 'posts', $number = 10, $order_by = 'order', $order = 'asc');
    public function findByWherePaginate(array $where, $type = 'posts', $number = 10, $order_by = 'order', $order = 'asc');
    public function getPostsAll($type = 'posts');
    public function getWithPagination($filters, $type);
    public function getPostByID($post_id);
    public function getPostMedias($post_id, $image_dimension= '');
    public function getPostUrl($post_id);

    public function getRelatedPosts($post_id, array $where, $number = 10, $order_by = 'order', $order = 'asc',  $columns = ['*']);
    public function getRelatedPostsPaginate($post_id, array $where, $number = 10, $order_by = 'order', $order = 'asc',  $columns = ['*']);
    public function getPostsWithCategory($category_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']);
    public function getPostsWithCategoryPaginate($category_id, array $where, $number = 10, $order_by = 'order', $order = 'asc', $columns = ['*']);
    public function getSearchResult($key_word,array $list_field  = ['title'], array $where, $category_id = 0,$number = 10,$order_by = 'order', $order = 'asc', $columns = ['*']);
    public function getSearchResultPaginate($key_word, array $list_field  = ['title'], array $where, $category_id = 0,$number = 10,$order_by = 'order', $order = 'asc', $columns = ['*']);

    public function getListHotPosts($numbert_of_posts = null, $type = 'posts');
    public function getListRelatedPosts($post, $numbert_of_posts = null);
    public function getListOfSearchingPosts($search, $numbert_of_posts = null, $type = 'posts', $absolute_search = false);
    public function getListPaginatedHotPosts($per_page = 15, $type = 'posts');
    public function getListPaginatedRelatedPosts($post, $per_page = 15);
    public function getListPaginatedOfSearchingPosts($search, $per_page = 15, $type = 'posts', $absolute_search = false);

    public function getListHotTranslatablePosts($numbert_of_posts = null, $type = 'posts');
    public function getListRelatedTranslatablePosts($post, $numbert_of_posts = null);
    public function getListOfSearchingTranslatablePosts($search, $numbert_of_posts = null, $type = 'posts', $absolute_search = false);
    public function getListPaginatedHotTranslatablePosts($per_page = 15, $type = 'posts');
    public function getListPaginatedRelatedTranslatablePosts($post, $per_page = 15);
    public function getListPaginatedOfSearchingTranslatablePosts($search, $per_page = 15, $type = 'posts', $absolute_search = false);
}
