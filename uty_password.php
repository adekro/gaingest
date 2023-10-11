<?php

/**
 * 
 * Gain Studios - Cambio password
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015 B2TEAM S.r.l. - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20141003 file creato
 *
 */

define('SOUNDPARK', true);
require('global.php');

$minpw = readsetup('MINPWLEN');
$killflag = FALSE;

intestazione("Cambio password");

if (isset($_POST['oldp'])) {
	if ($_SESSION['utente']['password'] != sha1($_POST['oldp'])) {
		echo "\n<p align='center'>La password attuale non &egrave; corretta.</p>";
		echo "\n<p align='center'>La password non &egrave; stata modificata.</p>";
	} else {
		if ($_POST['newp1'] != $_POST['newp1']) {
			echo "\n<p align='center'>Le due password nuove non coincidono.</p>";
			echo "\n<p align='center'>La password non &egrave; stata modificata.</p>";
		} else {
			if (strlen($_POST['newp1']) < $minpw) {
				echo "\n<p align='center'>La password nuova deve essere lunga almeno <b>$minpw</b> caratteri.</p>";
				echo "\n<p align='center'>La password non &egrave; stata modificata.</p>";
			} else {
				echo "\n<p align='center'>La password &egrave; stata modificata.</p>";
				echo "\n<p align='center'>Rifare l'accesso alla procedura con le nuove credenziali.</p>";
				$db->query("UPDATE utente SET password='" . sha1($_POST['newp1']) . "' WHERE idutente='" . $_SESSION['utente']['idutente'] . "'");
				$killflag = TRUE;
			}
		}
	}
} else {

	echo "\n<form action='uty_password.php' method='post'>";
	echo "\n<table border='0' align='center'>";
	echo "<tr><td align='right'><b>Password attuale:</b></td><td align='left'><input type='password' name='oldp' size='30' maxlength='50' /></td></tr>";
	echo "<tr><td align='right'><b>Nuova password:</b></td><td align='left'><input type='password' name='newp1' size='30' maxlength='50' /></td></tr>";
	echo "<tr><td align='right'><b>Ripeti:</b></td><td align='left'><input type='password' name='newp2' size='30' maxlength='50' /></td></tr>";
	echo "<tr><td align='center' colspan='2'><input type='submit' value='Conferma il cambio della password' alt='Conferma il cambio della password' /></td></tr>";
	echo "\n</table></form>";
	
	echo "\n<p align='center'>La password deve essere lunga almeno <b>$minpw</b> caratteri.";
}

piede();

if ($killflag) session_destroy();

// ### END OF FILE ###