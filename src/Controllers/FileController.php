<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\File;

class FileController
{

    public function uploadXlsx(Request $request, Response $response, $args)
    {
        // Obtener los archivos subidos
        $uploadedFiles = $request->getUploadedFiles();

        // Verificar si el archivo 'xlsx_file' ha sido subido
        if (isset($uploadedFiles['xlsx_file'])) {
            $xlsxFile = $uploadedFiles['xlsx_file'];

            // Verificar si no hubo errores al subir el archivo
            if ($xlsxFile->getError() === UPLOAD_ERR_OK) {
                // Leer el contenido del archivo utilizando el modelo
                $filePath = $xlsxFile->getStream()->getMetadata('uri');
                $articulos = new File();
                $req = $articulos->readXlsx($filePath);

                // Devolver el array de artículos en formato JSON
                $response->getBody()->write(json_encode($req));
                return $response->withHeader('Content-Type', 'application/json');
            }
        }

        // Si no se subió ningún archivo o hubo un error, devolver un error
        $response->getBody()->write(json_encode('Error al cargar el documento'));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listadoEscaneado(Request $request, Response $response, $args)
    {
        $classFile = new File();
        $lista = $classFile->listadoEscaneado();
        $response->getBody()->write(json_encode($lista));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function productosRestantes(Request $request, Response $response, $args)
    {
        $classFile = new File();
        $lista = $classFile->productosRestantes();
        $response->getBody()->write(json_encode($lista));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function agregarEscaneado(Request $request, Response $response, $args)
    {
        $body = $request->getParsedBody();
        $classFile = new File();
        $escanear = $classFile->agregarEscaneado($body['articulo']);
        $response->getBody()->write(json_encode($escanear));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
