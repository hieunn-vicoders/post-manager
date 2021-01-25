<?php

namespace VCComponent\Laravel\Post\Test\Feature\Web\Draft;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\Draftable;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Test\TestCase;

class WebDraftControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_get_show_draft_by_web_router()
    {
        $data  = factory(Draftable::class)->make(['draftable_id' => 1]);
        $draft = $data->toArray();
        $data->save();

        $response = $this->call('GET', 'post-management/post-preview/1');

        $response->assertStatus(200);
        $response->assertViewIs("post-manager::draft");
        $response->assertViewHasAll([
            'draft.draftable_type' => $draft['draftable_type'],
            'draft.draftable_id'   => $draft['draftable_id'],
            'draft.payload'        => $draft['payload'],
        ]);
    }

    /**
     * @test
     */
    public function can_get_show_draft_type_by_web_router()
    {
        $data  = factory(Draftable::class)->make(['draftable_id' => 1, 'draftable_type' => 'products']);
        $draft = $data->toArray();
        $data->save();

        $response = $this->call('GET', 'post-management/preview/products/1');

        $response->assertStatus(200);
        $response->assertViewIs("post-manager::draft");
        $response->assertViewHasAll([
            'draft.draftable_type' => $draft['draftable_type'],
            'draft.draftable_id'   => $draft['draftable_id'],
            'draft.payload'        => $draft['payload'],
        ]);
    }
}
