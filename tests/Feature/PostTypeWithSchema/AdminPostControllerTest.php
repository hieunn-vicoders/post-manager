<?php

namespace VCComponent\Laravel\Post\Test\Feature\PostTypeWithSchema;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\PostMeta;
use VCComponent\Laravel\Post\Test\Stubs\Models\WithSchemaAttributes\Post;

class AdminPostControllerTest extends PostTypeWithSchemaTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_create_post_with_schema_by_admin_router()
    {
        $data        = factory(Post::class)->make()->toArray();
        $schema_data = [
            'author' => 'test author',
        ];

        $response = $this->json('POST', 'api/post-management/admin/posts', array_merge($data, $schema_data));

        $response->assertStatus(200);
        $response->assertJson(['data' => array_merge($data, $schema_data)]);

        $this->assertDatabaseHas('posts', $data);
        $this->assertDatabaseHas('post_meta', [
            'key'   => 'author',
            'value' => 'test author',
        ]);
    }

    /**
     * @test
     */
    public function can_update_post_with_schema_by_admin_router()
    {
        $post = factory(Post::class)->make();
        $post->save();
        $schema_data = [
            'key'   => 'author',
            'value' => 'test author',
        ];
        $post->postMetas()->createMany([
            factory(PostMeta::class)->make($schema_data)->toArray(),
        ]);

        $id              = $post->id;
        $data            = $post->toArray();
        $request_payload = array_merge($data, $schema_data, ['author' => 'update author']);

        $response = $this->json('PUT', 'api/post-management/admin/posts/' . $id, $request_payload);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'title'  => $request_payload['title'],
                'author' => $request_payload['author'],
            ],
        ]);

        $this->assertDatabaseHas('posts', $data);
        $this->assertDatabaseHas('post_meta', [
            'key'   => 'author',
            'value' => 'update author',
        ]);
    }

    // /**
    //  * @test
    //  */
    // public function can_delete_post_by_admin_router()
    // {
    //     $post = factory(Post::class)->create()->toArray();

    //     $this->assertDatabaseHas('posts', $post);

    //     $response = $this->call('DELETE', 'api/post-management/admin/posts/' . $post['id']);

    //     $response->assertStatus(200);
    //     $response->assertJson(['success' => true]);

    //     $this->assertDatabaseMissing('posts', $post);
    // }

    // /**
    //  * @test
    //  */
    // public function can_retrieve_post_item_by_admin_router()
    // {
    //     $post = factory(Post::class)->create();

    //     $response = $this->call('GET', 'api/post-management/admin/posts/' . $post->id);

    //     $response->assertStatus(200);
    //     $response->assertJson([
    //         'data' => [
    //             'title'       => $post->title,
    //             'description' => $post->description,
    //             'content'     => $post->content,
    //         ],
    //     ]);
    // }

    // /**
    //  * @test
    //  */
    // public function can_retrieve_post_list_by_admin_router()
    // {
    //     $posts = factory(Post::class, 5)->create();

    //     $response = $this->call('GET', 'api/post-management/admin/posts');

    //     $response->assertStatus(200);
    // }
}
