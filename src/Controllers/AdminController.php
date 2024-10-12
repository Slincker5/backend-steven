<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use App\Models\Admin;

class   AdminController
{
    function verifyUser($request, $response, $args)
    {

        $body = $request->getParsedBody();
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;
        $classAuth = new Admin($user_uuid);
        $register = $classAuth->verifyUser($body['usuario']);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($register));
        return $response;
    }
}
