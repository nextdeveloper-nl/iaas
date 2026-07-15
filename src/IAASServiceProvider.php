<?php

namespace NextDeveloper\IAAS;

use NextDeveloper\Commons\AbstractServiceProvider;
use NextDeveloper\IAAS\Http\Middlewares\CheckEligibility;
use NextDeveloper\IAAS\Http\Middlewares\CheckIaasAccount;
use NextDeveloper\IAAS\Http\Middlewares\CheckSuspension;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;

/**
 * Class IAASServiceProvider
 *
 * @package NextDeveloper\IAAS
 */
class IAASServiceProvider extends AbstractServiceProvider {
    /**
     * @var bool
     */
    protected $defer = false;

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function boot() {
        $this->publishes([
            __DIR__.'/../config/iaas.php' => config_path('iaas.php'),
            __DIR__.'/../config/virtualization.php' => config_path('virtualization.php'),
        ], 'config');

        $this->loadViewsFrom($this->dir.'/../resources/views', 'IAAS');

//        $this->bootErrorHandler();
        $this->bootChannelRoutes();
        $this->bootModelBindings();
        $this->bootLogger();
    }

    /**
     * @return void
     */
    public function register() {
        $this->registerHelpers();
        $this->registerMiddlewares('generator');
        $this->registerRoutes();
        $this->registerCommands();

        $this->mergeConfigFrom(__DIR__.'/../config/iaas.php', 'iaas');
        $this->mergeConfigFrom(__DIR__.'/../config/virtualization.php', 'virtualization');
        $this->customMergeConfigFrom(__DIR__.'/../config/relation.php', 'relation');

        $this->registerHypervisorDrivers();
    }

    /**
     * @return void
     */
    public function bootLogger() {
//        $monolog = Log::getMonolog();
//        $monolog->pushProcessor(new \Monolog\Processor\WebProcessor());
//        $monolog->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor());
//        $monolog->pushProcessor(new \Monolog\Processor\MemoryPeakUsageProcessor());
    }

    /**
     * @return array
     */
    public function provides() {
        return ['generator'];
    }

//    public function bootErrorHandler() {
//        $this->app->singleton(
//            ExceptionHandler::class,
//            Handler::class
//        );
//    }

    /**
     * @return void
     */
    private function bootChannelRoutes() {
        if (file_exists(($file = $this->dir.'/../config/channel.routes.php'))) {
            require_once $file;
        }
    }

    /**
     * Register module routes
     *
     * @return void
     */
    protected function registerRoutes() {
        if ( ! $this->app->routesAreCached() && config('leo.allowed_routes.iaas', true) ) {
            $this->app['router']
                ->middleware([
                    CheckSuspension::class,
                    CheckIaasAccount::class,
                    CheckEligibility::class
                ])
                ->namespace('NextDeveloper\IAAS\Http\Controllers')
                ->group(__DIR__.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'api.routes.php');
        }
    }

    /**
     * Registers module based commands
     * @return void
     */
    protected function registerCommands() {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \NextDeveloper\IAAS\Console\Commands\RemoveLostServers::class,
                \NextDeveloper\IAAS\Console\Commands\RemoveDraftServers::class,
                \NextDeveloper\IAAS\Console\Commands\SyncCloudNode::class,
                \NextDeveloper\IAAS\Console\Commands\TransferVirtualMachine::class,
                \NextDeveloper\IAAS\Console\Commands\ComputeMemberServiceCheck::class,
                \NextDeveloper\IAAS\Console\Commands\SyncVirtualMachine::class,
                \NextDeveloper\IAAS\Console\Commands\SyncStorageVolume::class,
                \NextDeveloper\IAAS\Console\Commands\MigrateVirtualMachine::class,
                \NextDeveloper\IAAS\Console\Commands\MigrateLocalVirtualMachine::class,
                \NextDeveloper\IAAS\Console\Commands\ListenVmAgentEvents::class,
                \NextDeveloper\IAAS\Console\Commands\UpdateConfigurationIso::class,
                \NextDeveloper\IAAS\Console\Commands\StageToolkitForDocker::class,
                \NextDeveloper\IAAS\Console\Commands\SyncServiceRolesCatalog::class,
                \NextDeveloper\IAAS\Console\Commands\DetectIpCollisions::class,
            ]);
        }
    }

    /**
     * This is here, in case of shit happens!
     * @return void
     */
    private function checkDatabaseConnection() {
        $isSuccessfull = false;

        try {
            \DB::connection()->getPdo();

            $isSuccessfull = true;
        } catch (\Exception $e) {
            die('Could not connect to the database. Please check your configuration. error:'.$e);
        }

        return $isSuccessfull;
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    /**
     * Binds VirtualMachineManager as a singleton and registers every driver listed in
     * config/virtualization.php against it, so every caller resolving the manager gets
     * the same, fully-registered registry. Also aliases it as the 'vm.manager' facade
     * accessor for NextDeveloper\IAAS\Facades\VM.
     */
    private function registerHypervisorDrivers() {
        $this->app->singleton(VirtualMachineManager::class, function () {
            $manager = new VirtualMachineManager();

            foreach (config('virtualization.platforms', []) as $platform => $platformConfig) {
                $manager->registerAdapter($platform, $platformConfig['driver']);
            }

            return $manager;
        });

        $this->app->alias(VirtualMachineManager::class, 'vm.manager');
    }
}
