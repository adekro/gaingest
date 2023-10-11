<?php

/**
 * 
 * Gain Studios - Setup
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141002 file creato
 * 20190202 conversione normalizza(),coloreriga in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Setup");

if (isset($_POST['p'])) {
	foreach($_POST['p'] as $id=>$x) {
		$a = array();
		$a[] = "valore='" . $b2->normalizza($x['valore'], B2_NORM_SQL | B2_NORM_TRIM)) . "'";
		$db->query("UPDATE setup SET " . implode(',', $a) . " WHERE idsetup='$id'");
	}
}

$q = $db->query("SELECT * FROM setup ORDER BY item");

echo "\n<form action='sys_setup.php' method='post'>";
echo "\n<table border='0' cellpadding='2' cellspacing='0' align='center'>";
echo "\n<tr>
      <th><b>Opzione</b></th>
      <th><b>Valore</b></th>
      </tr>";

while ($r = $q->fetch_array()) {
	$bgcol = $b2->bgcolor();
	$id = $r['idsetup'];
	echo "\n<tr bgcolor='$bgcol'>";
	echo "<td align='left' bgcolor='$bgcol'>$r[descrizione] ($r[item])</td>";
	$x = $b2->normalizza($r['valore'], B2_NORM_FORM);
	echo "<td align='left' bgcolor='$bgcol'><input type='text' name='p[$id][valore]' value=\"$x\" size='50' maxlength='250'></td>";
	echo "</tr>";
}

echo "\n</table>";
echo "\n<p align='center'><input type='submit' value='Conferma le modifiche' alt='Conferma le modifiche'></p>\n</form>";

piede();

### END OF FILE ###