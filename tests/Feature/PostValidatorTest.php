<?php

namespace VCComponent\Laravel\Post\Test;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Test\Stubs\Validators\PostValidator;
use VCComponent\Laravel\Post\Test\TestCase;
use VCComponent\Laravel\Post\Validators\PostValidatorInterface;

class PostValidatorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(PostValidatorInterface::class, PostValidator::class);
    }

    /**
     * @test
     */
    public function can_create_post_by_admin_router_using_custom_validator()
    {
        $data = [
            'title' => 'new title',
        ];

        $response = $this->json('POST', 'api/post-management/admin/posts', $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('posts', $data);
    }
}
