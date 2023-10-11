<?php

/**
 * 
 * Gain Studios - Dettaglio di una voce di planning multipla (server)
 * Copyright (C) 2016-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20160622 file creato
 * 20161112 tabella MEMORY per la gestione degli invitati
 * 20161130 luogo
 * 20190128 conversione ora da int a char
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

//error_log(print_r($_POST, TRUE));

// autocompletamento
if (isset($_GET['term'])) {
	$a = array();
	$q = $db->query("SELECT DISTINCT luogo FROM planning WHERE luogo LIKE '%" . $b2->normalizza($_GET['term']) . "%' ORDER BY luogo LIMIT 20");
	while ($r = $q->fetch_array()) {
		$a[] = $r['luogo'];
	}
	$json = json_encode($a);
	echo $json;
	die();
}


if (isset($_POST['dispatch']) and 'idutente' == $_POST['dispatch']) {
	try {
		//code...
		
		$a[] = $b2->campoSQL('idplanning', $_SESSION['idplanning']);
		$a[] = $b2->campoSQL('idutente', $_POST['idutente']);
		$a[] = $b2->campoSQL('idplanningstatocol', 1);
		$a[] = $b2->campoSQL('modificato', time());
		$a[] = $b2->campoSQL('idtemp', $_SESSION['idtemp']);
		$r = $db->query("SELECT colore FROM planningstato WHERE idplanningstato='1'")->fetch_array();
		$a[] = $b2->campoSQL('colore', $r['colore']);
		$r = $db->query("SELECT cognome,nome FROM utente WHERE idutente='" . $b2->normalizza($_POST['idutente']) . "'")->fetch_array();
		$a[] = $b2->campoSQL('cognome', $r['cognome']);
		$a[] = $b2->campoSQL('nome', $r['nome']);
		$db->query("INSERT INTO tmp_planningedit SET " . implode(',', $a));
		echo planningcollaboratori($_SESSION['idplanning']);
	} catch (\Throwable $th) {
		echo "session idplanning ".$_SESSION['idplanning'];
	}
}


if (isset($_POST['dispatch']) and 'delete' == $_POST['dispatch']) {
	$r = $db->query("SELECT idutente,idplanning,ismail FROM tmp_planningedit WHERE idplanningedit='" . $b2->normalizza($_POST['idplanningedit']) . "'")->fetch_array();
	if ('1' == $r['ismail']) notificaplanningmultiplo($r['idutente'], $_SESSION['uuid_gruppo'], true);
	$db->query("DELETE FROM tmp_planningedit WHERE idplanningedit='" . $b2->normalizza($_POST['idplanningedit']) . "'");
	echo planningcollaboratori($r['idplanning']);
}


if (isset($_POST['dispatch']) and 'togglemail' == $_POST['dispatch']) {
	$r = $db->query("SELECT ismail,idplanning FROM tmp_planningedit WHERE idplanningedit='" . $b2->normalizza($_POST['idplanningedit']) . "'")->fetch_array();
	$ismail = '1' == $r['ismail'] ? '0' : '1';
	$db->query("UPDATE tmp_planningedit SET ismail='$ismail' WHERE idplanningedit='" . $b2->normalizza($_POST['idplanningedit']) . "'");
	echo planningcollaboratori($r['idplanning']);
}


if (isset($_POST['dispatch']) and 'cambiastato' == $_POST['dispatch']) {
	$r = $db->query("SELECT colore FROM planningstatocol WHERE idplanningstatocol='" . $b2->normalizza($_POST['idplanningstatocol']) . "'")->fetch_array();
	$a = array();
	$a[] = $b2->campoSQL('idplanningstatocol', $_POST['idplanningstatocol']);
	$a[] = $b2->campoSQL('colore', $r['colore']);
	$a[] = $b2->campoSQL('modificato', time());
	$db->query("UPDATE tmp_planningedit SET " . implode(',', $a) . " WHERE idplanningedit='" . $b2->normalizza($_POST['idplanningedit']) . "'");
	$r = $db->query("SELECT idplanning FROM tmp_planningedit WHERE idplanningedit='" . $b2->normalizza($_POST['idplanningedit']) . "'")->fetch_array();
	echo planningcollaboratori($r['idplanning']);
}


if (isset($_POST['dispatch']) and 'form' == $_POST['dispatch']) {
	global $db, $b2;
	$uuid_gruppo = $b2->normalizza($_POST['uuid_gruppo']);
	// gli utenti assegnati li cancello comunque perche' poi ripopolo la tabella da tmp_planningedit
	$q = $db->query("SELECT idplanning FROM planning WHERE uuid_gruppo='$uuid_gruppo'");
	while ($r = $q->fetch_array()) $db->query("DELETE FROM planningutente WHERE idplanning='$r[idplanning]'");
	// cancellazione di gruppo
	if (isset($_POST['yyy1']) and isset($_POST['yyy2']) and isset($_POST['yyy3'])) {
		$q = $db->query("SELECT idplanning FROM planning WHERE uuid_gruppo='$uuid_gruppo'");
		while ($r = $q->fetch_array()) {
			$db->query("DELETE FROM planningarticolo WHERE idplanning='$r[idplanning]'");
			$db->query("DELETE FROM planning WHERE idplanning='$r[idplanning]'");
		}
	} else {
		$a = array();
		if (isset($_POST['idplanningstato'])) $a[] = $b2->campoSQL('idplanningstato', $_POST['idplanningstato']);
		if (isset($_POST['idcliente'])) $a[] = $b2->campoSQL('idcliente', $_POST['idcliente']);
		if (isset($_POST['titolo'])) $a[] = $b2->campoSQL('titolo', $_POST['titolo']);
		if (isset($_POST['luogo'])) $a[] = $b2->campoSQL('luogo', $_POST['luogo']);
		if (isset($_POST['dettaglio'])) $a[] = $b2->campoSQL('dettaglio', $_POST['dettaglio']);
		$qx = $db->query("SELECT idplanning FROM planning WHERE uuid_gruppo='$uuid_gruppo'");
		while ($rx = $qx->fetch_array()) {
			$db->query("UPDATE planning SET " . implode(',', $a) . " WHERE idplanning='$rx[idplanning]'");
		}
		// ripopolo i collaboratori
		$q = $db->query("SELECT * FROM tmp_planningedit WHERE idtemp='$_SESSION[idtemp]'");
		while ($r = $q->fetch_array()) {
			$qq = $db->query("SELECT idplanning FROM planning WHERE uuid_gruppo='$uuid_gruppo'");
			while ($rr = $qq->fetch_array()) {
				$a = array();
				$a[] = $b2->campoSQL('idplanning', $rr['idplanning']);
				$a[] = $b2->campoSQL('idutente', $r['idutente']);
				$a[] = $b2->campoSQL('modificato', $r['modificato']);
				$a[] = $b2->campoSQL('idplanningstatocol', $r['idplanningstatocol']);
				$a[] = $b2->campoSQL('note', $r['note']);
				$db->query("INSERT INTO planningutente SET " . implode(',', $a));
			}
			if ('1' == $r['ismail']) notificaplanningmultiplo($r['idutente'], $uuid_gruppo);
		}
		$q = $db->query("DELETE FROM tmp_planningedit WHERE idtemp='$_SESSION[idtemp]'");
	}
	echo "OK";
}


// ### END OF FILE ###