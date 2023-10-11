<?php

/**
 * 
 * Gain Studios - Anagrafica tipo cliente
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141003 file creato
 * 20190202 conversione normalizza(),coloreriga() in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Anagrafica tipi di clienti");

if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $idtipocli=>$x) {
		if (isset($x['xxx'])) {
			$db->query("DELETE FROM tipocli WHERE idtipocli='$idtipocli'");
		} else {
			$a = array();
			$a[] = "tipocli='" . $b2->normalizza($x['tipocli']) . "'";
			if (!isset($x['ps'])) $x['ps'] = 'P';
			$a[] = "ps='" . $b2->normalizza($x['ps']) . "'";
			if (0 == $idtipocli and '' != trim($x['tipocli'])) {
				$db->query("INSERT INTO tipocli SET " . implode(',', $a));
			} else {
				$db->query("UPDATE tipocli SET " . implode(',', $a) . " WHERE idtipocli='$idtipocli'");
			}
		}
	}
}

$q = $db->query("SELECT * FROM tipocli ORDER BY tipocli");

echo "\n<form action='ana_tipocli.php' method='post'>";
echo "\n<table border='0' cellpadding='2' cellspacing='0' align='center'>";
echo "\n<tr>
          <th><b>Descrizione</b></th>
          <th><b>Persona/Societ&agrave;</b></th>
          <th><b>Del</b></th>
        </tr>";

// nuovo record
$bg = $b2->bgcolor();
echo "\n<tr $bg>";
// tipocli
echo "<td $bg align='center'><input type='text' name='p[0][tipocli]' value='' size='20' maxlength='100' /></td>";
// ps
echo "<td $bg align='center'><label><input type='radio' name='p[0][ps]' value='P'/>Persona</label> <label><input type='radio' name='p[0][ps]' value='S'/>Societ&agrave;</label></td>";
// xxx
echo "<td $bg align='center'><b>Nuovo</b></td>";
echo "\n</tr>";

while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	$id = $r['idtipocli'];
	echo "\n<tr $bg>";
	// tipocli
	$x = $b2->normalizza($r['tipocli'], B2_NORM_FORM);
	echo "<td $bg align='center'><input type='text' name='p[$id][tipocli]' value=\"$x\" size='20' maxlength='100' /></td>";
	//ps
	if ('P' == $r['ps']) {
		echo "<td $bg align='center'><label><input type='radio' name='p[$id][ps]' value='P' checked/>Persona</label> <label><input type='radio' name='p[$id][ps]' value='S'/>Societ&agrave;</label></td>";
	} else {
		echo "<td $bg align='center'><label><input type='radio' name='p[$id][ps]' value='P'/>Persona</label> <label><input type='radio' name='p[$id][ps]' value='S' checked/>Societ&agrave;</label></td>";
	}
	// xxx
	$r1 = $db->query("SELECT COUNT(*) FROM clienti WHERE idtipocli='$id'")->fetch_array();
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