<?php

namespace App\Models;

use Ramsey\Uuid\UuidFactory;
use App\Models\Database;
use Slim\Psr7\Response;

class Mensaje extends Database
{

    private $mensaje;
    public $response = [];

    public function __construct($mensaje = '')
    {
        $this->mensaje = $mensaje;
    }

    public function contarCategorias()
    {
        $sql = "SELECT COUNT(*) as total FROM categoria_mensaje";
        $consulta = $this->ejecutarConsulta($sql);
        $resultado = $consulta->fetch(\PDO::FETCH_ASSOC);
        return $resultado['total'];
    }


    private static function validarmensaje($mensaje)
    {
        if (empty($mensaje)) {
            return true;
        }
    }

    public function crearMensaje($rol, $user_uuid, $categoria_uuid)
    {
        if ($rol !== 'Admin' && $rol !== 'Editor') {
            return "No estas autorizado para esta accion 1";
        }
        if (self::validarmensaje($this->mensaje)) {
            $response['status'] = 'error';
            $response['message'] = 'El mensaje no puede estar vacio.';
            return $response;
        }else if ($this->contarCategorias() === 0) {
            $response['status'] = 'error';
            $response['message'] = 'Debes crear una categoria primero';
            return $response;
        }

        #GENERANDO UN UUID UNICO PARA EL PERFIL
        $uuidFactory = new UuidFactory();
        $uuid = $uuidFactory->uuid4();
        $mensaje_uuid = $uuid->toString();

        $sql = 'INSERT INTO mensajes_personalizados (uuid, mensaje, categoria, user_uuid) VALUES (?, ?, ?, ?)';
        $consulta = $this->ejecutarConsulta($sql, [$mensaje_uuid, $this->mensaje, $categoria_uuid, $user_uuid]);
        if ($consulta) {
            $response['status'] = "ok";
            $response['message'] = "Mensaje creada exitosamente.";
            return $response;
        }
    }

    public function obtenerPlantilla($user_uuid, $categoria)
    {
        $sql = 'SELECT uuid, mensaje, categoria, user_uuid FROM mensajes_personalizados WHERE user_uuid = ? AND categoria = ? ORDER BY fecha DESC';
        $consulta = $this->ejecutarConsulta($sql, [$user_uuid, $categoria]);
        $list = $consulta->fetchAll(\PDO::FETCH_ASSOC);
        return $list;
    }
}
