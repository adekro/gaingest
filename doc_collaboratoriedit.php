<?php

/**
 * 
 * Gain Studios - Modifica documenti collaboratori
 * Copyright (C) 2015-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20150623 file creato
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

$filedir = readsetup('DOCDIR');

if (isset($_POST['iddocumento'])) {
	// se devo cancellare, non faccio molto cinema
	if (isset($_POST['xxx'])) {
		$r = $db->query("SELECT nomefilesystem FROm documento WHERE iddocumento='" . $b2->normalizza($_POST['iddocumento']) . "'")->fetch_array();
		unlink($filedir . '/' . $r['nomefilesystem']);
		$db->query("DELETE FROM documento WHERE iddocumento='" . $b2->normalizza($_POST['iddocumento']) . "'");
		header('Location: doc_collaboratori.php');
		die();
	} else {
		$a = array();
		// id
		$a[] = $b2->campoSQL('id', $_POST['id']);
		// idtipodocumento
		$a[] = $b2->campoSQL('idtipodocumento', $_POST['idtipodocumento']);
		// scadenza
		$a[] = $b2->campoSQL('scadenza', $b2->dt2iso($_POST['scadenza']));
		// note
		$a[] = $b2->campoSQL('note', $_POST['note']);
		if ($_POST['iddocumento'] > 0) {
			$db->query("UPDATE documento SET " . implode(',', $a) . " WHERE iddocumento='" . $b2->normalizza($_POST['iddocumento']) . "'");
		} else {
			if ('' != $_FILES['nomefile']['name'] and is_uploaded_file($_FILES['nomefile']['tmp_name']) ) {
				$nomefilesystem = uniqid();
				$a[] = $b2->campoSQL('nomefilesystem', $nomefilesystem);
				$a[] = $b2->campoSQL('mime', $_FILES['nomefile']['type']);
				$a[] = $b2->campoSQL('nomefile', $_FILES['nomefile']['name']);
				$db->query("INSERT INTO documento SET " . implode(',', $a));
				move_uploaded_file($_FILES['nomefile']['tmp_name'], $filedir . '/' . $nomefilesystem);
			}
		}
	}
	header('Location: doc_collaboratori.php');
	die();
}

if (isset($_GET['iddocumento'])) {
	if (is_numeric($_GET['iddocumento'])) {
		if (0 == $_GET['iddocumento']) {
			$r = array();
			$r['iddocumento'] = 0;
			$r['idtipodocumento'] = 0;
			$r['nomefile'] = '';
			$r['scadenza'] = date('Y-m-d');
			$r['id'] = 0;
			$r['note'] = '';
			$head = "Inserimento di un nuovo documento";
		} else {
			$q = $db->query("SELECT * FROM documento WHERE iddocumento='" . $b2->normalizza($_GET['iddocumento']) . "'");
			if ($q->num_rows > 0) {
				$r = $q->fetch_array();
				$head = "Modifica $r[nomefile]";
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

echo "\n<form id='coll' method='post' action='doc_collaboratoriedit.php' enctype='multipart/form-data'>";
echo $b2->inputHidden('iddocumento', $r['iddocumento']);

echo "\n<table border='0' align='center'>";

// collaboratore
$a = array();
$qq = $db->query("SELECT idutente,cognome,nome FROM utente WHERE sviluppatore='0' ORDER BY cognome,nome");
while ($rr = $qq->fetch_array()) $a[$rr['idutente']] = "$rr[cognome] $rr[nome]";
echo $b2->rigaEdit("Collaboratore:", $b2->inputSelect('id', $a, $r['id']));

// tipo di documento
$a = array();
$qq = $db->query("SELECT idtipodocumento,tipodocumento FROM tipodocumento WHERE classe='C' ORDER BY tipodocumento");
while ($rr = $qq->fetch_array()) $a[$rr['idtipodocumento']] = "$rr[tipodocumento]";
echo $b2->rigaEdit("Tipo:", $b2->inputSelect('idtipodocumento', $a, $r['id']));

// nomefile
if ($r['iddocumento'] > 0) {
	$dimensione = filesize($filedir . '/' . $r['nomefilesystem']);
	echo $b2->rigaEdit('File:', "<a href='doc_download.php?iddocumento=$r[iddocumento]' target='_blank'>$r[nomefile] (" . number_format($dimensione / 1024, 0, ',', '.') . "k)</a>");
} else {
	echo $b2->rigaEdit('File:', "<input type='file' name='nomefile' size='50'/>");
}
// scadenza
echo $b2->rigaEdit("Data di scadenza:", $b2->inputText('scadenza', $b2->dt2ita($r['scadenza'], B2_DT_ZEROFILL), 11, 10, 'scadenza', '', B2_IT_CENTER));
// note
echo $b2->rigaEdit("Note:", $b2->inputText('note', $r['note'], 50, 250));

if ($r['iddocumento'] > 0) {
	echo $b2->rigaEdit("Elimina documento:", $b2->inputCheck('xxx', false));
}

// submit
echo "\n<tr><td align='center' colspan='2'><input type='submit' value=\"Aggiorna\"></td></tr>";

echo "\n</table>";
echo "\n</form>";

?>

<script>
  $.datepicker.setDefaults( $.datepicker.regional[ "it" ] );
  $(function() {
    $( "#scadenza" ).datepicker({
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
      $( "#scadenza" ).datepicker( "option", "dateFormat", $( this ).val() );
    });
  });
</script>

<?php

piede();

### END OIF FILE ###