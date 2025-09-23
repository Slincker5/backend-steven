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
        // Solo procesar si existe el input "file"
        if (!isset($files['file'])) {
            $response->getBody()->write(json_encode([
                "error" => "No se seleccionó archivo"
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $file = $files['file'];

        // Guardar temporal y generar nombre único
        $name = uniqid() . "-" . $file->getClientFilename();
        $temp = sys_get_temp_dir() . "/" . $name;
        $file->moveTo($temp);

        // Subir a S3
        $s3 = new AmazonS3();
        $url = $s3->uploadFile($temp, "uploads/" . $user_uuid . "/" . $name);

        // Respuesta JSON
        $response->getBody()->write(json_encode($url));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
