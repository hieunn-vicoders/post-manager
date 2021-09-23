<?php

namespace VCComponent\Laravel\Post\Events;

use Illuminate\Queue\SerializesModels;

class PostUpdatedEvent
{
    use SerializesModels;

    public $post;

    public function __construct($post)
    {
        $this->post = $post;
    }
}
