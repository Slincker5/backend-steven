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

    function actualizarCliente($request, $response, $args)
    {
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $body = $request->getParsedBody();

        $uuid = $body['uuid'] ?? '';
        $cliente = $body['cliente'] ?? '';
        $nombre = $body['nombre'] ?? '';
        $numero = $body['numero'] ?? '';
        $fecha = $body['fecha'] ?? '';

        if (empty($uuid) || empty($numero)) {
            $response->getBody()->write(json_encode(["status" => false, "message" => "Los campos uuid y numero son obligatorios"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $classCliente = new Cliente();
        $numeroNormalizado = $classCliente->normalizarNumeroSV($numero);

        if ($numeroNormalizado === null) {
            $response->getBody()->write(json_encode(["status" => false, "message" => "Numero invalido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $resultado = $classCliente->actualizarCliente($uuid, $cliente, $nombre, $numeroNormalizado, $fecha, $user_uuid);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($resultado));
        return $response;
    }

    function eliminarCliente($request, $response, $args)
    {
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $uuid = $args['uuid'] ?? '';

        if (empty($uuid)) {
            $response->getBody()->write(json_encode(["status" => false, "message" => "El uuid del cliente es obligatorio"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $classCliente = new Cliente();
        $resultado = $classCliente->eliminarCliente($uuid, $user_uuid);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($resultado));
        return $response;
    }

    function agregarCliente($request, $response, $args)
    {
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $body = $request->getParsedBody();

        $cliente = $body['cliente'] ?? '';
        $nombre = $body['nombre'] ?? '';
        $numero = $body['numero'] ?? '';
        $fecha = $body['fecha'] ?? '';

        if (empty($numero)) {
            $response->getBody()->write(json_encode(["status" => false, "message" => "El campo numero es obligatorio"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $classCliente = new Cliente();
        $numeroNormalizado = $classCliente->normalizarNumeroSV($numero);

        if ($numeroNormalizado === null) {
            $response->getBody()->write(json_encode(["status" => false, "message" => "Numero invalido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $resultado = $classCliente->agregarCliente($cliente, $nombre, $numeroNormalizado, $fecha, $user_uuid);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($resultado));
        return $response;
    }

}
