<?php

namespace Hungnm28\LaravelModuleAdmin\Commands;

use Hungnm28\LaravelModuleAdmin\Traits\CommandTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeEditCommand extends Command
{
    use CommandTrait;

    protected $signature = 'lma:make-edit {name} {--model=} {--force} {--parent=}';

    protected $description = 'Make edit';

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
        $this->makeClass();
        $this->makeView();
    }

    private function makeClass()
    {
        $pathSave = $this->class_path("Edit.php");
        $stub = $this->getStub('edit-class.stub');
        if(!$pathSave){
            return false;
        }
        $dumpFields = "";
        $dumpRules = "";
        $dumpFormField = "";
        $dumDataSet = "";
        foreach ($this->fields as $f => $row) {
            $dumpFields .= $this->generateField($row);
            $dumpRules .= "'$f' => '$row->rule', \r\n\t\t";
            $dumpFormField .=  $this->generateFormField($row) . "\r\n\t\t\t";
            $dumDataSet .= '$this->'.$f. ' = $data->'.$f . "; \r\n\t\t";
        }
        $dumpFields = rtrim($dumpFields, ", ");
        $route = config('lma.module.route') . "." . $this->getPermissionName();
        $stub = str_replace([
            'DumMyNamespace',
            'DumMyModelClass',
            'DumMyListFields',
            'DumMyRules',
            'DumMySetData',
            'DumMyModelName',
            'DumMyFormFields',
            'DumMyRoute',
            'DumMyViewFolder',
            'DumMyPermission',
            'DumMyTitle'
        ], [
            $this->getNamespace(),
            "App\Models\\$this->model",
            $dumpFields,
            $dumpRules,
            $dumDataSet,
            $this->model,
            $dumpFormField,
            $route,
            $this->getFonderDot(),
            $this->getPermissionName("edit"),
            Str::headline(Str::replace("/"," ",$this->folder))
        ], $stub);


        return File::put($pathSave, $stub);
    }

    private function makeView()
    {
        $stub = $this->getStub("edit-view.stub");
        $pathSave = $this->view_path("edit.blade.php");
        if(!$pathSave){
            return false;
        }
        $route = config('lma.module.route') . "." . $this->getPermissionName();
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
            case "image":
                return "$" . $item->name . ", $" . $item->name . "_field, $" . $item->name . "_url, ";
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
            case 'image':
                return '<x-lma.form.image name="' . $item->name . '_field" label="' . $item->label . '" :url="$'.$item->name.'_url" />';
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
