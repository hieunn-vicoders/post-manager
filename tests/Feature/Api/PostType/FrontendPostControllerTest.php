<?php

namespace VCComponent\Laravel\Post\Test\Feature\Api\PostType;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Entities\PostMeta;
use VCComponent\Laravel\Post\Entities\PostSchema;
use VCComponent\Laravel\Post\Test\TestCase;

class FrontendPostControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_create_about_post_by_frontend_router()
    {
        $data = factory(Post::class)->state('about')->make()->toArray();

        $response = $this->json('POST', 'api/post-management/about', $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('posts', $data);
    }

    /**
     * @test
     */
    public function can_update_about_post_by_frontend_router()
    {
        $post = factory(Post::class)->state('about')->make();
        $post->save();

        unset($post['updated_at']);
        unset($post['created_at']);

        $id = $post->id;
        $post->title = 'update title';
        $data = $post->toArray();

        $response = $this->json('PUT', 'api/post-management/about/' . $id, $data);

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
    public function can_delete_about_post_by_frontend_router()
    {
        $post = factory(Post::class)->state('about')->create()->toArray();

        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->call('DELETE', 'api/post-management/about/' . $post['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertSoftDeleted('posts', $post);
    }

    /**
     * @test
     */
    public function can_get_post_item_by_frontend_router()
    {
        $post = factory(Post::class)->state('about')->create();

        $response = $this->call('GET', 'api/post-management/about/' . $post->id);

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
    public function can_get_post_type_list_by_frontend_router()
    {
        $post = factory(Post::class, 5)->state('about')->create();

        $response = $this->call('GET', 'api/post-management/about');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function can_get_post_type_list_with_no_paginate_by_frontend_router()
    {
        $post = factory(Post::class)->state('about')->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $response = $this->call('GET', 'api/post-management/about/all');
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
    public function can_bulk_update_status_post_type_by_frontend_router()
    {
        $posts = factory(Post::class, 5)->state('about')->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ['ids' => $listIds, 'status' => 5];

        $response = $this->json('GET', 'api/post-management/about/all');
        $response->assertJsonFragment(['status' => 1]);

        $response = $this->json('PUT', 'api/post-management/about/status/bulk', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->json('GET', 'api/post-management/about/all');
        $response->assertJsonFragment(['status' => 5]);
    }

    /**
     * @test
     */
    public function can_update_status_a_page_by_frontend_router()
    {
        $post = factory(Post::class)->state('about')->create()->toArray();
        unset($post['updated_at']);
        unset($post['created_at']);

        $this->assertDatabaseHas('posts', $post);

        $data = ['status' => 2];
        $response = $this->json('PUT', 'api/post-management/about/' . $post['id'] . '/status', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->json('GET', 'api/post-management/about/' . $post['id']);

        $response->assertJson(['data' => $data]);
    }

    /**
     * @test
     */
    public function can_create_schema_when_create_post_of_type_about_by_frontend_router()
    {
        $schemas = factory(PostSchema::class, 1)->state('about')->create();
        $post_metas = [];
        foreach ($schemas as $schema) {
            $post_metas[$schema->name] = $schema->name . "_value";
        }
        $post = factory(Post::class)->state('about')->make($post_metas)->toArray();

        $response = $this->call('POST', 'api/post-management/about', $post);

        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseHas('post_meta', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @test
     */
    public function can_skip_create_undefined_schema_when_create_post_of_type_about_by_frontend_router()
    {
        $post_metas = [
            'an_undefine_schema_key' => 'its_value',
        ];
        $post = factory(Post::class)->state('about')->make($post_metas)->toArray();

        unset($post['an_undefine_schema_key']);

        $response = $this->call('POST', 'api/post-management/about', $post);

        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseMissing('post_meta', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @test
     */
    public function can_create_new_schema_when_update_post_of_type_about_by_frontend_router()
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

        $response = $this->call('PUT', 'api/post-management/about/' . $post['id'], $update_post_data);

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
    public function can_update_existed_schema_when_update_post_of_type_about_by_frontend_router()
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

        $response = $this->call('PUT', 'api/post-management/about/' . $post[0]->id, $update_post_data);

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
    public function can_skip_update_undefined_schema_when_update_post_of_type_about_by_frontend_router()
    {
        $post_metas = [
            'an_undefine_schema_key' => 'its_value',
        ];
        $post = factory(Post::class)->state('about')->create()->toArray();

        $new_data_with_undefin_schema = factory(Post::class)->make($post_metas)->toArray();

        unset($new_data_with_undefin_schema['an_undefine_schema_key']);

        $response = $this->call('PUT', 'api/post-management/about/' . $post['id'], $new_data_with_undefin_schema);

        $response->assertStatus(200);
        $response->assertJson(['data' => $new_data_with_undefin_schema]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseMissing('post_meta', ['key' => $key, 'value' => $value]);
        }
    }
}
