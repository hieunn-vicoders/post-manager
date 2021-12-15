<?php

namespace VCComponent\Laravel\Post\Test\Feature\Api\Post;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Entities\PostMeta;
use VCComponent\Laravel\Post\Entities\PostSchema;
use VCComponent\Laravel\Post\Test\TestCase;

class AdminPostControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_create_post_by_admin_router()
    {
        $token = $this->loginToken();
        $data = factory(Post::class)->make()->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('POST', 'api/post-management/admin/posts', $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('posts', $data);
    }

    /**
     * @test
     */
    public function can_update_post_by_admin_router()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->make();
        $post->save();

        unset($post['updated_at']);
        unset($post['created_at']);

        $id = $post->id;
        $post->title = 'update title';
        $data = $post->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/' . $id, $data);

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
        $token = $this->loginToken();
        $post = factory(Post::class)->create()->toArray();

        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/' . $post['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertSoftDeleted('posts', $post);
    }

    /**
     * @test
     */
    public function can_get_post_item_by_admin_router()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->create();

        unset($post['updated_at']);
        unset($post['created_at']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/' . $post->id);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'title' => $post->title,
                'description' => $post->description,
                'content' => $post->content,
                'blocks' => $post->blocks,
                'editor_type' => $post->editor_type
            ],
        ]);
    }

    /**
     * @test
     */
    public function can_get_post_list_by_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        array_multisort($listIds, SORT_DESC, $posts);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts');

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
        $token = $this->loginToken();
        factory(PostSchema::class)->states('posts')->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/field-meta');
        $response->assertStatus(200);
        $schemas = PostSchema::get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'label' => $item->label,
                'schema_type_id' => $item->schema_type_id,
                'schema_rule_id' => $item->schema_rule_id,
                'post_type' => $item->post_type,
                'timestamps' => [
                    'created_at' => $item->created_at->toJSON(),
                    'updated_at' => $item->updated_at->toJSON(),
                ],
            ];
        })->toArray();

        $response->assertJson([
            'data' => $schemas,
        ]);
    }

    /**
     * @test
     */
    public function can_delete_post_by_admin_router()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->create()->toArray();

        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/' . $post['id'] . '/force');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDeleted('posts', $post);
    }

    /**
     * @test
     */
    public function can_delete_all_trash_post_by_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ["ids" => $listIds];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/bulk', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/trash/all');

        $response->assertJsonCount(5, 'data');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/trash/all');
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
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ["ids" => $listIds];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/trash/bulk', $data);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'post not found']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/bulk', $data);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/trash/all');
        $response->assertJsonCount(5, 'data');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/trash/bulk', $data);
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
        $token = $this->loginToken();
        $post = factory(Post::class)->create()->toArray();

        unset($post['updated_at']);
        unset($post['created_at']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/' . $post['id'] . '/trash');

        $response->assertJson(['success' => true]);
        $this->assertDeleted('posts', $post);
    }

    /**
     * @test
     */
    public function can_get_trash_list_with_no_paginate_by_admin()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/' . $post['id']);
        $response->assertJson(['success' => true]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/trash/all');
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
        $token = $this->loginToken();
        $post = factory(Post::class)->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/' . $post['id']);
        $response->assertJson(['success' => true]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/trash');
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
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ["ids" => $listIds];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/bulk', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        foreach ($posts as $item) {
            $this->assertSoftDeleted('posts', $item);
        }

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/trash/bulk/restores', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        foreach ($posts as $item) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/' . $item['id']);
            $response->assertStatus(200);
            $response->assertJson(['data' => $item]);
        }
    }

    /**
     * @test
     */
    public function can_restore_a_post_by_admin_router()
    {
        $token = $this->loginToken();

        $post = factory(Post::class)->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/' . $post['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertSoftDeleted('posts', $post);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/trash/' . $post['id'] . '/restore');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/' . $post['id']);
        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);
    }

    /**
     * @test
     */
    public function can_change_published_date_a_post_by_admin()
    {
        $token = $this->loginToken();
        $post = factory(post::class)->create()->toArray();

        $data = ['published_date' => date('Y-m-d', strtotime('20-10-2020'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/' . $post['id'] . '/date', $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);
    }

    /**
     * @test
     */
    public function can_get_all_post_by_admin()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        array_multisort($listIds, SORT_DESC, $posts);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all');

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
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ['ids' => $listIds, 'status' => 5];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all');
        $response->assertJsonFragment(['status' => 1]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/status/bulk', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all');
        $response->assertJsonFragment(['status' => 5]);
    }

    /**
     * @test
     */
    public function can_update_status_a_post_by_admin()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $data = ['status' => 2];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/' . $post['id'] . '/status', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/' . $post['id']);

        $response->assertJson(['data' => $data]);
    }

    /**
     * @test
     */
    public function can_get_post_type_by_admin()
    {
        $token = $this->loginToken();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/postTypes');

        $entity = new \VCComponent\Laravel\Post\Test\Stubs\Models\Post;
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
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ["ids" => $listIds];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/bulk', $data);

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
        $token = $this->loginToken();
        $post = factory(Post::class)->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all');
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
    public function should_not_delete_post_exist_not_by_admin_router()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', ['id' => 2]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/2/force');
        $this->assertExits($response, 'Post not found');

    }

    /**
     * @test
     */
    public function should_not_bulk_delete_posts_ids_required_trash_by_admin()
    {
        $token = $this->loginToken();
        $data = ["ids" => []];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/trash/bulk', $data);
        $this->assertValidator($response, 'ids', 'The ids field is required.');
    }
    /**
     * @test
     */
    public function should_not_bulk_delete_posts_ids_array_trash_by_admin()
    {
        $token = $this->loginToken();
        $data = ["ids" => 1];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/trash/bulk', $data);
        $this->assertValidator($response, 'ids', 'The ids must be an array.');
    }
    /**
     * @test
     */
    public function should_not_bulk_delete_posts_exist_not_trash_by_admin()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', ['id' => 1]);
        $data = ["ids" => [1]];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/trash/bulk', $data);
        $this->assertExits($response, 'post not found');

    }

    /**
     * @test
     */
    public function should_not_bulk_restore_posts_ids_required_by_admin_router()
    {
        $token = $this->loginToken();
        $data = ["ids" => []];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/trash/bulk/restores', $data);
        $this->assertValidator($response, 'ids', 'The ids field is required.');

    }

    /**
     * @test
     */
    public function should_not_bulk_restore_posts_ids_array_by_admin_router()
    {
        $token = $this->loginToken();
        $data = ["ids" => 1];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/trash/bulk/restores', $data);
        $this->assertValidator($response, 'ids', 'The ids must be an array.');

    }

    /**
     * @test
     */
    public function should_not_bulk_restore_posts_exist_not_by_admin_router()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', ['id' => 1]);
        $data = ["ids" => [1]];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/trash/bulk/restores', $data);
        $this->assertExits($response, 'Post not found');
    }

    /**
     * @test
     */
    public function should_not_change_published_date_a_post_by_admin()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', ['id' => 1]);
        $data = ['published_date' => date('Y-m-d', strtotime('20-10-2020'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/1/date', $data);
        $this->assertExits($response, 'Posts entity not found');

    }
    /**
     * @test
     */
    public function should_not_change_published_date_required_post_by_admin()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->create()->toArray();
        $data = ['published_date' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/' . $post['id'] . '/date', $data);
        $this->assertValidator($response, 'published_date', 'The published date field is required.');

    }
    /**
     * @test
     */
    public function should_not_get_list_post_from_field_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => '', 'from' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_list_post_from_field_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'test', 'from' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_list_post_field_from_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'updated', 'from' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all', $data);
        $this->assertRequired($response, 'Data missing');
    }
    /**
     * @test
     */
    public function should_get_list_posts_with_from_date_by_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create(['created_at' => '01/08/2021'])->toArray();
        foreach ($posts as $post) {
            unset($post['updated_at']);
            unset($post['created_at']);
        }
        $data = ['field' => 'created', 'from' => date('Y-m-d', strtotime('02/08/2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all', $data);
        $response->assertJsonFragment([
            'data' => [],
        ]);
        $response->assertJsonMissing([
            'data' => $posts,
        ]);
        $response->assertStatus(200);

    }
    /**
     * @test
     */
    public function should_not_get_list_post_field_from_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'updated', 'from' => '3/8/2021'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all', $data);
        $response->assertStatus(500);
    }
    /**
     * @test
     */
    public function should_not_get_list_post_to_field_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => '', 'to' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_list_post_to_field_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'test', 'to' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_list_post_field_to_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'updated', 'to' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all', $data);
        $this->assertRequired($response, 'Data missing');
    }
    /**
     * @test
     */
    public function should_not_get_list_post_field_to_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'updated', 'to' => '3/8/2021'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all', $data);
        $response->assertStatus(500);
    }
    /**
     * @test
     */
    public function should_get_list_posts_with_to_date_by_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create()->toArray();
        foreach ($posts as $post) {
            unset($post['updated_at']);
            unset($post['created_at']);
        }
        $data = ['field' => 'created', 'to' => date('Y-m-d', strtotime('02/08/2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all', $data);
        $response->assertJsonFragment([
            'data' => [],
        ]);
        $response->assertJsonMissing([
            'data' => $posts,
        ]);
        $response->assertStatus(200);

    }
    /**
     * @test
     */
    public function should_not_get_list_posts_with_status_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create()->toArray();
        $data = ['status' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all', $data);
        $this->assertRequired($response, 'The input status is incorrect');
    }
    /**
     * @test
     */
    public function should_get_list_posts_with_status_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create()->toArray();
        factory(Post::class, 5)->create(['status' => 2])->toArray();
        $data = ['status' => 1];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all', $data);
        $response->assertJsonFragment([
            'status' => 1,
        ]);
        $response->assertJsonMissing([
            'status' => 2,
        ]);

    }
    /**
     * @test
     */
    public function should_get_list_posts_with_constraints_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();
        $title_constraints = $posts[0]->title;
        $posts = $posts->map(function ($post) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post;
        })->toArray();

        $constraints = '{"title":"' . $title_constraints . '"}';

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all?constraints=' . $constraints);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [$posts[0]],
        ]);

        $response->assertJsonStructure([
            'data' => [],
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
    public function should_get_list_posts_with_search_admin_router()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $post = factory(Post::class)->create(['title' => 'test_post'])->toArray();
        unset($post['created_at']);
        unset($post['updated_at']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all?search=test_post');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [$post],
        ]);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonStructure([
            'data' => [],
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
    public function should_get_list_posts_with_order_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();
        $posts = $posts->map(function ($post) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post;
        })->toArray();
        $order_by = '{"id":"desc"}';
        $listId = array_column($posts, 'id');
        array_multisort($listId, SORT_DESC, $posts);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/list-all?order_by=' . $order_by);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => $posts,
        ]);
        $response->assertJsonStructure([
            'data' => [],
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
    public function should_not_get_all_post_from_field_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => '', 'from' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_post_from_field_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'test', 'from' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_post_field_from_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'updated', 'from' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all', $data);
        $this->assertRequired($response, 'Data missing');
    }
    /**
     * @test
     */
    public function should_get_all_posts_with_from_date_by_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create(['created_at' => '01/08/2021'])->toArray();
        foreach ($posts as $post) {
            unset($post['updated_at']);
            unset($post['created_at']);
        }
        $data = ['field' => 'created', 'from' => date('Y-m-d', strtotime('02/08/2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all', $data);
        $response->assertJsonFragment([
            'data' => [],
        ]);
        $response->assertJsonMissing([
            'data' => $posts,
        ]);
        $response->assertStatus(200);

    }
    /**
     * @test
     */
    public function should_not_get_all_post_field_from_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'updated', 'from' => '3/8/2021'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all', $data);
        $response->assertStatus(500);
    }
    /**
     * @test
     */
    public function should_not_get_all_post_to_field_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => '', 'to' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_post_to_field_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'test', 'to' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_post_field_to_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'updated', 'to' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all', $data);
        $this->assertRequired($response, 'Data missing');
    }
    /**
     * @test
     */
    public function should_not_get_all_post_field_to_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'updated', 'to' => '3/8/2021'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all', $data);
        $response->assertStatus(500);
    }
    /**
     * @test
     */
    public function should_get_all_posts_with_to_date_by_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create()->toArray();
        foreach ($posts as $post) {
            unset($post['updated_at']);
            unset($post['created_at']);
        }
        $data = ['field' => 'created', 'to' => date('Y-m-d', strtotime('02/08/2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all', $data);
        $response->assertJsonFragment([
            'data' => [],
        ]);
        $response->assertJsonMissing([
            'data' => $posts,
        ]);
        $response->assertStatus(200);

    }
    /**
     * @test
     */
    public function should_not_get_all_posts_with_status_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create()->toArray();
        $data = ['status' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all', $data);
        $this->assertRequired($response, 'The input status is incorrect');
    }
    /**
     * @test
     */
    public function should_get_all_posts_with_status_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create()->toArray();
        factory(Post::class, 5)->create(['status' => 2])->toArray();
        $data = ['status' => 1];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all', $data);
        $response->assertJsonFragment([
            'status' => 1,
        ]);
        $response->assertJsonMissing([
            'status' => 2,
        ]);

    }
    /**
     * @test
     */
    public function should_get_all_posts_with_constraints_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();
        $title_constraints = $posts[0]->title;
        $posts = $posts->map(function ($post) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post;
        })->toArray();

        $constraints = '{"title":"' . $title_constraints . '"}';

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all?constraints=' . $constraints);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [$posts[0]],
        ]);
    }
    /**
     * @test
     */
    public function should_get_all_posts_with_search_admin_router()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $post = factory(Post::class)->create(['title' => 'test_post'])->toArray();
        unset($post['created_at']);
        unset($post['updated_at']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all?search=test_post');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJson([
            'data' => [$post],
        ]);

    }
    /**
     * @test
     */
    public function should_get_all_posts_with_order_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();
        $posts = $posts->map(function ($post) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post;
        })->toArray();
        $order_by = '{"id":"desc"}';
        $listId = array_column($posts, 'id');
        array_multisort($listId, SORT_DESC, $posts);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/all?order_by=' . $order_by);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => $posts,
        ]);

    }

    /**
     * @test
     */
    public function should_not_bulk_update_status_posts_ids_required_by_admin()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();
        $data = ['ids' => [], 'status' => 5];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/status/bulk', $data);
        $this->assertValidator($response, 'ids', 'The ids field is required.');
    }
    /**
     * @test
     */
    public function should_not_bulk_update_status_posts_status_required_by_admin()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create()->toArray();
        $listIds = array_column($posts, 'id');
        $data = ['ids' => $listIds, 'status' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/status/bulk', $data);
        $this->assertValidator($response, 'status', 'The status field is required.');
    }
    /**
     * @test
     */
    public function should_not_bulk_update_status_posts_status_not_exits_by_admin()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', [
            'id' => 1,
        ]);
        $data = ['ids' => [1], 'status' => 2];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/status/bulk', $data);
        $this->assertExits($response, 'Post not found');
    }
    /**
     * @test
     */
    public function should_not_update_status_posts_status_not_exits_by_admin()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', [
            'id' => 1,
        ]);
        $data = ['status' => 2];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/1/status', $data);
        $this->assertExits($response, 'posts entity not found');
    }
    /**
     * @test
     */
    public function should_not_update_status_posts_status_required_by_admin()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->create();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
        ]);
        $data = ['status' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/1/status', $data);
        $this->assertValidator($response, 'status', 'The status field is required.');
    }

    /**
     * @test
     */
    public function should_not_delete_bulk_post_ids_required_by_admin_router()
    {
        $token = $this->loginToken();
        $data = ["ids" => []];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/bulk', $data);
        $this->assertValidator($response, 'ids', 'The ids field is required.');

    }
    /**
     * @test
     */
    public function should_not_delete_bulk_post_ids_array_by_admin_router()
    {
        $token = $this->loginToken();
        $data = ["ids" => 1];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/bulk', $data);
        $this->assertValidator($response, 'ids', 'The ids must be an array.');
    }
    /**
     * @test
     */
    public function should_not_delete_bulk_post_not_exits_by_admin_router()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', [
            'id' => 1,
        ]);
        $data = ["ids" => [1]];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/bulk', $data);
        $this->assertExits($response, 'Post not found');

    }

    /**
     * @test
     */
    public function should_not_get_all_paginate_post_from_field_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => '', 'from' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_post_from_field_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'test', 'from' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_post_field_from_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'updated', 'from' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts', $data);
        $this->assertRequired($response, 'Data missing');
    }
    /**
     * @test
     */
    public function should_get_all_paginate_posts_with_from_date_by_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create(['created_at' => '01/08/2021'])->toArray();
        foreach ($posts as $post) {
            unset($post['updated_at']);
            unset($post['created_at']);
        }
        $data = ['field' => 'created', 'from' => date('Y-m-d', strtotime('02/08/2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts', $data);
        $response->assertJsonFragment([
            'data' => [],
        ]);
        $response->assertJsonMissing([
            'data' => $posts,
        ]);
        $response->assertJsonStructure([
            'data' => [],
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);

        $response->assertStatus(200);

    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_post_field_from_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'updated', 'from' => '3/8/2021'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts', $data);
        $response->assertStatus(500);
    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_post_to_field_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => '', 'to' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_post_to_field_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'test', 'to' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_post_field_to_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'updated', 'to' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts', $data);
        $this->assertRequired($response, 'Data missing');
    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_post_field_to_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $data = ['field' => 'updated', 'to' => '3/8/2021'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts', $data);
        $response->assertStatus(500);
    }
    /**
     * @test
     */
    public function should_get_all_paginate_posts_with_to_date_by_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create()->toArray();
        foreach ($posts as $post) {
            unset($post['updated_at']);
            unset($post['created_at']);
        }
        $data = ['field' => 'created', 'to' => date('Y-m-d', strtotime('02/08/2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts', $data);
        $response->assertJsonFragment([
            'data' => [],
        ]);
        $response->assertJsonMissing([
            'data' => $posts,
        ]);
        $response->assertJsonStructure([
            'data' => [],
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);

        $response->assertStatus(200);

    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_posts_with_status_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create()->toArray();
        $data = ['status' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts', $data);
        $this->assertRequired($response, 'The input status is incorrect');
    }
    /**
     * @test
     */
    public function should_get_all_paginate_posts_with_status_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create()->toArray();
        factory(Post::class, 5)->create(['status' => 2])->toArray();
        $data = ['status' => 1];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts', $data);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'status' => 1,
        ]);
        $response->assertJsonMissing([
            'status' => 2,
        ]);
        $response->assertJsonStructure([
            'data' => [],
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
    public function should_get_all_paginate_posts_with_constraints_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();
        $title_constraints = $posts[0]->title;
        $posts = $posts->map(function ($post) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post;
        })->toArray();

        $constraints = '{"title":"' . $title_constraints . '"}';

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts?constraints=' . $constraints);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [$posts[0]],
        ]);
        $response->assertJsonStructure([
            'data' => [],
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
    public function should_get_all_paginate_posts_with_search_admin_router()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $post = factory(Post::class)->create(['title' => 'test_post'])->toArray();
        unset($post['created_at']);
        unset($post['updated_at']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts?search=test_post');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJson([
            'data' => [$post],
        ]);
        $response->assertJsonStructure([
            'data' => [],
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
    public function should_get_all_paginate_posts_with_order_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();
        $posts = $posts->map(function ($post) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post;
        })->toArray();
        $order_by = '{"id":"desc"}';
        $listId = array_column($posts, 'id');
        array_multisort($listId, SORT_DESC, $posts);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts?order_by=' . $order_by);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => $posts,
        ]);
        $response->assertJsonStructure([
            'data' => [],
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

    public function can_create_schema_when_create_post__by_admin()
    {
        $token = $this->loginToken();
        $schemas = factory(PostSchema::class, 1)->create();
        $post_metas = [];
        foreach ($schemas as $schema) {
            $post_metas[$schema->name] = $schema->name . "_value";
        }
        $post = factory(Post::class)->make($post_metas)->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('POST', 'api/post-management/admin/posts', $post);

        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseHas('post_meta', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @test
     */
    public function should_not_get_post_item_not_exits_by_admin_router()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', [
            'id' => 1,
        ]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/1');
        $this->assertExits($response, 'posts entity not found');
    }

    /**
     * @test
     */
    public function should_not_create_post_title_required_by_admin_router()
    {
        $token = $this->loginToken();
        $data = factory(Post::class)->make(['title' => ''])->toArray();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('POST', 'api/post-management/admin/posts', $data);
        $this->assertValidator($response, 'title', 'The title field is required.');
        $this->assertDatabaseMissing('posts', $data);
    }
    /**
     * @test
     */
    public function should_not_create_post_content_required_by_admin_router()
    {
        $token = $this->loginToken();
        $data = factory(Post::class)->make(['content' => ''])->toArray();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('POST', 'api/post-management/admin/posts', $data);
        $this->assertValidator($response, 'content', 'The content field is required.');
        $this->assertDatabaseMissing('posts', $data);
    }
    /**
     * @test
     */
    public function should_not_update_post_not_exits_by_admin_router()
    {
        $token = $this->loginToken();
        $data = factory(Post::class)->make()->toArray();
        $this->assertDatabaseMissing('posts', ['id' => 1]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/1', $data);
        $this->assertExits($response, 'Posts entity not found');
    }
    /**
     * @test
     */
    public function should_not_update_post_title_required_by_admin_router()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->create();
        $data = factory(Post::class)->make(['title' => ''])->toArray();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/' . $post->id, $data);
        $this->assertValidator($response, 'title', 'The title field is required.');
    }
    /**
     * @test
     */
    public function should_not_update_post_content_required_by_admin_router()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->create();
        $data = factory(Post::class)->make(['content' => ''])->toArray();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/' . $post->id, $data);
        $this->assertValidator($response, 'content', 'The content field is required.');
    }
    /**
     * @test
     */
    public function should_not_delete_post_not_exits_by_admin_router()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', ['id' => 1]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/posts/1');
        $this->assertExits($response, 'Posts not found');

    }

    /**
     * @test
     */
    public function should_not_get_all_pages_from_field_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => '', 'from' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_pages_from_field_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => 'test', 'from' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_pages_field_from_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => 'updated', 'from' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all', $data);
        $this->assertRequired($response, 'Data missing');
    }
    /**
     * @test
     */
    public function should_get_all_pages_with_from_date_by_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->states('pages')->create(['created_at' => '01/08/2021'])->toArray();
        foreach ($posts as $post) {
            unset($post['updated_at']);
            unset($post['created_at']);
        }
        $data = ['field' => 'created', 'from' => date('Y-m-d', strtotime('02/08/2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all', $data);
        $response->assertJsonFragment([
            'data' => [],
        ]);
        $response->assertJsonMissing([
            'data' => $posts,
        ]);
        $response->assertStatus(200);

    }
    /**
     * @test
     */
    public function should_not_get_all_pages_field_from_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => 'updated', 'from' => '3/8/2021'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all', $data);
        $response->assertStatus(500);
    }
    /**
     * @test
     */
    public function should_not_get_all_pages_to_field_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => '', 'to' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_pages_to_field_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => 'test', 'to' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_pages_field_to_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => 'updated', 'to' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all', $data);
        $this->assertRequired($response, 'Data missing');
    }
    /**
     * @test
     */
    public function should_not_get_all_pages_field_to_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => 'updated', 'to' => '3/8/2021'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all', $data);
        $response->assertStatus(500);
    }
    /**
     * @test
     */
    public function should_get_all_pages_with_to_date_by_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->states('pages')->create()->toArray();
        foreach ($posts as $post) {
            unset($post['updated_at']);
            unset($post['created_at']);
        }
        $data = ['field' => 'created', 'to' => date('Y-m-d', strtotime('02/08/2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all', $data);
        $response->assertJsonFragment([
            'data' => [],
        ]);
        $response->assertJsonMissing([
            'data' => $posts,
        ]);
        $response->assertStatus(200);

    }
    /**
     * @test
     */
    public function should_not_get_all_pages_with_status_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->states('pages')->create()->toArray();
        $data = ['status' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all', $data);
        $this->assertRequired($response, 'The input status is incorrect');
    }
    /**
     * @test
     */
    public function should_get_all_pages_with_status_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->states('pages')->create()->toArray();
        factory(Post::class, 5)->states('pages')->create(['status' => 2])->toArray();
        $data = ['status' => 1];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all', $data);
        $response->assertJsonFragment([
            'status' => 1,
        ]);
        $response->assertJsonMissing([
            'status' => 2,
        ]);

    }
    /**
     * @test
     */
    public function should_get_all_pages_with_constraints_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->states('pages')->create();
        $title_constraints = $posts[0]->title;
        $posts = $posts->map(function ($post) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post;
        })->toArray();

        $constraints = '{"title":"' . $title_constraints . '"}';

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all?constraints=' . $constraints);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [$posts[0]],
        ]);
    }
    /**
     * @test
     */
    public function should_get_all_pages_with_search_admin_router()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $post = factory(Post::class)->states('pages')->create(['title' => 'test_post'])->toArray();
        unset($post['created_at']);
        unset($post['updated_at']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all?search=test_post');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJson([
            'data' => [$post],
        ]);

    }
    /**
     * @test
     */
    public function should_get_all_pages_with_order_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->states('pages')->create();
        $posts = $posts->map(function ($post) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post;
        })->toArray();
        $order_by = '{"id":"desc"}';
        $listId = array_column($posts, 'id');
        array_multisort($listId, SORT_DESC, $posts);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/all?order_by=' . $order_by);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => $posts,
        ]);

    }
    /**
     * @test
     */
    public function should_not_bulk_update_status_pages_ids_required_by_admin()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create();
        $data = ['ids' => [], 'status' => 5];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/pages/status/bulk', $data);
        $this->assertValidator($response, 'ids', 'The ids field is required.');
    }
    /**
     * @test
     */
    public function should_not_bulk_update_status_pages_status_required_by_admin()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->create()->toArray();
        $listIds = array_column($posts, 'id');
        $data = ['ids' => $listIds, 'status' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/pages/status/bulk', $data);
        $this->assertValidator($response, 'status', 'The status field is required.');
    }
    /**
     * @test
     */
    public function should_not_bulk_update_status_pages_not_exits_by_admin()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', [
            'id' => 1,
        ]);
        $data = ['ids' => [1], 'status' => 2];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/pages/status/bulk', $data);
        $this->assertExits($response, 'Post not found');
    }

    /**
     * @test
     */
    public function should_not_update_status_pages_status_required_by_admin()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->states('pages')->create();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
        ]);
        $data = ['status' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/pages/1/status', $data);
        $this->assertValidator($response, 'status', 'The status field is required.');
    }
    /**
     * @test
     */
    public function should_not_update_status_pages_not_exits_by_admin()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', [
            'id' => 1,
        ]);
        $data = ['status' => 2];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/pages/1/status', $data);
        $this->assertExits($response, 'pages entity not found');
    }
    /**
     * @test
     */

    public function can_skip_create_undefined_schema_when_create_post__by_admin()
    {
        $token = $this->loginToken();
        $post_metas = [
            'an_undefine_schema_key' => 'its_value',
        ];
        $post = factory(Post::class)->make($post_metas)->toArray();

        unset($post['an_undefine_schema_key']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('POST', 'api/post-management/admin/posts', $post);

        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseMissing('post_meta', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @test
     */
    public function can_create_new_schema_when_update_post_by_admin()
    {
        $token = $this->loginToken();
        //Fake a new schema in post_schema TABLE
        $schemas = factory(PostSchema::class, 1)->create();
        $post_metas = [];
        foreach ($schemas as $schema) {
            $post_metas[$schema->name] = $schema->name . "_value";
        }

        //Fake a new post in posts TABLE in order to update
        $post = factory(Post::class)->create()->toArray();

        //Fake upddate data of the post with meta datas
        $update_post_data = factory(Post::class)->make($post_metas)->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/' . $post['id'], $update_post_data);

        //Assert post has been updated
        $response->assertStatus(200);
        $response->assertJson(['data' => $update_post_data]);

        //Assert poste metas have been created
        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseHas('post_meta', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @test
     */
    public function should_not_get_all_paginate_pages_from_field_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => '', 'from' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_pages_from_field_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => 'test', 'from' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_pages_field_from_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => 'updated', 'from' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages', $data);
        $this->assertRequired($response, 'Data missing');
    }
    /**
     * @test
     */
    public function should_get_all_paginate_pages_with_from_date_by_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->states('pages')->create(['created_at' => '01/08/2021'])->toArray();
        foreach ($posts as $post) {
            unset($post['updated_at']);
            unset($post['created_at']);
        }
        $data = ['field' => 'created', 'from' => date('Y-m-d', strtotime('02/08/2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages', $data);
        $response->assertJsonFragment([
            'data' => [],
        ]);
        $response->assertJsonMissing([
            'data' => $posts,
        ]);
        $response->assertJsonStructure([
            'data' => [],
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);

        $response->assertStatus(200);

    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_pages_field_from_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => 'updated', 'from' => '3/8/2021'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages', $data);
        $response->assertStatus(500);
    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_pages_to_field_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => '', 'to' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_pages_to_field_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => 'test', 'to' => date('Y-m-d', strtotime('3-08-2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages', $data);
        $this->assertRequired($response, 'Undefined variable: field');
    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_pages_field_to_required_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => 'updated', 'to' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages', $data);
        $this->assertRequired($response, 'Data missing');
    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_pages_field_to_by_admin()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->states('pages')->create();
        $data = ['field' => 'updated', 'to' => '3/8/2021'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages', $data);
        $response->assertStatus(500);
    }
    /**
     * @test
     */
    public function should_get_all_paginate_pages_with_to_date_by_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->states('pages')->create()->toArray();
        foreach ($posts as $post) {
            unset($post['updated_at']);
            unset($post['created_at']);
        }
        $data = ['field' => 'created', 'to' => date('Y-m-d', strtotime('02/08/2021'))];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages', $data);
        $response->assertJsonFragment([
            'data' => [],
        ]);
        $response->assertJsonMissing([
            'data' => $posts,
        ]);
        $response->assertJsonStructure([
            'data' => [],
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);

        $response->assertStatus(200);

    }
    /**
     * @test
     */
    public function should_not_get_all_paginate_pages_with_status_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->states('pages')->create()->toArray();
        $data = ['status' => ''];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages', $data);
        $this->assertRequired($response, 'The input status is incorrect');
    }
    /**
     * @test
     */
    public function should_get_all_paginate_pages_with_status_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->states('pages')->create()->toArray();
        factory(Post::class, 5)->create(['status' => 2])->toArray();
        $data = ['status' => 1];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages', $data);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'status' => 1,
        ]);
        $response->assertJsonMissing([
            'status' => 2,
        ]);
        $response->assertJsonStructure([
            'data' => [],
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
    public function should_get_all_paginate_pages_with_constraints_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->states('pages')->create();
        $title_constraints = $posts[0]->title;
        $posts = $posts->map(function ($post) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post;
        })->toArray();

        $constraints = '{"title":"' . $title_constraints . '"}';

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages?constraints=' . $constraints);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [$posts[0]],
        ]);
        $response->assertJsonStructure([
            'data' => [],
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
    public function should_get_all_paginate_pages_with_search_admin_router()
    {
        $token = $this->loginToken();
        factory(Post::class, 5)->create();
        $page = factory(Post::class)->states('pages')->create(['title' => 'test_pages'])->toArray();
        unset($page['created_at']);
        unset($page['updated_at']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [$page],
        ]);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonStructure([
            'data' => [],
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
    public function should_get_all_paginate_pages_with_order_admin_router()
    {
        $token = $this->loginToken();
        $posts = factory(Post::class, 5)->states('pages')->create();
        $posts = $posts->map(function ($post) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post;
        })->toArray();
        $order_by = '{"id":"desc"}';
        $listId = array_column($posts, 'id');
        array_multisort($listId, SORT_DESC, $posts);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages?order_by=' . $order_by);
        $response->assertStatus(200);
        $response->assertJson([
            'data' => $posts,
        ]);
        $response->assertJsonStructure([
            'data' => [],
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
    public function should_not_get_pages_item_not_exits_by_admin_router()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', [
            'id' => 1,
        ]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/1');
        $this->assertExits($response, 'pages entity not found');
    }

    /**
     * @test
     */
    public function should_not_create_pages_title_required_by_admin_router()
    {
        $token = $this->loginToken();
        $data = factory(Post::class)->states('pages')->make(['title' => ''])->toArray();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('POST', 'api/post-management/admin/pages', $data);
        $this->assertValidator($response, 'title', 'The title field is required.');
        $this->assertDatabaseMissing('posts', $data);
    }
    /**
     * @test
     */
    public function should_not_create_pages_content_required_by_admin_router()
    {
        $token = $this->loginToken();
        $data = factory(Post::class)->states('pages')->make(['content' => ''])->toArray();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('POST', 'api/post-management/admin/pages', $data);
        $this->assertValidator($response, 'content', 'The content field is required.');
        $this->assertDatabaseMissing('posts', $data);
    }
    /**
     * @test
     */
    public function should_not_update_pages_not_exits_by_admin_router()
    {
        $token = $this->loginToken();
        $data = factory(Post::class)->states('pages')->make()->toArray();
        $this->assertDatabaseMissing('posts', ['id' => 1]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/pages/1', $data);
        $this->assertExits($response, 'Pages entity not found');
    }
    /**
     * @test
     */
    public function should_not_update_pages_title_required_by_admin_router()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->states('pages')->create();
        $data = factory(Post::class)->make(['title' => ''])->toArray();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/pages/' . $post->id, $data);
        $this->assertValidator($response, 'title', 'The title field is required.');
    }
    /**
     * @test
     */
    public function should_not_update_pages_content_required_by_admin_router()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->states('pages')->create();
        $data = factory(Post::class)->make(['content' => ''])->toArray();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/pages/' . $post->id, $data);
        $this->assertValidator($response, 'content', 'The content field is required.');
    }
    /**
     * @test
     */
    public function should_not_delete_pages_not_exits_by_admin_router()
    {
        $token = $this->loginToken();
        $this->assertDatabaseMissing('posts', ['id' => 1]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('DELETE', 'api/post-management/admin/pages/1');
        $this->assertExits($response, 'Pages not found');

    }
    /**
     * @test
     */

    public function can_update_existed_schema_when_update_post_by_admin()
    {
        $token = $this->loginToken();
        //Fake a new schema in post_schema TABLE
        $schemas = factory(PostSchema::class, 1)->create();

        $post_meta_datas = [];
        $post_metas = [];
        foreach ($schemas as $schema) {
            $post_meta_datas[$schema->name] = $schema->name . "_value";
            array_push($post_metas, factory(PostMeta::class)->make([
                'key' => $schema->name,
                'value' => "",
            ]));
        }

        //Fake a new post in posts TABLE in order to update
        $post = factory(Post::class)->create()->postMetas()->saveMany($post_metas);

        //Fake upddate data of the post with meta datas
        $update_post_data = factory(Post::class)->make($post_meta_datas)->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/' . $post[0]->id, $update_post_data);

        //Assert post has been updated
        $response->assertStatus(200);
        $response->assertJson(['data' => $update_post_data]);

        //Assert poste metas have been updated
        foreach ($post_meta_datas as $key => $value) {
            $this->assertDatabaseHas('post_meta', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @test
     */
    public function can_skip_update_undefined_schema_when_update_post_by_admin()
    {
        $token = $this->loginToken();
        $post_metas = [
            'an_undefine_schema_key' => 'its_value',
        ];
        $post = factory(Post::class)->create()->toArray();

        $new_data_with_undefin_schema = factory(Post::class)->make($post_metas)->toArray();

        unset($new_data_with_undefin_schema['an_undefine_schema_key']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/' . $post['id'], $new_data_with_undefin_schema);

        // $response->assertStatus(200);
        $response->assertJson(['data' => $new_data_with_undefin_schema]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseMissing('post_meta', ['key' => $key, 'value' => $value]);
        }
    }
}
