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
        $response->assertJson([
            'data' => [
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
        $response->assertJson([
            'data' => [
                'draftable_type' => $draft['draftable_type'],
                'draftable_id'   => $draft['draftable_id'],
                'payload'        => $draft['payload'],
            ],
        ]);
    }

    /** @test */
    public function can_get_list_paginate_draft_with_constraint_by_admin()
    {
        $drafts = factory(Draftable::class, 5)->create();

        $constraint = $drafts[0]->draftable_id;

        $drafts = $drafts->filter(function ($draft) use ($constraint) {
            unset($draft['created_at']);
            unset($draft['updated_at']);
            return $draft->draftable_id == $constraint;
        })->toArray();

        $listIds = array_column($drafts, 'id');
        array_multisort($listIds, SORT_DESC, $drafts);

        $response = $this->call('GET', 'api/post-management/admin/draft?constraints={"draftable_id":"' . $constraint . '"}');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => $drafts
        ]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_list_paginate_draft_with_search_by_admin()
    {
        $drafts = factory(Draftable::class, 5)->create();

        $search = $drafts[0]->payload;

        $drafts = $drafts->filter(function ($draft) use ($search) {
            unset($draft['created_at']);
            unset($draft['updated_at']);
            return $draft->payload == $search;
        })->toArray();

        $listIds = array_column($drafts, 'id');
        array_multisort($listIds, SORT_DESC, $drafts);

        $response = $this->call('GET', 'api/post-management/admin/draft?search=' . $search);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => $drafts
        ]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_list_paginate_draft_with_order_by_by_admin()
    {
        $drafts = factory(Draftable::class, 5)->create();

        $drafts = $drafts->map(function ($draft) {
            unset($draft['created_at']);
            unset($draft['updated_at']);
            return $draft;
        })->toArray();

        $listIds = array_column($drafts, 'id');
        array_multisort($listIds, SORT_DESC, $drafts);

        $listPayload = array_column($drafts, 'payload');
        array_multisort($listPayload, SORT_DESC, $drafts);

        $response = $this->call('GET', 'api/post-management/admin/draft?order_by={"payload":"DESC"}');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => $drafts
        ]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function should_not_create_draft_with_null_payload_by_admin() {
        $data = factory(Draftable::class)->make([
            'payload' => null
        ])->toArray();

        $response = $this->call('POST', 'api/post-management/admin/draft', $data);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'The given data was invalid.']);
    }

    /** @test */
    public function should_not_update_draft_with_null_payload_by_admin() {
        $data = factory(Draftable::class)->create()->toArray();

        $data['payload'] = null;

        $response = $this->call('PUT', 'api/post-management/admin/draft/'.$data['id'], $data);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'The given data was invalid.']);
    }

    /** @test */
    public function should_not_delete_undefined_draft_by_admin() {
        $response = $this->call('DELETE', 'api/post-management/admin/draft/'.rand(1, 5));

        $response->assertStatus(400);
        $response->assertJson(['message' => 'draft not found']);
    }
}
