<?php

namespace VCComponent\Laravel\Post\Test\Feature\Api\Draft;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\Draftable;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Test\TestCase;

class AdminDraftControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_get_post_list_by_admin_router()
    {
        $drafts = factory(Draftable::class, 5)->create();

        $drafts = $drafts->map(function ($e) {
            unset($e['updated_at']);
            unset($e['created_at']);
            return $e;
        })->toArray();

        $listIds = array_column($drafts, 'id');
        array_multisort($listIds, SORT_DESC, $drafts);

        $response = $this->call('GET', 'api/post-management/admin/draft');

        $response->assertStatus(200);

        foreach ($drafts as $item) {
            $this->assertDatabaseHas('draftables', $item);
        }
    }

    /**
     * @test
     */
    public function can_create_draft_by_admin_router()
    {
        $data = factory(Draftable::class)->make()->toArray();
      
        $response = $this->json('POST', 'api/post-management/admin/draft', $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'draftable_type' => $data['draftable_type'],
            'draftable_id'   => $data['draftable_id'],
            'payload'        => $data['payload'],
        ],
        ]);

        $this->assertDatabaseHas('draftables', $data);
    }

    /**
     * @test
     */
    public function can_update_draft_by_admin_router()
    {
        $draft = factory(Draftable::class)->create();

        unset($draft['updated_at']);
        unset($draft['created_at']);

        $id                    = $draft->id;
        $draft->draftable_type = 'update title';
        $data                  = $draft->toArray();

        $response = $this->json('PUT', 'api/post-management/admin/draft/' . $id, $data);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'draftable_type' => $data['draftable_type'],
            ],
        ]);

        $this->assertDatabaseHas('draftables', $data);
    }

    /**
     * @test
     */
    public function can_delete_draft_by_admin_router()
    {
        $draft = factory(Draftable::class)->create();

        $draft = $draft->toArray();

        unset($draft['updated_at']);
        unset($draft['created_at']);

        $this->assertDatabaseHas('draftables', $draft);

        $response = $this->call('DELETE', 'api/post-management/admin/draft/' . $draft['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDeleted('draftables', $draft);
    }

    /**
     * @test
     */
    public function can_get_draft_item_by_admin_router()
    {
        $draft = factory(Draftable::class)->create();

        $response = $this->call('GET', 'api/post-management/admin/draft/' . $draft->id);

        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'draftable_type' => $draft['draftable_type'],
            'draftable_id'   => $draft['draftable_id'],
            'payload'        => $draft['payload'],
        ],
        ]);
    }
}
