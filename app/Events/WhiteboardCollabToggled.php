<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhiteboardCollabToggled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $courseId;
    public $collabMode;

    /**
     * Create a new event instance.
     *
     * @param int  $courseId   The course/session ID
     * @param bool $collabMode New collaborative mode state
     */
    public function __construct($courseId, $collabMode)
    {
        $this->courseId = $courseId;
        $this->collabMode = $collabMode;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new PresenceChannel('whiteboard.' . $this->courseId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'whiteboard.collab-toggled';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'collab_mode' => $this->collabMode,
        ];
    }

    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return \App\Models\ExternalApp::where('slug', 'interactive-whiteboard')
            ->where('is_active', true)
            ->exists();
    }
}
