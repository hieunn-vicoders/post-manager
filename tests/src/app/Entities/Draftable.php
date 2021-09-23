<?php

namespace VCComponent\Laravel\Post\Entities;

use Illuminate\Database\Eloquent\Model;
use VCComponent\Laravel\Post\Traits\DraftSchemaTrait;

class Draftable extends Model
{
    use DraftSchemaTrait;

    protected $fillable = [
        'draftable_type',
        'draftable_id',
        'payload',
    ];
}
