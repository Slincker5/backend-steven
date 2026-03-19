<?php

namespace App\Models;

use App\Models\Database;

class Admin extends Database
{
    private $user_uuid;
    private $autowat_url = 'http://localhost:3300';

    public function __construct($user_uuid)
    {
        $this->user_uuid = $user_uuid;
    }

    private function getRol()
    {
        $sql = 'SELECT rol FROM usuarios WHERE user_uuid = ?';
        $result = $this->ejecutarConsulta($sql, [$this->user_uuid]);
        $data = $result->fetchAll(\PDO::FETCH_ASSOC);
        return $data[0]['rol'] ?? null;
    }

    private function esAdmin()
    {
        return $this->getRol() === 'Admin';
    }

    private function noAutorizado()
    {
        return ['status' => 'error', 'message' => 'No estas autorizado para esta accion.'];
    }

    public function listarUsuarios()
    {
        if (!$this->esAdmin()) return $this->noAutorizado();
        $sql = 'SELECT user_uuid, username, rol, fecha, verificado, bloqueado FROM usuarios ORDER BY fecha DESC';
        $result = $this->ejecutarConsulta($sql);
        return $result->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function verifyUser($usuario)
    {
        if (!$this->esAdmin()) return $this->noAutorizado();
        $sql = 'UPDATE usuarios SET verificado = 1 WHERE user_uuid = ?';
        $this->ejecutarConsulta($sql, [$usuario]);
        return ['status' => 'OK', 'message' => 'Usuario autorizado exitosamente.'];
    }

    public function editarUsuario($usuario, $data)
    {
        if (!$this->esAdmin()) return $this->noAutorizado();

        $campos = [];
        $valores = [];

        if (!empty($data['username'])) {
            $campos[] = 'username = ?';
            $valores[] = $data['username'];
        }
        if (!empty($data['rol'])) {
            $campos[] = 'rol = ?';
            $valores[] = $data['rol'];
        }

        if (empty($campos)) {
            return ['status' => 'error', 'message' => 'No hay campos para actualizar.'];
        }

        $valores[] = $usuario;
        $sql = 'UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE user_uuid = ?';
        $this->ejecutarConsulta($sql, $valores);
        return ['status' => 'OK', 'message' => 'Usuario actualizado.'];
    }

    public function eliminarUsuario($usuario)
    {
        if (!$this->esAdmin()) return $this->noAutorizado();
        if ($usuario === $this->user_uuid) {
            return ['status' => 'error', 'message' => 'No puedes eliminarte a ti mismo.'];
        }
        $sql = 'DELETE FROM usuarios WHERE user_uuid = ?';
        $this->ejecutarConsulta($sql, [$usuario]);
        return ['status' => 'OK', 'message' => 'Usuario eliminado.'];
    }

    public function bloquearUsuario($usuario)
    {
        if (!$this->esAdmin()) return $this->noAutorizado();
        if ($usuario === $this->user_uuid) {
            return ['status' => 'error', 'message' => 'No puedes bloquearte a ti mismo.'];
        }
        $sql = 'UPDATE usuarios SET bloqueado = IF(bloqueado = 1, 0, 1) WHERE user_uuid = ?';
        $this->ejecutarConsulta($sql, [$usuario]);

        $sql2 = 'SELECT bloqueado FROM usuarios WHERE user_uuid = ?';
        $result = $this->ejecutarConsulta($sql2, [$usuario]);
        $estado = $result->fetchAll(\PDO::FETCH_ASSOC);
        $bloqueado = $estado[0]['bloqueado'] ?? 0;

        return [
            'status' => 'OK',
            'message' => $bloqueado ? 'Usuario bloqueado.' : 'Usuario desbloqueado.',
            'bloqueado' => (int) $bloqueado
        ];
    }

    public function restablecerPassword($usuario, $newPass)
    {
        if (!$this->esAdmin()) return $this->noAutorizado();
        if (strlen($newPass) < 8) {
            return ['status' => 'error', 'message' => 'La contraseña debe tener al menos 8 caracteres.'];
        }
        $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $sql = 'UPDATE usuarios SET pass = ? WHERE user_uuid = ?';
        $this->ejecutarConsulta($sql, [$hash, $usuario]);
        return ['status' => 'OK', 'message' => 'Contraseña restablecida.'];
    }

    public function sesionesWhatsapp()
    {
        if (!$this->esAdmin()) return $this->noAutorizado();
        return $this->proxyGet('/admin/sessions');
    }

    public function cancelarEnvio($targetUuid)
    {
        if (!$this->esAdmin()) return $this->noAutorizado();
        return $this->proxyPost('/admin/cancel-batch', ['userUuid' => $targetUuid]);
    }

    public function cerrarSesionWhatsapp($targetUuid)
    {
        if (!$this->esAdmin()) return $this->noAutorizado();
        return $this->proxyPost('/admin/close-session', ['userUuid' => $targetUuid]);
    }

    private function proxyGet($path)
    {
        $ch = curl_init($this->autowat_url . $path);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?? ['error' => 'Sin respuesta de autowat-api'];
    }

    private function proxyPost($path, $data = [])
    {
        $ch = curl_init($this->autowat_url . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true) ?? ['error' => 'Sin respuesta de autowat-api'];
    }
}
