<?php

namespace Hungnm28\LaravelModuleAdmin\Commands;

use Hungnm28\LaravelModuleAdmin\Traits\CommandTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModelCommand extends Command
{

    use CommandTrait;

    protected $signature = 'lma:model {name} {--force}';

    protected $description = 'Make model page';


    public function handle()
    {
        $this->generateModel($this->argument("name"));
        $this->createModel();

    }

    protected function createModel()
    {
        $dumpFields = "";
        $dumCastClass = [];
        $dumCasts = "";
        $dumpListFields = "\"id\"";
        foreach ($this->fields as $f => $row) {
            $dumpFields .= "\"$f\", ";
            $dumpListFields .= ", \"$f\"";
            switch ($row->type) {
                case "json":
                case "array":
                case "object":
                    $dumCastClass["use App\Casts\JsonCast"] = "use App\Casts\JsonCast;";
                    $dumCasts .= "\"$f\" => JsonCast::class,\r\n\t\t";
                    break;
                case "image":
                case "text":
                case "url":
                    $dumCastClass["use App\Casts\StringCast"] = "use App\Casts\StringCast;";
                    $dumCasts .= "\"$f\" => StringCast::class,\r\n\t\t";
                    break;
                case "email":
                    $dumCastClass["use App\Casts\EmailCast"] = "use App\Casts\EmailCast;";
                    $dumCasts .= "\"$f\" => EmailCast::class,\r\n\t\t";
                    break;
                case "textarea":
                    $dumCastClass["use App\Casts\HtmlCast"] = "use App\Casts\HtmlCast;";
                    $dumCasts .= "\"$f\" => HtmlCast::class,\r\n\t\t";
                    break;
                case "slug":
                    $dumCastClass["use App\Casts\SlugCast"] = "use App\Casts\SlugCast;";
                    $dumCasts .= "\"$f\" => SlugCast::class,\r\n\t\t";
                    break;
                case "number":
                    $dumCastClass["use App\Casts\IntegerCast"] = "use App\Casts\IntegerCast;";
                    $dumCasts .= "\"$f\" => IntegerCast::class,\r\n\t\t";
                    break;
                case "boolean":
                    $dumCastClass["use App\Casts\BooleanCast"] = "use App\Casts\BooleanCast;";
                    $dumCasts .= "\"$f\" => BooleanCast::class,\r\n\t\t";
                    break;
                default:
                    $dumCasts .= "\"$f\" => \"$row->type\",\r\n\t\t";
            }
        }
        $dumpFields = trim($dumpFields, ", ");
        $dumCasts = trim($dumCasts, ",");
        $dumpListFields .= ", \"updated_at\", \"created_at\"";
        $stub = $this->getStub("model.stub");
        $template = str_replace(
            [
                'DumMyCastsClass',
                'DumMyClassName',
                'DumMyFillable',
                'DumMyListFields',
                'DumMyCasts'
            ],
            [
                implode("\n", $dumCastClass),
                $this->argument("name"),
                $dumpFields,
                $dumpListFields,
                $dumCasts
            ],
            $stub
        );
        $pathSave = app_path("Models/" . $this->argument("name") . ".php");
        // Backup model
        $pathBackup =  app_path("Models/backups/" . $this->argument("name") . date("_Y_m_d_H_i_s",time()) . ".backup");
        $this->info("Backup old model to $pathBackup");
        $this->ensureDirectoryExists($pathBackup);
        File::put($pathBackup,file_get_contents($pathSave));
        return File::put($pathSave, $template);

    }
}
