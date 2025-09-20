<?php

namespace App\Models;

use Ramsey\Uuid\UuidFactory;
use App\Models\Database;
use Slim\Psr7\Response;

class Cliente extends Database
{
    private $response = [];
    protected $cliente;
    protected $nombre;
    protected $numero;
    protected $fecha;

    public function __construct($cliente = '', $nombre = '', $numero, $fecha = '')
    {
        $this->cliente = $cliente;
        $this->nombre = $nombre;
        $this->numero = $numero;
        $this->fecha = $fecha;
    }

    public function cargarBase($user_uuid)
    {
        if (empty(trim($this->numero)) || is_numeric($this->numero)) {
            $this->response["status"] = "error";
            $this->response["message"] = "La fila numero no puede estar vacia.";
            return $this->response;
        } else if (is_numeric($this->cliente)) {
            $this->response["status"] = "error";
            $this->response["message"] = "La fila cliente solo admite numeros o id de cliente.";
            return $this->response;
        } else {
            #GENERANDO UN UUID UNICO PARA EL PERFIL
            $uuidFactory = new UuidFactory();
            $uuid = $uuidFactory->uuid4();
            $client_uuid = $uuid->toString();
            $sql = 'INSERT INTO base (uuid, cliente, nombre, numero, fecha, user_uuid) VALUES (?, ?, ?, ?, ?, ?)';
            $consulta = $this->ejecutarConsulta($sql, [$client_uuid, $this->cliente, $this->nombre, $this->numero, $this->fecha, $user_uuid]);
            if ($consulta) {
                $this->response["status"] = "ok";
                $this->response["message"] = "Base cargada exitosamente.";
                return $this->response;
            }
        }
    }

    public function obtenerBase($user_uuid)
    {
        $sql = 'SELECT * FROM base WHERE user_uuid = ? ORDER BY currentDate DESC';
        $consulta = $this->ejecutarConsulta($sql, [$user_uuid]);
        $list = $consulta->fetchAll(\PDO::FETCH_ASSOC);
        return $list;
    }
}
