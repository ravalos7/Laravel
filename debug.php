<?php

namespace App\Helpers;


use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\DebugModel;
use Illuminate\Support\Facades\Log;

/**
 * DEBUG HELPERS---------------------
 */

class Debug
{
    public $title;
    public $stop;
    public $json;
    public $show_errors;
    private $variable;
    public $logfile_type;
    private $logdb_dateformat;
    public $model;

    public function __construct($title = "Log Variable", $json = false, $stop = false, $show_errors = true, $logfile_type = "info")
    {

        //--------------------------------------------------------------
        //  Definir formato DateTime según base de datos utilizada
        //--------------------------------------------------------------
                $this->logdb_dateformat = "d-m-Y H:i:s";
        //--------------------------------------------------------------
        //--------------------------------------------------------------

        $this->model = app("App\Models\DebugModel");
        $this->variable = "";
        $this->title = $title;
        $this->json = $json;
        $this->stop = $stop;
        $this->show_errors = $show_errors;
        $this->logfile_type = $logfile_type;

        if ($this->show_errors)
            self::show_errors();
    }

    public function install()
    {
        if (!Schema::hasTable('debug_log')) {
            Schema::create('debug_log', function ($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->dateTime('fecha');
                $table->string('titulo', 200)->nullable();
                $table->string('tipo', 50)->nullable();
                $table->string('variable', 50)->nullable();
                $table->text('valor');
                //    $table->timestamps();
            });
        }
    }

    public function log_bd()
    {
        $args = func_get_args();

        foreach ($args as $arg) {

            $this->variable = $arg;
            if (gettype($this->variable) == "array" || gettype($this->variable) == "object") {
                //$this->variable = json_encode($this->variable, JSON_PRETTY_PRINT);

                foreach ($this->variable as $key => $value) {

                    if (gettype($value) == "array" || gettype($value) == "object") {

                        foreach ($value as $ind => $elem) {
                            $fecha = $this->get_date();

                            if (gettype($elem) !== "array" && gettype($elem) !== "object") {

                                $this->model->crear_log([
                                    "fecha" => $fecha,
                                    "titulo" => "{$this->title} | {$key}",
                                    "tipo" => gettype($elem),
                                    "variable" => $key,
                                    "valor" => '[ ' . $ind . ' ]' . " = {$elem}"
                                ]);
                            } else {

                                $tipo = gettype($elem);

                                if ($this->json) {
                                    $elem = json_encode($elem);
                                    $this->model->crear_log([
                                        "fecha" => $fecha,
                                        "titulo" => "{$this->title} | {$key} | [{$ind}]",
                                        "tipo" => gettype($elem),
                                        "variable" => $key,
                                        "valor" => "{$elem}"
                                    ]);
                                } else {
                                    foreach ($elem as $c => $content) {

                                        if (gettype($content) !== "array" && gettype($content) !== "object") {
                                            $elem = json_encode($elem);
                                        }

                                        $this->model->crear_log([
                                            "fecha" => $fecha,
                                            "titulo" => "{$this->title} | {$key} | [{$ind}]",
                                            "tipo" => gettype($content),
                                            "variable" => $key,
                                            "valor" => '[ ' . $c . ' ]' . " = {$content}"
                                        ]);
                                    }
                                }
                            }
                        }
                    } else {
                        $fecha = $this->get_date();
                        $tipo = gettype($value);

                        if ($this->json)
                            $value = json_encode($value);

                        $this->model->crear_log([
                            "fecha" => $fecha,
                            "titulo" => $this->title,
                            "tipo" => $tipo,
                            "variable" => $key,
                            //"valor" => "{$key} = {$value}"
                            "valor" => '[ ' . $key . ' ]' . " = {$value}"
                        ]);
                    }
                }
            } else {

                $fecha = $this->get_date();
                $tipo = gettype($this->variable);

                if ($this->json)
                    $this->variable = json_encode($this->variable);

                $this->model->crear_log([
                    "fecha" => $fecha,
                    "tipo" => $tipo,
                    "titulo" => $this->title,
                    "variable" => "-",
                    "valor" => $this->variable,
                ]);
            }
        }
        if ($this->stop)
            die;
    }

    public function log_db()
    {
        self::log_bd();
    }

    public function logbd_clean()
    {
        self::logdb_clean();
    }
    public function logdb_clean()
    {
        $this->model->limpiar_log();
    }

    public function log_file()
    {
        /* 
        Log::emergency($message);
        Log::alert($message);
        Log::critical($message);
        Log::error($message);
        Log::warning($message);
        Log::notice($message);
        Log::info($message);
        Log::debug($message);
       */

        $this->variable = func_get_args();

        if (gettype($this->variable) == "array" || gettype($this->variable) == "object") {
            $this->variable = json_encode($this->variable, JSON_PRETTY_PRINT);
        }

        $header = $this->create_variable_header();

        $channel = Log::channel('stack');

        switch ($this->logfile_type) {
            case "emergency":
                $channel->emergency($header . $this->variable);
                break;
            case "alert":
                $channel->alert($header . $this->variable);
                break;
            case "critical":
                $channel->critical($header . $this->variable);
                break;
            case "error":
                $channel->error($header . $this->variable);
                break;
            case "warning":
                $channel->warning($header . $this->variable);
                break;
            case "notice":
                $channel->notice($header . $this->variable);
                break;
            case "info":
                $channel->info($header . $this->variable);
                break;
            case "debug":
                $channel->debug($header . $this->variable);
                break;
        }

        if ($this->stop) {
            die;
        }
    }

    public function log_request(Request $request, $log_type = "dd")
    {
        $this->variable = $request->all();

        switch ($log_type) {
            case "bd":
            case "db":
                self::logbd_clean();
                self::log_bd();
                break;
            case "file":
                self::log_file();
                break;

            case "dd":
                self::dd();
                break;
        }
    }

    public function dd()
    {
        $this->variable = func_get_args();

        if (function_exists('dd')) {

            $encabezado = $this->create_variable_header();
            dd($encabezado, $this->variable);
        } else {

            if (gettype($this->variable) == "array" || gettype($this->variable) == "object") {
                $this->variable = json_encode($this->variable, JSON_PRETTY_PRINT);
            }

            $this->variable = $this->create_variable_log();

            echo ('<pre>');
            print_r($this->variable);
            echo ('</pre>');
        }

        if ($this->stop) {
            die;
        }
    }
    private function create_variable_log()
    {
        $fecha = $this->get_date();
        $tipo = $this->var_type();
        $title = "---------------------\n
                  Fecha:{$fecha} ---- {$this->title} \n;
                  Tipo: {$tipo} \n";
        $sep = "\n\n-------------------------------------------------------------------------\n\n\n\n";
        return $title . $this->variable . $sep;
    }
    private function create_variable_header()
    {
        $fecha = $this->get_date();
        $tipo = $this->var_type();
        $header = "
-------------------------------------------------------------------------
- Fecha:  {$fecha}
- Título: {$this->title}
- Tipo:   {$tipo}
-------------------------------------------------------------------------
        ";
        return $header;
    }

    private function var_type()
    {
        return gettype($this->variable);
    }

    public function show_errors()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
    private function get_date()
    {
        return date($this->logdb_dateformat);
        
        // return date("Y-m-d H:i:s");
    }
}
