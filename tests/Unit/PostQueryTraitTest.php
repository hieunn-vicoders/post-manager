<?php

namespace VCComponent\Laravel\Post\Test\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
// use VCComponent\Laravel\Category\Categories\Facades\Category;
use VCComponent\Laravel\Post\Entities\Post as BasePost;
use VCComponent\Laravel\Post\Repositories\PostRepository;
use VCComponent\Laravel\Post\Test\Stubs\Models\Post;
use VCComponent\Laravel\Post\Test\Unit\PostQueryTraitTestCase;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;
use VCComponent\Laravel\Post\Repositories\PostRepositoryEloquent;
// use VCComponent\Laravel\Post\Repositories\PostRepository;


class PostQueryTraitTest extends PostQueryTraitTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_get_post_by_type()
    {
        $posts  = factory(BasePost::class, 5)->create();
        $abouts = factory(BasePost::class, 10)->create(['type' => 'about']);
        $this->assertInstanceOf(Collection::class, Post::getByType());
        $this->assertInstanceOf(Collection::class, Post::getByType('about', 5));
        $this->assertCount(5, Post::getByType()->toArray());
        $this->assertCount(10, Post::getByType('about')->toArray());
    }

    /**
     * @test
     */

    public function can_get_post_by_type_with_pagination()
    {
        $posts  = factory(BasePost::class, 5)->create();
        $abouts = factory(BasePost::class, 10)->create(['type' => 'about']);

        $this->assertInstanceOf(LengthAwarePaginator::class, Post::getByTypeWithPagination());
        $this->assertInstanceOf(LengthAwarePaginator::class, Post::getByTypeWithPagination('about', 5));

        $this->assertCount(5, Post::getByTypeWithPagination()->getCollection()->toArray());
        $this->assertCount(5, Post::getByTypeWithPagination('about', 5)->getCollection()->toArray());
    }

    /**
     * @test
     */
    public function can_find_post_by_type()
    {
        $post  = factory(BasePost::class)->create();
        $about = factory(BasePost::class)->create(['type' => 'about']);

        $this->assertSame($post->id, Post::findByType($post->id)->id);
        $this->assertSame($about->id, Post::findByType($about->id, 'about')->id);
    }

    /**
     * @test
     */
    public function expect_not_found_exception_if_no_post_found_when_find_post_by_type()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('About not found');

        $post = factory(BasePost::class)->create();
        Post::findByType($post->id, 'about');
    }


    /**
     * @test
     */
    public function can_find_by_field()
    {
        $repository = App::make(PostRepository::class);
        $post  = factory(BasePost::class)->create();
        $about = factory(BasePost::class)->create(['type' => 'about']);
        $this->assertSame($post->id, $repository->findByField('id', $post->id)[0]->id);
        $this->assertSame($about->id, $repository->findByField('id', $about->id, 'about')[0]->id);
    }
    /**
     * @test
     */
    public function can_find_by_where()
    {
        $repository = App::make(PostRepository::class);
        $post  = factory(BasePost::class)->create();
        $about = factory(BasePost::class)->create(['type' => 'about']);
        $this->assertSame($post->id, $repository->findByWhere(['id' => $post->id])[0]->id);
        $this->assertSame($about->id, $repository->findByWhere(['id' => $about->id], 'about')[0]->id);
    }
    /**
     * @test
     */
    public function can_find_by_where_paginate()
    {
        $repository = App::make(PostRepository::class);
        $post  = factory(BasePost::class)->create();
        $about = factory(BasePost::class)->create(['type' => 'about']);
        $this->assertSame($post->id, $repository->findByWherePaginate(['id' => $post->id])[0]->id);
        $this->assertSame($about->id, $repository->findByWherePaginate(['id' => $about->id], 'about')[0]->id);
    }
    /**
     * @test
     */
    public function can_get_post_all()
    {
        $repository = App::make(PostRepository::class);
        $post  = factory(BasePost::class)->create();
        $about = factory(BasePost::class)->create(['type' => 'about']);
        $this->assertSame($post->id, $repository->getPostsAll()[0]->id);
        $this->assertSame($about->id, $repository->getPostsAll('about')[0]->id);
    }

    /**
     * @test
     */
    public function can_get_post_by_id()
    {
        $repository = App::make(PostRepository::class);
        $post  = factory(BasePost::class)->create();
        $about = factory(BasePost::class)->create(['type' => 'about']);
        $this->assertSame($post->id, $repository->getPostByID($post->id)->id);
        $this->assertSame($about->id, $repository->getPostByID($about->id)->id);
    }

    /**
     * @test
     */
    public function can_get_post_url()
    {
        $repository = App::make(PostRepository::class);
        $post  = factory(BasePost::class)->create();
        $about = factory(BasePost::class)->create(['type' => 'about']);
        $this->assertSame('/posts/'.$post->slug, $repository->getPostUrl($post->id));
        $this->assertSame('/about/'.$about->slug, $repository->getPostUrl($about->id));
    }

    /**
     * @test
     */
    public function can_get_post_meta_data()
    {
        $post = factory(BasePost::class)->create();
        $post->postMetas()->create(['key' => 'address', 'value' => 'test']);

        $this->assertSame('test', $post->getMetaField('address'));
    }

    /**
     * @test
     */
    public function expect_not_found_exception_if_post_has_no_meta_data()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('address field not found');

        $post = factory(BasePost::class)->create();

        $this->assertSame('test', $post->getMetaField('address'));
    }

    /**
     * @test
     */


    public function can_get_search_result_paginate() {
        $repository = App::make(PostRepository::class);
        $post_about  = factory(BasePost::class)->create(['title'=>'post about']);
        $post_test  = factory(BasePost::class)->create(['title'=>'post test']);
        $this->assertSame($post_test->title, $repository->getSearchResultPaginate('test',['title'],[])[0]->title);
    }
    /**
     * @test
     */

    public function can_get_search_result() {
        $repository = App::make(PostRepository::class);
        $post_about  = factory(BasePost::class)->create(['title'=>'post about']);
        $post_test  = factory(BasePost::class)->create(['title'=>'post test']);
        $this->assertSame($post_test->title, $repository->getSearchResult('test',['title'],[])[0]->title);
    }
    /**
     * @test
     */

    public function can_get_related_posts_paginate() {
        $repository = App::make(PostRepository::class);
        $post_a  = factory(BasePost::class)->create(['title'=>'a']);
        $post_c  = factory(BasePost::class)->create(['title'=>'c','type'=>'about']);
        $post_b  = factory(BasePost::class)->create(['title'=>'b']);
        $this->assertSame($post_b->title, $repository->getRelatedPostsPaginate($post_a->id,['type'=>'posts'])[0]->title);
    }
    /**
     * @test
     */

    public function can_get_related_posts() {
        $repository = App::make(PostRepository::class);
        $post_a  = factory(BasePost::class)->create(['title'=>'a']);
        $post_c  = factory(BasePost::class)->create(['title'=>'c','type'=>'about']);
        $post_b  = factory(BasePost::class)->create(['title'=>'b']);

        $this->assertSame($post_b->title, $repository->getRelatedPosts($post_a->id,['type'=>'posts'])[0]->title);
    }
    /**
     * @test
     */

    public function expect_not_found_exception_if_post_does_not_have_meta_field()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('address field not found');

        $post = factory(BasePost::class)->create();
        $post->postMetas()->create(['key' => 'phone', 'value' => 'test']);

        $this->assertSame('test', $post->getMetaField('phone'));
        $this->assertSame('test', $post->getMetaField('address'));
    }






}
