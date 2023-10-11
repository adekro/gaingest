<?php

/**
 * 
 * Gain Studios - Anagrafica strumenti musicali
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141003 file creato
 * 20190202 conversione normalizza(),coloreriga() in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Anagrafica strumenti musicali");

if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $idstrumento=>$x) {
		if (isset($x['xxx'])) {
			$db->query("DELETE FROM strumenti WHERE idstrumento='$idstrumento'");
		} else {
			$a = array();
			$a[] = "strumento='" . $b2->normalizza($x['strumento']) . "'";
			if (0 == $idstrumento and '' != trim($x['strumento'])) {
				$db->query("INSERT INTO strumenti SET " . implode(',', $a));
			} else {
				$db->query("UPDATE strumenti SET " . implode(',', $a) . " WHERE idstrumento='$idstrumento'");
			}
		}
	}
}

$q = $db->query("SELECT * FROM strumenti ORDER BY strumento");

echo "\n<form action='ana_strumenti.php' method='post'>";
echo "\n<table border='0' cellpadding='2' cellspacing='0' align='center'>";
echo "\n<tr>
          <th><b>Descrizione</b></th>
          <th><b>Del</b></th>
        </tr>";

// nuovo record
$bg = $b2->bgcolor();
echo "\n<tr $bg>";
// strumento
echo "<td $bg align='center'><input type='text' name='p[0][strumento]' value='' size='40' maxlength='100' /></td>";
// xxx
echo "<td $bg align='center'><b>Nuovo</b></td>";
echo "\n</tr>";

while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	$id = $r['idstrumento'];
	echo "\n<tr $bg>";
	// strumento
	$x = $b2->normalizza($r['strumento'], B2_NORM_FORM);
	echo "<td $bg align='center'><input type='text' name='p[$id][strumento]' value=\"$x\" size='40' maxlength='100' /></td>";
	// xxx
	$r1 = $db->query("SELECT COUNT(*) FROM clientistrumenti WHERE idstrumento='$id'")->fetch_array();
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