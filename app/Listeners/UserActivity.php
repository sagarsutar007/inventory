<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\UserLog;
use Jenssegers\Agent\Facades\Agent;

class UserActivity
{
    private $request;

    /**
     * Create the event listener.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle(mixed $event): void
    {
        if ($event instanceof Login) {
            $this->createUserLog('login', $event->user);
        } else if ($event instanceof Logout) {
            $this->createUserLog('logout', $event->user);
        }
    }

    private function createUserLog(string $actionType, $user)
    {
        UserLog::create([
            'user_id' => $user->id,
            'action_type' => $actionType,
            'ip_address' => $this->request->ip(),
            'mac_address' => '',
            'client' => Agent::browser(),
            'device_type' => Agent::device(),
        ]);
    }

    private function getDeviceType(): string
    {
        $userAgent = Agent::device();

        if (Agent::isDesktop()) {
            $userAgent = 'Desktop';
        } else if (Agent::isTablet()) {
            $userAgent = 'Tablet';
        } else if (Agent::isPhone()) {
            if ($userAgent == 'WebKit') {
                $userAgent = 'Phone';
            }
        }

        return $userAgent;
    }
}
