<?php

namespace VCComponent\Laravel\Post\Contracts;

interface PostSchema
{
    public function postTypes();
    public function postMetas();
    public function schema();
}
