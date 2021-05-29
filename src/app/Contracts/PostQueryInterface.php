<?php

namespace VCComponent\Laravel\Post\Contracts;

interface PostQueryInterface
{
    public function findByField($field, $value, $columns = ['*']);
    public function findWhere(array $where, $columns = ['*']);

}
