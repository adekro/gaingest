<?php

/**
 * 
 * Gain Studios - DDT, modifica testata
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20141207 file creato
 * 20150310 causale tabellata
 * 20190128 migrazione a jQuery e rimozione doc_ddtedit.server.php
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

//error_log(print_r($_POST, TRUE));

$acontosaldo = array('C'=>'Conto','S'=>'Saldo','X'=>'Non specificato');
$amezzo = array('E'=>'Cedente','S'=>'Cessionario','V'=>'Vettore');

// ajax
if (isset($_POST['dispatch']) and 'idcliente' == $_POST['dispatch']) {
	$a = array();
	$idcliente = $b2->normalizza($_POST['idcliente']);
	if ($idcliente > 0) {
		$r = $db->query("SELECT cognome,nome,ragsoc,indirizzo1,indirizzo2,citta,provincia,stato,destmerce1,destmerce2,destmerce3 FROM clienti WHERE idcliente='$idcliente'")->fetch_array();
		$conta = 1;
		if ('' == trim($r['ragsoc'])) {
			$a["cessionario$conta"] = "$r[cognome] $r[nome]";
		} else {
			$a["cessionario$conta"] = $r['ragsoc'];
		}
		$conta++;
		if ('' != trim($r['indirizzo1'])) {
			$a["cessionario$conta"] = $r['indirizzo1'];
			$conta++;
		} 
		if ('' != trim($r['indirizzo2'])) {
			$a["cessionario$conta"] = $r['indirizzo2'];
			$conta++;
		} 
		if ('' != trim("$r[citta] $r[provincia] $r[stato]")) {
			$a["cessionario$conta"] = "$r[citta] - $r[provincia] - $r[stato]";
			$conta++;
		} 
		$conta = 1;
		if ('' != trim($r['destmerce1'])) {
			$a["destinatario$conta"] = $r['destmerce1'];
			$conta++;
		} 
		if ('' != trim($r['destmerce2'])) {
			$a["destinatario$conta"] = $r['destmerce2'];
			$conta++;
		} 
		if ('' != trim($r['destmerce3'])) {
			$a["destinatario$conta"] = $r['destmerce3'];
			$conta++;
		} 
	}
	echo json_encode($a);
	die();
}


if (isset($_POST['idddt'])) {
	$idddt = $b2->normalizza($_POST['idddt']);
	$a = array();
	if (isset($_POST['xxx'])) {
		$db->query("DELETE FROM ddtrighe WHERE idddt='$idddt'");
		$db->query("DELETE FROM ddt WHERE idddt='$idddt'");
	} else {
		// anno
		list($dd,$mm,$yy) = explode('/', $_POST['data']);
		if ($yy < 2000) $yy += 2000;
		$a[] = $b2->campoSQL("anno", $yy);
		$a[] = $b2->campoSQL("data", $b2->dt2iso($_POST['data']));
		$a[] = $b2->campoSQL("idcliente", $_POST['idcliente']);
		$a[] = $b2->campoSQL("idcausaleddt", $_POST['idcausaleddt']);
		$a[] = $b2->campoSQL("contosaldo", $_POST['contosaldo']);
		$a[] = $b2->campoSQL("rifordine", $_POST['rifordine']);
		$a[] = $b2->campoSQL("mezzo", $_POST['mezzo']);
		$a[] = $b2->campoSQL("cessionario1", $_POST['cessionario1']);
		$a[] = $b2->campoSQL("cessionario2", $_POST['cessionario2']);
		$a[] = $b2->campoSQL("cessionario3", $_POST['cessionario3']);
		$a[] = $b2->campoSQL("cessionario4", $_POST['cessionario4']);
		$a[] = $b2->campoSQL("destinazione1", $_POST['destinazione1']);
		$a[] = $b2->campoSQL("destinazione2", $_POST['destinazione2']);
		$a[] = $b2->campoSQL("destinazione3", $_POST['destinazione3']);
		$a[] = $b2->campoSQL("destinazione4", $_POST['destinazione4']);
		$a[] = $b2->campoSQL("aspetto", $_POST['aspetto']);
		$a[] = $b2->campoSQL("colli", $_POST['colli']);
		$a[] = $b2->campoSQL("peso", $_POST['peso']);
		$a[] = $b2->campoSQL("porto", $_POST['porto']);
		if ($_POST['idddt'] > 0 ) {
			$db->query("UPDATE ddt SET " . implode(',', $a) . " WHERE idddt='$idddt'");
		} else {
			if ('' == trim($_POST['cessionario1'])) {
				header("Location: doc_ddt.php");
				die();
			} else {
				$a[] = "stato='L'";
				$db->query("INSERT INTO ddt SET " . implode(',', $a));
				$idddt = $db->insert_id;
			}
		}
		header("Location: doc_ddtrighe.php?idddt=$idddt");
		die();
	}
	header("Location: doc_ddt.php");
	die();
}


if (isset($_GET['idddt']) and is_numeric($_GET['idddt'])) {
	if (0 == $_GET['idddt']) {
		$r = array();
		$r['idddt'] = 0;
		$r['data'] = date('Y-n-d');
		$r['stato'] = 'L';
		$r['idcliente'] = 0;
		$r['vendita'] = 0;
		$r['idcausaleddt'] = 0;
		$r['contosaldo'] = 'X';
		$r['rifordine'] = '';
		$r['mezzo'] = 'S';
		$r['cessionario1'] = '';
		$r['cessionario2'] = '';
		$r['cessionario3'] = '';
		$r['cessionario4'] = '';
		$r['destinazione1'] = '';
		$r['destinazione2'] = '';
		$r['destinazione3'] = '';
		$r['destinazione4'] = '';
		$r['aspetto'] = 'A VISTA';
		$r['colli'] = '';
		$r['peso'] = '';
		$r['porto'] = 'FRANCO';
		$head = "Inserimento di un nuovo DDT";
	} else {
		$q = $db->query("SELECT * FROM ddt WHERE idddt='" . $b2->normalizza($_GET['idddt']) . "'");
		if ($q->num_rows > 0) {
			$r = $q->fetch_array();
			$head = "Modifica DDT $r[numero]/$r[anno]";
		} else {
			header('Location: home.php');
			die();
		}
	}
} else {
	header('Location: home.php');
	die();
}

intestazione($head);

echo "\n<form method='post' action='doc_ddtedit.php'>";
echo "\n<input type='hidden' name='idddt' id='idddt' value=\"$r[idddt]\" />";

echo "\n<table border='0' align='center'>";
// data
echo $b2->rigaEdit("Data:", $b2->inputText('data', $b2->dt2ita($r['data'], B2_DT_ZEROFILL), 11, 10, 'data', '', B2_IT_CENTER));
// idcliente
echo $b2->rigaEdit('Cliente:', $b2->inputSelect('idcliente', $b2->creaArraySelect("SELECT idcliente,CONCAT(ragsoc,' ',cognome,' ',nome) FROM clienti ORDER BY ragsoc,cognome"), $r['idcliente']));
// idcausaleddt
echo $b2->rigaEdit('Causale del trasporto:', $b2->inputSelect('idcausaleddt', $b2->creaArraySelect("SELECT idcausaleddt,causaleddt FROM causaleddt ORDER BY causaleddt"), $r['idcausaleddt']));
// rifordine
echo $b2->rigaEdit('Riferimenti ordine:', $b2->inputText('rifordine', $r['rifordine'], 50, 250));
// contosaldo
echo "\n<tr><td align='right'><b>Conto o saldo:</b></td>";
echo "<td align='left'><select name='contosaldo' id='contosaldo'/>";
foreach ($acontosaldo as $contosaldo=>$contosaldodesc) {
	echo "<option value='$contosaldo'";
	if ($contosaldo == $r['contosaldo']) echo ' selected';
	echo ">$contosaldodesc</option>";
}
echo "</select></td>";
// mezzo
echo "\n<tr><td align='right'><b>Trasporto a mezzo:</b></td>";
echo "<td align='left'><select name='mezzo' id='mezzo'/>";
foreach ($amezzo as $mezzo=>$mezzodesc) {
	echo "<option value='$mezzo'";
	if ($mezzo == $r['mezzo']) echo ' selected';
	echo ">$mezzodesc</option>";
}
echo "</select></td>";
// cessionario
echo $b2->rigaEdit('Cessionario:', $b2->inputText('cessionario1', $r['cessionario1'], 50, 250) . '<br/>' .
                                   $b2->inputText('cessionario2', $r['cessionario2'], 50, 250) . '<br/>' .
                                   $b2->inputText('cessionario3', $r['cessionario3'], 50, 250) . '<br/>' .
                                   $b2->inputText('cessionario4', $r['cessionario4'], 50, 250), B2_ED_VTOP);
// destinazione
echo $b2->rigaEdit('Destinazione:', $b2->inputText('destinazione1', $r['destinazione1'], 50, 250) . '<br/>' .
                                    $b2->inputText('destinazione2', $r['destinazione2'], 50, 250) . '<br/>' .
                                    $b2->inputText('destinazione3', $r['destinazione3'], 50, 250) . '<br/>' .
                                    $b2->inputText('destinazione4', $r['destinazione4'], 50, 250), B2_ED_VTOP);
// aspetto
echo $b2->rigaEdit('Aspetto esteriore dei beni:', $b2->inputText('aspetto', $r['aspetto'], 50, 250));
// colli
echo $b2->rigaEdit('Numero di colli:', $b2->inputText('colli', $r['colli'], 50, 250));
// peso
echo $b2->rigaEdit('Peso:', $b2->inputText('peso', $r['peso'], 50, 250));
// porto
echo $b2->rigaEdit('Porto:', $b2->inputText('porto', $r['porto'], 50, 250));
if ($r['idddt'] > 0) {
	// delete
	echo "\n<tr><td align='right'><b>Elimina dall'archivio:</b></td>";
	echo "<td align='left'><input type='checkbox' name='xxx'/><br/>";
}
// submit
$x = 0 == $r['idddt'] ? 'Aggiungi questo DDT' : 'Aggiorna questo DDT';
echo "\n<tr><td align='center' colspan='2'><input type='submit' value=\"$x\"/></td></tr>";
echo "\n</table>";
echo "\n</form>";

?>
<script>
	$.datepicker.setDefaults( $.datepicker.regional[ "it" ] );
	$(function() {
	  $( "#data" ).datepicker({
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
	    $( "#data" ).datepicker( "option", "dateFormat", $( this ).val() );
	  });
	});

  $(document).ready(function() {
		// cambio cliente
		$("#idcliente").change(function(){
			// valore della combo
	    var idcliente = $(this).find('option:selected').val();
			$.post("doc_ddtedit.php", 
				{dispatch: "idcliente", idcliente: idcliente})
				.done(function( data ) {
					var json = $.parseJSON(data);
					$("#cessionario1").val(json.cessionario1);
					$("#cessionario2").val(json.cessionario2);
					$("#cessionario3").val(json.cessionario3);
					$("#cessionario4").val(json.cessionario4);
					$("#destinatario1").val(json.destinatario1);
					$("#destinatario2").val(json.destinatario2);
					$("#destinatario3").val(json.destinatario3);
					$("#destinatario4").val(json.destinatario4);
				});
		});
	});
  
</script>

<?php

piede();

### END OIF FILE ###