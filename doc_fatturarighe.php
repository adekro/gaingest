<?php

/**
 * 
 * Gain Studios - fattura, modifica dettaglio
 * Copyright (C) 2015-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20150522 file creato
 * 20190202 migrazione a jQuery e rimozione doc_fatturerighe.server.php
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

function displayfatturarighe($idfattura) {
	global $db, $b2;
	$retval = '';
	$q = $db->query("SELECT * FROM fatturarighe WHERE idfattura='$idfattura' ORDER BY riga");
	if ($q->num_rows > 0) {
		$retval .= "<table border='0' align='center'>";
		while ($r = $q->fetch_array()) {
			$retval .= "<tr>";
			$retval .= "<td align='right'>$r[riga]</td>";
			$retval .= "<td align='right'>$r[codice]</td>";
			$retval .= "<td align='left'>$r[descrizione]</td>";
			$retval .= "<td align='right'>$r[qta]</td>";
			$retval .= "<td align='right'>$r[prezzo]</td>";
			$retval .= "<td align='right'>" . $r['qta'] * $r['prezzo'] . "</td>";
			$retval .= "<td align='center'><input type='button' value='Cancella' onClick=\"return rimuoviriga($r[idfatturarighe], $idfattura);\"/></td>";
			$retval .= "</tr>";
		}
		$retval .= "</table>";
	}
	return $retval;
}

// ajax
if (isset($_POST['dispatch']) and 'rimuoviriga' == $_POST['dispatch']) {
	$idfatturarighe = $b2->normalizza($_POST['idfatturarighe']);
	$idfattura = $b2->normalizza($_POST['idfattura']);
	$db->query("DELETE FROM fatturarighe WHERE idfatturarighe='$idfatturarighe'");
	echo displayfatturarighe($idfattura);
	die();
}
if (isset($_POST['dispatch']) and 'codice' == $_POST['dispatch']) {
	$codice = $b2->normalizza($_POST['codice']);
	$q = $db->query("SELECT articolo,vendita FROM articoli WHERE codice='$codice'");
	if ($q->num_rows > 0 ) {
		$r = $q->fetch_array();
		echo json_encode($r);
	} else {
		echo "NOFOUND";
	}
	die();
}
if (isset($_POST['dispatch']) and 'add' == $_POST['dispatch']) {
	$idfattura = $b2->normalizza($_POST['idfattura']);
	$a = array();
	$riga = is_numeric($_POST['riga']) ? $_POST['riga'] : 10;
	$a[] = $b2->campoSQL("riga", $riga);
	$a[] = $b2->campoSQL("idfattura", $_POST['idfattura']);
	$a[] = $b2->campoSQL("prezzo", $_POST['prezzo']);
	$a[] = $b2->campoSQL("codice", $_POST['codice']);
	$a[] = $b2->campoSQL("descrizione", $_POST['descrizione']);
	$a[] = $b2->campoSQL("qta", $_POST['qta']);
	if ('' != trim($_POST['descrizione'])) $db->query("INSERT INTO fatturarighe SET " . implode(',', $a));
	echo displayfatturarighe($idfattura);
	die();
}


if (isset($_GET['idfattura']) and is_numeric($_GET['idfattura'])) {
	$idfattura = $b2->normalizza($_GET['idfattura']);
	$qt = $db->query("SELECT idfattura,data FROM fattura WHERE idfattura='$idfattura'");
	if ($qt->num_rows > 0) {
		$rt = $qt->fetch_array();
		intestazione("Righe fattura del " . $b2->dt2ita($rt['data']));
		echo "\n<span id='elenco'>" . displayfatturarighe($idfattura) . "</span>";

		echo "\n<form method='post' action='doc_fatturerighe.php'>";
		echo $b2->inputHidden('idfattura', $idfattura);
		echo "\n<table border='0' align='center'>";

		// riga
		$qu = $db->query("SELECT riga FROM  fatturarighe WHERE idfattura='$idfattura' ORDER BY riga DESC LIMIT 1");
		if ($qu->num_rows > 0) {
			$ru = $qu->fetch_array();
			$riga = $ru['riga'] + 10;
		} else {
			$riga = 10;
		}
		echo $b2->rigaEdit("Riga:", $b2->inputText('riga', $riga, 4));
		// codice
		echo $b2->rigaEdit("Codice:", $b2->inputText('codice', '', 20, 20));
		// descrizione
		echo $b2->rigaEdit("Descrizione:", $b2->inputText('descrizione', '', 50, 250));
		// prezzo
		echo $b2->rigaEdit("Prezzo:", $b2->inputText('prezzo', '', 10));
		// qta
		echo $b2->rigaEdit("Quantit&agrave;:", $b2->inputText('qta', 1, 6));
		// submit
		echo "\n<tr><td align='center' colspan='2'><input type='button' id='btnadd' value='Aggiungi questa riga'/></td></tr>";
		// info
		echo "\n<tr><td align='center' colspan='2'>Se una descrizione inizia con <b>\</b> verr&agrave; trattata come riga di messaggio e non riga di articolo</td></tr>";
		echo "\n</table>";
		echo "\n</form>";
		
		?>
		<script>
			function rimuoviriga(idfatturarighe, idfattura) {
				$.post("doc_fatturarighe.php", 
					{dispatch: "rimuoviriga", idfatturarighe: idfatturarighe, idfattura: idfattura})
					.done(function( data ) {
						$("#elenco").html(data);	
						$("#codice").focus();	
					});
			};
		  $(document).ready(function() {
  			$('#btnadd').click(function() {
			    var idfattura = $("#idfattura").val();
			    var riga = $("#riga").val();
			    var codice = $("#codice").val();
			    var descrizione = $("#descrizione").val();
			    var prezzo = $("#prezzo").val();
			    var qta = $("#qta").val();
					$.post("doc_fatturarighe.php", 
						{dispatch: "add", codice: codice, idfattura: idfattura, prezzo: prezzo, descrizione: descrizione, qta: qta, riga: riga})
						.done(function( data ) {
							$("#elenco").html(data);	
							$("#codice").focus();	
						});
				});
				// cambio codice
				$("#codice").change(function(){
			    var codice = $("#codice").val();
					$.post("doc_fatturarighe.php", 
						{dispatch: "codice", codice: codice})
						.done(function( data ) {
							if ("NOFOUND" != data) {
								var json = $.parseJSON(data);
								$("#descrizione").val(json.articolo);
								$("#prezzo").val(json.vendita);
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
	header('Location: doc_fattura.php');
	die();
}
header('Location: doc_fattura.php');


### END OIF FILE ###