<?php

/**
 * 
 * Gain Studios - Anagrafica causali DDT
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20150310 file creato
 * 20190202 conversione normalizza(),coloreriga() in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Anagrafica causali DDT");

if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $idcausaleddt=>$x) {
		if (isset($x['xxx'])) {
			$db->query("DELETE FROM causaleddt WHERE idcausaleddt='$idcausaleddt'");
		} else {
			$a = array();
			$a[] = "causaleddt='" . $b2->normalizza($x['causaleddt']) . "'";
			if (0 == $idcausaleddt and '' != trim($x['causaleddt'])) {
				$db->query("INSERT INTO causaleddt SET " . implode(',', $a));
			} else {
				$db->query("UPDATE causaleddt SET " . implode(',', $a) . " WHERE idcausaleddt='$idcausaleddt'");
			}
		}
	}
}

$q = $db->query("SELECT * FROM causaleddt ORDER BY causaleddt");

echo "\n<form action='ana_causaleddt.php' method='post'>";
echo "\n<table border='0' cellpadding='2' cellspacing='0' align='center'>";
echo "\n<tr>
          <th><b>Causale</b></th>
          <th><b>Del</b></th>
        </tr>";

// nuovo record
$bg = $b2->bgcolor();
echo "\n<tr $bg>";
// causaleddt
echo "<td $bg align='center'><input type='text' name='p[0][causaleddt]' value='' size='50' maxlength='200' /></td>";
// xxx
echo "<td $bg align='center'><b>Nuova</b></td>";
echo "\n</tr>";

while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	$id = $r['idcausaleddt'];
	echo "\n<tr $bg>";
	// causaleddt
	$x = $b2->normalizza($r['causaleddt'], B2_NORM_FORM);
	echo "<td $bg align='center'><input type='text' name='p[$id][causaleddt]' value=\"$x\" size='50' maxlength='200' /></td>";
	// xxx
	$r1 = $db->query("SELECT COUNT(*) FROM ddt WHERE idcausaleddt='$id'")->fetch_array();
	if (0 == $r1[0]) {
		echo "<td $bg align='center'><input type='checkbox' name='p[$id][xxx]' /></td>";
	} else {
		echo "<td $bg>In uso</td>";
	}
	echo "\n</tr>";
}

echo "\n</table>";
echo "\n<p align='center'><input type='submit' value='Conferma le modifiche' alt='Conferma le modifiche'></p>\n</form>";


piede();

// ### END OF FILE ###