<?php

namespace App\Models;

use App\Models\Database;

class Admin extends Database
{
    private $user_uuid;
    private $response;
    public function __construct($user_uuid)
    {
        $this->user_uuid = $user_uuid;
    }

    private function getRoles()
    {
        $sql = 'SELECT rol FROM usuarios WHERE user_uuid = ?';
        $roles = $this->ejecutarConsulta($sql, [$this->user_uuid]);
        $listRoles = $roles->fetchAll(\PDO::FETCH_ASSOC);
        return $listRoles;
    }

    public function verifyUser($usuario)
    {
        if ($this->getRoles()[0]["rol"] === "Admin") {
            $sql = 'UPDATE usuarios SET verificado = 1 WHERE user_uuid = ?';
            $verificar = $this->ejecutarConsulta($sql, [$usuario]);
            if ($verificar) {
                $this->response['status'] = 'OK';
                $this->response['message'] = 'Usuario autorizado exitosamente.';
                return $this->response;
            }
        } else {
            $this->response['status'] = 'error';
            $this->response['message'] = 'No estas autorizado para esta accion.';
            return $this->response;
        }
    }
}
