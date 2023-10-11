<?php

/**
 * 
 * Gain Studios - Anagrafica articoli, distinta base
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141113 file creato
 * 20190202 conversione normalizza(),coloreriga() in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Distinta base");

// filtro che puo' arrivare un po' da ogni parte
$idarticolo = 0;
if (isset($_POST['idarticolo']) and is_numeric($_POST['idarticolo'])){
	$idarticolo = trim($_POST['idarticolo']);
}
if (isset($_GET['idarticolo']) and is_numeric($_GET['idarticolo'])){
	$idarticolo = trim($_GET['idarticolo']);
}
$idarticolo = $b2->normalizza($idarticolo);

if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $iddistinta=>$x) {
		if (isset($x['xxx'])) {
			$db->query("DELETE FROM distinta WHERE iddistinta='$iddistinta'");
		} else {
			$a = array();
			$a[] = "idarticolo='" . $b2->normalizza($x['idarticolo']) . "'";
			$a[] = "quantita='" . $b2->normalizza($x['quantita']) . "'";
			if ((0 == $iddistinta) and (0 != $x['idarticolo'])) {
				$a[] = "idpadre='$idarticolo'";
				$db->query("INSERT INTO distinta SET " . implode(',', $a));
			} else {
				if ($iddistinta > 0) {
					$db->query("UPDATE distinta SET " . implode(',', $a) . " WHERE iddistinta='$iddistinta'");
				}
			}
		}
	}
}

// precarico gli articoli
$aarticoli = array();
$aarticoli[0] = 'Nessuno';
$q = $db->query("SELECT idarticolo,articolo FROM articoli ORDER BY articolo");
while ($r = $q->fetch_array()) {
	if ($r['idarticolo'] != $idarticolo) $aarticoli[$r['idarticolo']] = $r['articolo'];
}


if ($idarticolo > 0) {
	echo "\n<form action='ana_distinta.php' method='post'>";
	echo "\n<input type='hidden' name='idarticolo' value='$idarticolo'/>";
	$r = $db->query("SELECT codice,articolo FROM articoli WHERE idarticolo='$idarticolo'")->fetch_array();
	echo "\n<p align='center'><b>Figli dell'articolo $r[codice] $r[articolo]</b></p>";
	
	$q = $db->query("SELECT * FROM distinta WHERE idpadre='$idarticolo'");

	echo "\n<table border='0' align='center'>";
	echo "\n<tr>
	          <th><b>Articolo</b></th>
	          <th><b>Quantit&agrave;</b></th>
	          <th><b>Cancella</b></th>
	        </tr>";
	
	// nuovo record
	$bg = $b2->bgcolor();
	echo "\n<tr $bg>";
	// idarticolo
	echo "<td $bg align='center'><select name='p[0][idarticolo]'>";
	foreach($aarticoli as $xidarticolo=>$xarticolo) echo "<option value='$xidarticolo'>$xarticolo</option>";
	echo "</select></td>";
	// quantita
	echo "<td $bg align='center'><input type='text' name='p[0][quantita]' value='' size='3' maxlength='3'/></td>";
	// xxx
	echo "<td $bg align='center'><b>Nuovo</b></td>";
	echo "\n</tr>";
	
	while ($r = $q->fetch_array()) {
		$bg = $b2->bgcolor();
		$id = $r['iddistinta'];
		echo "\n<tr $bg>";
		// idarticolo
		echo "<td $bg align='center'><select name='p[$id][idarticolo]'>";
		foreach ($aarticoli as $xidarticolo=>$xarticolo) {
			echo "<option value='$xidarticolo'";
			if ($xidarticolo == $r['idarticolo']) echo ' selected';
			echo ">$xarticolo</option>";
		}
		echo "</select></td>";
		// quantita
		$x = $b2->normalizza($r['quantita'], B2_NORM_FORM);
		echo "<td $bg align='center'><input type='text' name='p[$id][quantita]' value=\"$x\" size='3' maxlength='3'/></td>";
		// xxx
		echo "<td $bg align='center'><input type='checkbox' name='p[$id][xxx]' /></td>";
		echo "\n</tr>";
	}
	
	echo "\n</table>";
	echo "\n<p align='center'><input type='submit' value='Conferma le modifiche' alt='Conferma le modifiche'></p>\n</form>";

}

piede();

// ### END OF FILE ###