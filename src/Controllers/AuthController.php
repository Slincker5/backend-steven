<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use App\Models\Auth;

class AuthController
{

    function createAccount($request, $response, $args)
    {

        $body = $request->getParsedBody();
        $classAuth = new Auth();
        $register = $classAuth->createAccount($body['username'], $body['pass'], $body['repass']);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($register));
        return $response;
    }
}
