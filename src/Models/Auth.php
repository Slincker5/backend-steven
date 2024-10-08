<?php

namespace App\Models;

use App\Models\Database;
use Firebase\JWT\JWT;
use Ramsey\Uuid\UuidFactory;

class Auth extends Database
{

    #PROPIEDADES CLASE
    private $nombres = '/^[a-zA-ZñÑ]+$/';
    private $response;
    private $key = "PlankThuthu";


    public function createAccount($username, $pass, $repass)
    {
        if (empty($username) || empty($pass)) {
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
        } else if($pass !== $repass){
            $this->response['status'] = 'error';
            $this->response['message'] = 'Tu contraseña no coincide.';
            return $this->response;
        }else {

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

}
