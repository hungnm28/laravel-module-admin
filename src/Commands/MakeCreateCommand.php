<?php

namespace Hungnm28\LaravelModuleAdmin\Commands;

use Hungnm28\LaravelModuleAdmin\Traits\CommandTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCreateCommand extends Command
{
    use CommandTrait;

    protected $signature = 'lma:make-create {name} {--model=} {--force}';

    protected $description = 'Make create';

    public function handle()
    {
        $name = $this->argument('name');
        $name = Str::studly($name);
        $modelName = $this->option("model");
        $this->folder = $name;
        if (!$modelName) {
            $modelName = Str::singular($name);
            $modelName = Str::studly($modelName);
        }
        $this->model = $modelName;

        $this->generateModel($modelName);
        $this->makeClass();
        $this->makeView();
    }

    private function makeClass()
    {
        $pathSave = $this->class_path("Create.php");
        if(!$pathSave){
            return false;
        }
        $stub = $this->getStub('create-class.stub');
        $dumpFields = "";
        $dumpRules = "";
        $dumpFormField = "";
        foreach ($this->fields as $f => $row) {
            $dumpFields .= $this->generateField($row);
            $dumpRules .= "'$f' => '$row->rule', \r\n\t\t";
            $dumpFormField .=  $this->generateFormField($row) . "\r\n\t\t\t";
        }
        $dumpFields = rtrim($dumpFields, ", ");
        $route = config('lma.module.route') . "." . $this->getFonderDot();
        $stub = str_replace([
            'DumMyNamespace',
            'DumMyModelClass',
            'DumMyListFields',
            'DumMyRules',
            'DumMyModelName',
            'DumMyFormFields',
            'DumMyRoute',
            'DumMyViewFolder',
            'DumMyTitle'
        ], [
            $this->getNamespace(),
            "App\Models\\$this->model",
            $dumpFields,
            $dumpRules,
            $this->model,
            $dumpFormField,
            $route,
            $this->getFonderDot(),
            Str::headline(Str::replace("/"," ",$this->folder))
        ], $stub);


        return File::put($pathSave, $stub);
    }

    private function makeView()
    {
        $stub = $this->getStub("create-view.stub");
        $pathSave = $this->view_path("create.blade.php");
        if(!$pathSave){
            return false;
        }
        $route = config('lma.module.route') . "." . $this->getFonderDot();
        $content = '';
        foreach ($this->fields as $row) {
            $content .= $this->generateView($row) . "\r\n\t\t";
        }
        $stub = str_replace([
            'DumMyTitle',
            'DumMyRoute',
            'DumMyContent',
        ],
            [
                Str::headline($this->folder),
                $route,
                $content
            ], $stub);

        return File::put($pathSave, $stub);
    }

    private function generateFormField($item){
        switch ($item->type) {
            case "json":
            case "array":
            case "object":
            return  "'$item->name' => " . '$this->getArrayParams(\'' . $item->name . "'),";
            default:
                return  "'$item->name' => " . '$this->' . $item->name . ",";
        }
    }

    private function generateField($item)
    {
        switch ($item->type) {
            case "boolean":
                return '$' . $item->name . '=' . $item->default . ', ';
            case "json":
            case "array":
            case "object":
                return "$" . $item->name . "= [], ";
            case "text":
            case "textarea":
            case "number":
            case "decimal":
                if ($item->default) {
                    return '$' . $item->name . '=' . $item->default . ', ';
                }
            default:
                return "$" . $item->name . ", ";
        }
    }

    private function generateView($item)
    {
        switch ($item->type) {
            case 'textarea':
                return '<x-lma.form.textarea name="' . $item->name . '" label="' . $item->label . '" />';
            case 'boolean':
                return '<x-lma.form.toggle name="' . $item->name . '" label="' . $item->label . '" />';
            case 'slug':
                return '<x-lma.form.input mode=".debounce.900ms" type="text" name="' . $item->name . '" label="' . $item->label . '" />';
            case 'json':
            case 'array':
            case 'object':
                return '<x-lma.form.tags  name="' . $item->name . '" label="' . $item->label . '" :params="$' . $item->name . '"/>';
            default:
                return '<x-lma.form.input type="' . $item->type . '" name="' . $item->name . '" label="' . $item->label . '" />';
        }
    }
}
