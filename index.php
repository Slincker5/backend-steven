<?php

use Slim\Factory\AppFactory;
use App\Controllers\FileController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\UserController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Slim\Psr7\Response;

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->setBasePath('/api');
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$validateJwtMiddleware = function ($request, $handler) {
    $response = new Response();
    $key = "PlankThuthu";
    $authHeader = $request->getHeaderLine('Authorization');
    if (!$authHeader) {
        $response->getBody()->write(json_encode(["error" => "Token no proporcionado"]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    #EXTRAER TOKEN DE LA CABEZERA
    list($jwt) = sscanf($authHeader, 'Bearer %s');

    #VALIDAR SI LA CABEZERA CONTIENE ALGUN TOKEN
    if (!$jwt) {
        $response->getBody()->write(json_encode(["error" => "Token no encontrado en la cabecera Authorization"]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    try {
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        // Aquí puedes incluso agregar el payload decodificado al request si lo necesitas después
        $request = $request->withAttribute('payload', $decoded);
        $request = $request->withAttribute('jwt', $jwt);
    } catch (Exception $e) {
        $response = new Response();
        $paquete = [
            "status" => "invalid",
            "message" => "Token no valido."
        ];
        $response->getBody()->write(json_encode($paquete));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    return $handler->handle($request);
};


$app->group('/document', function ($group) {
    $group->post('/upload', FileController::class . ':uploadXlsx');
    $group->get('/list', FileController::class . ':listadoEscaneado');
    $group->get('/productos-restantes', FileController::class . ':productosRestantes');
    $group->put('/escanear', FileController::class . ':agregarEscaneado');
    $group->post('/trigger', FileController::class . ':exportarEstado');
    $group->post('/list', FileController::class . ':productosGlobal');
})->add($validateJwtMiddleware);

$app->group('/auth', function ($group) {
    $group->post('/register', AuthController::class . ':createAccount');
    $group->post('/login', AuthController::class . ':login');
});

$app->group('/user', function ($group) {
    $group->get('/list', UserController::class . ':getUsers');
    $group->get('/profile', UserController::class . ':getInfo');
})->add($validateJwtMiddleware);

$app->group('/admin', function ($group) {
    $group->post('/approve-user', AdminController::class . ':verifyUser');
})->add($validateJwtMiddleware);

$app->run();
