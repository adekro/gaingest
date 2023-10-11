<?php

/**
 * 
 * Gain Studios - Login alla procedura - server side
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2016 B2TEAM S.r.l. - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20141002 file creato
 * 20150628 anceh il livello nella $_SESSION
 * 20150807 jQuery
 * 20160525 aggiunto il livello all'array dell'utente
 * 20161111 tolto isriparatore dall'anagrafica utente e spostato a livello di livello
 *
 */

// serve per impedire abusi
define('SOUNDPARK', true);
ini_set("session.gc_maxlifetime", "8000"); 
session_start();

// sicurezza X-Content-Type-Options: nosniff
header("X-Frame-Options: deny");
header("Frame-Options: deny");
header("X-XSS-Protection: \"1; mode=block\"");
header("X-Content-Type-Options: nosniff");

// non mi serve global e fa casino perche' verifica l'utente, quindi includo solamente la definizione del database
require('database.php');

if(isset($_POST['utente']) and isset($_POST['password'])) {
	$login = trim($db->escape_string($_POST['utente']));
	$password = $db->escape_string(sha1($_POST['password']));
	$ip = $db->escape_string($_SERVER['REMOTE_ADDR']);
	$loginok = FALSE;
	if ('' !=  $login) {
		$q = $db->query("SELECT * FROM utente WHERE login='$login' AND isattivo='1'");
		// utente sconociuto
		if ($q->num_rows == 1) {
			$r = $q->fetch_array();
			if ($r['password'] == $password) {
				$loginok = TRUE;
			} else {
				$db->query("UPDATE utente SET lastloginko=NOW(),lastipko='$ip' WHERE idutente='$r[idutente]'");
			}
		}
	}
	// se il login è OK, valorizzo le variabili di sessione
	if ($loginok) {
		$_SESSION['utente'] = $r;  // $_SESSION['utente'] è un ARRAY che contiene il record dell'utente
		// valorizzo anche il tipo di utente cosi' non ci penso piu'
		$rr = $db->query("SELECT * FROM livello WHERE idlivello='$r[idlivello]'")->fetch_array();
		$_SESSION['utente']['isadmin'] = $rr['isadmin'];
		$_SESSION['utente']['isoperatore'] = $rr['isoperatore'];
		$_SESSION['utente']['isriparatore'] = $rr['isriparatore'];
		$db->query("UPDATE utente SET lastloginok=NOW(),lastipok='$ip' WHERE idutente='$r[idutente]'");
		echo "OK";
	}
}


### END OF FILE ###