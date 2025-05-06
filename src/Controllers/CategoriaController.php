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
        $body = $request->getParsedBody();
        $classAuth = new Categoria($body['titulo']);
        $categoria = $classAuth->crearCategoria($rol);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($categoria));
        return $response;
    }

}
