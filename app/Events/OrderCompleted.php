<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $order;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('order-completed');
    }
    public function broadcastAs()
    {
        return 'OrderCompleted';
    }
    public function broadcastWith()
    {
        $items = $this->order->orderItems ?? collect([]);
        $items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->orderItem->name ?? "N/A",
                'price' => $item->price,
                'quantity' => $item->quantity,
            ];
        })->toArray();
        return [
            'order_id' => $this->order->id ?? null,
            'price' => $this->order->total ?? 0,
            'table' => $this->order->diningTable->name ?? 'N/A',
            'items' => $items,
        ];
    }
}
