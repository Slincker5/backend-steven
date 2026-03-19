<?php

namespace App\Controllers;

use App\Models\Admin;

class AdminController
{
    private function getAdmin($request)
    {
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $jwt = $request->getAttribute('jwt');
        return new Admin($user_uuid, $jwt);
    }

    private function jsonResponse($response, $data)
    {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($data));
        return $response;
    }

    function listarUsuarios($request, $response, $args)
    {
        return $this->jsonResponse($response, $this->getAdmin($request)->listarUsuarios());
    }

    function verifyUser($request, $response, $args)
    {
        $body = $request->getParsedBody();
        return $this->jsonResponse($response, $this->getAdmin($request)->verifyUser($body['usuario']));
    }

    function editarUsuario($request, $response, $args)
    {
        $body = $request->getParsedBody();
        return $this->jsonResponse($response, $this->getAdmin($request)->editarUsuario($body['usuario'], $body));
    }

    function eliminarUsuario($request, $response, $args)
    {
        $body = $request->getParsedBody();
        return $this->jsonResponse($response, $this->getAdmin($request)->eliminarUsuario($body['usuario']));
    }

    function bloquearUsuario($request, $response, $args)
    {
        $body = $request->getParsedBody();
        return $this->jsonResponse($response, $this->getAdmin($request)->bloquearUsuario($body['usuario']));
    }

    function restablecerPassword($request, $response, $args)
    {
        $body = $request->getParsedBody();
        return $this->jsonResponse($response, $this->getAdmin($request)->restablecerPassword($body['usuario'], $body['password']));
    }

    function sesionesWhatsapp($request, $response, $args)
    {
        return $this->jsonResponse($response, $this->getAdmin($request)->sesionesWhatsapp());
    }

    function cancelarEnvio($request, $response, $args)
    {
        $body = $request->getParsedBody();
        return $this->jsonResponse($response, $this->getAdmin($request)->cancelarEnvio($body['usuario']));
    }

    function cerrarSesionWhatsapp($request, $response, $args)
    {
        $body = $request->getParsedBody();
        return $this->jsonResponse($response, $this->getAdmin($request)->cerrarSesionWhatsapp($body['usuario']));
    }
}
