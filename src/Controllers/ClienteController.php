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
        $classClient = new Cliente($body["cliente"], $body["nombre"], $body["numero"], $body["fecha"]);
        $categoria = $classClient->cargarBase($user_uuid);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($categoria));
        return $response;
    }

    function obtenerBase($request, $response, $args)
    {
        $rol = $request->getAttribute('payload')->data->rol;
        $classAuth = new Mensaje();
        $categoria = $classAuth->obtenerMensajes($rol);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($categoria));
        return $response;
    }

}
