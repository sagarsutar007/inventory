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
            if ($user->type === "superadmin") {
                return true;
            } else {
                return false;
            }
        });

        Gate::define('view-commodities', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-commodities');
        });

        Gate::define('add-commodity', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('add-commodity');
        });

        Gate::define('edit-commodity', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('edit-commodity');
        });

        Gate::define('delete-commodity', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('delete-commodity');
        });


        // Category Gates

        Gate::define('view-categories', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-categories');
        });

        Gate::define('add-category', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('add-category');
        });

        Gate::define('edit-category', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('edit-category');
        });

        Gate::define('delete-category', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('delete-category');
        });

        // Raw Materials Gates

        Gate::define('view-raw-materials', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-raw-materials');
        });

        Gate::define('add-raw-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('add-raw-material');
        });

        Gate::define('edit-raw-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('edit-raw-material');
        });

        Gate::define('delete-raw-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('delete-raw-material');
        });

        Gate::define('view-add-raw-materials', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            $stats1 = $user->hasPermission('view-raw-materials');
            $stats2 = $user->hasPermission('add-raw-material');

            if ($stats1 || $stats2) {
                return true;
            } else {
                return false;
            }
        });

        // Semi Finished Materials Gates

        Gate::define('view-semi-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-semi-material');
        });

        Gate::define('add-semi-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('add-semi-material');
        });

        Gate::define('edit-semi-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('edit-semi-material');
        });

        Gate::define('delete-semi-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('delete-semi-material');
        });

        Gate::define('view-add-semi-materials', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }

            $stats1 = $user->hasPermission('view-semi-material');
            $stats2 = $user->hasPermission('add-semi-material');

            if ($stats1 || $stats2) {
                return true;
            } else {
                return false;
            }
        });

        // Finished Materials Gates

        Gate::define('view-finish-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-finish-material');
        });

        Gate::define('add-finish-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('add-finish-material');
        });

        Gate::define('edit-finish-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('edit-finish-material');
        });

        Gate::define('delete-finish-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('delete-finish-material');
        });

        Gate::define('view-add-finish-materials', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }

            $stats1 = $user->hasPermission('view-finish-material');
            $stats2 = $user->hasPermission('add-finish-material');

            if ($stats1 || $stats2) {
                return true;
            } else {
                return false;
            }
        });

        // Finished Materials Gates

        Gate::define('view-bom', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-bom');
        });

        Gate::define('add-bom', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('add-bom');
        });

        Gate::define('edit-bom', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('edit-bom');
        });

        Gate::define('delete-bom', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('delete-bom');
        });
    }

    protected function isAdmin($user){
        if ($user->type === "superadmin") {
            return true;
        } else {
            return false;
        }
    }
}
