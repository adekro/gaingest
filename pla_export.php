<?php

/**
 * 
 * Gain Studios - Planning, esportazione dati
 * Copyright (C) 2019-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20170213 file creato
 * 20180928 PhpSpreadsheet
 * 20190114 fix data
 * 20220903 nuovo PHPSpreadsheet, estrazione in ordine di data
 *
 */

define('SOUNDPARK', true);
require('global.php');

// PhpSpreadsheet
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
	$objPHPExcel->getProperties()->setTitle("Esportazione planning dal $datada al $dataa");
	$objPHPExcel->getProperties()->setSubject("Esportazione planning dal $datada al $dataa");
	header('Content-Type: application/vnd.ms-excel');
	header("Content-Disposition: attachment; filename=\"planning.xls\"");
	header('Cache-Control: max-age=0');
	header("Pragma: public");
	header("Content-Description: PHP Generated Data");
	// questi tre header servono per Internet Explorer
	// gli header sono stati presi dai sorgenti di Squirrel Mail, file functions/mime.php
	header ("Cache-Control: no-store, max-age=0, no-cache, must-revalidate"); // HTTP/1.1
	header ("Cache-Control: post-check=0, pre-check=0", false);
	header ("Cache-Control: private");
	
	$objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A1', 'Stato')
              ->setCellValue('B1', 'Data')
              ->setCellValue('C1', 'Cliente')
              ->setCellValue('D1', 'Titolo')
              ->setCellValue('E1', 'Inizio')
              ->setCellValue('F1', 'Fine')
              ->setCellValue('G1', 'Luogo')
              ->setCellValue('H1', 'Note amministrative')
              ->setCellValue('I1', 'Dettaglio');
	$sheet = $objPHPExcel->getActiveSheet();	
	$sheet->setTitle("Planning");
	
	// data
	$whdata = "(planning.data>='" . $b2->dt2iso($datada) . "' AND planning.data<='" . $b2->dt2iso($dataa) . "')";
	// idcliente
	if ($_POST['idcliente'] != 0) {
		$whcliente = "planning.idcliente='" . $b2->normalizza($_POST['idcliente']) . "'";
	} else {
		$whcliente = '1=1';
	}
	// stati
	$whs = array();
	foreach ($_POST['p'] as $idplanningstato=>$stato) $whs[] = "planning.idplanningstato='$idplanningstato'";

	$q = $db->query("SELECT planning.data,planning.titolo,planning.orainizio,planning.orafine,planning.luogo,planning.noteadmin,planning.dettaglio,
	                        planningstato.planningstato,
	                        clienti.ragsoc,clienti.cognome,clienti.nome
	                 FROM planning
	                 JOIN clienti ON clienti.idcliente=planning.idcliente
	                 JOIN planningstato ON planningstato.idplanningstato=planning.idplanningstato
	                 WHERE $whcliente AND $whdata AND (" . implode(' OR ', $whs) . ")
	                 ORDER BY planning.data,planning.orainizio");

	$row = 2;
	while ($r = $q->fetch_array()) {
		$sheet->setCellValueByColumnAndRow(1, $row, $r['planningstato']);
		list($aa,$mm,$gg) = explode('-', $r['data']);
		$sheet->setCellValueByColumnAndRow(2, $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(gmmktime(0,0,0,$mm,$gg,$aa)));
		$cliente = trim($r['ragsoc']) == '' ? trim($r['cognome']) . ' ' . trim($r['nome']) : trim($r['ragsoc']);
		$sheet->setCellValueByColumnAndRow(3, $row, $cliente);
		$sheet->setCellValueByColumnAndRow(4, $row, $r['titolo']);
		$sheet->setCellValueByColumnAndRow(5, $row, $r['orainizio']);
		$sheet->setCellValueByColumnAndRow(6, $row, $r['orafine']);
		$sheet->setCellValueByColumnAndRow(7, $row, $r['luogo']);
		$sheet->setCellValueByColumnAndRow(8, $row, $r['noteadmin']);
		$sheet->setCellValueByColumnAndRow(9, $row, $r['dettaglio']);
		$row++;
	}

	// formatto
	$sheet->getStyle('A1:I1')->getFont()->setBold(true);
	$sheet->getStyle("A2:A$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
	$sheet->getStyle("B2:B$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY);
	$sheet->getStyle("C2:I$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
		
	// autosize
	$sheet->getColumnDimension('A')->setAutoSize(true);
	$sheet->getColumnDimension('B')->setAutoSize(true);
	$sheet->getColumnDimension('C')->setAutoSize(true);
	$sheet->getColumnDimension('D')->setAutoSize(true);
	$sheet->getColumnDimension('E')->setAutoSize(true);
	$sheet->getColumnDimension('F')->setAutoSize(true);
	$sheet->getColumnDimension('G')->setAutoSize(true);
	$sheet->getColumnDimension('H')->setAutoSize(true);
	$sheet->getColumnDimension('I')->setAutoSize(true);

	// rendo attivo il primo foglio e la cella in alto a sinistra
	$objPHPExcel->setActiveSheetIndex(0);	
	$objPHPExcel->getActiveSheet()->setSelectedCell('A1');
		
	// Redirect output to a client’s web browser
	$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xls');
	$objWriter->save('php://output');

	die();
}

intestazione("Esportazione planning");

echo "\n<form action='pla_export.php' method='post'>";
echo "\n<table border='0' align='center'>";

// datada
echo $b2->rigaEdit("Dal:", $b2->inputText('datada', $datada, 10, 11));
// dataa
echo $b2->rigaEdit("Al:", $b2->inputText('dataa', $dataa, 10, 11));
// cliente
$acli = array(0=>'Tutti');
$qq = $db->query("SELECT DISTINCT planning.idcliente,clienti.ragsoc,clienti.cognome,clienti.nome
                  FROM planning
                  JOIN clienti ON planning.idcliente=clienti.idcliente
                  ORDER BY clienti.ragsoc");
while ($rr = $qq->fetch_array()) {
	$acli[$rr['idcliente']] = trim($rr['ragsoc']) == '' ? trim($rr['cognome']) . ' ' . trim($rr['nome']) : trim($rr['ragsoc']);
}
echo $b2->rigaEdit('Cliente:', $b2->inputSelect('idcliente', $acli));
// stati
$a = array();
$q = $db->query("SELECT DISTINCT planning.idplanningstato,planningstato.planningstato
                 FROM planning
                 JOIN planningstato ON planning.idplanningstato=planningstato.idplanningstato
                 ORDER BY planningstato.planningstato");
while ($r = $q->fetch_array()) {
	$a[] = "<label id='l$r[idplanningstato]'>" . $b2->inputCheck("p[" . $r['idplanningstato'] . "]", true) . " $r[planningstato]</label>";
}
echo $b2->rigaEdit('Stati:', implode('<br/>', $a), B2_ED_VTOP);

echo "\n<tr><td align='center' colspan='2'><input type='submit' value='Esporta queste voci del planning'/></td></tr>";
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