<?php

namespace App\Models;

use App\Models\Database;

class Whatsapp
{

    private $idInstancia = '7105232320';
    private $apiToken = '09b5699313d0435788620ed6a92485d8a3c12eecb8bd4cb499';

    public function obtenerQr($rol)
    {
        if ($rol === 'Admin') {
            $url = "https://7105.api.greenapi.com/waInstance{$this->idInstancia}/qr/{$this->apiToken}";

            $curl = curl_init($url);

            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            if (!$response) {
                return [
                    'success' => false,
                    'message' => '❌ Error al conectar con la API.'
                ];
            }

            $data = json_decode($response, true);

            if ($data['type'] === 'qrCode' && !empty($data['message']) && $data['message'] !== 'NOT_AUTHORIZED') {
                return [
                    'success' => true,
                    'qr_base64' => $data['message']
                ];
            } elseif ($data['type'] === 'alreadyLogged' || $data['message'] === 'NOT_AUTHORIZED') {
                return [
                    'success' => false,
                    'message' => '✅ Ya autorizado o el QR ha expirado.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'QR aún no disponible.'
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'No estas autorizado para esta accion.'
            ];
        }
    }

    public function cerrarSesion($rol)
    {
        if ($rol === 'Admin') {

            $url = "https://7105.api.greenapi.com/waInstance{$this->idInstancia}/logout/{$this->apiToken}";

            $curl = curl_init($url);

            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST", // método correcto
                CURLOPT_POSTFIELDS => '{}',      // cuerpo vacío como JSON
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 10
            ]);

            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if (!$response) {
                return [
                    'success' => false,
                    'message' => 'No se pudo conectar con la API.'
                ];
            }

            $data = json_decode($response, true);

            if (isset($data['isLogout']) && $data['isLogout'] === true) {
                return [
                    'success' => true,
                    'message' => 'Sesión cerrada correctamente.'
                ];
            } elseif ($http_code === 200 && isset($data['isLogout']) && $data['isLogout'] === false) {
                return [
                    'success' => false,
                    'message' => 'La sesión ya estaba cerrada o no se pudo cerrar.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Respuesta inesperada: ' . json_encode($data)
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'No estas autorizado para esta accion'
            ];
        }
    }
}
