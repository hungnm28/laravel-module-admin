<?php

namespace Hungnm28\LaravelModuleAdmin\Commands;

use Hungnm28\LaravelModuleAdmin\Traits\CommandTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeShowCommand extends Command
{
    use CommandTrait;

    protected $signature = 'lma:make-show {name} {--model=} {--force} {--parent=}';

    protected $description = 'Make show';

    public function handle()
    {
        $name = $this->argument('name');
        $name = Str::studly($name);
        $modelName = $this->option("model");
        $parent = $this->option("parent");
        if($parent){
            $this->folder = Str::studly($parent) . "/" . $name;
        }else{
            $this->folder = $name;
        }
        if (!$modelName) {
            $modelName = Str::singular($name);
            $modelName = Str::studly($modelName);
        }
        $this->model = $modelName;

        $this->generateModel($modelName);
        $this->generateComponent();

        $this->makeClass();
        $this->makeView();
    }

    private function makeClass()
    {
        $pathSave = $this->class_path("Show.php");
        $stub = $this->getStub('show-class.stub');
        if(!$pathSave){
            return false;
        }

        $stub = str_replace([
            'DumMyNamespace',
            'DumMyModelClass',
            'DumMyModelName',
            'DumMyViewFolder',
            'DumMyTitle'
        ], [
            $this->getNamespace(),
            "App\Models\\$this->model",
            $this->model,
            $this->getFonderDot(),
            Str::headline(Str::replace("/"," ",$this->folder))
        ], $stub);


        return File::put($pathSave, $stub);
    }

    private function makeView()
    {
        $stub = $this->getStub("show-view.stub");
        $pathSave = $this->view_path("show.blade.php");
        if(!$pathSave){
            return false;
        }
        $route = config('lma.module.route') . "." . $this->getPermissionName();
        $content = "";
        foreach ($this->fields as $row) {
            $content .= $this->generateView($row) . "\r\n\t\t";
        }
        $stub = str_replace([
            'DumMyTitle',
            'DumMyRoute',
            'DumMyContent'
        ],
            [
                Str::headline(Str::replace("/"," ",$this->folder)),
                $route,
                $content
            ], $stub);

        return File::put($pathSave, $stub);
    }

    protected function generateView($item){
        switch ($item->type) {
            case 'textarea':
                return '<tr><th class=" text-right border-r">'.$item->label.':</th><td >{{$data->'.$item->name.'}}</td></tr>';
            case 'boolean':

                return '<tr><th class=" text-right border-r">'.$item->label.':</th><td ><x-lma.label.toggle :val="$data->'.$item->name.'" /></td></tr>';
            case 'json':
            case 'array':
            case 'object':
                return '<tr><th class=" text-right border-r">'.$item->label.':</th><td ><x-lma.label.tags :params="$data->' . $item->name . '" /></td></tr>';
            default:
                return '<tr><th class=" text-right border-r">'.$item->label.':</th><td >{{$data->'.$item->name.'}}</td></tr>';
        }
    }
}
