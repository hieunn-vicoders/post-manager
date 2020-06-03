<?php

namespace VCComponent\Laravel\Post\Entities;

use Illuminate\Database\Eloquent\Model;
use VCComponent\Laravel\Post\Traits\DraftSchemaTrait;

class Draftable extends Model
{
    use DraftSchemaTrait;

    public function drafTypes()
    {
        return [
            'products',
            'posts',
        ];
    }

    protected $fillable = [
        'draftable_type',
        'draftable_id',
        'payload',
    ];
}
