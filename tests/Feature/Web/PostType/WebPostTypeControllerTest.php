<?php

namespace VCComponent\Laravel\Post\Test\Feature\Web\PostType;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Test\TestCase;

class WebPostTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_get_list_posts_type_by_web_router()
    {
        $post = factory(Post::class)->state('about')->create()->toArray();

        $response = $this->call('GET', 'post-management/about');

        $response->assertStatus(200);
        $response->assertViewIs("post-manager::post-list");
    }

    /**
     * @test
     */
    public function can_get_a_posts_type_by_web_router()
    {

        $post = factory(Post::class)->state('about')->create()->toArray();

        $response = $this->call('GET', 'post-management/about/' . $post['slug']);

        $response->assertStatus(200);
        $response->assertViewIs("post-manager::post-detail");
        $response->assertViewHasAll([
            'post.title'       => $post['title'],
            'post.slug'        => $post['slug'],
            'post.description' => $post['description']
        ]);
    }

}
