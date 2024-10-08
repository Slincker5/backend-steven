<?php
use Slim\Factory\AppFactory;
use App\Controllers\FileController;
use App\Controllers\AuthController;
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


$app->group('/document', function ($group) {
    $group->post('/upload', FileController::class . ':uploadXlsx');
    $group->get('/list', FileController::class . ':listadoEscaneado');
    $group->get('/productos-restantes', FileController::class . ':productosRestantes');
    $group->put('/escanear', FileController::class . ':agregarEscaneado');
});

$app->group('/user', function ($group) {
    $group->post('/register', AuthController::class . ':createAccount');
});

$app->run();