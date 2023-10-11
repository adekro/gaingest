<?php

/**
 * 
 * Gain Studios - Nuova voce multipla di planning (server)
 * Copyright (C) 2016-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20160526 file creato
 * 20161130 luogo
 * 20170207 note amministrative
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


if (isset($_POST['dispatch']) and 'form' == $_POST['dispatch']) {
	$datacorrente = $b2->dt2iso($_POST['datainizio']);
	$uuid_gruppo = $b2->uuid();
	$step =  is_numeric($_POST['ripetiogni']) ? $b2->normalizza($_POST['ripetiogni']) : 1;
	while ($datacorrente <= $b2->dt2iso($_POST['datafine'])) {
		$a = array();
		$a[] = $b2->campoSQL('idplanningstato', $_POST['idplanningstato']);
		$a[] = $b2->campoSQL('idcliente', $_POST['idcliente']);
		$a[] = $b2->campoSQL('titolo', $_POST['titolo']);
		$a[] = $b2->campoSQL('luogo', $_POST['luogo']);
		$a[] = $b2->campoSQL('data', $datacorrente);
		$a[] = $b2->campoSQL('orainizio', ora2int($_POST['orainizioh'], $_POST['orainiziom']));
		$a[] = $b2->campoSQL('orafine', ora2int($_POST['orafineh'], $_POST['orafinem']));
		$a[] = $b2->campoSQL('dettaglio', $_POST['dettaglio']);
		$a[] = $b2->campoSQL('noteadmin', $_POST['noteadmin']);
		$a[] = $b2->campoSQL('uuid', $b2->uuid());
		$a[] = $b2->campoSQL('uuid_gruppo', $uuid_gruppo);
		$db->query("INSERT INTO planning SET " . implode(',', $a));
  	$datacorrente = date("Y-m-d", strtotime("+$step day", strtotime($datacorrente)));  
	}
	echo "OK";
}

// ### END OF FILE ###