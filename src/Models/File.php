<?php

namespace App\Models;

use App\Models\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;

class File extends Database
{

    private $response;

    protected function eliminarProductos()
    {
        $sql = 'DELETE FROM productos';
        $response = $this->ejecutarConsulta($sql);
        return $response->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function readXlsx($filePath, $user_uuid)
    {
        // Crear un lector y cargar el archivo desde el stream
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Inicializar el array de artículos
        $articulos = array();

        // Recorrer las filas del archivo Excel y obtener los datos necesarios
        foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
            if ($rowIndex == 1) {
                // Saltar la fila de encabezados
                continue;
            }

            $rowData = [];
            $rowData['articulo'] = $worksheet->getCell('A' . $rowIndex)->getValue();
            $rowData['descripcion'] = $worksheet->getCell('B' . $rowIndex)->getValue();
            $rowData['precio'] = $worksheet->getCell('C' . $rowIndex)->getValue();
            $rowData['costo'] = $worksheet->getCell('D' . $rowIndex)->getValue();
            $rowData['antiguedad'] = $worksheet->getCell('E' . $rowIndex)->getValue();

            $articulos[] = $rowData;
        }
        $this->eliminarProductos();

        foreach ($articulos as $articulo) {
            $sql = 'INSERT INTO productos (articulo, descripcion, precio, costo, antiguedad, user_uuid) VALUES (?, ?, ?, ?, ?, ?)';
            $this->ejecutarConsulta($sql, [$articulo['articulo'], $articulo['descripcion'], $articulo['precio'], $articulo['costo'], $articulo['antiguedad'], $user_uuid]);
        }
        $this->response['status'] = 'OK';
        $this->response['message'] = 'Se cargo el archivo a la base de datos';
        return $this->response;
    }

    public function listadoEscaneado()
    {
        $sql = 'SELECT * FROM productos WHERE escaneado = 1 ORDER BY fecha DESC';
        $lista = $this->ejecutarConsulta($sql);
        return $lista->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function productosRestantes()
    {
        $sql = 'SELECT * FROM productos WHERE escaneado = 0';
        $lista = $this->ejecutarConsulta($sql);
        return $lista->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function busquedaArticulo($articulo)
    {
        $sql = 'SELECT * FROM productos WHERE articulo = ?';
        $stmt = $this->ejecutarConsulta($sql, [$articulo]);
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $list;
    }

    private function validarArticulo($articulo)
    {
        $sql = 'SELECT * FROM productos WHERE articulo = ?';
        $stmt = $this->ejecutarConsulta($sql, [$articulo]);

        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function validarArticuloEscaneado($articulo)
    {
        $sql = 'SELECT * FROM productos WHERE articulo = ? AND escaneado = 1';
        $stmt = $this->ejecutarConsulta($sql, [$articulo]);

        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function agregarEscaneado($articulo)
    {
        $fecha = date("Y-m-d H:i:s");
        if (!$this->validarArticulo($articulo)) {
            $this->response['status'] = 'error';
            $this->response['message'] = 'El articulo escaneado no se encuentra en la base de datos.';
            return $this->response;
        } else if ($this->validarArticuloEscaneado($articulo)) {
            $this->response['status'] = 'error';
            $this->response['message'] = 'El articulo ya ha sido escaneado.';
            return $this->response;
        } else {
            $sql = 'UPDATE productos SET escaneado = 1, fecha = ? WHERE articulo = ?';
            $this->ejecutarConsulta($sql, [$fecha, $articulo]);
            $this->response['status'] = 'OK';
            $this->response['message'] = 'Producto escaneado con exito';
            $this->response['articulos'] = $this->busquedaArticulo($articulo);
            return $this->response;
        }
    }
}
