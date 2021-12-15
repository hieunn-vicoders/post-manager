<?php

namespace VCComponent\Laravel\Post\Test\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Repositories\PostRepositoryEloquent;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Test\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_get_list_hot_posts_by_reposotory_function()
    {
        $post_repository = app(PostRepositoryEloquent::class);

        $data_posts = factory(Post::class, 3)->create(['is_hot' => 1])->sortBy('name')->sortByDesc('created_at')->sortBy('order');
        $data_pages = factory(Post::class, 3)->state('pages')->create(['is_hot' => 1])->sortBy('name')->sortByDesc('created_at')->sortBy('order');

        $posts = $post_repository->getListHotPosts(3);
        $pages = $post_repository->getListHotPosts(3, 'pages');

        $this->assertPostsEqualDatas($posts, $data_posts);
        $this->assertPostsEqualDatas($pages, $data_pages);
    }

    /**
     * @test
     */
    public function can_get_list_paginated_hot_posts_by_reposotory_function()
    {
        $post_repository = app(PostRepositoryEloquent::class);

        $data_posts = factory(Post::class, 1)->create(['is_hot' => 1])->sortBy('name')->sortByDesc('created_at')->sortBy('order');
        $data_pages = factory(Post::class, 1)->state('pages')->create(['is_hot' => 1])->sortBy('name')->sortByDesc('created_at')->sortBy('order');

        $posts = $post_repository->getListPaginatedHotPosts(1);
        $pages = $post_repository->getListPaginatedHotPosts(1, 'pages');

        $this->assertTrue($posts instanceof LengthAwarePaginator);
        $this->assertTrue($pages instanceof LengthAwarePaginator);
        $this->assertPostsEqualDatas($posts, $data_posts);
        $this->assertPostsEqualDatas($pages, $data_pages);
    }

    /**
     * @test
     */
    public function can_get_list_related_posts_by_reposotory_function()
    {
        $post_repository = app(PostRepositoryEloquent::class);

        $post = factory(Post::class)->create();
        $related_posts = factory(Post::class, 3)->create()->sortBy('name')->sortByDesc('created_at')->sortBy('order');

        $posts = $post_repository->getListRelatedPosts($post ,3);

        $this->assertPostsEqualDatas($posts, $related_posts);
    }

    /**
     * @test
     */
    public function can_get_list_paginated_related_posts_by_reposotory_function()
    {
        $post_repository = app(PostRepositoryEloquent::class);

        $post = factory(Post::class)->create();
        $related_posts = factory(Post::class, 3)->create()->sortBy('name')->sortByDesc('created_at')->sortBy('order');

        $posts = $post_repository->getListPaginatedRelatedPosts($post ,3);

        $this->assertPostsEqualDatas($posts, $related_posts);

        $this->assertTrue($posts instanceof LengthAwarePaginator);
        $this->assertPostsEqualDatas($posts, $related_posts);
    }

    /**
     * @test
     */
    public function can_get_list_of_searching_posts_by_reposotory_function()
    {
        $post_repository = app(PostRepositoryEloquent::class);

        factory(Post::class, 2)->create();
        $of_searching_posts = factory(Post::class, 3)->create([
            'title' => 'searching_title'
        ])->sortByDesc('created_at')->sortBy('order');

        $posts = $post_repository->getListOfSearchingPosts('searching_title');

        $this->assertPostsEqualDatas($posts, $of_searching_posts);
    }

    /**
     * @test
     */
    public function can_get_list_paginated_of_searching_posts_by_reposotory_function()
    {
        $post_repository = app(PostRepositoryEloquent::class);

        factory(Post::class, 2)->create();
        $of_searching_posts = factory(Post::class, 3)->create([
            'title' => 'searching_title'
        ])->sortByDesc('created_at')->sortBy('order');

        $posts = $post_repository->getListPaginatedOfSearchingPosts('searching_title');

        $this->assertTrue($posts instanceof LengthAwarePaginator);;
        $this->assertPostsEqualDatas($posts, $of_searching_posts);

    }

    protected function assertPostsEqualDatas($posts, $datas) {
        $this->assertEquals($posts->pluck('title'), $datas->pluck('title'));
        $this->assertEquals($posts->pluck('description'), $datas->pluck('description'));
        $this->assertEquals($posts->pluck('content'), $datas->pluck('content'));
        $this->assertEquals($posts->pluck('order'), $datas->pluck('order'));
    }
}
