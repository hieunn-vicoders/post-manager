<?php

namespace VCComponent\Laravel\Post\Contracts;

interface PostUtilitiesInterface
{
    public function getID();
    public function getTitle();
    public function getDescription();
    public function getContent();
    public function getSlug();
    public function getThumbnail();
    public function getStatus();
    public function getIsHot();
    public function getCreateDate();
    public function getType();
    public function getFields($fields);

}
