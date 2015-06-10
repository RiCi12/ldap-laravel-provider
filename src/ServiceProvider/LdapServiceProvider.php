<?php

namespace RiCi12\LdapLaravelProvider\ServiceProvider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use RiCi12\LdapLaravelProvider\Provider\LdapProvider;

/**
 *
 * Service provider for LdapLaravelProvider - extends Auth system registering ldap auth system
 * @package RiCi12\LdapLaravelProvider\ServiceProvider
 */

class LdapServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Auth::extend('ldap', function($app) {
            return new LdapProvider($app['config']['auth.model']);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}