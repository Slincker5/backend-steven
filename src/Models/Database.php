<?php

namespace App\Models;

use App\Config;

class Database
{
    public function conectar()
    {
        try {
            $dsn = "mysql:host=" . Config::dbHost() . ";dbname=" . Config::dbName() . ";charset=utf8mb4";
            $con = new \PDO($dsn, Config::dbUser(), Config::dbPass(), [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            return $con;
        } catch (\PDOException $e) {
            echo "ERROR: " . $e->getMessage();
        }
    }

    protected function ejecutarConsulta($sql, $params = [])
    {
        $conexion = $this->conectar();
        $consulta = $conexion->prepare($sql);
        $consulta->execute($params);
        return $consulta;
    }

}
