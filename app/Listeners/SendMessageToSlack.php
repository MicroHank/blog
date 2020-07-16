<?php

namespace App\Listeners;

use App\Events\OrderMessageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMessageToSlack
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Event  $event
     * @return void
     */
    public function handle(OrderMessageEvent $event)
    {
        var_dump("Listeners:SendMessageToSlack");
        var_dump($event->order);
    }
}
