<?php
/**
 * Created by PhpStorm.
 * User: rciman
 * Date: 10/06/2015
 * Time: 10:10
 */

namespace RiCi12\LdapLaravelProvider\ServiceProvider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use RiCi12\LdapLaravelProvider\Provider\LdapProvider;

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