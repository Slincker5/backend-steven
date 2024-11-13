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
            $sheet->setCellValue('A' . $fila, $articulo['articulo']);
            $sheet->setCellValue('B' . $fila, $articulo['descripcion']);
            $sheet->setCellValue('C' . $fila, $articulo['precio']);
            $sheet->setCellValue('D' . $fila, $articulo['costo']);
            $sheet->setCellValue('E' . $fila, $articulo['antiguedad']);
            if ($articulo['escaneado'] == 0) {
                $sheet->setCellValue('F' . $fila, "NO");
            } else {
                $sheet->setCellValue('F' . $fila, "SI");
            }

            $fila++;
        }

        #   // Obtener la fecha y hora actual en formato 'Y-m-d-His'
        #   $fechaHora = date('Y-m-d-His');
        #
        #   // Crear el nombre del archivo
        #   $fileName = "TRIGGER-{$fechaHora}.xlsx";
        #
        #   // Guardar el archivo XLSX con el nombre dinámico
        #   $writer = new Xlsx($spreadsheet);
        #   $writer->save($fileName);
        #
        #   $this->response['status'] = 'OK';
        #   $this->response['message'] = 'Documento generado con exito.';
        #   return $this->response;

        // Establecer el tipo de contenido y nombre de archivo para descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="TRIGGER-' . date('Y-m-d-His') . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Enviar el archivo para descargar en lugar de guardarlo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
