<?php

namespace VCComponent\Laravel\Post\Entities;

use Illuminate\Database\Eloquent\Model;

class PostTypeMeta extends Model
{
    protected $table = "post_type_meta";
    protected $fillable = ["type","key","value"];
}
