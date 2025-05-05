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

    function cerrarSesion($request, $response, $args)
    {
        $rol = $request->getAttribute('payload')->data->rol;
        $classAuth = new Whatsapp();
        $logout = $classAuth->cerrarSesion($rol);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($logout));
        return $response;
    }

    function logueado($request, $response, $args)
    {
        $classAuth = new Whatsapp();
        $logout = $classAuth->logueado();
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($logout));
        return $response;
    }

    function obtenerInfoWhatsapp($request, $response, $args)
    {
        $classAuth = new Whatsapp();
        $logout = $classAuth->obtenerInfoWhatsapp();
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($logout));
        return $response;
    }
}
