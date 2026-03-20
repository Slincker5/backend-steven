<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use App\Models\Cliente;

class  ClienteController
{
    function cargarBase($request, $response, $args)
    {
        $rol = $request->getAttribute('payload')->data->rol;
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $body = $request->getParsedBody();
        if (!isset($body['base']) || !is_array($body['base']) || empty($body['base'])) {
            $response->getBody()->write(json_encode(["status" => false, "message" => "El campo base debe ser un array no vacío"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $classClient = new Cliente();
        $categoria = $classClient->cargarBase($body, $user_uuid);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($categoria));
        return $response;
    }

    function eliminarBase($request, $response, $args)
    {
        $rol = $request->getAttribute('payload')->data->rol;
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $classCliente = new Cliente();
        $categoria = $classCliente->eliminarBaseActual($user_uuid);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($categoria));
        return $response;
    }

    function obtenerBase($request, $response, $args)
    {
        $rol = $request->getAttribute('payload')->data->rol;
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $classCliente = new Cliente();
        $categoria = $classCliente->obtenerBase($user_uuid);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($categoria));
        return $response;
    }

}
