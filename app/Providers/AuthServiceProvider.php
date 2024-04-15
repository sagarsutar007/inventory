<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //

        Gate::define('admin', function($user){
            $this->isAdmin($user);
            return false;
        });

        Gate::define('view-commodities', function ($user) {
            $this->isAdmin($user);
            return $user->hasPermission('view-commodities');
        });

        Gate::define('add-commodity', function ($user) {
            $this->isAdmin($user);
            return $user->hasPermission('add-commodity');
        });

        Gate::define('edit-commodity', function ($user) {
            $this->isAdmin($user);
            return $user->hasPermission('edit-commodity');
        });

        Gate::define('delete-commodity', function ($user) {
            $this->isAdmin($user);
            return $user->hasPermission('delete-commodity');
        });
    }

    protected function isAdmin($user){
        if ($user->type === "superadmin") {
            return true;
        } 
    }
}
