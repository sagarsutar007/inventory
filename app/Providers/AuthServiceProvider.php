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

        Gate::define('view-raw-vendor-price', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-raw-vendor-price');
        });

        Gate::define('import-raw-vendor-price', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('import-raw-vendor-price');
        });

        Gate::define('view-add-raw-materials', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            $stats1 = $user->hasPermission('view-raw-materials');
            $stats2 = $user->hasPermission('add-raw-material');
            $stats3 = $user->hasPermission('view-raw-vendor-price');

            if ($stats1 || $stats2 || $stats3) {
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

        Gate::define('export-semi-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('export-semi-material');
        });

        Gate::define('import-semi-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('import-semi-material');
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

        Gate::define('export-finish-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('export-finish-material');
        });

        Gate::define('import-finish-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('import-finish-material');
        });

        // Dependent Materials Gates

        Gate::define('view-dependent-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-dependent-material');
        });

        Gate::define('add-dependent-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('add-dependent-material');
        });

        Gate::define('edit-dependent-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('edit-dependent-material');
        });

        Gate::define('delete-dependent-material', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('delete-dependent-material');
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

        // Bill of Materials Gates

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

        Gate::define('export-bom', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('export-bom');
        });

        Gate::define('import-bom', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('import-bom');
        });

        // Warehouse Gates

        Gate::define('view-stock', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-stock');
        });

        Gate::define('issue-warehouse', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('issue-warehouse');
        });

        Gate::define('receive-warehouse', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('receive-warehouse');
        });

        Gate::define('view-transaction', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-transaction');
        });

        Gate::define('view-warehouse', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }

            $stats1 = $user->hasPermission('view-stock');
            $stats2 = $user->hasPermission('issue-warehouse');
            $stats3 = $user->hasPermission('receive-warehouse');
            $stats4 = $user->hasPermission('view-transaction');

            if ($stats1 || $stats2 || $stats3 || $stats4) {
                return true;
            } else {
                return false;
            }
        });

        // Production Order Gates

        Gate::define('view-po', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-po');
        });

        Gate::define('add-po', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('add-po');
        });

        Gate::define('delete-po', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('delete-po');
        });

        Gate::define('view-po-menu', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }

            $stats1 = $user->hasPermission('view-po');
            $stats2 = $user->hasPermission('add-po');

            if ($stats1 || $stats2) {
                return true;
            } else {
                return false;
            }
        });

        // Production Order Kitting Gates

        Gate::define('view-kitting', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-kitting');
        });

        Gate::define('issue-kitting', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('issue-kitting');
        });

        // Bill of Materials Gates

        Gate::define('view-vendor', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-vendor');
        });

        Gate::define('add-vendor', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('add-vendor');
        });

        Gate::define('edit-vendor', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('edit-vendor');
        });

        Gate::define('delete-vendor', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('delete-vendor');
        });

        //Reports Gate

        Gate::define('view-rm-price', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-rm-price');
        });

        Gate::define('view-material-master', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-material-master');
        });
        
        Gate::define('view-bom-view', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-bom-view');
        });

        Gate::define('view-bom-cost', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-bom-cost');
        });

        Gate::define('view-fg-cost', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-fg-cost');
        });

        Gate::define('view-raw-stock', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-raw-stock');
        });
        
        Gate::define('view-po-report', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-po-report');
        });

        Gate::define('view-po-shortage', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-po-shortage');
        });

        Gate::define('view-po-short-cons', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-po-short-cons');
        });
        
        Gate::define('view-plan-short', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-plan-short');
        });

        Gate::define('view-raw-pur', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-raw-pur');
        });

        Gate::define('view-raw-issu', function ($user) {
            if ($this->isAdmin($user)) {
                return true;
            }
            return $user->hasPermission('view-raw-issu');
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
