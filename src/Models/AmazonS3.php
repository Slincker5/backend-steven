<?php

namespace App\Models;

use Dotenv\Dotenv;
use Aws\S3\S3Client;

class AmazonS3
{
    private $s3Client;
    private $bucket;
    private $region;
    private $cdnDomain = "https://cdn.multimarcas.app";

    public function __construct()
    {
        try {
            $dotenv = Dotenv::createImmutable('/var/www/');
            $dotenv->load();
        } catch (\Exception $e) {
            throw new \Exception("Error cargando variables de entorno: " . $e->getMessage());
        }

        $this->bucket = $_ENV['AMAZON_S3_BUCKET_LABEL'];
        $this->region = $_ENV['AMAZON_S3_REGION'];

        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => $this->region,
            'credentials' => [
                'key'    => $_ENV['AMAZON_ACCESS_KEY_ID'],
                'secret' => $_ENV['AMAZON_SECRET_ACCESS_KEY'],
            ],
        ]);
    }

    /**
     * Subir archivo a S3 y retornar URL CDN
     */
    public function uploadFile($filePath, $keyName)
    {
        // Validar extensiones permitidas
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'mp4'];
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception("Formato no permitido. Solo se aceptan: jpg, jpeg, png, mp4");
        }

        // Subir archivo
        $this->s3Client->putObject([
            'Bucket'      => $this->bucket,
            'Key'         => $keyName,
            'SourceFile'  => $filePath,
            'ContentType' => mime_content_type($filePath),
        ]);

        // Retornar URL desde el CDN
        return [
            "status" => "ok",
            "url" => "{$this->cdnDomain}/{$keyName}"
        ];
    }
}
