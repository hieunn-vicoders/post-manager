<?php

namespace VCComponent\Laravel\Post\Test\Feature\Api\Page;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Entities\PostMeta;
use VCComponent\Laravel\Post\Entities\PostSchema;
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
            'title' => $data['title'],
            'description' => $data['description'],
            'content' => $data['content'],
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

        $id = $post->id;
        $post->title = 'update title';
        $data = $post->toArray();

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
                'title' => $post->title,
                'description' => $post->description,
                'content' => $post->content,
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
        $data = ['ids' => $listIds, 'status' => 5];

        $response = $this->json('GET', 'api/post-management/admin/pages/all');
        $response->assertJsonFragment(['status' => 1]);

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

        $data = ['status' => 2];
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
        factory(PostSchema::class)->states('pages')->create();

        $response = $this->json('GET', 'api/post-management/admin/pages/field-meta');
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
    public function can_create_schema_when_create_post_of_type_pages_by_admin()
    {
        $schemas = factory(PostSchema::class, 1)->state('pages')->create();
        $post_metas = [];
        foreach ($schemas as $schema) {
            $post_metas[$schema->name] = $schema->name . "_value";
        }
        $post = factory(Post::class)->state('pages')->make($post_metas)->toArray();

        $response = $this->call('POST', 'api/post-management/admin/pages', $post);

        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseHas('post_meta', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @test
     */
    public function can_skip_create_undefined_schema_when_create_post_of_type_pages_by_admin()
    {
        $post_metas = [
            'an_undefine_schema_key' => 'its_value'
        ];
        $post = factory(Post::class)->state('pages')->make($post_metas)->toArray();

        unset($post['an_undefine_schema_key']);

        $response = $this->call('POST', 'api/post-management/admin/pages', $post);

        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseMissing('post_meta', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @test
     */
    public function can_create_new_schema_when_update_post_of_type_pages_by_admin()
    {
        //Fake a new schema in post_schema TABLE
        $schemas = factory(PostSchema::class, 1)->state('pages')->create();
        $post_metas = [];
        foreach ($schemas as $schema) {
            $post_metas[$schema->name] = $schema->name . "_value";
        }

        //Fake a new post in posts TABLE in order to update
        $post = factory(Post::class)->state('pages')->create()->toArray();

        //Fake upddate data of the post with meta datas
        $update_post_data = factory(Post::class)->make($post_metas)->toArray();

        $response = $this->call('PUT', 'api/post-management/admin/pages/' . $post['id'], $update_post_data);

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
    public function can_update_existed_schema_when_update_post_of_type_pages_by_admin()
    {
        //Fake a new schema in post_schema TABLE
        $schemas = factory(PostSchema::class, 1)->state('pages')->create();

        $post_meta_datas = [];
        $post_metas = [];
        foreach ($schemas as $schema) {
            $post_meta_datas[$schema->name] = $schema->name . "_value";
            array_push($post_metas, factory(PostMeta::class)->make([
                'key' => $schema->name,
                'value' => ""
            ]));
        }

        //Fake a new post in posts TABLE in order to update
        $post = factory(Post::class)->state('pages')->create()->postMetas()->saveMany($post_metas);

        //Fake upddate data of the post with meta datas
        $update_post_data = factory(Post::class)->state('pages')->make($post_meta_datas)->toArray();

        $response = $this->call('PUT', 'api/post-management/admin/pages/' . $post[0]->id, $update_post_data);

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
    public function can_skip_update_undefined_schema_when_update_post_of_type_pages_by_admin()
    {
        $post_metas = [
            'an_undefine_schema_key' => 'its_value'
        ];
        $post = factory(Post::class)->state('pages')->create()->toArray();

        $new_data_with_undefin_schema = factory(Post::class)->make($post_metas)->toArray();

        unset($new_data_with_undefin_schema['an_undefine_schema_key']);

        $response = $this->call('PUT', 'api/post-management/admin/pages/' . $post['id'], $new_data_with_undefin_schema);

        $response->assertStatus(200);
        $response->assertJson(['data' => $new_data_with_undefin_schema]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseMissing('post_meta', ['key' => $key, 'value' => $value]);
        }
    }
}
