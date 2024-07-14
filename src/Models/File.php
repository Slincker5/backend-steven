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

    public function readXlsx($filePath)
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
            $sql = 'INSERT INTO productos (articulo, descripcion, precio, costo, antiguedad) VALUES (?, ?, ?, ?, ?)';
            $this->ejecutarConsulta($sql, [$articulo['articulo'], $articulo['descripcion'], $articulo['precio'], $articulo['costo'], $articulo['antiguedad']]);
        }
        $this->response['status'] = 'OK';
        $this->response['message'] = 'Se cargo el archivo a la base de datos';
        return $this->response;

    }

    public function listadoEscaneado(){
        $sql = 'SELECT * FROM productos WHERE escaneado = 1';
        $lista = $this->ejecutarConsulta($sql);
        return $lista->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function agregarEscaneado ($articulo) {
        $fecha = date("Y-m-d H:i:s");
        $sql = 'UPDATE productos SET escaneado = 1, fecha = ? WHERE articulo = ?';
        $this->ejecutarConsulta($sql, [$fecha, $articulo]);
        $this->response['status'] = 'OK';
        $this->response['message'] = 'Producto escaneado con exito';
        return $this->response;
    }

    public function productosRestantes() {
        $sql = 'SELECT * FROM productos WHERE escaneado = 0';
        $lista = $this->ejecutarConsulta($sql);
        return $lista->fetchAll(\PDO::FETCH_ASSOC);
    }

}
