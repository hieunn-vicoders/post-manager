<?php

namespace VCComponent\Laravel\Post\Events;

use Illuminate\Queue\SerializesModels;

class PostUpdatedByAdminEvent
{
    use SerializesModels;

    public $post;

    public function __construct($post)
    {
        $this->post = $post;
    }
}
