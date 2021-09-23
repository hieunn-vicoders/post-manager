<?php

namespace VCComponent\Laravel\Post\Events;

use Illuminate\Queue\SerializesModels;

class PostDeletedEvent
{
    use SerializesModels;


    public function __construct()
    {
        
    }
}
