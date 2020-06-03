<?php

namespace VCComponent\Laravel\Post\Events;

use Illuminate\Queue\SerializesModels;

class PostCreatedEvent
{
    use SerializesModels;

    public $post;

    public function __construct($post)
    {
        $this->post = $post;
    }
}
