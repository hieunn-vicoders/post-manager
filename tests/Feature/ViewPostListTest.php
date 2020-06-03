<?php

namespace VCComponent\Laravel\Post\Test\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Test\TestCase;

class ViewPostListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_access_post_list_view()
    {
        $response = $this->get('post-management/posts');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function can_access_page_list_view()
    {
        $response = $this->get('post-management/pages');

        $response->assertStatus(200);
    }
}
