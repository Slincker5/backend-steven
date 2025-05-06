<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use App\Models\Mensaje;

class  MensajeController
{
    function crearMensaje($request, $response, $args)
    {
        $rol = $request->getAttribute('payload')->data->rol;
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $body = $request->getParsedBody();
        $classAuth = new Mensaje($body['mensaje']);
        $categoria = $classAuth->crearMensaje($rol, $user_uuid, $body['categoria']);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($categoria));
        return $response;
    }

    function obtenerMensajes($request, $response, $args)
    {
        $rol = $request->getAttribute('payload')->data->rol;
        $classAuth = new Mensaje();
        $categoria = $classAuth->obtenerMensajes($rol);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($categoria));
        return $response;
    }

}
