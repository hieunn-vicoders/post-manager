<?php

namespace VCComponent\Laravel\Post\Test\Feature\Web\Page;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Test\TestCase;

class WebPageControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_get_list_pages_by_web_router()
    {
        $post = factory(Post::class)->state('pages')->create()->toArray();

        $response = $this->call('GET', 'post-management/pages');

        $response->assertStatus(200);
        $response->assertViewIs("post-manager::post-list");
    }

    /**
     * @test
     */
    public function can_get_a_page_by_web_router()
    {

        $post = factory(Post::class)->state('pages')->create()->toArray();

        $response = $this->call('GET', 'post-management/pages/' . $post['slug']);

        $response->assertStatus(200);
        $response->assertViewIs("post-manager::post-detail");
        $response->assertViewHasAll([
            'post.title'       => $post['title'],
            'post.slug'        => $post['slug'],
            'post.description' => $post['description']
        ]);
    }

}
