<?php

namespace VCComponent\Laravel\Post\Test\Feature\Post;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Test\TestCase;

class AdminPageControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_create_page_by_admin_router()
    {
        $data = factory(Post::class)->state('pages')->create()->toArray();
        
        unset($data['updated_at']);
        unset($data['created_at']);

        $response = $this->json('POST', 'api/post-management/admin/pages', $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'title'       => $data['title'],
            'description' => $data['description'],
            'content'     => $data['content'],
        ],
        ]);

        $this->assertDatabaseHas('posts', $data);
    }

    /**
     * @test
     */
    public function can_update_post_by_admin_router()
    {
        $post = factory(Post::class)->state('pages')->create();

        unset($post['updated_at']);
        unset($post['created_at']);

        $id          = $post->id;
        $post->title = 'update title';
        $data        = $post->toArray();

        $response = $this->json('PUT', 'api/post-management/admin/pages/' . $id, $data);

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
    public function can_delete_post_by_admin_router()
    {
        $post = factory(Post::class)->state('pages')->create();

        $post = $post->toArray();

        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->call('DELETE', 'api/post-management/admin/pages/' . $post['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertSoftDeleted('posts', $post);
    }

    /**
     * @test
     */
    public function can_get_post_item_by_admin_router()
    {
        $post = factory(Post::class)->state('pages')->create();

        $response = $this->call('GET', 'api/post-management/admin/pages/' . $post->id);
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
    public function can_get_post_list_by_admin_router()
    {
        $posts = factory(Post::class)->state('pages')->create();

        $response = $this->call('GET', 'api/post-management/admin/pages');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function can_get_all_pages_with_no_paginate_by_admin_router()
    {
        $posts = factory(Post::class)->state('pages')->create();

        $response = $this->call('GET', 'api/post-management/admin/pages/all');

        $response->assertStatus(200);
        $response->assertJsonMissingExact([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function can_bulk_update_status_pages_by_admin()
    {
        $posts = factory(Post::class, 5)->state('pages')->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data    = ['ids' => $listIds, 'status' => 5];

        $response = $this->json('GET', 'api/post-management/admin/pages/all');
        $response->assertJsonFragment(['status' => 0]);

        $response = $this->json('PUT', 'api/post-management/admin/pages/status/bulk', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->json('GET', 'api/post-management/admin/pages/all');
        $response->assertJsonFragment(['status' => 5]);
    }

     /**
     * @test
     */
    public function can_update_status_a_page_by_admin()
    {
        $post = factory(Post::class)->state('pages')->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $data     = ['status' => 2];
        $response = $this->json('PUT', 'api/post-management/admin/pages/' . $post['id'] . '/status', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->json('GET', 'api/post-management/admin/pages/' . $post['id']);

        $response->assertJson(['data' => $data]);
    }

    /**
     * @test
     */
    public function can_get_field_meta_pages_by_admin_router()
    {
        $post = new \VCComponent\Laravel\Post\Test\Stubs\Models\Post;
    
        $response = $this->call('GET', 'api/post-management/admin/pages/field-meta');

        $response->assertStatus(200);
        $response->assertJson(['data' => $post->pagesSchema()]);
    }
}
