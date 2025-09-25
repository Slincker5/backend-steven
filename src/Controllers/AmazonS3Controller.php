<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Models\AmazonS3;

class AmazonS3Controller
{
    public function upload(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $files = $request->getUploadedFiles();
        $user_uuid = $request->getAttribute('payload')->data->user_uuid;

        // Validar que exista archivo
        if (!isset($files['file'])) {
            $response->getBody()->write(json_encode([
                "error" => "No se seleccionó archivo"
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $file = $files['file'];

        // Obtener extensión del archivo original
        $originalName = $file->getClientFilename();
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Guardar temporal con nombre único (no importa el original)
        $temp = sys_get_temp_dir() . "/" . uniqid() . "." . $extension;
        $file->moveTo($temp);

        // Generar key en S3 con el user_uuid como nombre final
        $keyName = "uploads/" . $user_uuid . "/" . uniqid() . "." . $extension;

        try {
            $s3 = new AmazonS3();
            $url = $s3->uploadFile($temp, $keyName);

            $response->getBody()->write(json_encode($url));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                "error" => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
