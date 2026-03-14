<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhiteboardDrawEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $courseId;
    public $type;
    public $data;
    public $userId;
    public $userName;

    /**
     * Create a new event instance.
     *
     * @param int    $courseId  The course/session ID
     * @param string $type     Event type: draw, erase, clear, object-added, object-modified, object-removed
     * @param array  $data     Serialized canvas object data (minimal payload)
     * @param int    $userId   The user who triggered the event
     * @param string $userName Display name for presence
     */
    public function __construct($courseId, $type, $data, $userId, $userName)
    {
        $this->courseId = $courseId;
        $this->type = $type;
        $this->data = $data;
        $this->userId = $userId;
        $this->userName = $userName;
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
        return 'whiteboard.draw';
    }

    /**
     * Get the data to broadcast — keep payload minimal.
     */
    public function broadcastWith()
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'userId' => $this->userId,
            'userName' => $this->userName,
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
