<?php

namespace App\Models;

use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\Uuid;
use App\Models\Database;
use Slim\Psr7\Response;

class Cliente extends Database
{
    private $response = [];
    protected $cliente;
    protected $nombre;
    protected $numero;
    protected $fecha;

    public function __construct($cliente = '', $nombre = '', $numero = '', $fecha = '')
    {
        $this->cliente = $cliente;
        $this->nombre = $nombre;
        $this->numero = $numero;
        $this->fecha = $fecha;
    }

    public function cargarBase(array $body, $user_uuid)
    {
        if (count($this->obtenerBase($user_uuid)) > 0) {
            $this->eliminarBaseActual($user_uuid);
        }

        $clientes = $body['base'];
        $omitidos = 0;
        $clienteSinNumero = [];

        foreach ($clientes as $cliente) {

            $idCliente = $cliente["cliente"];
            $nombreCliente = $cliente["nombre"];
            $numeroCliente = $cliente["numero"];
            $fechaVencCliente = $cliente["fecha"];

            $validarNumero = $this->normalizarNumeroSV($numeroCliente);

            if ($validarNumero === null) {
                $omitidos++;
                $clienteSinNumero[] = [
                    "cliente" => $idCliente,
                    "nombre" => $nombreCliente,
                    "error" => "Numero invalido"
                ];
                continue;
            }
            $client_uuid = Uuid::uuid4()->toString();
            $sql = 'INSERT INTO base (uuid, cliente, nombre, numero, fecha, user_uuid) VALUES (?, ?, ?, ?, ?, ?)';
            $consulta = $this->ejecutarConsulta($sql, [$client_uuid, $idCliente, $nombreCliente, $validarNumero, $fechaVencCliente, $user_uuid]);
        }

        $this->response["status"] = "ok";
        $this->response["message"] = "Base cargada exitosamente.";
        $this->response["omitidos"] = $omitidos;
        $this->response["clientesSinNumero"] = $clienteSinNumero;
        return $this->response;
    }

    public function eliminarBaseActual($user_uuid)
    {
        $sql = 'DELETE FROM base WHERE user_uuid = ?';
        $this->ejecutarConsulta($sql, [$user_uuid]);
        return ["status" => "ok", "message" => "Base eliminada exitosamente."];
    }

    public function obtenerBase($user_uuid)
    {
        $sql = 'SELECT * FROM base WHERE user_uuid = ? ORDER BY currentDate DESC';
        $consulta = $this->ejecutarConsulta($sql, [$user_uuid]);
        $list = $consulta->fetchAll(\PDO::FETCH_ASSOC);
        return $list;
    }

    public function normalizarNumeroSV($input): ?string
    {
        $s = trim((string)$input);
        if ($s === '') return null;

        // Solo dígitos
        $digits = preg_replace('/\D+/', '', $s);

        // Quita 503 si viene incluido
        if (strlen($digits) === 11 && substr($digits, 0, 3) === '503') {
            $digits = substr($digits, 3);
        }

        // Valida 8 dígitos
        if (!preg_match('/^[2-9]\d{7}$/', $digits)) {
            return null;
        }

        return '503' . $digits;
    }
}
