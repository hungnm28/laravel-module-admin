<?php

namespace Hungnm28\LaravelModuleAdmin\Traits;

use Hungnm28\LaravelModuleAdmin\Supports\ModelGenerator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait CommandTrait
{
    private $folder, $component, $model, $modelPath, $fields = [];

    private function initModule()
    {
        $name = config("lma.module.name");
        if ($this->laravel['modules']->find($name)) {
            return true;
        }
        $this->error("Module not found: $name");
        return false;
    }

    private function generateModel($name)
    {
        $this->info("$this->description ");
        $ModelGenerator = new ModelGenerator($name);
        $this->fields = $ModelGenerator->getFields();
    }

    private function generateComponent()
    {

    }

    private function checkFilePath($path)
    {
        if (File::exists($path) && ! $this->isForce()) {
            $this->error(" WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->error("File already exists:</> $path \n ");
            return false;
            // unlink($path);
        }
        return true;
    }

    private function getStub($path)
    {
        $path = __DIR__ . "/../Commands/stubs/$path";
        if (!File::exists($path)) {
            $this->error("WHOOPS-IE-TOOTLES  😳 \n");
            $this->error("Stubs not exists: $path \n ");
            return false;
        }
        return file_get_contents($path);
    }

    private function view_path($path)
    {
        $view_folder = $this->getViewFolder();
        $path = module_path(config("lma.module.name"), "Resources/views/livewire/$view_folder/$path");
        if(!$this->checkFilePath($path)){
            return false;
        }
        $this->ensureDirectoryExists($path);
        return $path;
    }

    private function class_path($path)
    {
        $path = module_path(config("lma.module.name"), "Http/Livewire/$this->folder/$path");
        if(!$this->checkFilePath($path)){
            return false;
        }
        $this->ensureDirectoryExists($path);
        return $path;
    }

    private function ensureDirectoryExists($path)
    {
        $path = dirname($path);
        (new Filesystem)->ensureDirectoryExists($path);
    }

    private function getClassFolder()
    {
        $folder = $this->folder;

    }

    private function getViewFolder()
    {
        $arr = explode("/", $this->folder);

        foreach ($arr as $k => $a) {
            $arr[$k] = Str::snake($a, '-');
        }
        return implode("/", $arr);
    }

    private function getNamespace()
    {
        return Str::replace("/", "\\", config("lma.module.namespace") . "\Http\Livewire\\$this->folder");
    }

    private function getFonderDot($prefix="")
    {
        $str = Str::replace("/", ".", $this->getViewFolder());
        if($prefix !=""){
            $str = "$prefix.$str";
        }
        return $str;
    }

    private function getHeadline()
    {
        return Str::headline(Str::replace("/", " ", $this->folder));
    }

    private function getPermissionName($type = "")
    {
        $name = $this->getFonderDot($this->option("parent"));
        if ($type) {
            $name .= "." . Str::kebab($type);
        }
        return $name;
    }
    protected function isForce()
    {
        return $this->option('force') === true;
    }
}
