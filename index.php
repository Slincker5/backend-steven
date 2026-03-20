<?php

use Slim\Factory\AppFactory;
use App\Controllers\FileController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\AmazonS3Controller;
use App\Controllers\UserController;
use App\Controllers\CategoriaController;
use App\Controllers\ClienteController;
use App\Controllers\MensajeController;
use App\Config;
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
        ->withHeader('Access-Control-Allow-Origin', Config::corsOrigin())
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$validateJwtMiddleware = function ($request, $handler) {
    $response = new Response();
    $key = Config::jwtKey();
    $authHeader = $request->getHeaderLine('Authorization');
    if (!$authHeader) {
        $response->getBody()->write(json_encode(["error" => "Token no proporcionado"]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    list($jwt) = sscanf($authHeader, 'Bearer %s');

    if (!$jwt) {
        $response->getBody()->write(json_encode(["error" => "Token no encontrado en la cabecera Authorization"]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    try {
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $request = $request->withAttribute('payload', $decoded);
        $request = $request->withAttribute('jwt', $jwt);
    } catch (Exception $e) {
        $response = new Response();
        $response->getBody()->write(json_encode(["status" => "invalid", "message" => "Token no valido."]));
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
    $group->get('/users', AdminController::class . ':listarUsuarios');
    $group->post('/approve-user', AdminController::class . ':verifyUser');
    $group->put('/user/edit', AdminController::class . ':editarUsuario');
    $group->delete('/user/delete', AdminController::class . ':eliminarUsuario');
    $group->post('/user/block', AdminController::class . ':bloquearUsuario');
    $group->post('/user/reset-password', AdminController::class . ':restablecerPassword');
    $group->get('/whatsapp/sessions', AdminController::class . ':sesionesWhatsapp');
    $group->post('/whatsapp/cancel-batch', AdminController::class . ':cancelarEnvio');
    $group->post('/whatsapp/close-session', AdminController::class . ':cerrarSesionWhatsapp');
})->add($validateJwtMiddleware);

$app->group('/category', function ($group) {
    $group->post('/new', CategoriaController::class . ':crearCategoria');
    $group->get('/list', CategoriaController::class . ':obtenerCategorias');
})->add($validateJwtMiddleware);

$app->group('/messages', function ($group) {
    $group->post('/new', MensajeController::class . ':crearMensaje');
    $group->post('/list', MensajeController::class . ':obtenerPlantilla');
})->add($validateJwtMiddleware);

$app->group('/base', function ($group) {
    $group->post('/create', ClienteController::class . ':cargarBase');
    $group->get('/list', ClienteController::class . ':obtenerBase');
    $group->delete('/delete', ClienteController::class . ':eliminarBase');
})->add($validateJwtMiddleware);

$app->group('/files', function ($group) {
    $group->post('/upload', AmazonS3Controller::class . ':upload');
})->add($validateJwtMiddleware);


$app->run();
