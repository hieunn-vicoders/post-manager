<?php

namespace VCComponent\Laravel\Post\Test\Feature\Api\PostType;

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
    public function can_create_about_post_type_by_admin_router()
    {
        $data = factory(Post::class)->state('about')->make()->toArray();

        $response = $this->json('POST', 'api/post-management/admin/about', $data);
        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('posts', $data);
    }

    /**
     * @test
     */
    public function can_update_about_post_type_by_admin_router()
    {
        $post = factory(Post::class)->state('about')->create()->toArray();

        $id = $post['id'];
        $post['title'] = 'update title';
        $data = $post;

        unset($data['updated_at']);
        unset($data['created_at']);

        $response = $this->json('PUT', 'api/post-management/admin/about/' . $id, $data);

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
    public function can_delete_about_post_type_by_admin_router()
    {
        $post = factory(Post::class)->state('about')->create()->toArray();

        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->call('DELETE', 'api/post-management/admin/about/' . $post['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertSoftDeleted('posts', $post);
    }

    /**
     * @test
     */
    public function can_get_post_type_item_by_admin_router()
    {
        $post = factory(Post::class)->state('about')->create();

        unset($post['updated_at']);
        unset($post['created_at']);

        $response = $this->call('GET', 'api/post-management/admin/about/' . $post->id);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'title' => $post->title,
                'description' => $post->description,
                'content' => $post->content,
            ],
        ]);
    }

    /**
     * @test
     */
    public function can_get_post_type_list_by_admin_router()
    {
        $posts = factory(Post::class, 5)->state('about')->create();

        $response = $this->call('GET', 'api/post-management/admin/about');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /**
     * @test
     */
    public function can_get_field_meta_post_type_by_admin_router()
    {
        factory(PostSchema::class)->states('about')->create();

        $response = $this->json('GET', 'api/post-management/admin/about/field-meta');
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
    public function can_bulK_delete_posts_type_trash_by_admin()
    {
        $posts = factory(Post::class, 5)->state('about')->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ["ids" => $listIds];

        $response = $this->call('DELETE', 'api/post-management/admin/about/trash/bulk', $data);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'post not found']);

        $response = $this->call('DELETE', 'api/post-management/admin/about/bulk', $data);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->call('GET', 'api/post-management/admin/about/trash/all');
        $response->assertJsonCount(5, 'data');

        $response = $this->call('DELETE', 'api/post-management/admin/about/trash/bulk', $data);
        $response->assertJson(['success' => true]);

        foreach ($posts as $item) {
            $this->assertDeleted('posts', $item);
        }
    }

    /**
     * @test
     */
    public function can_force_delete_post_type_by_admin_router()
    {
        $post = factory(Post::class)->state('about')->create()->toArray();

        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->call('DELETE', 'api/post-management/admin/about/' . $post['id'] . '/force');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDeleted('posts', $post);
    }

    /**
     * @test
     */
    public function can_delete_all_trash_post_type_by_admin_router()
    {
        $posts = factory(Post::class, 5)->state('about')->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ["ids" => $listIds];

        $response = $this->call('DELETE', 'api/post-management/admin/about/bulk', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->call('GET', 'api/post-management/admin/about/trash/all');

        $response->assertJsonCount(5, 'data');

        $response = $this->call('DELETE', 'api/post-management/admin/about/trash/all');
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
        $posts = factory(Post::class, 5)->state('about')->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ["ids" => $listIds];

        $response = $this->call('DELETE', 'api/post-management/admin/about/trash/bulk', $data);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'post not found']);

        $response = $this->call('DELETE', 'api/post-management/admin/about/bulk', $data);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->call('GET', 'api/post-management/admin/about/trash/all');
        $response->assertJsonCount(5, 'data');

        $response = $this->call('DELETE', 'api/post-management/admin/about/trash/bulk', $data);
        $response->assertJson(['success' => true]);

        foreach ($posts as $item) {
            $this->assertDeleted('posts', $item);
        }
    }

    /**
     * @test
     */
    public function can_delete_a_posts_type_trash_by_admin()
    {
        $post = factory(Post::class)->state('about')->create()->toArray();

        unset($post['updated_at']);
        unset($post['created_at']);

        $response = $this->json('DELETE', 'api/post-management/admin/about/' . $post['id'] . '/trash');

        $response->assertJson(['success' => true]);
        $this->assertDeleted('posts', $post);
    }

    /**
     * @test
     */
    public function can_get_trash_list_of_posts_type_with_no_paginate_by_admin()
    {
        $post = factory(Post::class)->state('about')->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->call('DELETE', 'api/post-management/admin/about/' . $post['id']);
        $response->assertJson(['success' => true]);

        $response = $this->call('GET', 'api/post-management/admin/about/trash/all');
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
    public function can_bulk_restore_posts_type_by_admin_router()
    {
        $posts = factory(Post::class, 5)->state('about')->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ["ids" => $listIds];

        $response = $this->call('DELETE', 'api/post-management/admin/about/bulk', $data);

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
    public function can_restore_a_post_type_by_admin_router()
    {

        $post = factory(Post::class)->state('about')->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $response = $this->call('DELETE', 'api/post-management/admin/about/' . $post['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertSoftDeleted('posts', $post);

        $response = $this->call('PUT', 'api/post-management/admin/about/trash/' . $post['id'] . '/restore');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->call('GET', 'api/post-management/admin/about/' . $post['id']);
        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);

    }

    /**
     * @test
     */
    public function can_get_post_type_list_with_no_paginate_by_admin_router()
    {
        $posts = factory(Post::class, 5)->state('about')->create();

        $response = $this->call('GET', 'api/post-management/admin/about');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
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
    public function can_bulk_update_status_posts_type_by_admin()
    {
        $posts = factory(Post::class, 5)->state('about')->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ['ids' => $listIds, 'status' => 5];

        $response = $this->json('GET', 'api/post-management/admin/about/all');
        $response->assertJsonFragment(['status' => 1]);

        $response = $this->json('PUT', 'api/post-management/admin/about/status/bulk', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->json('GET', 'api/post-management/admin/about/all');
        $response->assertJsonFragment(['status' => 5]);
    }

    /**
     * @test
     */
    public function can_update_status_a_post_type_by_admin()
    {
        $post = factory(Post::class)->state('about')->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $data = ['status' => 2];
        $response = $this->json('PUT', 'api/post-management/admin/about/' . $post['id'] . '/status', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->json('GET', 'api/post-management/admin/about/' . $post['id']);

        $response->assertJson(['data' => $data]);
    }

    /**
     * @test
     */
    public function can_create_schema_when_create_post_of_type_about_by_admin()
    {
        $schemas = factory(PostSchema::class, 1)->state('about')->create();
        $post_metas = [];
        foreach ($schemas as $schema) {
            $post_metas[$schema->name] = $schema->name . "_value";
        }
        $post = factory(Post::class)->state('about')->make($post_metas)->toArray();

        $response = $this->call('POST', 'api/post-management/admin/about', $post);

        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseHas('post_meta', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @test
     */
    public function can_skip_create_undefined_schema_when_create_post_of_type_about_by_admin()
    {
        $post_metas = [
            'an_undefine_schema_key' => 'its_value',
        ];
        $post = factory(Post::class)->state('about')->make($post_metas)->toArray();

        unset($post['an_undefine_schema_key']);

        $response = $this->call('POST', 'api/post-management/admin/about', $post);

        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseMissing('post_meta', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @test
     */
    public function can_create_new_schema_when_update_post_of_type_about_by_admin()
    {
        //Fake a new schema in post_schema TABLE
        $schemas = factory(PostSchema::class, 1)->state('about')->create();
        $post_metas = [];
        foreach ($schemas as $schema) {
            $post_metas[$schema->name] = $schema->name . "_value";
        }

        //Fake a new post in posts TABLE in order to update
        $post = factory(Post::class)->state('about')->create()->toArray();

        //Fake upddate data of the post with meta datas
        $update_post_data = factory(Post::class)->make($post_metas)->toArray();

        $response = $this->call('PUT', 'api/post-management/admin/about/' . $post['id'], $update_post_data);

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
    public function can_update_existed_schema_when_update_post_of_type_about_by_admin()
    {
        //Fake a new schema in post_schema TABLE
        $schemas = factory(PostSchema::class, 1)->state('about')->create();

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
        $post = factory(Post::class)->state('about')->create()->postMetas()->saveMany($post_metas);

        //Fake upddate data of the post with meta datas
        $update_post_data = factory(Post::class)->state('about')->make($post_meta_datas)->toArray();

        $response = $this->call('PUT', 'api/post-management/admin/about/' . $post[0]->id, $update_post_data);

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
    public function can_skip_update_undefined_schema_when_update_post_of_type_about_by_admin()
    {
        $post_metas = [
            'an_undefine_schema_key' => 'its_value',
        ];
        $post = factory(Post::class)->state('about')->create()->toArray();

        $new_data_with_undefin_schema = factory(Post::class)->make($post_metas)->toArray();

        unset($new_data_with_undefin_schema['an_undefine_schema_key']);

        $response = $this->call('PUT', 'api/post-management/admin/about/' . $post['id'], $new_data_with_undefin_schema);

        $response->assertStatus(200);
        $response->assertJson(['data' => $new_data_with_undefin_schema]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseMissing('post_meta', ['key' => $key, 'value' => $value]);
        }
    }
}
