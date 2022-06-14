<?php

namespace Hungnm28\LaravelModuleAdmin\Commands;

use Hungnm28\LaravelModuleAdmin\Traits\CommandTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Str;


class MakeRouteCommand extends Command
{
    use CommandTrait;

    protected $signature = 'lma:make-route {name} {--force}  {--parent=}';

    protected $description = 'Make Admin Route';

    public function handle()
    {


        $this->info($this->description);
        $this->folder = $this->argument('name');
        $this->showRoute();
    }

    public function showRoute()
    {

        $stub = $this->getStub('route.stub');
        $template = str_replace(
            [
                'DumMyModule',
                'DumMyNamespace'
                ,'DumMyPermission'
                ,'DumMyTag'
            ],
            [
                $this->getFonderDot(),
                $this->getNamespace()
                , $this->getPermissionName()
                ,'//** Add To ' . strtolower($this->argument("name")) . ' Routes **//'
            ],
            $stub);

        $this->comment('Add the following route to: ' . module_path(config('lma.module.name'), 'Route/web.php'));
        $this->line('');
        $this->line($template);
        $this->installRoute($template);
    }

    protected function installRoute($routes)
    {
        $flag = '//** Add New Routes **//';
        $parent = $this->option("parent");
        $parent = strtolower($parent);
        if ($parent) {
            $flag = '//** Add To ' . $parent . ' Routes **//';
        }


        if (!Str::contains($appRoutes = file_get_contents(module_path(config('lma.module.name'), 'Routes/web.php')), $routes)) {
            file_put_contents(module_path(config('lma.module.name'), 'Routes/web.php'), str_replace(
                $flag,
                $routes . PHP_EOL . $flag,
                $appRoutes
            ));
        }
    }

}
