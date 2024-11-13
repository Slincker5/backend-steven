<?php

namespace App\Models;

use App\Models\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class File extends Database
{

    private $response;

    protected function eliminarProductos($user_uuid)
    {
        $sql = 'DELETE FROM productos WHERE user_uuid = ?';
        $response = $this->ejecutarConsulta($sql, [$user_uuid]);
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
        $this->eliminarProductos($user_uuid);

        foreach ($articulos as $articulo) {
            $sql = 'INSERT INTO productos (articulo, descripcion, precio, costo, antiguedad, user_uuid) VALUES (?, ?, ?, ?, ?, ?)';
            $this->ejecutarConsulta($sql, [$articulo['articulo'], $articulo['descripcion'], $articulo['precio'], $articulo['costo'], $articulo['antiguedad'], $user_uuid]);
        }
        $this->response['status'] = 'OK';
        $this->response['message'] = 'Se cargo el archivo a la base de datos';
        return $this->response;
    }

    public function listadoEscaneado($user_uuid)
    {
        $sql = 'SELECT * FROM productos WHERE escaneado = 1 AND user_uuid = ? ORDER BY fecha DESC';
        $lista = $this->ejecutarConsulta($sql, [$user_uuid]);
        return $lista->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function productosRestantes($user_uuid)
    {
        $sql = 'SELECT * FROM productos WHERE escaneado = 0 AND user_uuid = ?';
        $lista = $this->ejecutarConsulta($sql, [$user_uuid]);
        return $lista->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function busquedaArticulo($articulo, $user_uuid)
    {
        $sql = 'SELECT * FROM productos WHERE articulo = ? AND user_uuid = ?';
        $stmt = $this->ejecutarConsulta($sql, [$articulo, $user_uuid]);
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $list;
    }

    private function validarArticulo($articulo, $user_uuid)
    {
        $sql = 'SELECT * FROM productos WHERE articulo = ? AND  user_uuid = ?';
        $stmt = $this->ejecutarConsulta($sql, [$articulo, $user_uuid]);

        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function validarArticuloEscaneado($articulo, $user_uuid)
    {
        $sql = 'SELECT * FROM productos WHERE articulo = ? AND escaneado = 1 AND user_uuid = ?';
        $stmt = $this->ejecutarConsulta($sql, [$articulo, $user_uuid]);

        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }


    public function agregarEscaneado($articulo, $user_uuid)
    {
        $fecha = date("Y-m-d H:i:s");
        if (!$this->validarArticulo($articulo, $user_uuid)) {
            $this->response['status'] = 'error';
            $this->response['message'] = 'El articulo escaneado no se encuentra en la base de datos.';
            return $this->response;
        } else if ($this->validarArticuloEscaneado($articulo, $user_uuid)) {
            $this->response['status'] = 'error';
            $this->response['message'] = 'El articulo ya ha sido escaneado.';
            return $this->response;
        } else {
            $sql = 'UPDATE productos SET escaneado = 1, fecha = ? WHERE articulo = ? AND user_uuid = ?';
            $this->ejecutarConsulta($sql, [$fecha, $articulo, $user_uuid]);
            $this->response['status'] = 'OK';
            $this->response['message'] = 'Producto escaneado con exito';
            $this->response['articulos'] = $this->busquedaArticulo($articulo, $user_uuid);
            return $this->response;
        }
    }

    public function productosGlobal($user_uuid)
    {
        $sql = 'SELECT * FROM productos WHERE user_uuid = ? ORDER BY fecha DESC';
        $lista = $this->ejecutarConsulta($sql, [$user_uuid]);
        return $lista->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function exportarEstado($user_uuid)
    {
        $articulos = $this->productosGlobal($user_uuid);
        // Crear una nueva hoja de cálculo
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Especificar los encabezados
        $sheet->setCellValue('A1', 'Articulo');
        $sheet->setCellValue('B1', 'Descripcion');
        $sheet->setCellValue('C1', 'Precio');
        $sheet->setCellValue('D1', 'Costo');
        $sheet->setCellValue('E1', 'Antiguedad');
        $sheet->setCellValue('F1', 'Escaneado');
        $fila = 2;
        foreach ($articulos as $articulo) {
            $sheet->setCellValue('A' . $fila, $articulo[0]['articulo']);
            $sheet->setCellValue('B' . $fila, $articulo[0]['descripcion']);
            $sheet->setCellValue('C' . $fila, $articulo[0]['precio']);
            $sheet->setCellValue('D' . $fila, $articulo[0]['costo']);
            $sheet->setCellValue('E' . $fila, $articulo[0]['antiguedad']);
            ($articulo[0]['escaneado'] === 0) ? $sheet->setCellValue('F' . $fila, "NO") :
                $sheet->setCellValue('F' . $fila, "SI");
            $fila++;
        }

        // Guardar el archivo XLSX
        $writer = new Xlsx($spreadsheet);
        $writer->save('articulos.xlsx');
        $this->response['status'] = 'OK';
        $this->response['message'] = 'Documento generado con exito.';
        return $this->response;
    }
}
