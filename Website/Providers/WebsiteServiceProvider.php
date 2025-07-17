<?php

namespace Modules\Website\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Passport;

class WebsiteServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Website';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'website';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerHelpers();
        $this->registerResource();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        Passport::cookie('schoolify_token');
        Passport::tokensExpireIn(Carbon::now()->addHours(24));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
        // Passport::personalAccessTokensExpireIn(Carbon::now()->addMonth(6));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        
        // Register commands
        $this->commands([
            \Modules\Website\Console\PublishAssetsCommand::class,
        ]);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
        
        // Register socialite config for the services config
        if (file_exists(module_path($this->moduleName, 'Config/socialite.php'))) {
            $this->mergeConfigFrom(
                module_path($this->moduleName, 'Config/socialite.php'), 'services'
            );
        }
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Register helpers file
     *
     * @return array
     */

    public function registerHelpers()
    {
        if (File::exists(module_path($this->moduleName, 'Helpers/Functions.php'))) {
            require_once module_path($this->moduleName, 'Helpers/Functions.php');
        }
    }

    public function registerResource()
    {
        // Publish all frontend assets
        $this->publishes([
            __DIR__ . '/../Resources/assets/css' => public_path('css'),
            __DIR__ . '/../Resources/assets/js' => public_path('js'),
            __DIR__ . '/../Resources/assets/fonts' => public_path('fonts'),
            __DIR__ . '/../Resources/assets/images' => public_path('images'),
        ], 'website-assets');

        // Publish PWA files to root public directory
        $this->publishes([
            __DIR__ . '/../Resources/assets/manifest.json' => public_path('manifest.json'),
            __DIR__ . '/../Resources/assets/sw.js' => public_path('sw.js'),
        ], 'website-pwa');

        // Publish all assets together
        $this->publishes([
            __DIR__ . '/../Resources/assets/css' => public_path('css'),
            __DIR__ . '/../Resources/assets/js' => public_path('js'),
            __DIR__ . '/../Resources/assets/fonts' => public_path('fonts'),
            __DIR__ . '/../Resources/assets/images' => public_path('images'),
            __DIR__ . '/../Resources/assets/manifest.json' => public_path('manifest.json'),
            __DIR__ . '/../Resources/assets/sw.js' => public_path('sw.js'),
        ], 'website-all');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
