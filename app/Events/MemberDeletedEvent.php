<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;
use App\Models\MemberLogs;

class MemberDeletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($member)
    {
        $ml = new MemberLogs ;
        $ml->user_id = $member->user_id ;
        $ml->account = $member->account ; 
        $ml->user_name = $member->user_name ;
        $ml->log = 'Delete' ; 
        $ml->save() ;

        Log::info('Delete a User: ', [
            'user_id' => $member->user_id,
            'account' => $member->account,
            'user_name' => $member->user_name
        ]) ;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
