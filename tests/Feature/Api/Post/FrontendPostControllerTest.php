<?php

namespace VCComponent\Laravel\Post\Test\Feature\Api\Post;

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

        $id = $post->id;
        $post->title = 'update title';
        $data = $post->toArray();

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
                'title' => $post->title,
                'description' => $post->description,
                'content' => $post->content,
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

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ['ids' => $listIds, 'status' => 5];

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

        $data = ['status' => 2];
        $response = $this->json('PUT', 'api/post-management/posts/' . $post['id'] . '/status', $data);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $response = $this->json('GET', 'api/post-management/posts/' . $post['id']);

        $response->assertJson(['data' => $data]);
    }

    /**
     * @test
     */
    public function can_get_paginate_posts_with_constraints_by_frontend_router()
    {
        $data = factory(Post::class, 5)->create();

        $constraint = $data[0]->title;

        $data = $data->filter(function ($d) use ($constraint) {
            unset($d['created_at']);
            unset($d['updated_at']);
            return $d->title == $constraint;
        })->toArray();

        $response = $this->json('GET', 'api/post-management/posts?constraints={"title":"' . $constraint . '"}');

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);
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
    public function can_get_paginate_posts_with_search_by_frontend_router()
    {
        $data = factory(Post::class, 5)->create();

        $search = $data[0]->title;

        $data = $data->filter(function ($d) use ($search) {
            unset($d['created_at']);
            unset($d['updated_at']);
            return $d->title == $search;
        })->toArray();

        $response = $this->json('GET', 'api/post-management/posts?search=' . $search, $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);
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
    public function can_get_paginate_posts_with_order_by_by_frontend_router()
    {
        $data = factory(Post::class, 1)->create();

        $data = $data->map(function ($d) {
            unset($d['created_at']);
            unset($d['updated_at']);
            return $d;
        })->toArray();

        $listTitles = array_column($data, 'description');
        array_multisort($listTitles, SORT_DESC, $data);

        $response = $this->json('GET', 'api/post-management/posts?order_by={"description":"desc"}');

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);
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
    public function can_get_all_posts_with_constraints_by_frontend_router()
    {
        $data = factory(Post::class, 5)->create();

        $constraint = $data[0]->title;

        $data = $data->filter(function ($d) use ($constraint) {
            unset($d['created_at']);
            unset($d['updated_at']);
            return $d->title == $constraint;
        })->toArray();

        $response = $this->json('GET', 'api/post-management/posts/all?constraints={"title":"' . $constraint . '"}');

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);
    }

    /**
     * @test
     */
    public function can_get_all_posts_with_search_by_frontend_router()
    {
        $data = factory(Post::class, 5)->create();

        $search = $data[0]->title;

        $data = $data->filter(function ($d) use ($search) {
            unset($d['created_at']);
            unset($d['updated_at']);
            return $d->title == $search;
        })->toArray();

        $response = $this->json('GET', 'api/post-management/posts/all?search=' . $search, $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);
    }

    /**
     * @test
     */
    public function can_get_all_posts_with_order_by_by_frontend_router()
    {
        $data = factory(Post::class, 1)->create();

        $data = $data->map(function ($d) {
            unset($d['created_at']);
            unset($d['updated_at']);
            return $d;
        })->toArray();

        $listTitles = array_column($data, 'description');
        array_multisort($listTitles, SORT_DESC, $data);

        $response = $this->json('GET', 'api/post-management/posts/all?order_by={"description":"desc"}');

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);
    }

    /** @test */
    public function should_not_bulk_update_status_many_posts_with_null_ids_by_frontend_router()
    {
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $data = ['status' => 5];

        $response = $this->json('PUT', 'api/post-management/posts/status/bulk', $data);

        $response->assertStatus(500);
        $response->assertJson(['message' => 'Undefined index: ids']);
    }

    /** @test */
    public function should_not_bulk_update_status_many_posts_with_null_status_by_frontend_router()
    {
        $posts = factory(Post::class, 5)->create();

        $posts = $posts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($posts, 'id');
        $data = ['ids' => $listIds];

        $response = $this->json('PUT', 'api/post-management/posts/status/bulk', $data);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'The given data was invalid.']);
    }

    /** @test */
    public function should_not_bulk_update_status_undefined_posts_by_frontend_router()
    {
        $data = ['ids' => [1, 2, 3, 4, 5], 'status' => "asdsdad"];

        $response = $this->json('PUT', 'api/post-management/posts/status/bulk', $data);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'posts entities not found']);
    }

    /** @test */
    public function should_not_update_status_undefined_posts_by_frontend_router()
    {
        $data = ['status' => "asdsdad"];

        $response = $this->json('PUT', 'api/post-management/posts/1/status', $data);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'posts entity not found']);
    }

    /** @test */
    public function should_not_update_status_posts_with_null_status_by_frontend_router()
    {
        $post = factory(Post::class)->create();
        $data = [];

        $response = $this->json('PUT', 'api/post-management/posts/' . $post->id . '/status', $data);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'The given data was invalid.']);
    }

    /** @test */
    public function should_not_update_post_with_indefined_id_by_frontend_router()
    {
        $response = $this->json('GET', 'api/post-management/posts/' . rand(1, 5));

        $response->assertStatus(400);
        $response->assertJson(['message' => 'posts not found']);
    }

    /** @test */
    public function should_not_create_posts_with_null_title_by_frontend_router()
    {
        $post = factory(Post::class)->make([
            'title' => null,
        ])->toArray();

        $response = $this->json('POST', 'api/post-management/posts/', $post);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'The given data was invalid.']);
    }

    /** @test */
    public function should_not_create_posts_with_null_content_by_frontend_router()
    {
        $post = factory(Post::class)->make([
            'content' => null,
        ])->toArray();

        $response = $this->json('POST', 'api/post-management/posts/', $post);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'The given data was invalid.']);
    }

    /** @test */
    public function should_not_update_undefined_posts_by_frontend_router()
    {
        $post = factory(Post::class)->make()->toArray();

        $response = $this->json('PUT', 'api/post-management/posts/' . rand(1, 3), $post);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'posts entity not found']);
    }

    /** @test */
    public function should_not_update_post_with_null_title_by_frontend_router()
    {
        $post = factory(Post::class)->create()->toArray();

        $post['title'] = null;

        $response = $this->json('PUT', 'api/post-management/posts/' . $post['id'], $post);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'The given data was invalid.']);
    }

    /** @test */
    public function should_not_update_post_with_null_content_by_frontend_router()
    {
        $post = factory(Post::class)->create()->toArray();

        $post['content'] = null;

        $response = $this->json('PUT', 'api/post-management/posts/' . $post['id'], $post);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'The given data was invalid.']);
    }

    /** @test */
    public function should_not_delete_post_with_undefined_id_by_frontend_router()
    {

        $response = $this->json('DELETE', 'api/post-management/posts/' . rand(1, 5));

        $response->assertStatus(400);
        $response->assertJson(['message' => 'posts entity not found']);
    }

    /**
     * @test
     */
    public function can_create_schema_when_create_post__by_frontend_router()
    {
        $schemas = factory(PostSchema::class, 1)->create();
        $post_metas = [];
        foreach ($schemas as $schema) {
            $post_metas[$schema->name] = $schema->name . "_value";
        }
        $post = factory(Post::class)->make($post_metas)->toArray();

        $response = $this->call('POST', 'api/post-management/posts', $post);

        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseHas('post_meta', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @test
     */
    public function can_skip_create_undefined_schema_when_create_post__by_frontend_router()
    {
        $post_metas = [
            'an_undefine_schema_key' => 'its_value'
        ];
        $post = factory(Post::class)->make($post_metas)->toArray();

        unset($post['an_undefine_schema_key']);

        $response = $this->call('POST', 'api/post-management/posts', $post);

        $response->assertStatus(200);
        $response->assertJson(['data' => $post]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseMissing('post_meta', ['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @test
     */
    public function can_create_new_schema_when_update_post_by_frontend_router()
    {
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

        $response = $this->call('PUT', 'api/post-management/posts/' . $post['id'], $update_post_data);

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
    public function can_update_existed_schema_when_update_post_by_frontend_router()
    {
        //Fake a new schema in post_schema TABLE
        $schemas = factory(PostSchema::class, 1)->create();

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
        $post = factory(Post::class)->create()->postMetas()->saveMany($post_metas);

        //Fake upddate data of the post with meta datas
        $update_post_data = factory(Post::class)->make($post_meta_datas)->toArray();

        $response = $this->call('PUT', 'api/post-management/posts/' . $post[0]->id, $update_post_data);

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
    public function can_skip_update_undefined_schema_when_update_post_by_frontend_router()
    {
        $post_metas = [
            'an_undefine_schema_key' => 'its_value'
        ];
        $post = factory(Post::class)->create()->toArray();

        $new_data_with_undefin_schema = factory(Post::class)->make($post_metas)->toArray();

        unset($new_data_with_undefin_schema['an_undefine_schema_key']);

        $response = $this->call('PUT', 'api/post-management/posts/' . $post['id'], $new_data_with_undefin_schema);

        $response->assertStatus(200);
        $response->assertJson(['data' => $new_data_with_undefin_schema]);

        foreach ($post_metas as $key => $value) {
            $this->assertDatabaseMissing('post_meta', ['key' => $key, 'value' => $value]);
        }
    }
}
