<?php

namespace VCComponent\Laravel\Post\Traits;

trait PostUtilitiesTrait
{
    public function getID()
    {
        return $this->id;
    }
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
    public function getStatus()
    {
        return $this->status;
    }
    public function getIsHot()
    {
        return $this->is_hot;
    }
    public function getCreateDate()
    {
        return $this->updated_at;
    }
    public function getType()
    {
        return $this->type;
    }


    // get any field by post
    public function getFields($field)
    {
        if($this->getAttribute($field))
            return $this->getAttribute($field);
        else {
            return $this->getMetaField($field);
        }
    }

}
