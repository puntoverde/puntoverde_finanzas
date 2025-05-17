<?php

namespace App\Controllers;

use XMLElementIterator;
use \XMLWriter;
use \XMLReader;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
// use Symfony\Component\HttpFoundation\StreamedResponse;
use App\DAO\ReporteInscripcionesDAO;
use Exception;

use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;



use Laravel\Lumen\Routing\Controller;

class ReporteInscripcionesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }


    //consultas para mostrar en tablas
    public function getDatosInscripciones(Request $req)
    {       
        return ReporteInscripcionesDAO::getDatosInscripciones((object) $req->all());
    }   

    public function getCuotasInscripcion()
    {
        return ReporteInscripcionesDAO::getCuotasInscripcion();
    }

    //creacion de excel
    public function getXLSNomina(Request $req)
    {
        $data_nomina = ReporteInscripcionesDAO::getNominaXML((object)$req->all());

        //se gregan los demas campos
        $data_nomina["body"]->each(function ($i) use ($data_nomina) {
            $data_nomina["headers"]->each(function ($j) use ($i, $data_nomina) {

                $data_temp = $data_nomina["body_extra"]->first(function ($k) use ($i, $j) {
                    return $k->uuid == $i->file_name && $k->tipo == $j->tipo &&  $k->concepto == $j->concepto;
                });
                $i->{$j->concepto} = $data_temp->importe ?? "";
            });
        });


        //crea hoja de calculo
        $spreadsheet = new Spreadsheet();

        //asigna los metadatos
        $spreadsheet->getProperties()
            ->setCreator("Sistemas PV")
            ->setLastModifiedBy("PV")
            ->setTitle("Template")
            ->setSubject("template")
            ->setDescription("es un template para realizar creacion reporte de nomina")
            ->setKeywords("1")
            ->setCategory("2");


        //obtiene la hoja con la que se tarabajara
        $sheet = $spreadsheet->getActiveSheet();
        //define t5 celdas se usan como emcabezados
        $sheet->setCellValue('A1', 'Folio');
        $sheet->setCellValue('B1', 'Forma Pago');
        $sheet->setCellValue('C1', 'Lugar Expedicion');
        $sheet->setCellValue('D1', 'Metodo Pago');
        $sheet->setCellValue('E1', 'Moneda');
        $sheet->setCellValue('F1', 'Serie');
        $sheet->setCellValue('G1', 'Subtotal');
        $sheet->setCellValue('H1', 'Total');
        $sheet->setCellValue('I1', 'Tipo Comprobante');
        $sheet->setCellValue('J1', 'Version');
        $sheet->setCellValue('K1', 'Nombre Emisor');
        $sheet->setCellValue('L1', 'Regimen Fiscal Emisor');
        $sheet->setCellValue('M1', 'Rfc Emisor');
        $sheet->setCellValue('N1', 'Domicilio Receptor');
        $sheet->setCellValue('O1', 'Nombre Receptor');
        $sheet->setCellValue('P1', 'Regimen Fiscal Receptor');
        $sheet->setCellValue('Q1', 'Rfc Receptor');
        $sheet->setCellValue('R1', 'Uso Cfdi Receptor');
        $sheet->setCellValue('S1', 'UUID');
        $sheet->setCellValue('T1', 'Fecha Comprobante');
        $sheet->setCellValue('U1', 'Fecha Timbre');
        $sheet->setCellValue('V1', 'Tipo Nomina');
        $sheet->setCellValue('W1', 'Fecha Pago');
        $sheet->setCellValue('X1', 'Fecha Inicial Pago');
        $sheet->setCellValue('Y1', 'Fecha Final Pago');
        // $sheet->setCellValue('E1', 'Numero dias Pagados');
        // $sheet->setCellValue('E1', 'total Percepciones');
        // $sheet->setCellValue('E1', 'Total Deducciones');
        // $sheet->setCellValue('E1', 'Total Otros Pagos');

        //se oculta la columan A, por que se encuentran los datos de los id de alumnos
        /////$sheet->getColumnDimension('A')->setVisible(false);
        //se oculta la fila o row 1, en ella se encuentran los datos de las preguntas
        // $sheet->getRowDimension('1')->setVisible(false);
        //se ahbilita la seguridad en el documento, falta especificar el nivel de proteccion
        $sheet->getProtection()->setSheet(true);


        //llenado de los datos medinate fromArray

        // dd($data_nomina["headers"]->map(function($i){return $i->concepto;})->toArray());
        //cabecera dinamica
        $sheet->fromArray($data_nomina["headers"]->map(function ($i) {
            return $i->concepto_header;
        })->toArray(), NULL, 'Z1');

        $sheet->fromArray($data_nomina["body"]->map(function ($i) {
            return collect($i)->flatten(2);
        })->toArray(), NULL, 'A2');

        //$sheet->fromArray([1, 2, 3, 4], NULL, 'A3');


        //obtiene el ultimo row insertado ejempolo 4
        $hRow = $sheet->getHighestRow();
        //obtiene la ultima columan insertada ejemplo Z
        $hColumn = $sheet->getHighestColumn();
        // se incrementa  ejemplo de "E" a "F"
        $hColumn++;

        //agregar estilos(background) en un rango de celdas de la B2:Z2 ejemplo
        $sheet->getStyle("A1:" . $sheet->getHighestColumn() . "1")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(Color::COLOR_BLACK);

        //agregar estilos(font) en un rango de celdas de la A1:Z1 ejemplo
        $sheet->getStyle("A1:" . $sheet->getHighestColumn() . "1")
            ->getFont()
            ->getColor()->setARGB(Color::COLOR_WHITE);


        //ajusta el tamañp de columan deade la B hasta la ultima insertada
        for ($col = 'A'; $col != $hColumn; ++$col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }



        //remover seguridad en celdas de preguntas 
        for ($row = 3; $row <= $hRow; ++$row) {
            //primer for lee las filas 1,2,3,4 etc

            for ($col2 = 'F'; $col2 != $hColumn; ++$col2) {
                //segundo for lee las columnas desde la F hasta la ultima insertada

                //se asigna el estilo para remover seguridad en celda
                $sheet->getCell($col2 . $row)->getStyle()->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
            }
        }


        $writer = new Xlsx($spreadsheet);

        // $writer->save('reporte.xlsx');
       // $img = file_get_contents("reporte.xlsx");

       $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
       $tempFile = $tempFile ?: __DIR__ . '/temp.xlsx';
       $writer->save($tempFile);

       $stream = fopen($tempFile, 'r+');
        
        return response(fread($stream,(int)fstat($stream)['size']))->header('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

}
