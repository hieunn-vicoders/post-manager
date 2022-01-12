<?php

namespace VCComponent\Laravel\Post\Test\Feature\Api\PostBlock;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Entities\PostBlock;
use VCComponent\Laravel\Post\Test\TestCase;

class PostBlockTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_get_post_blocks()
    {

        $token = $this->loginToken();
        $data = factory(Post::class)->state('pages')->create()->toArray();
        $post_blocks = factory(PostBlock::class, 3)->create(['post_id' => $data['id']])->each(function ($post_block) {
            unset($post_block['updated_at']);
            unset($post_block['created_at']);
            unset($post_block['post_id']);
        })->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/pages/'. $data['id'] . '/post-block');
        $response->assertStatus(200);
        $response->assertJson(['data' => $post_blocks]);
    }

}
