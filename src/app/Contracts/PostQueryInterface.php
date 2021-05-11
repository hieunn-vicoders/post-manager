<?php

namespace VCComponent\Laravel\Post\Contracts;

interface PostQueryInterface
{
    public function all($columns = ['*']);

}
