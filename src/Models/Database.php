<?php

namespace App\Models;

class Database
{
    private static $parametros = "mysql:host=localhost;dbname=steven;charset=utf8mb4";
    private static $usuario = "root";
    private static $clave = "";

    public function conectar()
    {
        try {
            $con = new \PDO(self::$parametros, self::$usuario, self::$clave, [
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
