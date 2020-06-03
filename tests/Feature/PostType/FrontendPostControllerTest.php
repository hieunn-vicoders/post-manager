<?php

namespace VCComponent\Laravel\Post\Test\Feature\PostType;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Test\Feature\PostType\PostTypeTestCase;

class FrontendPostControllerTest extends PostTypeTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_create_about_post_by_frontend_router()
    {
        $data = factory(Post::class)->make()->toArray();

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
        $post = factory(Post::class)->make(['type' => 'about']);
        $post->save();

        $id          = $post->id;
        $post->title = 'update title';
        $data        = $post->toArray();

        $response = $this->json('PUT', 'api/post-management/about/' . $id, $data);
        // dd($response);

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
        $post = factory(Post::class)->create(['type' => 'about'])->toArray();

        $this->assertDatabaseHas('posts', $post);

        $response = $this->call('DELETE', 'api/post-management/about/' . $post['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('posts', $post);
    }

    /**
     * @test
     */
    public function can_retrieve_post_item_by_frontend_router()
    {
        $post = factory(Post::class)->create(['type' => 'about']);

        $response = $this->call('GET', 'api/post-management/about/' . $post->id);

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
    public function can_retrieve_post_list_by_frontend_router()
    {
        $posts = factory(Post::class, 5)->create(['type' => 'about']);

        $response = $this->call('GET', 'api/post-management/about');

        $response->assertStatus(200);
    }
}
