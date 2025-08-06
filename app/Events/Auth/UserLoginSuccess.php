<?php

namespace App\Events\Auth;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class UserLoginSuccess
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    public $request;

    /**
     * Login Success Event Construct.
     */
    public function __construct(Request $request, User $user)
    {
        $this->user = $user;
        $this->request = $this->prepareRequestData($request);
    }

    public function prepareRequestData($request)
    {
        // Only include safe, non-sensitive request data
        $data = $request->only([
            'user_agent' => $request->header('User-Agent'),
            'accept_language' => $request->header('Accept-Language'),
            'referer' => $request->header('Referer')
        ]);

        $data['last_ip'] = optional(request())->getClientIp();
        $data['timestamp'] = now();

        return $data;
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
