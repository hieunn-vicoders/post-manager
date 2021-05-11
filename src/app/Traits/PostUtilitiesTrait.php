<?php

namespace VCComponent\Laravel\Post\Traits;

trait PostUtilitiesTrait
{
    public function getTitle()
    {
        return $this->title;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function getSlug()
    {
        return $this->slug;
    }
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function __get($field)
    {
        if($this->getAttribute($field))
            return $this->getAttribute($field);
        else {
            return $this->getMetaField($field);
        }
    }

}
