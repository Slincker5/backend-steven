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
        if (empty($body['mensaje'])) {
            $response->getBody()->write(json_encode(["status" => false, "message" => "El campo mensaje es requerido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        if (empty($body['categoria'])) {
            $response->getBody()->write(json_encode(["status" => false, "message" => "El campo categoria es requerido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $classMensaje = new Mensaje($body['mensaje']);
        $categoria = $classMensaje->crearMensaje($rol, $user_uuid, $body['categoria']);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($categoria));
        return $response;
    }

    function obtenerPlantilla($request, $response, $args)
    {
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $body = $request->getParsedBody();
        $classMensaje = new Mensaje();
        $categoria = $classMensaje->obtenerPlantilla($user_uuid, $body["categoria"]);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($categoria));
        return $response;
    }

}
