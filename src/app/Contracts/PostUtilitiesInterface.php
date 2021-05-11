<?php

namespace VCComponent\Laravel\Post\Contracts;

interface PostUtilitiesInterface
{
    public function getTitle();
    public function getDescription();
    public function getContent();
    public function getSlug();
    public function getThumbnail();
    public function __get($fields);

}
