<?php

/**
 * 
 * Gain Studios - Riparazioni, esportazione dati
 * Copyright (C) 2016-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20161129 file creato
 * 20180928 PhpSpreadsheet
 * 20190114 fix data
 *
 */

define('SOUNDPARK', true);
require('global.php');

// PhpSpreadsheet
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

// filtro che puo' arrivare un po' da ogni parte
$datada = date('01/m/Y');
$dataa = date('t/m/Y');
if (isset($_POST['datada'])){
	list($gg,$mm,$aa) = explode('/', $_POST['datada']);
	if (checkdate($mm, $gg, $aa)) {
		$datada = "$gg/$mm/$aa";
	}
}
if (isset($_POST['dataa'])){
	list($gg,$mm,$aa) = explode('/', $_POST['dataa']);
	if (checkdate($mm, $gg, $aa)) {
		$dataa = "$gg/$mm/$aa";
	}
}

// esportazione
if (isset($_POST['datada'])) {
	$objPHPExcel = new Spreadsheet();
	// Set document properties
	$objPHPExcel->getProperties()->setCreator("GainGest by B2TEAM S.r.l. https://b2team.com");
	$objPHPExcel->getProperties()->setLastModifiedBy("GainGest by B2TEAM S.r.l. https://b2team.com");
	$objPHPExcel->getProperties()->setTitle("Esportazione riparazioni dal $datada al $dataa");
	$objPHPExcel->getProperties()->setSubject("Esportazione riparazioni dal $datada al $dataa");
	header('Content-Type: application/vnd.ms-excel');
	header("Content-Disposition: attachment; filename=\"riparazioni.xls\"");
	header('Cache-Control: max-age=0');
	header("Pragma: public");
	header("Content-Description: PHP Generated Data");
	// questi tre header servono per Internet Explorer
	// gli header sono stati presi dai sorgenti di Squirrel Mail, file functions/mime.php
	header ("Cache-Control: no-store, max-age=0, no-cache, must-revalidate"); // HTTP/1.1
	header ("Cache-Control: post-check=0, pre-check=0", false);
	header ("Cache-Control: private");
	
	$objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A1', 'Cliente')
              ->setCellValue('B1', 'Creazione')
              ->setCellValue('C1', 'Riparatore')
              ->setCellValue('D1', 'Aggiornamento')
              ->setCellValue('E1', 'Stato')
              ->setCellValue('F1', 'Compenso')
              ->setCellValue('G1', 'Prezzo')
              ->setCellValue('H1', 'Prodotto');
	$sheet = $objPHPExcel->getActiveSheet();	
	$sheet->setTitle("Riparazioni");
	
	// data
	if ('cre' == $_POST['tipodata']) {
		$whdata = "riparazione.dcreazione>='" . $b2->dt2iso($datada) . "' AND riparazione.dcreazione<='" . $b2->dt2iso($dataa) . "'";
	} else {
		$whdata = "riparazione.daggiornamento>='" . $b2->dt2iso($datada) . "' AND riparazione.daggiornamento<='" . $b2->dt2iso($dataa) . "'";
	}
	// stati
	$whs = array();
	foreach ($_POST['p'] as $idriparazionestato=>$stato) $whs[] = "riparazionestato.idriparazionestato='$idriparazionestato'";

	$q = $db->query("SELECT riparazione.idriparazione,riparazione.dcreazione,riparazione.prodotto,riparazione.compenso,riparazione.prezzo,riparazione.daggiornamento,
	                        clienti.ragsoc,
	                        riparazionestato.riparazionestato,
	                        utente.login
	                 FROM riparazione
	                 JOIN clienti ON clienti.idcliente=riparazione.idcliente
	                 JOIN riparazionestato ON riparazionestato.idriparazionestato=riparazione.idriparazionestato
	                 JOIN utente ON utente.idutente=riparazione.idutente
	                 WHERE $whdata AND (" . implode(' OR ', $whs) . ")");

	$row = 2;
	while ($r = $q->fetch_array()) {
		$sheet->setCellValueByColumnAndRow(1, $row, $r['ragsoc']);
		list($aa,$mm,$gg) = explode('-', $r['dcreazione']);
		$sheet->setCellValueByColumnAndRow(2, $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(gmmktime(0,0,0,$mm,$gg,$aa)));
		$sheet->setCellValueByColumnAndRow(3, $row, $r['login']);
		list($aa,$mm,$gg) = explode('-', $r['daggiornamento']);
		$sheet->setCellValueByColumnAndRow(4, $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(gmmktime(0,0,0,$mm,$gg,$aa)));
		$sheet->setCellValueByColumnAndRow(5, $row, $r['riparazionestato']);
		$sheet->setCellValueByColumnAndRow(6, $row, $r['compenso'] / 100);
		$sheet->setCellValueByColumnAndRow(7, $row, $r['prezzo'] / 100);
		$sheet->setCellValueByColumnAndRow(8, $row, $r['prodotto']);
		$row++;
	}

	// formatto
	$sheet->getStyle('A1:H1')->getFont()->setBold(true);
	$sheet->getStyle("A2:A$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
	$sheet->getStyle("B2:B$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY);
	$sheet->getStyle("C2:C$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
	$sheet->getStyle("D2:D$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY);
	$sheet->getStyle("E2:E$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
	$sheet->getStyle("F2:G$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	$sheet->getStyle("H2:H$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
		
	// autosize
	$sheet->getColumnDimension('A')->setAutoSize(true);
	$sheet->getColumnDimension('B')->setAutoSize(true);
	$sheet->getColumnDimension('C')->setAutoSize(true);
	$sheet->getColumnDimension('D')->setAutoSize(true);
	$sheet->getColumnDimension('E')->setAutoSize(true);
	$sheet->getColumnDimension('F')->setAutoSize(true);
	$sheet->getColumnDimension('G')->setAutoSize(true);
	$sheet->getColumnDimension('H')->setAutoSize(true);

	// rendo attivo il primo foglio e la cella in alto a sinistra
	$objPHPExcel->setActiveSheetIndex(0);	
	$objPHPExcel->getActiveSheet()->setSelectedCell('A1');
		
	// Redirect output to a client’s web browser
	$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xls');
	$objWriter->save('php://output');

	die();
}

intestazione("Esportazione riparazioni");

echo "\n<form action='rip_export.php' method='post'>";
echo "\n<table border='0' align='center'>";

// datada
echo $b2->rigaEdit("Dal:", $b2->inputText('datada', $datada, 10, 11));
// dataa
echo $b2->rigaEdit("Al:", $b2->inputText('dataa', $dataa, 10, 11));
// tipo di dataa
echo $b2->rigaEdit('La data &egrave; riferita a:', "<label id='a1'><input type='radio' name='tipodata' value='cre' checked /> creazione</label>
                                                    <label id='a2'><input type='radio' name='tipodata' value='agg' /> aggiornamento</label>");
// stati
$a = array();
$q = $db->query("SELECT DISTINCT riparazionestato.riparazionestato,riparazionestato.idriparazionestato
                 FROM riparazione
                 JOIN riparazionestato ON riparazionestato.idriparazionestato=riparazione.idriparazionestato
                 ORDER BY riparazionestato.riparazionestato");
while ($r = $q->fetch_array()) {
	$a[] = "<label id='l$r[idriparazionestato]'>" . $b2->inputCheck("p[" . $r['idriparazionestato'] . "]", true) . " $r[riparazionestato]</label>";
}
echo $b2->rigaEdit('Stati:', implode('<br/>', $a), B2_ED_VTOP);

echo "\n<tr><td align='center' colspan='2'><input type='submit' value='Esporta queste riparazioni'/></td></tr>";
echo "\n</table></form>";

?>
<script>
	// date
  $.datepicker.setDefaults( $.datepicker.regional[ "it" ] );
  $(function() {
    $( "#datada" ).datepicker({
    	changeYear: true,
    	yearRange: "-2:+2",
      showButtonPanel: true,
      showOtherMonths: true,
      selectOtherMonths: true,
      showButtonPanel: true,
      dateFormat: "dd/mm/yy",
      showOn: "button",
      buttonImage: "static/iconcalendar.gif",
      buttonImageOnly: true,
      buttonText: "Scegli la data attraverso il calendario"
    });
    $( "#dataa" ).datepicker({
    	yearRange: "-2:+2",
    	changeYear: true,
      showButtonPanel: true,
      showOtherMonths: true,
      selectOtherMonths: true,
      showButtonPanel: true,
      dateFormat: "dd/mm/yy",
      showOn: "button",
      buttonImage: "static/iconcalendar.gif",
      buttonImageOnly: true,
      buttonText: "Scegli la data attraverso il calendario"
    });
    $( "#format" ).change(function() {
      $( "#datada" ).datepicker( "option", "dateFormat", $( this ).val() );
      $( "#dataa" ).datepicker( "option", "dateFormat", $( this ).val() );
    });
  }); // function
</script>

<?php

piede();

// ### END OF FILE ###