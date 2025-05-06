<?php

namespace App\Models;

use Ramsey\Uuid\UuidFactory;
use App\Models\Database;
use Slim\Psr7\Response;

class Categoria extends Database
{

    private $titulo;
    public $response = [];

    public function __construct($titulo)
    {
        $this->titulo = $titulo;
    }

    private function validarTitulo($title)
    {
        if (empty($title)) {
            return true;
        }
    }

    public function crearCategoria($rol)
    {
        if ($rol !== 'Admin') {
            return "No estas autorizado para esta accion";
        } else {
            if ($this->validarTitulo($this->titulo)) {
                $response['status'] = 'error';
                $response['message'] = 'El titulo no puede estar vacio.';
                return $response;
            }

            #GENERANDO UN UUID UNICO PARA EL PERFIL
            $uuidFactory = new UuidFactory();
            $uuid = $uuidFactory->uuid4();
            $categoria_uuid = $uuid->toString();

            $sql = 'INSERT INTO categoria_mensaje (uuid, titulo) VALUES (?, ?)';
            $consulta = $this->ejecutarConsulta($sql, [$categoria_uuid, $this->titulo]);
            if (!$consulta) {
                $response['status'] = "ok";
                $response['message'] = "Categoria creada exitosamente.";
                return $response;
            }
        }
    }
}
