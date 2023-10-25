<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebugModel extends Model
{
    use HasFactory;

    protected $table = "debug_log";

    protected $fillable = [
        'id','fecha','titulo'
        ,'texto'
    ];

   
    function crear_log( $data)
    {
        self::insert($data);
        // return $this->db->insert_id();
    }

    function limpiar_log()
    {
        self::truncate();
    }
}
