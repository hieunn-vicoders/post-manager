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
    public function can_get_list_posts_with_constraints_by_web_router()
    {
        $posts = factory(Post::class, 5)->state('about')->create();

        $constraint_title = $posts[0]->title;

        $posts = $posts->filter(function ($post) use ($constraint_title) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post->title == $constraint_title;
        })->toArray();

        $response = $this->call('GET', 'post-management/about?constraints={"title":"' . $constraint_title . '"}');

        $response->assertStatus(200);
        $response->assertViewIs("post-manager::post-list");

        $this->assertResponseHasPost($response, $posts);
    }

    /**
     * @test
     */
    public function can_get_list_posts_with_search_by_web_router()
    {
        $posts = factory(Post::class, 5)->state('about')->create();

        $search = $posts[0]->content;

        $posts = $posts->filter(function ($post) use ($search) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post->title == $search || $post->description == $search || $post->content == $search;
        })->toArray();

        $response = $this->call('GET', 'post-management/about?search=' . $search);

        $response->assertStatus(200);
        $response->assertViewIs("post-manager::post-list");

        $this->assertResponseHasPost($response, $posts);
    }

    /**
     * @test
     */
    public function can_get_list_posts_with_order_by_by_web_router()
    {
        $posts = factory(Post::class, 5)->state('about')->create();

        $posts = $posts->map(function ($post) {
            unset($post['created_at']);
            unset($post['updated_at']);
            return $post;
        })->toArray();

        $listTitles = array_column($posts, 'title');
        array_multisort($listTitles, SORT_DESC, $posts);

        $response = $this->call('GET', 'post-management/about?order_by={"title":"DESC"}');

        $response->assertStatus(200);
        $response->assertViewIs("post-manager::post-list");

        $this->assertResponseHasPost($response, $posts);
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

    protected function assertResponseHasPost($response, $posts)
    {
        $response_post = $response['posts'];
        $this->assertEquals($response_post->count(), count($posts));

        for ($i = 0; $i < count($posts); $i++) {
            $this->assertEquals($response_post[$i]->id, $posts[$i]['id']);
            $this->assertEquals($response_post[$i]->slug, $posts[$i]['slug']);
            $this->assertEquals($response_post[$i]->title, $posts[$i]['title']);
            $this->assertEquals($response_post[$i]->description, $posts[$i]['description']);
            $this->assertEquals($response_post[$i]->content, $posts[$i]['content']);
            $this->assertEquals($response_post[$i]->status, $posts[$i]['status']);
        }
    }
}
