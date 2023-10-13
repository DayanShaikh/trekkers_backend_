<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use DB;
use Auth;
use App\Policies\UserPolicy;
use App\Policies\RolePolicy;
use App\Policies\AttributePolicy;
use App\Policies\FrontMenuPolicy;
use App\Policies\TripTourGuidePolicy;
use App\Models\User;
use App\Models\Role;
use App\Models\Attribute;
use App\Models\FrontMenu;
use App\Models\TripTourGuide;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Attribute::class => AttributePolicy::class,
        FrontMenu::class => FrontMenuPolicy::class,
        TripTourGuide::class => TripTourGuidePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
