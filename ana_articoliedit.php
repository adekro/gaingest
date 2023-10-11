<?php

/**
 * 
 * Gain Studios - Anagrafica articoli, modifica
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * @author Luigi Rosa <lrosa@venus.it>
 *
 * 20141012 file creato
 * 20180928 migrazione a jQuery
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

//error_log(print_r($_POST, true));

// AJAX - controllo codice
if (isset($_POST['dispatch']) and  'codice' == $_POST['dispatch']) {
	$idarticolo = $b2->normalizza($_POST['idarticolo']);
	$codice = $b2->normalizza($_POST['codice']);
	$q = $db->query("SELECT articolo FROM articoli WHERE idarticolo<>'$idarticolo' AND codice='$codice'");
	if ($q->num_rows > 0) {
		$r = $q->fetch_array();
		echo " Il codice &egrave; gi&agrave; in uso per l'articolo $r[articolo]";
	} else {
		echo "&nbsp;";
	}
	die();
}

if (isset($_POST['idarticolo'])) {
	$idarticolo = $b2->normalizza($_POST['idarticolo']);
	// se devo cancellare, non faccio molto cinema
	if (isset($_POST['xxx1']) and isset($_POST['xxx2']) and isset($_POST['xxx3'])) {
		$db->query("DELETE FROM articoli WHERE idarticolo='$idarticolo'");
	} else {
		$a[] = $b2->campoSQL("idcategoria", $_POST['idcategoria']);
		$a[] = $b2->campoSQL("codice", $_POST['codice']);
		$a[] = $b2->campoSQL("articolo", $_POST['articolo']);
		$a[] = $b2->campoSQL("giacenza", $_POST['giacenza']);
		$a[] = $b2->campoSQL("vendita", $_POST['venditaint'] * 100 + $_POST['venditadec']);
		$a[] = $b2->campoSQL("acquisto", $_POST['acquistoint'] * 100 + $_POST['acquistodec']);
		$a[] = $b2->campoSQL("noleggio", $_POST['noleggioint'] * 100 + $_POST['noleggiodec']);
		$a[] = $b2->campoSQL("urlcertificazione", $_POST['urlcertificazione']);
		$a[] = $b2->campoSQL("urlmanuale", $_POST['urlmanuale']);
		$a[] = $b2->campoSQL("urlpresentazione", $_POST['urlpresentazione']);
		$a[] = $b2->campoSQL("urldocumentazione", $_POST['urldocumentazione']);
		$a[] = $b2->campoSQL("note", $_POST['note']);
		// nuovo record
		if (0 == $_POST['idarticolo']) {
			if (strlen($_POST['articolo']) > 0) {
				$db->query("INSERT INTO articoli SET " . implode(',', $a));
			}
		} else {
			$db->query("UPDATE articoli SET " . implode(',', $a) . " WHERE idarticolo='$idarticolo'");
		}
	} 
	header("Location: ana_articoli.php");
	die();
}

//precarico le categorie
$acategoria = array();
$q = $db->query("SELECT idcategoria,codice,categoria FROM categoria ORDER BY codice");
while ($r = $q->fetch_array()) $acategoria[$r['idcategoria']] = "$r[codice] $r[categoria]";

if (isset($_GET['idarticolo'])) {
	if (is_numeric($_GET['idarticolo'])) {
		$idarticolo = $b2->normalizza($_GET['idarticolo']);
		if (0 == $_GET['idarticolo']) {
			$r = array();
			$r['idarticolo'] = 0;
			$r['idcategoria'] = 0;
			$r['codice'] = '';
			$r['articolo'] = '';
			$r['giacenza'] = 0;
			$r['vendita'] = 0;
			$r['acquisto'] = 0;
			$r['noleggio'] = 0;
			$r['urlcertificazione'] = '';
			$r['urlmanuale'] = '';
			$r['urlpresentazione'] = '';
			$r['urldocumentazione'] = '';
			$r['note'] = '';
			$head = "Inserimento di un nuovo articolo";
		} else {
			$q = $db->query("SELECT * FROM articoli WHERE idarticolo='$idarticolo'");
			if ($q->num_rows > 0) {
				$r = $q->fetch_array();
				$head = "Modifica $r[articolo]";
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

echo "\n<form method='post' action='ana_articoliedit.php'>";
echo $b2->inputHidden('idarticolo', $r['idarticolo']);

echo "\n<table border='0' align='center'>";

// categoria
echo $b2->rigaEdit('Categoria:', $b2->inputSelect('idcategoria', $acategoria, $r['idcategoria']));
// codice
echo $b2->rigaEdit('Codice:', $b2->inputText('codice', $r['codice'], 21, 20) . "<span class='err' id='errcodice' name='errcodice'></span>");
// articolo
echo $b2->rigaEdit('Descrizione:', $b2->inputText('articolo', $r['articolo'], 50, 250));
// giacenza
echo $b2->rigaEdit('Giacenza:', $b2->inputText('giacenza', $r['giacenza'], 10, 10, '', '', B2_IT_RIGHT));
// vendita
$xint = floor($r['vendita'] / 100);
$xdec = $r['vendita'] % 100;
echo "\n<tr><td align='right'><b>Vendita:</b></td>";
echo "<td align='left'>&#8364;<input style='text-align:right;' type='text' name='venditaint' id='venditaint' size='8' maxlength='8' value=\"$xint\"/>,<input style='text-align:right;' type='text' name='venditadec' id='venditadec' size='3' maxlength='2' value=\"$xdec\"/></td>";
// acquisto
$xint = floor($r['acquisto'] / 100);
$xdec = $r['acquisto'] % 100;
echo "\n<tr><td align='right'><b>Acquisto:</b></td>";
echo "<td align='left'>&#8364;<input style='text-align:right;' type='text' name='acquistoint' id='acquistoint' size='8' maxlength='8' value=\"$xint\"/>,<input style='text-align:right;' type='text' name='acquistodec' id='acquistodec' size='3' maxlength='2' value=\"$xdec\"/></td>";
// noleggio
$xint = floor($r['noleggio'] / 100);
$xdec = $r['noleggio'] % 100;
echo "\n<tr><td align='right'><b>Noleggio:</b></td>";
echo "<td align='left'>&#8364;<input style='text-align:right;' type='text' name='noleggioint' id='noleggioint' size='8' maxlength='8' value=\"$xint\"/>,<input style='text-align:right;' type='text' name='noleggiodec' id='noleggiodec' size='3' maxlength='2' value=\"$xdec\"/> al giorno</td>";
// urlcertificazione
echo $b2->rigaEdit('URL documento di certificazione:', $b2->inputText('urlcertificazione', $r['urlcertificazione'], 50, 255));
// urlmanuale
echo $b2->rigaEdit('URL documento del manuale:', $b2->inputText('urlmanuale', $r['urlmanuale'], 50, 255));
// urlpresentazione
echo $b2->rigaEdit('URL documento di presentazione:', $b2->inputText('urlpresentazione', $r['urlpresentazione'], 50, 255));
// urldocumentazione
echo $b2->rigaEdit('URL documentazione:', $b2->inputText('urldocumentazione', $r['urldocumentazione'], 50, 255));
// note
echo $b2->rigaEdit('Note:', $b2->inputText('note', $r['note'], 50, 255));

if ($r['idarticolo'] > 0) {
	// delete
	echo "\n<tr><td align='right'><b>Elimina dall'archivio:</b></td>";
	echo "\n<td align='left'><input type='checkbox' name='xxx1'/> <input type='checkbox' name='xxx2'/> <input type='checkbox' name='xxx3'/><br />";
	echo "Per cancellare definitivamente un cliente spuntare tutte e tre le caselle. <b>Non verr&agrave; chiesta alcuna ulteriore conferma!</b></td></tr>";
}

// submit
$x = 0 == $r['idarticolo'] ? 'Aggiungi questo articolo' : 'Aggiorna questo articolo';
echo "\n<tr><td align='center' colspan='2'><input type='submit' value=\"$x\"></td></tr>";

echo "\n</table>";
echo "\n</form>";

?>
<script type="text/javascript">
  $(document).ready(function() {
  	// cambio codice
  	$("#codice").change(function(){
	    var codice = $("#codice").val();
	    var idarticolo = $("#idarticolo").val();
			$.post("ana_articoliedit.php", 
				{dispatch: "codice", codice: codice, idarticolo: idarticolo})
				.done(function( data ) {
					$("#errcodice").html(data);
  			})  	});
  }); //document.ready
</script>  
<?php
piede();

### END OIF FILE ###