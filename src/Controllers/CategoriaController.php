<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use App\Models\Categoria;

class   CategoriaController
{
    function crearCategoria($request, $response, $args)
    {
        $rol = $request->getAttribute('payload')->data->rol;
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $body = $request->getParsedBody();
        if (empty($body['titulo'])) {
            $response->getBody()->write(json_encode(["status" => false, "message" => "El campo titulo es requerido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $classCategoria = new Categoria($body['titulo']);
        $categoria = $classCategoria->crearCategoria($rol, $user_uuid);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($categoria));
        return $response;
    }

    function obtenerCategorias($request, $response, $args)
    {
        $rol = $request->getAttribute('payload')->data->rol;
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $classCategoria = new Categoria();
        $categoria = $classCategoria->obtenerCategorias($rol, $user_uuid);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($categoria));
        return $response;
    }

}
