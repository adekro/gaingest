<?php

/**
 * 
 * Gain Studios - Nuova assenza (server)
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
	$datacorrente = $b2->dt2iso($_POST['datainizio']);
	$uuid_gruppo = $b2->uuid();
	if ('1' == $_SESSION['utente']['isadmin']) {
		$idutente = $b2->normalizza($_POST['idutente']);
	} else {
		$idutente = $_SESSION['utente']['idutente'];
	}
	while ($datacorrente <= $b2->dt2iso($_POST['datafine'])) {
		$a = array();
		$a[] = $b2->campoSQL('idutente', $idutente);
		$a[] = $b2->campoSQL('note', $_POST['note']);
		$a[] = $b2->campoSQL('data', $datacorrente);
		$a[] = $b2->campoSQL('orainizio', ora2int($_POST['orainizioh'], $_POST['orainiziom']));
		$a[] = $b2->campoSQL('orafine', ora2int($_POST['orafineh'], $_POST['orafinem']));
		$a[] = $b2->campoSQL('uuid', $b2->uuid());
		$a[] = $b2->campoSQL('uuid_gruppo', $uuid_gruppo);
		$db->query("INSERT INTO assenza SET " . implode(',', $a));
  	$datacorrente = date("Y-m-d", strtotime("+1 day", strtotime($datacorrente)));  
	}
	echo "OK";
}


// ### END OF FILE ###