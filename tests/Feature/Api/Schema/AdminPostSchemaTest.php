<?php

namespace VCComponent\Laravel\Post\Test\Feature\Api\Schema;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Post\Entities\PostSchema;
use VCComponent\Laravel\Post\Entities\PostSchemaRule;
use VCComponent\Laravel\Post\Entities\PostSchemaType;
use VCComponent\Laravel\Post\Test\TestCase;

class AdminPostSchemaTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function can_get_paginate_schemas_by_admin()
    {
        $schemas = factory(PostSchema::class, 5)->create();

        $schemas = $schemas->map(function ($schema) {
            unset($schema['created_at']);
            unset($schema['updated_at']);
            return $schema;
        })->toArray();

        $list_ids = array_column($schemas, 'id');
        array_multisort($list_ids, SORT_DESC, $schemas);

        $response = $this->call('GET', 'api/post-management/admin/post-schemas');

        $response->assertStatus(200);
        $response->assertJson(['data' => $schemas]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_paginate_schemas_with_constraints_by_admin()
    {
        $schemas = factory(PostSchema::class, 5)->create();

        $constraint = $schemas[0]->label;

        $schemas = $schemas->filter(function ($schema) use ($constraint) {
            unset($schema['created_at']);
            unset($schema['updated_at']);
            return $schema->label == $constraint;
        })->toArray();

        $list_ids = array_column($schemas, 'id');
        array_multisort($list_ids, SORT_DESC, $schemas);

        $response = $this->call('GET', 'api/post-management/admin/post-schemas?constraints={"label":"' . $constraint . '"}');

        $response->assertStatus(200);
        $response->assertJson(['data' => $schemas]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_paginate_schemas_with_search_by_admin()
    {
        $schemas = factory(PostSchema::class, 5)->create();

        $search = $schemas[0]->name;

        $schemas = $schemas->filter(function ($schema) use ($search) {
            unset($schema['created_at']);
            unset($schema['updated_at']);
            return $schema->name == $search;
        })->toArray();

        $list_ids = array_column($schemas, 'id');
        array_multisort($list_ids, SORT_DESC, $schemas);

        $response = $this->call('GET', 'api/post-management/admin/post-schemas?search=' . $search);

        $response->assertStatus(200);
        $response->assertJson(['data' => $schemas]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_paginate_schemas_with_order_by_by_admin()
    {
        $schemas = factory(PostSchema::class, 5)->create();

        $schemas = $schemas->map(function ($schema) {
            unset($schema['created_at']);
            unset($schema['updated_at']);
            return $schema;
        })->toArray();

        $list_ids = array_column($schemas, 'id');
        array_multisort($list_ids, SORT_DESC, $schemas);

        $list_names = array_column($schemas, 'name');
        array_multisort($list_names, SORT_DESC, $schemas);

        $response = $this->call('GET', 'api/post-management/admin/post-schemas?order_by={"name":"DESC"}');

        $response->assertStatus(200);
        $response->assertJson(['data' => $schemas]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_create_schema_by_admin()
    {
        $data = factory(PostSchema::class)->make([
            'post_type' => 'post'
        ])->toArray();

        unset($data['created_at']);
        unset($data['updated_at']);

        $response = $this->call('POST', 'api/post-management/admin/post-schemas', $data);

        $response->assertStatus(200);
        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('post_schemas', $data);
    }

    /** @test */
    public function should_not_create_schema_with_null_label_by_admin()
    {
        $data = factory(PostSchema::class)->make([
            'label' => null
        ])->toArray();

        $response = $this->call('POST', 'api/post-management/admin/post-schemas', $data);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'The given data was invalid.']);
        $response->assertJsonStructure([
            'errors' => [
                'label' => []
            ]
        ]);
    }

    /** @test */
    public function should_not_create_schema_with_null_schema_type_id_by_admin()
    {
        $data = factory(PostSchema::class)->make([
            'schema_type_id' => null
        ])->toArray();

        $response = $this->call('POST', 'api/post-management/admin/post-schemas', $data);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'The given data was invalid.']);
        $response->assertJsonStructure([
            'errors' => [
                'schema_type_id' => []
            ]
        ]);
    }

    /** @test */
    public function should_not_create_schema_with_null_schema_rule_id_by_admin()
    {
        $data = factory(PostSchema::class)->make([
            'schema_rule_id' => null
        ])->toArray();

        $response = $this->call('POST', 'api/post-management/admin/post-schemas', $data);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'The given data was invalid.']);
        $response->assertJsonStructure([
            'errors' => [
                'schema_rule_id' => []
            ]
        ]);
    }

    /** @test */
    public function should_not_create_schema_with_null_post_type_by_admin()
    {
        $data = factory(PostSchema::class)->make([
            'post_type' => null
        ])->toArray();

        $response = $this->call('POST', 'api/post-management/admin/post-schemas', $data);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'The given data was invalid.']);
        $response->assertJsonStructure([
            'errors' => [
                'post_type' => []
            ]
        ]);
    }

    /** @test */
    public function can_get_a_schema_by_admin()
    {
        $schema = factory(PostSchema::class)->create([
            'post_type' => 'post'
        ])->toArray();

        unset($schema['created_at']);
        unset($schema['updated_at']);

        $response = $this->call('GET', 'api/post-management/admin/post-schemas/' . $schema['id']);

        // $response->assertStatus(200);
        $response->assertJson(['data' => $schema]);
    }

    /** @test */
    public function should_not_get_an_undefined_schema_by_admin()
    {
        $response = $this->call('GET', 'api/post-management/admin/post-schemas/' . rand(1, 5));

        $response->assertStatus(500);
        $response->assertJson(['message' => 'Không tìm thấy thuộc tính !']);
    }

    /** @test */
    public function can_update_schema_by_admin()
    {
        $schema = factory(PostSchema::class)->create(['post_type' => 'post'])->toArray();
        $schema['name'] = 'new_name';
        $schema['label'] = 'new_label';

        unset($schema['created_at']);
        unset($schema['updated_at']);

        $response = $this->call('PUT', 'api/post-management/admin/post-schemas/' . $schema['id'], $schema);

        $response->assertStatus(200);
        $response->assertJson(['data' => $schema]);
    }

    /** @test */
    public function should_not_update_an_undefined_schema_by_admin()
    {
        $data = factory(PostSchema::class)->make(['post_type' => 'post'])->toArray();
        $response = $this->call('PUT', 'api/post-management/admin/post-schemas/' . rand(1, 5), $data);

        $response->assertStatus(500);
        $response->assertJson(['message' => 'Không tìm thấy thuộc tính !']);
    }

    /** @test */
    public function can_delete_a_schema_by_admin()
    {
        $schema = factory(PostSchema::class)->create(['post_type' => 'post'])->toArray();

        $response = $this->call('DELETE', 'api/post-management/admin/post-schemas/' . $schema['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDeleted('post_schemas', $schema);
    }

    /** @test */
    public function should_delete_an_undefined_schema_by_admin()
    {
        $response = $this->call('DELETE', 'api/post-management/admin/post-schemas/' . rand(1, 5));

        $response->assertStatus(500);
        $response->assertJson(['message' => 'Không tìm thấy thuộc tính !']);
    }

    /** @test */
    public function can_get_list_pagiante_schema_types_by_admin()
    {
        $schema_types = factory(PostSchemaType::class, 5)->create();

        $schema_types = $schema_types->map(function ($schema_type) {
            unset($schema_type['created_at']);
            unset($schema_type['updated_at']);
            return $schema_type;
        })->toArray();
        
        $schema_types = array_merge($schema_types, $this->getDefaultsSchemaType());

        $list_ids = array_column($schema_types, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_types);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-types');
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_types]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_list_pagiante_schema_types_with_constraints_by_admin()
    {
        $schema_types = factory(PostSchemaType::class, 5)->create();

        $constraint = $schema_types[0]->name;

        $schema_types = $schema_types->filter(function ($schema_type) use ($constraint) {
            unset($schema_type['created_at']);
            unset($schema_type['updated_at']);
            return $schema_type->name == $constraint;
        })->toArray();

        $list_ids = array_column($schema_types, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_types);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-types?constraints={"name": "' . $constraint . '"}');
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_types]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_list_pagiante_schema_types_with_search_by_admin()
    {
        $schema_types = factory(PostSchemaType::class, 5)->create();

        $search = $schema_types[0]->name;

        $schema_types = $schema_types->filter(function ($schema_type) use ($search) {
            unset($schema_type['created_at']);
            unset($schema_type['updated_at']);
            return $schema_type->name == $search;
        })->toArray();

        $list_ids = array_column($schema_types, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_types);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-types?search=' . $search);
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_types]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_list_pagiante_schema_types_with_order_by_by_admin()
    {
        $schema_types = factory(PostSchemaType::class, 5)->create();

        $schema_types = $schema_types->map(function ($schema_type) {
            unset($schema_type['created_at']);
            unset($schema_type['updated_at']);
            return $schema_type;
        })->toArray();

        $schema_types = array_merge($schema_types, $this->getDefaultsSchemaType());

        $list_ids = array_column($schema_types, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_types);

        $list_names = array_column($schema_types, 'name');
        array_multisort($list_names, SORT_DESC, $schema_types);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-types?order_by={"name":"DESC"}');
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_types]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_list_all_schema_types_by_admin()
    {
        $schema_types = factory(PostSchemaType::class, 5)->create();

        $schema_types = $schema_types->map(function ($schema_type) {
            unset($schema_type['created_at']);
            unset($schema_type['updated_at']);
            return $schema_type;
        })->toArray();
        
        $schema_types = array_merge($schema_types, $this->getDefaultsSchemaType());

        $list_ids = array_column($schema_types, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_types);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-types/all');
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_types]);
    }

    /** @test */
    public function can_get_list_all_schema_types_with_constraints_by_admin()
    {
        $schema_types = factory(PostSchemaType::class, 5)->create();

        $constraint = $schema_types[0]->name;

        $schema_types = $schema_types->filter(function ($schema_type) use ($constraint) {
            unset($schema_type['created_at']);
            unset($schema_type['updated_at']);
            return $schema_type->name == $constraint;
        })->toArray();

        $list_ids = array_column($schema_types, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_types);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-types/all?constraints={"name": "' . $constraint . '"}');
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_types]);
    }

    /** @test */
    public function can_get_list_all_schema_types_with_search_by_admin()
    {
        $schema_types = factory(PostSchemaType::class, 5)->create();

        $search = $schema_types[0]->name;

        $schema_types = $schema_types->filter(function ($schema_type) use ($search) {
            unset($schema_type['created_at']);
            unset($schema_type['updated_at']);
            return $schema_type->name == $search;
        })->toArray();

        $list_ids = array_column($schema_types, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_types);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-types/all?search=' . $search);
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_types]);
    }

    /** @test */
    public function can_get_list_all_schema_types_with_order_by_by_admin()
    {
        $schema_types = factory(PostSchemaType::class, 5)->create();

        $schema_types = $schema_types->map(function ($schema_type) {
            unset($schema_type['created_at']);
            unset($schema_type['updated_at']);
            return $schema_type;
        })->toArray();

        $schema_types = array_merge($schema_types, $this->getDefaultsSchemaType());

        $list_ids = array_column($schema_types, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_types);

        $list_names = array_column($schema_types, 'name');
        array_multisort($list_names, SORT_DESC, $schema_types);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-types/all?order_by={"name":"DESC"}');
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_types]);
    }

    /** @test */
    public function can_get_list_pagiante_schema_rules_by_admin()
    {
        $schema_rules = factory(PostSchemaRule::class, 5)->create();

        $schema_rules = $schema_rules->map(function ($schema_rule) {
            unset($schema_rule['created_at']);
            unset($schema_rule['updated_at']);
            return $schema_rule;
        })->toArray();

        $list_ids = array_column($schema_rules, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_rules);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-rules');
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_rules]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_list_pagiante_schema_rules_with_constraints_by_admin()
    {
        $schema_rules = factory(PostSchemaRule::class, 5)->create();

        $constraint = $schema_rules[0]->name;

        $schema_rules = $schema_rules->filter(function ($schema_rule) use ($constraint) {
            unset($schema_rule['created_at']);
            unset($schema_rule['updated_at']);
            return $schema_rule->name == $constraint;
        })->toArray();

        $list_ids = array_column($schema_rules, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_rules);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-rules?constraints={"name": "' . $constraint . '"}');
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_rules]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_list_pagiante_schema_rules_with_search_by_admin()
    {
        $schema_rules = factory(PostSchemaRule::class, 5)->create();

        $search = $schema_rules[0]->name;

        $schema_rules = $schema_rules->filter(function ($schema_rule) use ($search) {
            unset($schema_rule['created_at']);
            unset($schema_rule['updated_at']);
            return $schema_rule->name == $search;
        })->toArray();

        $list_ids = array_column($schema_rules, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_rules);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-rules?search=' . $search);
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_rules]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_list_pagiante_schema_rules_with_order_by_by_admin()
    {
        $schema_rules = factory(PostSchemaRule::class, 5)->create();

        $schema_rules = $schema_rules->map(function ($schema_rule) {
            unset($schema_rule['created_at']);
            unset($schema_rule['updated_at']);
            return $schema_rule;
        })->toArray();

        $list_ids = array_column($schema_rules, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_rules);

        $list_names = array_column($schema_rules, 'name');
        array_multisort($list_names, SORT_DESC, $schema_rules);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-rules?order_by={"name":"DESC"}');
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_rules]);
        $response->assertJsonStructure([
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_list_all_schema_rules_by_admin()
    {
        $schema_rules = factory(PostSchemaRule::class, 5)->create();

        $schema_rules = $schema_rules->map(function ($schema_rule) {
            unset($schema_rule['created_at']);
            unset($schema_rule['updated_at']);
            return $schema_rule;
        })->toArray();

        $list_ids = array_column($schema_rules, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_rules);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-rules/all');
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_rules]);
    }

    /** @test */
    public function can_get_list_all_schema_rules_with_constraints_by_admin()
    {
        $schema_rules = factory(PostSchemaRule::class, 5)->create();

        $constraint = $schema_rules[0]->name;

        $schema_rules = $schema_rules->filter(function ($schema_rule) use ($constraint) {
            unset($schema_rule['created_at']);
            unset($schema_rule['updated_at']);
            return $schema_rule->name == $constraint;
        })->toArray();

        $list_ids = array_column($schema_rules, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_rules);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-rules/all?constraints={"name": "' . $constraint . '"}');
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_rules]);
    }

    /** @test */
    public function can_get_list_all_schema_rules_with_search_by_admin()
    {
        $schema_rules = factory(PostSchemaRule::class, 5)->create();

        $search = $schema_rules[0]->name;

        $schema_rules = $schema_rules->filter(function ($schema_rule) use ($search) {
            unset($schema_rule['created_at']);
            unset($schema_rule['updated_at']);
            return $schema_rule->name == $search;
        })->toArray();

        $list_ids = array_column($schema_rules, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_rules);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-rules/all?search=' . $search);
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_rules]);
    }

    /** @test */
    public function can_get_list_all_schema_rules_with_order_by_by_admin()
    {
        $schema_rules = factory(PostSchemaRule::class, 5)->create();

        $schema_rules = $schema_rules->map(function ($schema_rule) {
            unset($schema_rule['created_at']);
            unset($schema_rule['updated_at']);
            return $schema_rule;
        })->toArray();

        $list_ids = array_column($schema_rules, 'id');
        array_multisort($list_ids, SORT_DESC, $schema_rules);

        $list_names = array_column($schema_rules, 'name');
        array_multisort($list_names, SORT_DESC, $schema_rules);

        $response = $this->call('GET', 'api/post-management/admin/post-schema-rules/all?order_by={"name":"DESC"}');
        $response->assertStatus(200);
        $response->assertJson(['data' => $schema_rules]);
    }

    protected function getDefaultsSchemaType()
    {
        return [
            [
                'id' => 1,
                'name' => 'text'
            ],
            [
                'id' => 2,
                'name' => 'textarea'
            ],
            [
                'id' => 3,
                'name' => 'tinyMCE'
            ],
            [
                'id' => 4,
                'name' => 'checkbox'
            ],
            [
                'id' => 5,
                'name' => 'image'
            ],
        ];
    }
}
