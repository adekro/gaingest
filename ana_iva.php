<?php

/**
 * 
 * Gain Studios - Anagrafica IVA
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141003 file creato
 * 20190202 conversione normalizza(),coloreriga() in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Anagrafica IVA");

if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $idiva=>$x) {
		if (isset($x['xxx'])) {
			$db->query("DELETE FROM job WHERE idiva='$idiva'");
		} else {
			$a = array();
			$a[] = "iva='" . $b2->normalizza($x['iva']) . "'";
			$a[] = "percento='" . ($x['pctint'] * 100 + $x['pctdec']) . "'";
			$a[] = "testofattura='" . $b2->normalizza($x['testofattura']) . "'";
			if ((0 == $idiva) and ('' != trim($x['iva']))) {
				$db->query("INSERT INTO iva SET " . implode(',', $a));
			} else {
				$db->query("UPDATE iva SET " . implode(',', $a) . " WHERE idiva='$idiva'");
			}
		}
	}
}

$flipflop = TRUE;

$q = $db->query("SELECT * FROM iva ORDER BY iva");

echo "\n<form action='ana_iva.php' method='post'>";
echo "\n<table border='0' cellpadding='2' cellspacing='0' align='center'>";
echo "\n<tr>
          <th><b>Descrizione</b></th>
          <th><b>%</b></th>
          <th><b>Testo fattura</b></th>
          <th><b>Del</b></th>
        </tr>";

// nuovo record
$bg = $b2->bgcolor();
echo "\n<tr $bg>";
// iva
echo "<td $bg align='center'><input type='text' name='p[0][iva]' value='' size='20' maxlength='100' /></td>";
// percento
echo "<td $bg align='center'><input type='text' name='p[0][pctint]' value='' size='3' maxlength='2'/>,<input type='text' name='p[0][pctdec]' value='' size='3' maxlength='2'/></td>";
// testofattura
echo "<td $bg align='center'><input type='text' name='p[0][testofattura]' value='' size='40' maxlength='250' /></td>";
// xxx
echo "<td $bg align='center'><b>Nuova</b></td>";
echo "\n</tr>";

while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	$id = $r['idiva'];
	echo "\n<tr $bg>";
	// iva
	$x = $b2->normalizza($r['iva'], B2_NORM_FORM);
	echo "<td $bg align='center'><input type='text' name='p[$id][iva]' value=\"$x\" size='20' maxlength='100' /></td>";
	//percento
	$xint = floor($r['percento'] / 100);
	$xdec = $r['percento'] % 100;
	echo "<td $bg align='center'><input type='text' name='p[$id][pctint]' value=\"$xint\" size='3' maxlength='2'/>,<input type='text' name='p[$id][pctdec]' value=\"$xdec\" size='3' maxlength='2'/></td>";
	// testofattura
	$x = $b2->normalizza($r['testofattura'], B2_NORM_FORM);
	echo "<td $bg align='center'><input type='text' name='p[$id][testofattura]' value=\"$x\" size='40' maxlength='250' /></td>";
	// xxx
	$r1 = $db->query("SELECT COUNT(*) FROM clienti WHERE idiva='$id'")->fetch_array();
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