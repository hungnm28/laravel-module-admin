<?php

namespace Hungnm28\LaravelModuleAdmin\Commands;

use App\Models\User;
use Hungnm28\LaravelModuleAdmin\Traits\CommandTrait;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    use CommandTrait;

    protected $signature = 'lma:install {--force}';

    protected $description = 'Make admin page';

    protected $module;
    protected $moduleName;

    public function handle()
    {
        $this->info('lma:install command');
        $this->info('Install admin to module: ' . config("lma.module.name"));
        $comfirm = $this->ask('Please enter Y to continue, any key to quit');
        if (strtolower($comfirm) != "y") {
            return false;
        }
        $this->moduleName = config("lma.module.name");
        if (!$this->initModule()) {
            $this->error("Module not found: $this->moduleName");
        }
        $this->copyAssets();
        $this->copyProvider();
        $this->copyView();
        $this->installAuthModule('Auth', 'Index');

        $this->installAuthModule('Auth/AdminMenus', 'Create');
        $this->installAuthModule('Auth/AdminMenus', 'Edit');
        $this->installAuthModule('Auth/AdminMenus', 'Show');
        $this->installAuthModule('Auth/AdminMenus', 'Listing');

        $this->installAuthModule('Auth/Admins', 'Create');
        $this->installAuthModule('Auth/Admins', 'Edit');
        $this->installAuthModule('Auth/Admins', 'Show');
        $this->installAuthModule('Auth/Admins', 'Listing');

        $this->installAuthModule('Auth/Permissions', 'Create');
        $this->installAuthModule('Auth/Permissions', 'CreateGroup');
        $this->installAuthModule('Auth/Permissions', 'Edit');
        $this->installAuthModule('Auth/Permissions', 'Show');
        $this->installAuthModule('Auth/Permissions', 'Listing');

        $this->installAuthModule('Auth/Roles', 'Create');
        $this->installAuthModule('Auth/Roles', 'Edit');
        $this->installAuthModule('Auth/Roles', 'Show');
        $this->installAuthModule('Auth/Roles', 'Listing');

        $this->publishLayout();
        $this->publishModuleProvider();
        $this->publishRoute();

        // make default permission
        $this->call('lma:add-permission', ['name' => "admin-menu"]);
        $this->call('lma:add-permission', ['name' => "admins"]);
        $this->call('lma:add-permission', ['name' => "roles"]);
        $this->call('lma:add-permission', ['name' => "permissions"]);
        $this->call('db:seed', ['--class' => "AdminMenuSeeder"]);

    }

    protected function initModule()
    {
        $module = $this->laravel['modules']->find($this->moduleName);
        if ($module) {
            $this->module = $module;
            return true;
        }
        return false;
    }

    protected function copyAssets()
    {
        $this->line("Copy Assets: ");
        (new Filesystem)->copyDirectory(__DIR__ . '/../../publishes/resources/assets', module_path($this->moduleName, 'Resources/assets'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../publishes/mix', module_path($this->moduleName));

    }

    protected function copyView()
    {
        $this->line("Copy Auth view: ");
        (new Filesystem)->copyDirectory(__DIR__ . '/../../publishes/resources/views/livewire/auth', module_path($this->moduleName, 'Resources/views/livewire/auth'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../publishes/resources/views/components', module_path($this->moduleName, 'Resources/views/components'));

    }

    protected function copyProvider()
    {
        copy(__DIR__ . '/../../publishes/app/Providers/PermissionsServiceProvider.php', app_path('Providers/PermissionsServiceProvider.php'));
        $this->installServiceProviderAfter('JetstreamServiceProvider', 'PermissionsServiceProvider');
    }

    protected function installServiceProviderAfter($after, $name)
    {
        if (!Str::contains($appConfig = file_get_contents(config_path('app.php')), 'App\\Providers\\' . $name . '::class')) {
            file_put_contents(config_path('app.php'), str_replace(
                'App\\Providers\\' . $after . '::class,',
                'App\\Providers\\' . $after . '::class,' . PHP_EOL . '        App\\Providers\\' . $name . '::class,',
                $appConfig
            ));
        }
    }

    protected function installPackageJson()
    {

    }

    protected function installAuthModule($name, $method)
    {
        $folder = "$name";
        $this->line("install $folder $method Class");
        $this->folder = Str::studly($folder);
        $stub = $this->getStub("$name/$method.stub");
        $pathSave = $this->class_path(Str::studly($method) . ".php");
        if(!$pathSave){
            return false;
        }
        $stub = str_replace([
            'DumMyNamespace',
            'DumMyComponent'
        ],
            [
                config("lma.module.namespace"),
                config("lma.module.component")

            ], $stub);
        File::put($pathSave, $stub);
    }

    protected function publishRoute()
    {
        $this->line("publish Route");

        $stub = $this->getStub("install-route.stub");
        $pathSave = module_path(config('lma.module.name'), "Routes/web.php");
        $stub = str_replace([
            'DumMyModulePrefix',
            'DumMyRouteName',
            'DumMyHomeController',
            'DumMyNamespace'
        ],
            [
                config("lma.module.prefix"),
                config("lma.module.route"),
                Str::studly(config('lma.module.name')) . "Controller",
                config("lma.module.namespace")

            ], $stub);
        File::put($pathSave, $stub);
    }

    protected function publishLayout()
    {
        $this->line("publish Layout");
        $stub = $this->getStub("layout-class.stub");
        $pathSave = module_path(config('lma.module.name'), "Views/Components/LayoutMaster.php");
        $this->ensureDirectoryExists($pathSave);
        $stub = str_replace([
            'DumMyNamespace',
            'DumMyComponent',
        ],
            [
                config("lma.module.namespace"),
                config("lma.module.component"),

            ], $stub);
        File::put($pathSave, $stub);

        $this->line("publish Layout");
        $stub = $this->getStub("Navbar.stub");
        $pathSave = module_path(config('lma.module.name'), "Views/Components/Navbar.php");
        $this->ensureDirectoryExists($pathSave);
        $stub = str_replace([
            'DumMyNamespace',
            'DumMyComponent',
        ],
            [
                config("lma.module.namespace"),
                config("lma.module.component"),

            ], $stub);
        File::put($pathSave, $stub);

        $stub = $this->getStub("layout-view.stub");
        $pathSave = module_path(config('lma.module.name'), "Resources/views/layouts/master.blade.php");
        $stub = str_replace([
            'DumMyComponent',
        ],
            [
                config("lma.module.component"),

            ], $stub);
        File::put($pathSave, $stub);
        $stub = $this->getStub("home-view.stub");
        $pathSave = module_path(config('lma.module.name'), "Resources/views/index.blade.php");
        $stub = str_replace([
            'DumMyComponent',
        ],
            [
                config("lma.module.component"),

            ], $stub);
        File::put($pathSave, $stub);
    }

    protected function createAdminusers()
    {
        $listSuper = explode(",", env('APP_SUPER_ADMIN'));
        $users = User::find($listSuper);
        if ($users) {
            $users->map(function ($user) {
                $user->update(["is_admin" => 1]);
            });
        } else {
            $this->line("Create new admin");
        }

    }

    protected function publishModuleProvider()
    {
        $moduleName = Str::studly(config("lma.module.name"));
        $stub = $this->getStub("ModuleServiceProvider.stub");
        $pathSave = module_path(config('lma.module.name'), "Providers/" . $moduleName . "ServiceProvider.php");
        $stub = str_replace([
            'DumpMyNamespace',
            'DumMyModuleName',
            'DumMyModuleLower',
            'DumMyComponent',
        ],
            [
                config("lma.module.namespace"),
                $moduleName,
                config("lma.module.name"),
                config("lma.module.component"),

            ], $stub);
        File::put($pathSave, $stub);

        $stub = $this->getStub("RouteServiceProvider.stub");
        $pathSave = module_path(config('lma.module.name'), "Providers/RouteServiceProvider.php");
        $stub = str_replace([
            'DumMyNamespace',
            'DumMyModuleName',
        ],
            [
                config("lma.module.namespace"),
                $moduleName,

            ], $stub);
        File::put($pathSave, $stub);
    }
}
