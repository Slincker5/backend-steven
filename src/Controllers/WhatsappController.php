<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use App\Models\Whatsapp;

class   WhatsappController
{
    function obtenerQr($request, $response, $args)
    {
        $rol = $request->getAttribute('payload')->data->rol;
        $classAuth = new Whatsapp();
        $qr = $classAuth->obtenerQr($rol);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($qr));
        return $response;
    }
}
