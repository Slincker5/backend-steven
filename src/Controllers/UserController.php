<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use App\Models\User;

class   UserController
{
    function getUsers($request, $response, $args)
    {
        $classAuth = new User();
        $register = $classAuth->getUsers();
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($register));
        return $response;
    }

    function getInfo($request, $response, $args)
    {
        $classAuth = new User();
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $register = $classAuth->getInfo($user_uuid);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($register));
        return $response;
    }
}
