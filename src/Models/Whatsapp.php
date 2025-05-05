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
                    'message' => 'Ya autorizado o el QR ha expirado.'
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
        if ($rol !== 'Admin') {
            return [
                'success' => false,
                'message' => 'No estás autorizado para esta acción.'
            ];
        }

        $baseUrl = "https://7105.api.greenapi.com/waInstance{$this->idInstancia}";
        $token   = $this->apiToken;

        // Paso 1: Verificar estado actual
        $estadoUrl = "{$baseUrl}/getStateInstance/{$token}";
        $curl = curl_init($estadoUrl);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $estadoResponse = curl_exec($curl);
        curl_close($curl);

        if (!$estadoResponse) {
            return [
                'success' => false,
                'message' => 'No se pudo verificar el estado de la sesión.'
            ];
        }

        $estadoData = json_decode($estadoResponse, true);
        $estado = $estadoData['stateInstance'] ?? null;

        if ($estado !== 'authorized') {
            return [
                'success' => false,
                'message' => "No hay sesión activa que cerrar. Estado actual: {$estado}."
            ];
        }

        // Paso 2: Cerrar sesión con GET
        $logoutUrl = "{$baseUrl}/logout/{$token}";
        $curl = curl_init($logoutUrl);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_TIMEOUT => 10
        ]);
        $logoutResponse = curl_exec($curl);
        curl_close($curl);

        if (!$logoutResponse) {
            return [
                'success' => false,
                'message' => 'No se pudo conectar al cerrar sesión.'
            ];
        }

        $logoutData = json_decode($logoutResponse, true);

        if (isset($logoutData['isLogout']) && $logoutData['isLogout'] === true) {
            return [
                'success' => true,
                'message' => 'Sesión cerrada correctamente.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No se pudo cerrar la sesión o ya estaba cerrada.'
            ];
        }
    }

    public function logueado() {
        $url = "https://api.green-api.com/waInstance{$this->idInstancia}/getStateInstance/{$this->apiToken}";
    
        // Iniciar cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        // Ejecutar la petición
        $response = curl_exec($ch);
        curl_close($ch);
    
        // Decodificar respuesta
        $data = json_decode($response, true);
    
        // Verificar estado
        return $data;
    }
}
