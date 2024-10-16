<?php

namespace App\Models;

use App\Models\Database;
use Firebase\JWT\JWT;
use Ramsey\Uuid\UuidFactory;

class Auth extends Database
{

    #PROPIEDADES CLASE
    private $nombres = '/^[0-9]+$/';
    private $response;
    private $key = "PlankThuthu";

    private function verifyAccount($username)
    {
        $sql = 'SELECT username FROM usuarios WHERE username = ?';
        $singin = $this->ejecutarConsulta($sql, [$username]);
        $list = $singin->fetchAll(\PDO::FETCH_ASSOC);
        return count($list);
    }


    public function createAccount($username, $pass, $repass)
    {
        if(self::verifyAccount($username) > 0){
            $this->response['status'] = 'error';
            $this->response['message'] = 'Este usuario no esta disponible, elige otro.';
            return $this->response;
        } else if (empty($username) || empty($pass)) {
            $this->response['status'] = 'error';
            $this->response['message'] = 'Completa todos los campos.';
            return $this->response;
        } else if (!preg_match($this->nombres, $username)) {
            $this->response['status'] = 'error';
            $this->response['message'] = 'El username  solo puede contener letras.';
            return $this->response;
        } else if (strlen($username) > 5) {
            $this->response['status'] = 'error';
            $this->response['message'] = 'El username no puede tener mas de 5 caracteres.';
            return $this->response;
        } else if (strlen($username) < 5) {
            $this->response['status'] = 'error';
            $this->response['message'] = 'El username debe tener al menos 5 caracteres.';
            return $this->response;
        } else if (strlen($pass) < 8) {
            $this->response['status'] = 'error';
            $this->response['message'] = 'Tu contraseña debe tener al menos 8 caracteres.';
            return $this->response;
        } else if ($pass !== $repass) {
            $this->response['status'] = 'error';
            $this->response['message'] = 'Tu contraseña no coincide.';
            return $this->response;
        } else {

            #GENERANDO UN UUID UNICO PARA EL PERFIL
            $uuidFactory = new UuidFactory();
            $uuid = $uuidFactory->uuid4();
            $profile_uuid = $uuid->toString();

            #ENCRIPTADO DE CLAVE
            $options = ['cost' => 12];
            $passwordHash = password_hash($pass, PASSWORD_BCRYPT, $options);

            #PROCEDER AL GUARDADO PERSISTENTE
            $sql = 'INSERT INTO usuarios (user_uuid, username, pass, rol) VALUES (?, ?, ?, ?)';
            $signUp = $this->ejecutarConsulta($sql, [$profile_uuid, $username, $passwordHash, 'User']);

            if ($signUp) {
                $payload = array(
                    "iss" => "maxiefectivo",
                    "aud" => $profile_uuid,
                    "iat" => time(),
                    "nbf" => time(),
                    "data" => array(
                        "user_uuid" => $profile_uuid,
                        "username" => $username,
                        "rol" => "User"
                    ),
                );
                $alg = "HS256";
                $token = JWT::encode($payload, $this->key, $alg);
                $this->response['status'] = 'OK';
                $this->response['message'] = 'Registro exitoso.';
                $this->response['username'] = $username;
                $this->response['user_uuid'] = $profile_uuid;
                $this->response['rol'] = "User";
                $this->response['token'] = $token;
                return $this->response;
            } else {
                $this->response['status'] = 'error';
                $this->response['message'] = 'Hubo algun problema a la hora de tu registro, intenta mas tarde.';
                return $this->response;
            }
        }
    }

    public function login($username, $pass)
    {
        $sql = 'SELECT * FROM usuarios WHERE username = ?';
        $logIn = $this->ejecutarConsulta($sql, [$username]);
        $accountData = $logIn->fetchAll(\PDO::FETCH_ASSOC);
        if (count($accountData) === 1) {
            if (password_verify($pass, $accountData[0]['pass'])) {

                // Crear un token
                $payload = array(
                    "iss" => "maxiefectivo",
                    "aud" => $accountData[0]['user_uuid'],
                    "iat" => time(),
                    "nbf" => time(),
                    "data" => array(
                        "user_uuid" => $accountData[0]['user_uuid'],
                        "username" => $accountData[0]['username'],
                        "rol" => $accountData[0]['rol'],
                    ),
                );
                $alg = "HS256";
                $token = JWT::encode($payload, $this->key, $alg);

                $this->response['status'] = 'OK';
                $this->response['message'] = 'Sesión exitosa.';
                $this->response['username'] = $accountData[0]['username'];
                $this->response['user_uuid'] = $accountData[0]['user_uuid'];
                $this->response['verificado'] = $accountData[0]['verificado'];
                $this->response['rol'] = $accountData[0]['rol'];
                $this->response['token'] = $token;
                return $this->response;
            } else {
                $this->response['status'] = 'error';
                $this->response['message'] = 'Usuario o contraseña incorrectos, valida tus datos';
                return $this->response;
            }
        } else {
            $this->response['status'] = 'error';
            $this->response['message'] = 'Usuario o contraseña incorrectos, valida tus datos';
            return $this->response;
        }
    }
}
