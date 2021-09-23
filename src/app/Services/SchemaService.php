<?php

namespace VCComponent\Laravel\Post\Services;

use Illuminate\Support\Collection;
use VCComponent\Laravel\Post\Entities\PostSchema;

/**
 * Class contains schema helper functions
 */
class SchemaService
{
    public function get(string $product_type): Collection
    {
        return PostSchema::ofPostType($product_type)->get();
    }

    public function getKey(string $product_type): Collection
    {
        $data = $this->get($product_type);
        return $data->map(function ($item) {
            return $item->name;
        });
    }
}
