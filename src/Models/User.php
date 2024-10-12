<?php

namespace App\Models;

use App\Models\Database;

class User extends Database
{

    public function getUsers()
    {
        $sql = 'SELECT username, rol, fecha, verificado FROM usuarios';
        $consult = $this->ejecutarConsulta($sql);
        $list = $consult->fetchAll(\PDO::FETCH_ASSOC);
        return $list;
    }
}
