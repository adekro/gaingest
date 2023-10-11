<?php

/**
 * 
 * Gain Studios - Modifica riparazioni
 * Copyright (C) 2016-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20160922 file creato
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

if (isset($_POST['idriparazione'])) {
	$idriparazione = $b2->normalizza($_POST['idriparazione']);
	// se devo cancellare, non faccio molto cinema
	if (isset($_POST['xxx1']) and isset($_POST['xxx2']) and isset($_POST['xxx3'])) {
		$db->query("DELETE FROM riparazione WHERE idriparazione='$idriparazione'");
	} else {
		$a = array();
		$a[] = $b2->campoSQL('idriparazionestato', $_POST['idriparazionestato']);
		$a[] = $b2->campoSQL('idcliente', $_POST['idcliente']);
		$a[] = $b2->campoSQL('prodotto', $_POST['prodotto']);
		$a[] = $b2->campoSQL('problema', $_POST['problema']);
		$a[] = $b2->campoSQL('note', $_POST['note']);
		$a[] = $b2->campoSQL('idutente', $_POST['idutente']);
		$a[] = $b2->campoSQL('daggiornamento', $b2->dt2iso($_POST['daggiornamento']));
		// l'input formattato lascia i caratteri di formattazione
		$compenso = str_replace(',', '', $_POST['compenso']);
		$compenso = str_replace('.', '', $compenso);
		$a[] = $b2->campoSQL('compenso', $compenso);
		$prezzo = str_replace(',', '', $_POST['prezzo']);
		$prezzo = str_replace('.', '', $prezzo);
		$a[] = $b2->campoSQL('prezzo', $prezzo);
		// controlli solo per i nuovi record
		if (0 == $_POST['idriparazione'] and '' != trim($_POST['prodotto'])) {
			$a[] = "dcreazione=CURDATE()";
			$db->query("INSERT INTO riparazione SET " . implode(',', $a));
			$idriparazione = $db->insert_id;
			logriparazione($idriparazione, "Nuova riparazione");
		} else {
			$db->query("UPDATE riparazione SET " . implode(',', $a) . " WHERE idriparazione='$idriparazione'");
			logriparazione($idriparazione, "Dati modificati");
		}
		// notifica?
		if (isset($_POST['notifica'])) {
			notificariparazione($idriparazione);
		}
	}
	header('Location: rip_riparazioni.php');
	die();
}

if (isset($_GET['idriparazione'])) {
	if (is_numeric($_GET['idriparazione'])) {
		if (0 == $_GET['idriparazione']) {
			$r = array();
			$r['idriparazione'] = 0;
			$r['idcliente'] = 0;
			$r['prodotto'] = '';
			$r['problema'] = '';
			$r['note'] = '';
			$r['idutente'] = 0;
			$r['compenso'] = 0;
			$r['prezzo'] = 0;
			$r['daggiornamento'] = date('Y-m-d');
			$rr = $db->query("SELECT idriparazionestato FROM riparazionestato WHERE isnew='1'")->fetch_array();
			$r['idriparazionestato'] = $rr['idriparazionestato'];
			$head = "Inserimento di una nuova riparazione";
		} else {
			$q = $db->query("SELECT * FROM riparazione WHERE idriparazione='" . $b2->normalizza($_GET['idriparazione']) . "'");
			if ($q->num_rows > 0) {
				$r = $q->fetch_array();
				$head = "Modifica $r[prodotto]";
			} else {
				header('Location: home.php');
				die();
			}
		}
	} else {
		header('Location: home.php');
		die();
	}
} else {
	header('Location: home.php');
	die();
}

intestazione($head);

echo "\n<form id='coll' method='post' action='rip_riparazioniedit.php'>";
echo $b2->inputHidden('idriparazione', $r['idriparazione']);

echo "\n<table border='0' align='center'>";
echo $b2->rigaEdit('Stato:', $b2->inputSelect('idriparazionestato', $b2->creaArraySelect("SELECT idriparazionestato,riparazionestato FROM riparazionestato ORDER BY riparazionestato"), $r['idriparazionestato']));
echo $b2->rigaEdit('Cliente:', $b2->inputSelect('idcliente', $b2->creaArraySelect("SELECT idcliente,ragsoc FROM clienti ORDER BY ragsoc"), $r['idcliente']));
echo $b2->rigaEdit("Prodotto:", $b2->inputText('prodotto', $r['prodotto'], 100, 250));
echo $b2->rigaEdit("Problema:", $b2->inputTextarea('problema', $r['problema'], 80, 4), B2_ED_VTOP);
echo $b2->rigaEdit("Note:", $b2->inputTextarea('note', $r['note'], 80, 6), B2_ED_VTOP);
echo $b2->rigaEdit('Riparatore:', $b2->inputSelect('idutente', $b2->creaArraySelect("SELECT idutente,login FROM utente JOIN livello on utente.idlivello=livello.idlivello WHERE livello.isriparatore='1' AND isattivo='1' AND sviluppatore='0' ORDER BY login"), $r['idutente']));
echo $b2->rigaEdit('Notifica il riparatore:', $b2->inputCheck('notifica', false));
echo $b2->rigaEdit('Data di aggiornamento:', $b2->inputText('daggiornamento', $b2->dt2ita($r['daggiornamento']), 10, 10, '', '', B2_IT_CENTER));
echo $b2->rigaEdit('Compenso del riparatore:', $b2->inputText('compenso', $r['compenso'], 10, 10, '', '', B2_IT_RIGHT) . " inserire il valore compreso di centesimi");
echo $b2->rigaEdit('Prezzo:', $b2->inputText('prezzo', $r['prezzo'], 10, 10, '', '', B2_IT_RIGHT) . " inserire il valore compreso di centesimi");
if ($r['idriparazione'] > 0) {
	echo "\n<tr><td align='right' valign='top'><b>Elimina dall'archivio:</b></td>";
	echo "<td align='left' valign='top'><input type='checkbox' name='xxx1'/> <input type='checkbox' name='xxx2'/> <input type='checkbox' name='xxx3'/><br />";
	echo "Per cancellare definitivamente una riparazione spuntare tutte e tre le caselle.<br/><b>Non verr&agrave; chiesta alcuna ulteriore conferma!</b></td>";
}
echo "\n<tr><td align='center' colspan='2'><input type='submit' value=\"Aggiorna i dati\"></td></tr>";
echo "\n</table>";
echo "\n</form>";

// log attivita'
$q = $db->query("SELECT riparazionelog.dataora,riparazionelog.messaggio,
                        riparazionestato.riparazionestato,
                        utente.login
                 FROM riparazionelog
                 JOIN riparazionestato ON riparazionestato.idriparazionestato=riparazionelog.idriparazionestato
                 JOIN utente ON utente.idutente=riparazionelog.idutente
                 WHERE riparazionelog.idriparazione='$r[idriparazione]'
                 ORDER BY riparazionelog.dataora DESC");
if ($q->num_rows > 0) {
	echo "\n<p>&nbsp;</p>";
	echo "\n<p>&nbsp;</p>";
	echo "\n<table border='0' align='center'>";
	echo $b2->intestazioneTabella(array("Data e ora", "Stato", "Utente", "Evento"));
	while ($r = $q->fetch_array()) {
		$bg = $b2->bgcolor();
		$id = 0;
		echo "\n<tr $bg>";
		echo "<td $bg align='right'>&nbsp;" . $b2->ts2ita($r['dataora']) . "&nbsp;</td>";
		echo "<td $bg align='left'>&nbsp;$r[riparazionestato]&nbsp;</td>";
		echo "<td $bg align='left'>&nbsp;$r[login]&nbsp;</td>";
		echo "<td $bg align='left'>&nbsp;$r[messaggio]&nbsp;</td>";
		echo "</tr>";
	}
	echo "\n</table>";
}

echo "\n<p>&nbsp;</p>";

?>

<script>
  $.datepicker.setDefaults( $.datepicker.regional[ "it" ] );
  $(function() {
    $( "#daggiornamento" ).datepicker({
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
      $( "#daggiornamento" ).datepicker( "option", "dateFormat", $( this ).val() );
    });
  });
	$(document).ready(function() {
		$('#compenso').mask("#.##0,00", {reverse: true});
		$('#prezzo').mask("#.##0,00", {reverse: true});
  });
</script>

<?php

piede();

### END OIF FILE ###