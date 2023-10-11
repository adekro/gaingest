<?php

/**
 * 
 * Gain Studios - Anagrafica collaboratori
 * Copyright (C) 2015 B2TEAM S.r.l. - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20150619 file creato
 *
 */

define('SOUNDPARK', true);
require('global.php');

//error_log(print_r($_POST, TRUE));

// cambia login
if (isset($_POST['dispatch']) and $_POST['dispatch'] == 'login') {
	$wh = $_POST['idutente'] != 0 ? " AND idutente<>'" . $b2->normalizza($_POST['idutente']) . "'" : '';
	$q = $db->query("SELECT login,cognome,nome FROM utente WHERE login='" . $b2->normalizza($_POST['login']) . "' $wh");
	if ($q->num_rows > 0) {
		$r = $q->fetch_array();
		echo "Login gi&agrave; in uso da $r[cognome] $r[nome]";
	} else {
		echo '&nbsp;';
	}
}

// cambia cf
if (isset($_POST['dispatch']) and $_POST['dispatch'] == 'cf') {
	if ($b2->chkCF($_POST['cf'])) {
		echo "codice fiscale OK";
	} else {
		echo 'codice fiscale errato';
	}
}


### END OF FILE ###