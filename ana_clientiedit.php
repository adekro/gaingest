<?php

/**
 * 
 * Gain Studios - Anagrafica clienti, modifica
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 10141006 file creato
 * 20161129 aggiunto campo nomarkupriparazione
 * 20161001 campi esenzione iva
 * 20190128 migrazione a jQuery, rimozione ana_clientiedit.server.php
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

//error_log(print_r($_POST, true));

// elenco dei contatti
function mostraContatti($idcliente) {
	global $db;
	$b = '';
	$qq = $db->query("SELECT * FROM contatto WHERE idcliente='$idcliente'");
	$b .= "<table border='0'>";
	$b .= "<tr>";
	$b .= "<th align=center><b>Tipo</b></th>
	       <th align=center><b>Contatto</b></th>
	       <th align=center>&nbsp</th>
	       </tr>";
	$b .= "<tr>";
	$b .= "<td align='center'><input type='text' size='40' maxlength='100' name='tipo' id='tipo'/></td>";
	$b .= "<td align='center'><input type='text' size='40' maxlength='250' name='contatto' id='contatto'/></td>";
	$b .= "<td align='center'><input type='button' value='Aggiungi' onClick=\"return aggiungicontatto(document.getElementById('tipo').value,document.getElementById('contatto').value,$idcliente);\"/></td>";
	$b .= "</tr>";
	while ($rr = $qq->fetch_array()) {
		$id = $rr['idcontatto'];
		$b .= "<tr>";
		$b .= "<td align='left'>$rr[tipo]</td>";
		$b .= "<td align='left'>$rr[contatto]</td>";
		$b .= "<td align='center'><input type='button' value='Cancella' onClick=\"return rimuovicontatto($rr[idcontatto], $idcliente);\"/></td>";
	} 
	$b .= "</table>";
	return $b;
}

// ajax
if (isset($_POST['dispatch']) and 'rimuovicontatto' == $_POST['dispatch']) {
	$db->query("DELETE FROM contatto WHERE idcontatto='" . $b2->normalizza($_POST['idcontatto']) . "'");
	echo mostraContatti($b2->normalizza($_POST['idcliente']));
	die();
}
if (isset($_POST['dispatch']) and 'aggiungicontatto' == $_POST['dispatch']) {
	if ('' != trim($_POST['contatto'])) {
		$a = array();
		$a[] = $b2->campoSQL("idcliente", $_POST['idcliente']);
		$a[] = $b2->campoSQL("tipo", $_POST['tipo']);
		$a[] = $b2->campoSQL("contatto", $_POST['contatto']);
		$db->query("INSERT INTO contatto SET " . implode(',', $a));
	}
	echo mostraContatti($b2->normalizza($_POST['idcliente']));
	die();
}

// elabora form
if (isset($_POST['idcliente'])) {
	$idcliente = $b2->normalizza($_POST['idcliente']);
	// se devo cancellare, non faccio molto cinema
	if (isset($_POST['xxx1']) and isset($_POST['xxx2']) and isset($_POST['xxx3'])) {
		$db->query("DELETE FROM clienti WHERE idcliente='$idcliente'");
	} else {
		$a = array();
		$a[] = $b2->campoSQL("ragsoc", $_POST['ragsoc']);
		$a[] = $b2->campoSQL("cognome", $_POST['cognome']);
		$a[] = $b2->campoSQL("nome", $_POST['nome']);
		$a[] = $b2->campoSQL("istemporaneo", isset($_POST['istemporaneo']) ? 1 : 0);
		$a[] = $b2->campoSQL("indirizzo1", $_POST['indirizzo1']);
		$a[] = $b2->campoSQL("indirizzo2", $_POST['indirizzo2']);
		$a[] = $b2->campoSQL("cap", $_POST['cap']);
		$a[] = $b2->campoSQL("provincia", $_POST['provincia']);
		$a[] = $b2->campoSQL("citta", $_POST['citta']);
		$a[] = $b2->campoSQL("stato", $_POST['stato']);
		$a[] = $b2->campoSQL("destfattura1", $_POST['destfattura1']);
		$a[] = $b2->campoSQL("destfattura2", $_POST['destfattura2']);
		$a[] = $b2->campoSQL("destfattura3", $_POST['destfattura3']);
		$a[] = $b2->campoSQL("email", $_POST['email']);
		$a[] = $b2->campoSQL("emaildoc", $_POST['emaildoc']);
		$a[] = $b2->campoSQL("sdi", $_POST['sdi']);
		$a[] = $b2->campoSQL("piva", $_POST['piva']);
		$a[] = $b2->campoSQL("cf", $_POST['cf']);
		$a[] = $b2->campoSQL("idiva", $_POST['idiva']);
		$a[] = $b2->campoSQL("isesenteivadic", isset($_POST['isesenteivadic']) ? 1 : 0);
		$a[] = $b2->campoSQL("dicestremi", $_POST['dicestremi']);
		$a[] = $b2->campoSQL("dettaglioesenzione", $_POST['dettaglioesenzione']);
		$a[] = $b2->campoSQL("idpagamento", $_POST['idpagamento']);
		$a[] = $b2->campoSQL("sconto", ($_POST['scontoint'] * 100 + $_POST['scontodec']));
		$a[] = $b2->campoSQL("nomarkupriparazione", isset($_POST['nomarkupriparazione']) ? 1 : 0);
		$a[] = $b2->campoSQL("destmerce1", $_POST['destmerce1']);
		$a[] = $b2->campoSQL("destmerce2", $_POST['destmerce2']);
		$a[] = $b2->campoSQL("destmerce3", $_POST['destmerce3']);
		$a[] = $b2->campoSQL("idtipocli", $_POST['idtipocli']);
		$a[] = $b2->campoSQL("note", $_POST['note']);
		if ($_POST['idcliente'] > 0 ) {
			$db->query("UPDATE clienti SET " . implode(',', $a) . " WHERE idcliente='$idcliente'");
		} else {
			$db->query("INSERT INTO clienti SET " . implode(',', $a));
		}
	}
	header('Location: ana_clienti.php');
	die();
}

if (isset($_GET['idcliente']) and is_numeric($_GET['idcliente'])) {
	$idcliente = $b2->normalizza($_GET['idcliente']);
	if (0 == $_GET['idcliente']) {
		$r = array();
		$r['idcliente'] = 0;
		$r['ragsoc'] = '';
		$r['email'] = '';
		$r['telefono'] = '';
		$r['indirizzo1'] = '';
		$r['indirizzo2'] = '';
		$r['citta'] = '';
		$r['piva'] = '';
		$r['sdi'] = '';
		$r['note'] = '';
		$r['cf'] = '';
		$r['provincia'] = '';
		$r['cap'] = '';
		$r['stato'] = 'Italia';
		$r['emaildoc'] = '';
		$r['cognome'] = '';
		$r['nome'] = '';
		$r['istemporaneo'] = '0';
		$r['idiva'] = '';
		$r['idtipocli'] = '';
		$r['idpagamento'] = '';
		$r['destmerce1'] = '';
		$r['destmerce2'] = '';
		$r['destmerce3'] = '';
		$r['destfattura1'] = '';
		$r['destfattura2'] = '';
		$r['destfattura3'] = '';
		$r['sconto'] = 0;
		$r['nomarkupriparazione'] = 0;
		$r['dicestremi'] = '';
		$r['dettaglioesenzione'] = '';
		$r['isesenteivadic'] = 0;
		$head = "Inserimento di un nuovo cliente";
	} else {
		$q = $db->query("SELECT * FROM clienti WHERE idcliente='$idcliente'");
		if ($q->num_rows > 0) {
			$r = $q->fetch_array();
			$head = "Modifica $r[ragsoc] $r[cognome] $r[nome]";
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

echo "\n<form method='post' action='ana_clientiedit.php'>";
echo $b2->inputHidden('idcliente', $r['idcliente']);

echo "\n<table border='0' align='center'>";

// ragsoc
echo $b2->rigaEdit('Ragione sociale:', $b2->inputText('ragsoc', $r['ragsoc'], 100, 255));
// cognome e nome
echo $b2->rigaEdit('Cognome e nome:', $b2->inputText('cognome', $r['cognome'], 50, 100) . '&nbsp;' . $b2->inputText('nome', $r['nome'], 50, 100));
// istemporaneo
echo $b2->rigaEdit('Temporaneo:', $b2->inputCheck('istemporaneo', $r['istemporaneo'] == '1'));
// indirizzo
echo $b2->rigaEdit('Indirizzo:', $b2->inputText('indirizzo1', $r['indirizzo1'], 50, 255) . '<br/>' . $b2->inputText('indirizzo2', $r['indirizzo2'], 50, 255), B2_ED_VTOP);
// cap, provincia, citta
echo $b2->rigaEdit('CAP, provincia, citt&agrave;:', $b2->inputText('cap', $r['cap'], 5, 10) . '&nbsp;' . $b2->inputText('provincia', $r['provincia'], 2, 2) . '&nbsp;' . $b2->inputText('citta', $r['citta'], 50, 250));
// destfattura
echo $b2->rigaEdit('Destinazione della fattura:', $b2->inputText('destfattura1', $r['destfattura1'], 50, 250) . '<br/>' . $b2->inputText('destfattura2', $r['destfattura2'], 50, 250) . '<br/>' . $b2->inputText('destfattura3', $r['destfattura3'], 50, 250), B2_ED_VTOP);
// stato
echo $b2->rigaEdit('Stato:', $b2->inputText('stato', $r['stato'], 50, 255));
// email
echo $b2->rigaEdit('Email:', $b2->inputText('email', $r['email'], 50, 255));
// emaildoc
echo $b2->rigaEdit('PEC per fatture:', $b2->inputText('emaildoc', $r['emaildoc'], 50, 255));
// sdi
echo $b2->rigaEdit('Codice SDI:', $b2->inputText('sdi', $r['sdi'], 7, 9));
if ($r['idcliente'] > 0) {
	// contatti
	echo "\n<tr><td align='right' valign='top'><b>Contatti:</b></td>";
	echo "<td align='left' valign='top'><span id='contattitutti'>" . mostraContatti($r['idcliente']) . "</div></td>";
}
// piva
echo $b2->rigaEdit('Partita IVA:', $b2->inputText('piva', $r['piva'], 30, 30));
// cf
echo $b2->rigaEdit('Codice fiscale:', $b2->inputText('cf', $r['cf'], 18, 16) . "<span id='errcf'></span>");
// idiva
echo $b2->rigaEdit('IVA predefinita:', $b2->inputSelect('idiva', $b2->creaArraySelect("SELECT idiva,iva FROM iva ORDER BY iva"), $r['idiva']));
// isesenteivadic
echo $b2->rigaEdit('Esente IVA:', $b2->inputCheck('isesenteivadic', $r['isesenteivadic'] == '1'));
// dicnumero dicann
echo $b2->rigaEdit("Estremi dichiarazione d'intento", $b2->inputText('dicestremi', $r['dicestremi'], 50, 250));
// dettaglioesenzione
echo $b2->rigaEdit('Dettaglio esenzione:', $b2->inputText('dettaglioesenzione', $r['dettaglioesenzione'], 50, 250));
// idpagamento
echo $b2->rigaEdit('Pagamento predefinito:', $b2->inputSelect('idpagamento', $b2->creaArraySelect("SELECT idpagamento,pagamento FROM pagamenti ORDER BY pagamento"), $r['idpagamento']));
// sconto
$xint = floor($r['sconto'] / 100);
$xdec = $r['sconto'] % 100;
echo "\n<tr><td align='right'><b>Sconto predefinito:</b></td>";
echo "<td align='left'><input type='text' name='scontoint' id='scontoint' size='3' maxlength='2' value=\"$xint\"/>,<input type='text' name='scontodec' id='scontodec' size='3' maxlength='2' value=\"$xdec\"/>%</td>";
// nomarkupriparazione
echo $b2->rigaEdit('Cliente interno:', $b2->inputCheck('nomarkupriparazione', $r['nomarkupriparazione'] == '1') . " se attivo, non viene applicato il markup standard della riparazione");
// destmerce
echo $b2->rigaEdit('Destinazione della merce:', $b2->inputText('destmerce1', $r['destmerce1'], 50, 250) . '<br/>' . $b2->inputText('destmerce2', $r['destmerce2'], 50, 250) . '<br/>' . $b2->inputText('destmerce3', $r['destmerce3'], 50, 250), B2_ED_VTOP);
// idtipocli
echo $b2->rigaEdit('Tipo di cliente:', $b2->inputSelect('idtipocli', $b2->creaArraySelect("SELECT idtipocli,tipocli FROM tipocli ORDER BY tipocli"), $r['idtipocli']));
// note
echo $b2->rigaEdit('Note:', $b2->inputText('note', $r['note'], 50, 250));
if ($r['idcliente'] > 0) {
	// delete
	echo "\n<tr><td align='right'><b>Elimina dall'archivio:</b></td>";
	echo "<td align='left'><input type='checkbox' name='xxx1'/> <input type='checkbox' name='xxx2'/> <input type='checkbox' name='xxx3'/><br/>";
	echo "Per cancellare definitivamente un cliente spuntare tutte e tre le caselle. <b>Non verr&agrave; chiesta alcuna ulteriore conferma!</b></td></tr>";
}

// submit
$x = 0 == $r['idcliente'] ? 'Aggiungi questo cliente' : 'Aggiorna questo cliente';
echo "\n<tr><td align='center' colspan='2'><input type='submit' value=\"$x\"></td></tr>";

echo "\n</table>";
echo "\n</form>";
echo "\n<p>&nbsp;</p>";

?>
<script>
	function rimuovicontatto(idcontatto, idcliente) {
		$.post("ana_clientiedit.php", 
			{dispatch: "rimuovicontatto", idcontatto: idcontatto, idcliente: idcliente})
			.done(function( data ) {
				$("#contattitutti").html(data);
			});
	};
	function aggiungicontatto(tipo, contatto, idcliente) {
		$.post("ana_clientiedit.php", 
			{dispatch: "aggiungicontatto", tipo: tipo, contatto: contatto, idcliente: idcliente})
			.done(function( data ) {
				$("#contattitutti").html(data);
			});
	};
</script>

<?php

piede();

### END OIF FILE ###