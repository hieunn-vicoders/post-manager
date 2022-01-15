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
        })->map(function ($post_block) {
            $post_block->block = json_decode($post_block->block, true);
            return $post_block;
        })->toArray();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('GET', 'api/post-management/admin/posts/'. $data['id'] . '/blocks');
        $response->assertStatus(200);
        $response->assertJson(['data' => $post_blocks]);
    }

    /**
     * @test
     */
    public function can_create_post_with_blocks()
    {

        $token = $this->loginToken();
        $data = factory(Post::class)->make([
            'post_blocks' => [
                ['key' => 'asdasd'],
                ['key' => 'sdasdads'],
            ]
        ])->toArray();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('POST', 'api/post-management/admin/posts', $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('post_blocks', ['block' => json_encode(['key' => 'asdasd'])]);
        $this->assertDatabaseHas('post_blocks', ['block' => json_encode(['key' => 'sdasdads'])]);
    }

    /**
     * @test
     */
    public function can_update_post_with_blocks()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->create();
        $post->postBlocks()->createMany([
            ['block' => '{"name":"nva","age":"22"}'],
            ['block' => '{"name":"nvb","age":"23"}'],
        ]);
        $data = factory(Post::class)->make([
            'post_blocks' => [
                ['key' => 'asdasd'],
                ['key' => 'sdasdads'],
            ]
        ])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/'.$post['id'], $data);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('post_blocks', ['block' => '{"name":"nva","age":"22"}']);
        $this->assertDatabaseMissing('post_blocks', ['block' => '{"name":"nvb","age":"23"}']);
    }

    /**
     * @test
     */
    public function can_update_post_without_blocks_to_new_post_has_blocks()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->create();
        $data = factory(Post::class)->make([
            'post_blocks' => [
                ['key' => 'asdasd'],
                ['key' => 'sdasdads'],
            ]
        ])->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/'.$post['id'], $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('post_blocks', ['block' => json_encode(['key' => 'asdasd'])]);
        $this->assertDatabaseHas('post_blocks', ['block' => json_encode(['key' => 'sdasdads'])]);
    }

    /**
     * @test
     */
    public function can_update_post_without_blocks()
    {
        $token = $this->loginToken();
        $post = factory(Post::class)->create();
        $post->postBlocks()->createMany([
            ['block' => '{"name":"nva","age":"22"}'],
            ['block' => '{"name":"nvb","age":"23"}'],
        ]);
        $data = factory(Post::class)->make()->toArray();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->json('PUT', 'api/post-management/admin/posts/'.$post['id'], $data);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('post_blocks', ['block' => '{"name":"nva","age":"22"}']);
        $this->assertDatabaseMissing('post_blocks', ['block' => '{"name":"nvb","age":"23"}']);
    }

}
