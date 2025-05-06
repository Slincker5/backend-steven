<?php

namespace App\Models;

use Ramsey\Uuid\UuidFactory;
use App\Models\Database;

class Categoria extends Database
{

    private $titulo;
    public $response = [];

    public function __construct($titulo)
    {
        $titulo = $this->titulo;
    }

    private function validarTitulo($title)
    {
        if (empty($title)) {
            return true;
        }
        return false;
    }

    public function crearCategoria($rol)
    {
        if ($rol !== 'Admin') {
            return "No estas autorizado para esta accion";
        } else {
            if ($this->validarTitulo($this->titulo)) {
                $response['status'] = 'error';
                $response['message'] = 'El titulo no puede estar vacio.';
                $response['data'] = $this->titulo;
                return $response;
            }

            #GENERANDO UN UUID UNICO PARA EL PERFIL
            $uuidFactory = new UuidFactory();
            $uuid = $uuidFactory->uuid4();
            $categoria_uuid = $uuid->toString();

            $sql = 'INSERT INTO categoria_mensaje (uuid, titulo) VALUES (?, ?)';
            $consulta = $this->ejecutarConsulta($sql, [$categoria_uuid, $this->titulo]);
            if (!$consulta) {
                return "Error al realizar peticion";
            }
        }
    }
}
