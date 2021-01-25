<?php

namespace VCComponent\Laravel\Post\Test\Feature\Api\Post;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Test\TestCase;

class FrontendPostControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_create_post_by_frontend_router()
    {
        $data = factory(Post::class)->make()->toArray();

        $response = $this->json('POST', 'api/post-management/posts', $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('posts', $data);
    }

    /**
     * @test
     */
    public function can_update_post_by_frontend_router()
    {
        $post = factory(Post::class)->make();
        $post->save();

        unset($post['updated_at']);
        unset($post['created_at']);

        $id          = $post->id;
        $post->title = 'update title';
        $data        = $post->toArray();

        $response = $this->json('PUT', 'api/post-management/posts/' . $id, $data);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'title' => $data['title'],
            ],
        ]);

        $this->assertDatabaseHas('posts', $data);
    }

    /**
     * @test
     */
    public function can_delete_post_by_frontend_router()
    {
        $post = factory(Post::class)->create()->toArray();
       
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);
       
        $response = $this->call('DELETE', 'api/post-management/posts/' . $post['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertSoftDeleted('posts', $post);
    }

    /**
     * @test
     */
    public function can_get_post_item_by_frontend_router()
    {
        $post = factory(Post::class)->create();

        $response = $this->call('GET', 'api/post-management/posts/' . $post->id);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'title'       => $post->title,
                'description' => $post->description,
                'content'     => $post->content,
            ],
        ]);
    }

    /**
     * @test
     */
    public function can_get_post_list_by_frontend_router()
    {
        $posts = factory(Post::class, 5)->create();

        $response = $this->call('GET', 'api/post-management/posts');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function can_get_post_list_with_no_paginate_by_frontend_router()
    {
        $post = factory(Post::class)->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);
        
        $response = $this->call('GET', 'api/post-management/posts/all');
        $response->assertJsonMissingExact([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
        $response->assertJson(['data' => [$post]]);
    }

     /**
     * @test
     */
    public function can_bulk_update_status_posts_by_frontend_router()
    {
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();
        
        $listIds = array_column($posts, 'id');
        $data    = ['ids' => $listIds, 'status' => 5];

        $response = $this->json('GET', 'api/post-management/posts/all');
        $response->assertJsonFragment(['status' => 1]);

        $response = $this->json('PUT', 'api/post-management/posts/status/bulk', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->json('GET', 'api/post-management/posts/all');
        $response->assertJsonFragment(['status' => 5]);
    }

    /**
     * @test
     */
    public function can_update_status_a_post_by_admin()
    {
        $post = factory(Post::class)->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $data     = ['status' => 2];
        $response = $this->json('PUT', 'api/post-management/posts/' . $post['id'] . '/status', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->json('GET', 'api/post-management/posts/' . $post['id']);

        $response->assertJson(['data' => $data]);
    }
}
