<?php

namespace VCComponent\Laravel\Post\Test\Feature\Post;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Test\TestCase;

class AdminPostControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_create_post_by_admin_router()
    {
        $data = factory(Post::class)->make()->toArray();

        $response = $this->json('POST', 'api/post-management/admin/posts', $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('posts', $data);
    }

    /**
     * @test
     */
    public function can_update_post_by_admin_router()
    {
        $post = factory(Post::class)->make();
        $post->save();

        unset($post['updated_at']);
        unset($post['created_at']);

        $id          = $post->id;
        $post->title = 'update title';
        $data        = $post->toArray();

        $response = $this->json('PUT', 'api/post-management/admin/posts/' . $id, $data);

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
    public function can_soft_delete_post_by_admin_router()
    {
        $post = factory(Post::class)->create()->toArray();

        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->call('DELETE', 'api/post-management/admin/posts/' . $post['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertSoftDeleted('posts', $post);
    }

    /**
     * @test
     */
    public function can_get_post_item_by_admin_router()
    {
        $post = factory(Post::class)->create();

        unset($post['updated_at']);
        unset($post['created_at']);

        $response = $this->call('GET', 'api/post-management/admin/posts/' . $post->id);

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
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();
        
        $listIds = array_column($posts, 'id');
        array_multisort($listIds, SORT_DESC, $posts);

        $response = $this->call('GET', 'api/post-management/admin/posts');

        $response->assertStatus(200);

        foreach ($posts as $item) {
           $this->assertDatabaseHas('posts', $item);
        }
    }

    /**
     * @test
     */
    public function can_get_field_meta_post_by_admin_router()
    {
        $post = new \VCComponent\Laravel\Post\Test\Stubs\Models\Post;
    
        $response = $this->call('GET', 'api/post-management/admin/posts/field-meta');
       
        $response->assertStatus(200);
        $response->assertJson(['data' => $post->schema()]);
    }

    /**
     * @test
     */
    public function can_delete_post_by_admin_router()
    {
        $post = factory(Post::class)->create()->toArray();

        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->call('DELETE', 'api/post-management/admin/posts/' . $post['id'] . '/force');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDeleted('posts', $post);
    }

    /**
     * @test
     */
    public function can_delete_all_trash_post_by_admin_router()
    {
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data    = ["ids" => $listIds];

        $response = $this->call('DELETE', 'api/post-management/admin/posts/bulk', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->call('GET', 'api/post-management/admin/posts/trash/all');

        $response->assertJsonCount(5, 'data');

        $response = $this->call('DELETE', 'api/post-management/admin/posts/trash/all');
        $response->assertJson(['success' => true]);

        foreach ($posts as $item) {
            $this->assertDeleted('posts', $item);
        }
    }

     /**
     * @test
     */
    public function can_bulk_delete_posts_trash_by_admin()
    {
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data    = ["ids" => $listIds];

        $response = $this->call('DELETE', 'api/post-management/admin/posts/trash/bulk', $data);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'post not found']);

        $response = $this->call('DELETE', 'api/post-management/admin/posts/bulk', $data);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->call('GET', 'api/post-management/admin/posts/trash/all');
        $response->assertJsonCount(5, 'data');

        $response = $this->call('DELETE', 'api/post-management/admin/posts/trash/bulk', $data);
        $response->assertJson(['success' => true]);

        foreach ($posts as $item) {
            $this->assertDeleted('posts', $item);
        }
    }

    /**
     * @test
     */
    public function can_delete_a_posts_by_admin()
    {
        $post = factory(Post::class)->create()->toArray();

        unset($post['updated_at']);
        unset($post['created_at']);

        $response = $this->json('DELETE', 'api/post-management/admin/posts/' . $post['id'] . '/trash');

        $response->assertJson(['success' => true]);
        $this->assertDeleted('posts', $post);
    }

    /**
     * @test
     */
    public function can_get_trash_list_with_no_paginate_by_admin()
    {
        $post = factory(Post::class)->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->call('DELETE', 'api/post-management/admin/posts/' . $post['id']);
        $response->assertJson(['success' => true]);

        $response = $this->call('GET', 'api/post-management/admin/posts/trash/all');
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
    public function can_get_trash_list_with_paginate_by_admin()
    {
        $post = factory(Post::class)->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->call('DELETE', 'api/post-management/admin/posts/' . $post['id']);
        $response->assertJson(['success' => true]);

        $response = $this->call('GET', 'api/post-management/admin/posts/trash');
        $response->assertJsonStructure([
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
    public function can_bulk_restore_posts_by_admin_router()
    {
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data    = ["ids" => $listIds];

        $response = $this->call('DELETE', 'api/post-management/admin/posts/bulk', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        foreach ($posts as $item) {
            $this->assertSoftDeleted('posts', $item);
        }

        $response = $this->call('PUT', 'api/post-management/admin/posts/trash/bulk/restores', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        foreach ($posts as $item) {
            $response = $this->call('GET', 'api/post-management/admin/posts/' . $item['id']);
            $response->assertStatus(200);
            $response->assertJson(['data' => $item]);
        }
    }

    /**
     * @test
     */
    public function can_restore_a_post_by_admin_router()
    {

        $post = factory(Post::class)->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $response = $this->call('DELETE', 'api/post-management/admin/posts/' . $post['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertSoftDeleted('posts', $post);

        $response = $this->call('PUT', 'api/post-management/admin/posts/trash/' . $post['id'] . '/restore');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->call('GET', 'api/post-management/admin/posts/' . $post['id']);
        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);

    }

     /**
     * @test
     */
    public function can_change_published_date_a_post_by_admin()
    {
        $post = factory(post::class)->create()->toArray();

        $data     = ['published_date' => date('Y-m-d', strtotime('20-10-2020'))];
        $response = $this->json('PUT', 'api/post-management/admin/posts/' . $post['id'] . '/date', $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);
    }

     /**
     * @test
     */
    public function can_get_all_post_by_admin()
    {
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();
        
        $listIds = array_column($posts, 'id');
        array_multisort($listIds, SORT_DESC, $posts);

        $response = $this->call('GET', 'api/post-management/admin/posts/list-all');

        $response->assertStatus(200);
        $response->assertJsonMissingExact([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);

        foreach ($posts as $item) {
           $this->assertDatabaseHas('posts', $item);
        }
    }

    /**
     * @test
     */
    public function can_bulk_update_status_posts_by_admin()
    {
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();
        
        $listIds = array_column($posts, 'id');
        $data    = ['ids' => $listIds, 'status' => 5];

        $response = $this->json('GET', 'api/post-management/admin/posts/all');
        $response->assertJsonFragment(['status' => 0]);

        $response = $this->json('PUT', 'api/post-management/admin/posts/status/bulk', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->json('GET', 'api/post-management/admin/posts/all');
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
        $response = $this->json('PUT', 'api/post-management/admin/posts/' . $post['id'] . '/status', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->json('GET', 'api/post-management/admin/posts/' . $post['id']);

        $response->assertJson(['data' => $data]);
    }

    /**
     * @test
     */
    public function can_get_post_type_by_admin()
    {
        $response = $this->json('GET', 'api/post-management/admin/postTypes');
        
        $entity       = new \VCComponent\Laravel\Post\Test\Stubs\Models\Post;
        $getpostTypes = $entity->postTypes();
        $response->assertJson([
            'data' => $getpostTypes,
        ]);
    }

    /**
     * @test
     */
    public function can_bulk_soft_delete_all_post_by_admin_router()
    {
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data    = ["ids" => $listIds];

        $response = $this->call('DELETE', 'api/post-management/admin/posts/bulk', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        foreach ($posts as $item) {
            $this->assertSoftDeleted('posts', $item);
        }
    }

    /**
     * @test
     */
    public function can_get_post_list_with_no_paginate_by_admin()
    {
        $post = factory(Post::class)->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);
        
        $response = $this->call('GET', 'api/post-management/admin/posts/all');
        $response->assertJsonMissingExact([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
        $response->assertJson(['data' => [$post]]);
    }
}
