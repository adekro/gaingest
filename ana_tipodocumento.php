<?php

/**
 * 
 * Gain Studios - Anagrafica tipi di documento
 * Copyright (C) 2015 B2TEAM S.r.l. - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20150622 file creato
 *
 */

define('SOUNDPARK', true);
require('global.php');

if (isset($_GET['classe'])) {
	if (isset($aclassedoc[$_GET['classe']])) {
		$titolo = $aclassedoc[$_GET['classe']];
		$classe = $b2->normalizza($_GET['classe']);
	} else {
		header('location: home.php');
		die();
	}
} else {
	header('location: home.php');
	die();
}

intestazione($titolo);

if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $idtipodocumento=>$x) {
		if (isset($x['xxx'])) {
			$db->query("DELETE FROM tipodocumento WHERE idtipodocumento='$idtipodocumento'");
		} else {
			$a = array();
			$a[] = $b2->campoSQL('classe', $classe);
			$a[] = $b2->campoSQL('tipodocumento', $x['tipodocumento']);
			if ('' != trim($x['tipodocumento']) and $idtipodocumento == 0) {
				$db->query("INSERT INTO tipodocumento SET " . implode(',', $a));
			} else {
				$db->query("UPDATE tipodocumento SET " . implode(',', $a) . " WHERE idtipodocumento='$idtipodocumento'");
			}
		}
	}
}

$q = $db->query("SELECT * FROM tipodocumento WHERE classe='$classe' ORDER BY tipodocumento");

echo "\n<form action='ana_tipodocumento.php?classe=$classe' method='post'>";
echo "\n<table border='0' align='center'>";
echo "\n<tr>
          <th><b>Tipo di documento</b></th>
          <th><b>Del</b></th>
        </tr>";

// nuovo record
$bg = $b2->bgcolor();
echo "\n<tr $bg>";
// tipodocumento
echo "<td $bg align='center'>" . $b2->inputText('p[0][tipodocumento]', '', 50, 100) . "</td>";
// xxx
echo "<td $bg align='center'><b>Nuovo</b></td>";
echo "\n</tr>";

while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	$id = $r['idtipodocumento'];
	echo "\n<tr $bg>";
	// tipodocumento
	echo "<td $bg align='center'>" . $b2->inputText("p[$id][tipodocumento]", $r['tipodocumento'], 50, 100) . "</td>";
	// xxx
	$r1 = $db->query("SELECT COUNT(*) FROM documento WHERE idtipodocumento='$id'")->fetch_array();
	if (0 == $r1[0]) {
		echo "<td $bg align='center'>" . $b2->inputCheck("p[$id][xxx]", false) . "</td>";
	} else {
		echo "<td $bg>In uso</td>";
	}
	echo "\n</tr>";
}

echo "\n</table>";
echo "\n<p align='center'><input type='submit' value='Conferma le modifiche' alt='Conferma le modifiche'></p>\n</form>";


piede();

// ### END OF FILE ###