<?php

/**
 * 
 * Gain Studios - DDT, modifica dettaglio
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20141207 file creato
 * 20190202 migrazione a jQuery e rimozione doc_ddtrighe.server.php
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

//error_log(print_r($_POST, TRUE));

$acontosaldo = array('C'=>'Conto','S'=>'Saldo','X'=>'Non specificato');
$amezzo = array('E'=>'Cedente','S'=>'Cessionario','V'=>'Vettore');

function displayDDTrighe($idddt) {
	global $db;
	$retval = '';
	$q = $db->query("SELECT * FROM ddtrighe WHERE idddt='$idddt' ORDER BY riga");
	if ($q->num_rows > 0) {
		$retval .= "<table border='0' align='center'>";
		while ($r = $q->fetch_array()) {
			$retval .= "<tr>";
			$retval .= "<td align='right'>$r[riga]</td>";
			$retval .= "<td align='right'>$r[codice]</td>";
			$retval .= "<td align='left'>$r[descrizione]</td>";
			$retval .= "<td align='right'>$r[qta]</td>";
			$retval .= "<td align='center'><input type='button' value='Cancella' onClick=\"return rimuoviriga($r[idddtrighe], $idddt);\"/></td>";
			$retval .= "</tr>";
		}
		$retval .= "</table>";
	}
	return $retval;
}

// ajax
if (isset($_POST['dispatch']) and 'rimuoviriga' == $_POST['dispatch']) {
	$idddtrighe = $b2->normalizza($_POST['idddtrighe']);
	$idddt = $b2->normalizza($_POST['idddt']);
	$db->query("DELETE FROM ddtrighe WHERE idddtrighe='$idddtrighe'");
	echo displayDDTrighe($idddt);
	die();
}
if (isset($_POST['dispatch']) and 'codice' == $_POST['dispatch']) {
	$codice = $b2->normalizza($_POST['codice']);
	$q = $db->query("SELECT articolo FROM articoli WHERE codice='$codice'");
	if ($q->num_rows > 0 ) {
		$r = $q->fetch_array();
		echo $r['articolo'];
		
	} else {
		echo "NOFOUND";
	}
	die();
}
if (isset($_POST['dispatch']) and 'add' == $_POST['dispatch']) {
	$idddt = $b2->normalizza($_POST['idddt']);
	$a = array();
	$riga = is_numeric($_POST['riga']) ? $_POST['riga'] : 10;
	$a[] = $b2->campoSQL("riga", $riga);
	$a[] = $b2->campoSQL("idddt", $idddt);
	$a[] = $b2->campoSQL("codice", $_POST['codice']);
	$a[] = $b2->campoSQL("descrizione", $_POST['descrizione']);
	$a[] = $b2->campoSQL("qta", $_POST['qta']);
	if ('' != trim($_POST['descrizione'])) $db->query("INSERT INTO ddtrighe SET " . implode(',', $a));
	echo displayDDTrighe($idddt);
	die();
}


if (isset($_GET['idddt']) and is_numeric($_GET['idddt'])) {
	$idddt = $b2->normalizza($_GET['idddt']);
	$qt = $db->query("SELECT idddt,data FROM ddt WHERE idddt='$idddt'");
	if ($qt->num_rows > 0) {
		$rt = $qt->fetch_array();
		intestazione("Righe DDT del " . $b2->dt2ita($rt['data']));
		echo "\n<span id='elenco'>" . displayDDTrighe($rt['idddt']) . "</span>";

		echo "\n<form method='post' action='doc_ddtrighe.php'>";
		echo $b2->inputHidden('idddt', $rt['idddt']);
		echo "\n<table border='0' align='center'>";

		// riga
		$qu = $db->query("SELECT riga FROM  ddtrighe WHERE idddt='$idddt' ORDER BY riga DESC LIMIT 1");
		if ($qu->num_rows > 0) {
			$ru = $qu->fetch_array();
			$riga = $ru['riga'] + 10;
		} else {
			$riga = 10;
		}
		echo $b2->rigaEdit("Riga:", $b2->inputText('riga', $riga, 4, 4));
		// codice
		echo $b2->rigaEdit("Codice:", $b2->inputText('codice', '', 20, 20));
		// descrizione
		echo $b2->rigaEdit("Descrizione:", $b2->inputText('descrizione', '', 50, 250));
		// qta
		echo $b2->rigaEdit("Quantit&agrave;:", $b2->inputText('qta', '', 6, 6));
		// submit
		echo "\n<tr><td align='center' colspan='2'><input type='button' id='btnadd' value='Aggiungi questa riga'/></td></tr>";
		// info
		echo "\n<tr><td align='center' colspan='2'>Se una descrizione inizia con <b>\</b> verr&agrave; trattata come riga di messaggio e non riga di articolo</td></tr>";
		echo "\n</table>";
		echo "\n</form>";

		?>
		<script>
			function rimuoviriga(idddtrighe, idddt) {
				$.post("doc_ddtrighe.php", 
					{dispatch: "rimuoviriga", idddtrighe: idddtrighe, idddt: idddt})
					.done(function( data ) {
						$("#elenco").html(data);	
						$("#codice").focus();	
					});
			};
		  $(document).ready(function() {
  			$('#btnadd').click(function() {
			    var idddt = $("#idddt").val();
			    var riga = $("#riga").val();
			    var codice = $("#codice").val();
			    var descrizione = $("#descrizione").val();
			    var qta = $("#qta").val();
					$.post("doc_ddtrighe.php", 
						{dispatch: "add", codice: codice, idddt: idddt, descrizione: descrizione, qta: qta, riga: riga})
						.done(function( data ) {
							$("#elenco").html(data);	
							$("#codice").focus();	
						});
				});
				// cambio codice
				$("#codice").change(function(){
			    var codice = $("#codice").val();
					$.post("doc_ddtrighe.php", 
						{dispatch: "codice", codice: codice})
						.done(function( data ) {
							if ("NOFOUND" != data) {
								$("#descrizione").val(data);	
								$("#qta").focus();	
							}
						});
				});
			});
		  
		</script>

		<?php

		piede();
		die();
	}
	header('Location: doc_ddt.php');
	die();
}
header('Location: doc_ddt.php');


### END OIF FILE ###