<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    protected $request;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        ActivityLog::create([
            'user_id'    => $event->user->id,
            'action'     => 'login',
            'description'=> 'User ' . $event->user->email . ' logged in at ' . now(),
        ]);
        // ตรวจสอบว่ามีการเลือก "Remember Me" หรือไม่
        if ($this->request->has('remember')) {
            ActivityLog::create([
                'user_id'    => $event->user->id,
                'action'     => 'remember_me',
                'description'=> 'User ' . $event->user->email . ' chose Remember Me at ' . now(),
            ]);
        }
    }
}
