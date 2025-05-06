<?php

namespace App\Models;

use Ramsey\Uuid\UuidFactory;
use App\Models\Database;
use Slim\Psr7\Response;

class Categoria extends Database
{

    private $titulo;
    public $response = [];

    public function __construct($titulo = '')
    {
        $this->titulo = $titulo;
    }

    private static function validarTitulo($titulo)
    {
        if (empty($titulo)) {
            return true;
        }
    }

    public function existeCategoria($titulo, $user_uuid)
    {
        $sql = "SELECT COUNT(*) as total FROM categoria_mensaje WHERE titulo = ? AND user_uuid = ?";
        $consulta = $this->ejecutarConsulta($sql, [$titulo, $user_uuid]);
        $resultado = $consulta->fetch(\PDO::FETCH_ASSOC);
        return $resultado['total'];
    }

    public function crearCategoria($rol, $user_uuid)
    {
        if ($rol !== 'Admin' && $rol !== 'Editor') {
            return "No estas autorizado para esta accion 1";
        }
        if (self::validarTitulo($this->titulo)) {
            $response['status'] = 'error';
            $response['message'] = 'El titulo no puede estar vacio.';
            return $response;
        } else if(self::existeCategoria($this->titulo, $user_uuid)){
            $response['status'] = 'error';
            $response['message'] = 'la categoria ya existe.';
            return $response;
        }

        #GENERANDO UN UUID UNICO PARA EL PERFIL
        $uuidFactory = new UuidFactory();
        $uuid = $uuidFactory->uuid4();
        $categoria_uuid = $uuid->toString();

        $sql = 'INSERT INTO categoria_mensaje (uuid, titulo, user_uuid) VALUES (?, ?, ?)';
        $consulta = $this->ejecutarConsulta($sql, [$categoria_uuid, $this->titulo, $user_uuid]);
        if ($consulta) {
            $response['status'] = "ok";
            $response['message'] = "Categoria creada exitosamente.";
            return $response;
        }
    }

    public function obtenerCategorias($rol)
    {
        if ($rol !== 'Admin' && $rol !== 'Editor') {
            return "No estas autorizado para esta accion";
        }
        $sql = 'SELECT uuid, titulo, user_uuid, fecha FROM categoria_mensaje ORDER BY fecha DESC';
        $consulta = $this->ejecutarConsulta($sql);
        $list = $consulta->fetchAll(\PDO::FETCH_ASSOC);
        return $list;
    }
}
