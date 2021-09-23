<?php

namespace VCComponent\Laravel\Post\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PreviewEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $draft;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($draft)
    {
        $this->draft = $draft;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
