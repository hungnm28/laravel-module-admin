<?php

namespace Hungnm28\LaravelModuleAdmin\Commands;

use Hungnm28\LaravelModuleAdmin\Traits\CommandTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeListCommand extends Command
{
    use CommandTrait;

    protected $signature = 'lma:make-list {name} {--model=} {--force} {--parent=}';

    protected $description = 'Make list';

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
        $pathSave = $this->class_path("Listing.php");
        $stub = $this->getStub('listing-class.stub');
        if(!$pathSave){
            return false;
        }
        $dumpFields = "";
        $dumToggleField = "";
        foreach ($this->fields as $f => $v) {
            $dumpFields .= "'$f'" . ' => true,' . "\r\n\t\t";
            if ($v->type == 'boolean') {
                $dumToggleField .= "\"$f\", ";
            }
        }
        $dumToggleField = trim($dumToggleField, ', ');
        $stub = str_replace([
            'DumMyNamespace',
            'DumMyModelClass',
            'DumMyFields',
            'DumMyModelName',
            'DumMyPermission',
            'DumMyToggleField',
            'DumMyViewFolder',
            'DumMyTitle'
        ], [
            $this->getNamespace(),
            "App\Models\\$this->model",
            $dumpFields,
            $this->model,
            $this->getPermissionName(),
            $dumToggleField,
            $this->getFonderDot(),
            Str::headline(Str::replace("/", " ", $this->folder))
        ], $stub);


        return File::put($pathSave, $stub);
    }

    private function makeView()
    {
        $stub = $this->getStub("listing-view.stub");
        $pathSave = $this->view_path("listing.blade.php");
        if(!$pathSave){
            return false;
        }
        $route = config('lma.module.route') . "." . $this->getPermissionName();
        $tHead = "";
        $content = "";
        foreach ($this->fields as $row) {
            $tHead .= '@if(data_get($fields,\'' . $row->name . '\'))<th>' . $row->label . '</th> @endif' . "\r\n\t\t\t\t\t";
            $content .= $this->generateView($row) . "\r\n\t\t\t\t\t";
        }
        $stub = str_replace([
            'DumMyTitle',
            'DumMyRoute',
            'DumMyPermission',
            'DumMyThead',
            'DumMyContent'
        ],
            [
                Str::headline($this->folder),
                $route,
                $this->getPermissionName(),
                $tHead,
                $content
            ], $stub);

        return File::put($pathSave, $stub);
    }

    protected function generateView($item)
    {
        switch ($item->type) {
            case "boolean":
                return '@if(data_get($fields,\'' . $item->name . '\'))<td> <x-lma.btn.toggle :val="$row->' . $item->name . '" wire:change="toggleField({{$row->id}},\'' . $item->name . '\')" /></td>@endif ';
            case "json":
            case "array":
            case "object":
                return '@if(data_get($fields,\'' . $item->name . '\'))<td><x-lma.label.tags :params="$row->' . $item->name . '"/></td>@endif';
            default:
                return '@if(data_get($fields,\'' . $item->name . '\'))<td>{{$row->' . $item->name . '}}</td> @endif ';
        }
    }

}
