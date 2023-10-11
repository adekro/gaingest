<?php

/**
 * 
 * Gain Studios - Dettaglio di una voce di assenza (server)
 * Copyright (C) 2016-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20160527 file creato
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

//error_log(print_r($_POST, TRUE));

if (isset($_POST['dispatch']) and 'form' == $_POST['dispatch']) {
	global $db, $b2;
	$idassenza = $b2->normalizza($_POST['idassenza']);
	// cancellazione di gruppo
	if (isset($_POST['yyy'])) {
		$r = $db->query("SELECT data,uuid_gruppo FROM assenza WHERE idassenza='$idassenza'")->fetch_array();
		$uuid_gruppo = $r['uuid_gruppo'];
		$data = $r['data'];
		$q = $db->query("SELECT idassenza FROM assenza WHERE uuid_gruppo='$uuid_gruppo' AND data>='$data'");
		while ($r = $q->fetch_array()) {
			$db->query("DELETE FROM assenza WHERE idassenza='$r[idassenza]'");
		}
	} elseif (isset($_POST['xxx'])) {
		$db->query("DELETE FROM assenza WHERE idassenza='$idassenza'");
	} else {
		$a = array();
		if (isset($_POST['idutente'])) $a[] = $b2->campoSQL('idutente', $_POST['idutente']);
		$a[] = $b2->campoSQL('data', $b2->dt2iso($_POST['data']));
		$a[] = $b2->campoSQL('orainizio', ora2int($_POST['orainizioh'], $_POST['orainiziom']));
		$a[] = $b2->campoSQL('orafine', ora2int($_POST['orafineh'], $_POST['orafinem']));
		$a[] = $b2->campoSQL('note', $_POST['note']);
		$db->query("UPDATE assenza SET " . implode(',', $a) . " WHERE idassenza='$idassenza'");
	}
	echo "OK";
}


// ### END OF FILE ###