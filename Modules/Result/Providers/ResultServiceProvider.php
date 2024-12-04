<?php

namespace Modules\Result\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Result\Console\InstallApp;
use Modules\Result\Console\SeedStudents;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Modules\Result\Console\ResultCleanup;
use Modules\Result\Console\SeedApp;
use Modules\Result\Console\SeedStaffs;
use Modules\Result\Console\Setup;
use Modules\Result\Http\Middleware\ResultMiddleware;

class ResultServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Result';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'result';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $router = $this->app['router'];
        // $router->pushMiddlewareToGroup('web', ResultMiddleware::class);

        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerHelpers();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        if ($this->app->runningInConsole()) {
            $this->commands([Setup::class, InstallApp::class, ResultCleanup::class, SeedApp::class]);
        }

        // $this->app->booted(function () {
        //     $schedule = app(Schedule::class);
        //     $schedule->command('result:cleanup')->daily();
        // });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
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
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
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
        if (File::exists(module_path($this->moduleName, 'Helpers/helper.php'))) {
            require_once module_path($this->moduleName, 'Helpers/helper.php');
        }
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
