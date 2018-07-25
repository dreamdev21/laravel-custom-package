<?php

namespace Rxmg\EspTailoredMail;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class EspConnectorProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
           __DIR__ . '/config/esp-connector.php' => config_path('esp-connector.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes/web.php';
        $this->mergeConfigFrom(__DIR__.'/config/esp-connector.php', 'esp-connector');
        $this->app->make('Rxmg\EspTailoredMail\EspTailoredMail');
        $this->app->singleton('EspTailoredMail', function ($app) {
            $config = $app->make('config');
            $bronto = $config->get('bronto');
            $access_key = $bronto['access_key'];
            $server_uri = $bronto['server_uri'];
            $host_id = $bronto['host_id'];
            $database_id = $bronto['database_id'];
            $details = collect([
                'access_key' => $access_key,
                'server_uri' => $server_uri,
                'host_id' => $host_id,
                'database_id' => $database_id
            ]);

            return new EspTailoredMail($details);
        });
    }

    public function provides()
    {
        return ['EspTailoredMail'];
    }
}
