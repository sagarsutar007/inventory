<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Inventory',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>Inventory</b>',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => true,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the admin panel.
    |
    | For detailed instructions you can look the laravel mix section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => true,
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // Sidebar items:
        // [
        //     'text' => 'Dashboard',
        //     'url' => 'app/dashbaord',
        //     'icon' => 'fas fa-fw fa-tachometer-alt',
        //     'active' => []
        // ],
        [
            'text' => 'Commodities',
            'url' => 'app/commodities',
            'icon' => 'fas fa-fw fa-th-large',
            'can' => 'view-commodities',
            'active' => []
        ],
        [
            'text' => 'Categories',
            'url' => 'app/categories',
            'icon' => 'fas fa-fw fa-th',
            'can' => 'view-categories',
            'active' => []
        ],
        [
            'text' => 'Raw Materials',
            'icon' => 'fas fa-fw fa-boxes',
            'can' => 'view-add-raw-materials',
            'submenu' => [
                [
                    'text' => 'Add New',
                    'url' => 'app/raw-materials/add',
                    'can' => 'add-raw-material',
                    'active' => []
                ],
                [
                    'text' => 'View All',
                    'url' => 'app/raw-materials',
                    'can' => 'view-raw-materials',
                    'active' => []
                ],
            ],
            'active' => []
        ],
        [
            'text' => 'Semi Finished Goods',
            'icon' => 'fas fa-fw fa-box',
            'can' => 'view-add-semi-materials',
            'submenu' => [
                [
                    'text' => 'Add New',
                    'url' => 'app/semi-finished-materials/add',
                    'can' => 'add-semi-material',
                ],
                [
                    'text' => 'View All',
                    'url' => 'app/semi-finished-materials',
                    'can' => 'view-semi-material',
                ],
            ],
        ],
        [
            'text' => 'Finished Goods',
            'icon' => 'fas fa-fw fa-archive',
            'can' => 'view-add-finish-materials',
            'submenu' => [
                [
                    'text' => 'Add New',
                    'url' => 'app/finished-materials/add',
                    'can' => 'add-finish-material',
                ],
                [
                    'text' => 'View All',
                    'url' => 'app/finished-materials',
                    'can' => 'view-finish-material',
                ],
            ],
        ],
        [
            'text' => 'Dependent Materials',
            'icon' => 'fas fa-fw fa-archive',
            'submenu' => [
                [
                    'text' => 'Add New',
                    'url' => 'app/dependent-materials/add',
                ],
                [
                    'text' => 'View All',
                    'url' => 'app/dependent-materials',
                ],
            ],
        ],
        [
            'text' => 'Bill Of Materials',
            'url' => 'app/bill-of-materials',
            'icon' => 'fas fa-fw fa-file-invoice',
            'can' => 'view-bom',
        ],
        [
            'text' => 'Warehouse',
            'icon' => 'fas fa-fw fa-people-carry',
            'can' => 'view-warehouse',
            'submenu' => [
                [
                    'text' => 'Issue',
                    'url' => 'app/warehouse/issue',
                    'can' => 'issue-warehouse',
                ],
                [
                    'text' => 'Receive',
                    'url' => 'app/warehouse/receive',
                    'can' => 'receive-warehouse',
                ],
                [
                    'text' => 'View Stock',
                    'url' => 'app/warehouse',
                    'can' => 'view-stock',
                ],
                [
                    'text' => 'View Transactions',
                    'url' => 'app/warehouse/transactions',
                    'can' => 'view-transaction',
                ],
            ],
        ],
        [
            'text' => 'Production Orders',
            'icon' => 'fas fa-fw fa-cart-plus',
            'can' => 'view-po-menu',
            'submenu' => [
                [
                    'text' => 'Add New',
                    'url' => 'app/production-orders/new',
                    'can' => 'add-po',
                ],
                [
                    'text' => 'View All',
                    'url' => 'app/production-orders',
                    'can' => 'view-po',
                ],
            ],
        ],
        [
            'text' => 'PO Kitting',
            'icon' => 'fas fa-fw fa-cart-plus',
            'url' => 'app/po-kitting',
            'can' => 'view-kitting',
        ],
        [
            'text' => 'Vendors',
            'icon' => 'fas fa-fw fa-user-tag',
            'url' => 'app/vendors',
            'can' => 'view-vendor',
        ],
        [
            'text' => 'Reports',
            'url' => '#',
            'icon' => 'fas fa-fw fa-clipboard-list',
            'submenu' => [
                [
                    'text' => 'RM Price List',
                    'url' => 'app/reports/rm-price-list',
                    'can' => 'view-rm-price',
                ],
                [
                    'text' => 'Material Master List',
                    'url' => 'app/reports/material-list',
                    'can' => 'view-material-master',
                ],
                [
                    'text' => 'BOM View',
                    'url' => 'app/reports/bom-view',
                    'can' => 'view-bom-view',
                ],
                [
                    'text' => 'BOM Cost View',
                    'url' => 'app/reports/bom-cost-view',
                    'can' => 'view-bom-cost',
                ],
                [
                    'text' => 'FG Cost Summary',
                    'url' => 'app/reports/fg-cost-summary',
                    'can' => 'view-fg-cost',
                ],
                [
                    'text' => 'RM Stock',
                    'url' => 'app/reports/rm-stock',
                    'can' => 'view-raw-stock',
                ],
                [
                    'text' => 'Production Order',
                    'url' => 'app/reports/po-stock',
                    'can' => 'view-po-report',
                ],
                [
                    'text' => 'PO Shortage',
                    'url' => 'app/reports/po-shortage-stock',
                    'can' => 'view-po-shortage',
                ],
                [
                    'text' => 'PO Shortage Consolidated',
                    'url' => 'app/reports/po-shortage-consolidated-stock',
                    'can' => 'view-po-short-cons',
                ],
                [
                    'text' => 'Planned Order Shortage',
                    'url' => 'app/reports/plo-shortage-report',
                    'can' => 'view-plan-short',
                ],
                [
                    'text' => 'RM Purchase',
                    'url' => 'app/reports/rm-purchase-report',
                    'can' => 'view-raw-pur',
                ],
                [
                    'text' => 'RM Issuance',
                    'url' => 'app/reports/rm-issuance-report',
                    'can' => 'view-raw-issu',
                ],
            ],
        ],
        ['header' => 'account_settings'],
        [
            'text' => 'profile',
            'url' => 'admin/settings',
            'icon' => 'fas fa-fw fa-user',
        ],
        [
            'text' => 'change_password',
            'url' => 'admin/settings',
            'icon' => 'fas fa-fw fa-lock',
        ],
        [
            'text' => 'Settings',
            'icon' => 'fas fa-fw fa-cog',
            'submenu' => [
                [
                    'text' => 'App Settings',
                    'url' => '#',
                ],
                [
                    'text' => 'User Settings',
                    'url' => '#',
                    'submenu' => [
                        [
                            'text' => 'User Roles',
                            'url' => 'app/roles',
                        ],
                        [
                            'text' => 'User Permissions',
                            'url' => 'app/permissions',
                        ],
                        [
                            'text' => 'Users List',
                            'url' => 'app/users',
                        ],
                    ],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-bs4/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-responsive/js/dataTables.responsive.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-responsive/js/responsive.bootstrap4.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-buttons/js/dataTables.buttons.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-buttons/js/buttons.bootstrap4.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/jszip/jszip.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/pdfmake/pdfmake.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/pdfmake/vfs_fonts.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-buttons/js/buttons.html5.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-buttons/js/buttons.print.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/datatables-buttons/js/buttons.colVis.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/datatables-bs4/css/dataTables.bootstrap4.min.css',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/datatables-buttons/css/buttons.bootstrap4.min.css',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/datatables-responsive/css/responsive.bootstrap4.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/toastr/toastr.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/toastr/toastr.min.css',
                ],

            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/select2/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/select2/css/select2.min.css',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
        'JqueryUi' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/jquery-ui/jquery-ui.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/jquery-ui/jquery-ui.min.css',
                ],
            ],
        ],
        'Date Range Picker' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/moment/moment.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/inputmask/jquery.inputmask.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/daterangepicker/daterangepicker.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/daterangepicker/daterangepicker.css',
                ],
            ],
        ],
        'CustomPlugin' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'assets/js/custom.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'assets/css/custom.css',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
