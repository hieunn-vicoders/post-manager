<?php

namespace VCComponent\Laravel\Post\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait Helpers
{
    private function applyQueryScope($query, $field, $value)
    {
        $query = $query->where($field, $value);

        return $query;
    }

    private function getPostTypeFromRequest(Request $request)
    {
        $path_items  = collect(explode('/', $request->path()));
        $check_admin = $path_items->filter(function ($item) {
            return $item === 'admin';
        })->count();

        if ($check_admin) {
            if (config('post.namespace') === '') {
                $path_items = $this->handlingPathArray($path_items, 3);
            } else {
                $path_items = $this->handlingPathArray($path_items, 4);
            }
        } else {
            if (config('post.namespace') === '') {
                $path_items = $this->handlingPathArray($path_items, 2);
            } else {
                $path_items = $this->handlingPathArray($path_items, 3);
            }
        }

        $type = $path_items->last();

        return $type;
    }

    private function handlingPathArray($path_array, $base)
    {
        switch ($path_array->count()) {
            case $base + 1:
                $path_array->pop();
                break;
            case $base + 2:
                $path_array->pop();
                $path_array->pop();
                break;
        }

        return $path_array;
    }

    private function filterPostRequestData(Request $request, $entity, $type)
    {
        $request_data = collect($request->all());
        // if ($request->has('status')) {
        //     $request_data->pull('status');
        // }
        if (!$request->has('status')) {
            $request_data['status'] = 1;
        }
        if (count($entity->postTypes()) && $type !== 'posts') {
            $schema_func = Str::camel($type . '_schema');
            $schema      = collect($entity->$schema_func());
        } else {
            $schema = collect($entity->schema());
        }

        $request_data_keys = $request_data->keys();
        $schema_keys       = $schema->keys()->toArray();

        $default_keys = $request_data_keys->diff($schema_keys)->all();

        $data            = [];
        $data['default'] = $request_data->filter(function ($value, $key) use ($default_keys) {
            return in_array($key, $default_keys);
        })->toArray();
        $data['schema'] = $request_data->filter(function ($value, $key) use ($schema_keys) {
            return in_array($key, $schema_keys);
        })->toArray();

        return $data;
    }

    private function getTypePost($request)
    {
        if (config('post.models.post') !== null) {
            $model_class = config('post.models.post');
        } else {
            $model_class = \VCComponent\Laravel\Post\Entities\Post::class;
        }
        $model      = new $model_class;
        $postTypes  = $model->postTypes();
        $path_items = collect(explode('/', $request->path()));
        $type       = 'posts';

        foreach ($postTypes as $value) {
            foreach ($path_items as $item) {
                if ($value === $item) {
                    $type = $value;
                } else if ($item === 'pages') {
                    $type = 'pages';
                }
            }
        }

        return $type;
    }

    private function draftTypes($request)
    {
        if (config('post.models.draft') !== null) {
            $model_class = config('post.models.draft');
        } else {
            $model_class = \VCComponent\Laravel\Post\Entities\Draftable::class;
        }
        $model      = new $model_class;
        $draftTypes = $model->draftTypes();
        $path_items = collect(explode('/', $request->path()));

        $type = 'posts';
        foreach ($draftTypes as $value) {
            foreach ($path_items as $item) {
                if ($value === $item) {
                    $type = $value;
                } else if ($item === 'pages') {
                    $type = 'pages';
                }
            }
        }
        return $type;
    }
}
