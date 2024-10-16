<?php

namespace App\Models;

use App\Models\Database;

class User extends Database
{

    public function getUsers()
    {
        $sql = 'SELECT user_uuid, username, rol, fecha, verificado FROM usuarios';
        $consult = $this->ejecutarConsulta($sql);
        $list = $consult->fetchAll(\PDO::FETCH_ASSOC);
        return $list;
    }

    public function getInfo($user_uuid)
    {
        $sql = 'SELECT user_uuid, username, rol, fecha, verificado FROM usuarios WHERE user_uuid = 1';
        $consult = $this->ejecutarConsulta($sql, [$user_uuid]);
        $list = $consult->fetchAll(\PDO::FETCH_ASSOC);
        return $list;
    }

}
