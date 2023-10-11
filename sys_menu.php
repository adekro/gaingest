<?php

/**
 * 
 * Gain Studios - Gestione menu
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141002 file creato
 * 20190202 conversione normalizza(),coloreriga in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Gstione menu");

if (isset($_POST['p'])) {
	foreach($_POST['p'] as $id=>$x) {
		$a = array();
		$peso = is_numeric($x['peso']) ? $x['peso'] : 1000;
		if ($peso < 0) $peso = 1000;
		$a[] = "peso='$peso'";
		$menu = trim('' == $x['menu']) ? '&nbsp;' : $x['menu'];
		$a[] = "menu='" . $b2->normalizza($menu, B2_NORM_SQL | B2_NORM_TRIM) . "'";
		$db->query("UPDATE menu SET " . implode(',', $a) . " WHERE idmenu='$id'");
	}
}

$q = $db->query("SELECT * FROM menu WHERE idpadre='0' ORDER BY peso");

echo "\n<form action='menu.php' method='post'>";
echo "\n<p align='center'><input type='submit' value='Conferma le modifiche' alt='Conferma le modifiche'></p>";
echo "\n<table border='0' cellpadding='2' cellspacing='0' align='center'>";
echo "\n<tr>
      <th><b>Peso</b></th>
      <th><b>Menu</b></th>
      </tr>";

while ($r = $q->fetch_array()) {
	$bgcolor = $b2->bgcolor();
	$id = $r['idmenu'];
	echo "\n<tr $bgcolor>";
	$x = $b2->normalizza($r['peso'], B2_NORM_FORM);
	echo "<td $bgcolor align='center'><input type='text' name='p[$id][peso]' value=\"$x\" size='10' maxlength='10'></td>";
	if ('&nbsp;' == $r['menu']) {
		echo "<td $bgcolor align='left'>Separatore<input type='hidden' name='p[$id][menu]' value=''></td>";
	} else {
		$x = $b2->normalizza($r['menu'], B2_NORM_FORM);
		echo "<td $bgcolor align='left'><input type='text' name='p[$id][menu]' value=\"$x\" size='30' maxlength='50'></td>";
	}
	echo "</tr>";
	// tiene figli?
	$qf = $db->query("SELECT * FROM menu WHERE idpadre='$r[idmenu]' ORDER BY peso");
	if ($qf->num_rows > 0) {
		while ($rf = $qf->fetch_array()) {
			$bgcolor = $b2->bgcolor();
			$id = $rf['idmenu'];
			echo "\n<tr>";
			$x = $b2->normalizza($rf['peso'], B2_NORM_FORM);
			echo "<td $bgcolor align='center'><input type='text' name='p[$id][peso]' value=\"$x\" size='10' maxlength='10'></td>";
			if ('&nbsp;' == $rf['menu']) {
				echo "<td $bgcolor align='left'>&nbsp;&nbsp;Separatore<input type='hidden' name='p[$id][menu]' value=''></td>";
			} else {
				$x = $b2->normalizza($rf['menu']);
				echo "<td $bgcolor align='left'>&nbsp;&nbsp;<input type='text' name='p[$id][menu]' value=\"$x\" size='30' maxlength='50'></td>";
			}
			echo "</tr>";
		}
	}
}

echo "\n</table>";
echo "\n<p align='center'><input type='submit' value='Conferma le modifiche' alt='Conferma le modifiche'></p>\n</form>";

piede();

### END OF FILE ###