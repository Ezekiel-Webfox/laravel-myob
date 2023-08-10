<?php

namespace Webfox\MYOB;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MYOBServiceProvider extends PackageServiceProvider
{
    public function register()
    {
        $this->app->singleton(MYOB::class, function () {
            return new MYOB();
        });
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('myob')
            ->hasMigration('create_myob_configurations_table')
            ->hasConfigFile();
    }
}