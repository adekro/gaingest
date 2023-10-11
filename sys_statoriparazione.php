<?php

/**
 * 
 * Gain Studios - Anagrafica stato riparazione
 * Copyright (C) 2016 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20160922 file creato
 * 20161112 ordine
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Anagrafica stati riparazione");

$acoll = array('V'=>"Vede",'M'=>"Modifica",'X'=>"Non vede",);


if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $idriparazionestato=>$x) {
		if (isset($x['xxx'])) {
			$db->query("DELETE FROM riparazionestato WHERE idriparazionestato='$idriparazionestato'");
		} else {
			$a = array();
			$a[] = $b2->campoSQL('riparazionestato', $x['riparazionestato']);
			$a[] = $b2->campoSQL('collaboratore', $x['collaboratore']);
			$a[] = $b2->campoSQL('ordine', $x['ordine']);
			$a[] = $b2->campoSQL('flag', $x['flag']);
			$xx = isset($x['isnew']) ? 1 : 0;
			$a[] = $b2->campoSQL('isnew', $xx);
			$xx = isset($x['isattesa']) ? 1 : 0;
			$a[] = $b2->campoSQL('isattesa', $xx);
			$xx = isset($x['islavoro']) ? 1 : 0;
			$a[] = $b2->campoSQL('islavoro', $xx);
			$xx = isset($x['ischiusa']) ? 1 : 0;
			$a[] = $b2->campoSQL('ischiusa', $xx);
			if ((0 == $idriparazionestato) and ('' != trim($x['riparazionestato']))) {
				$db->query("INSERT INTO riparazionestato SET " . implode(',', $a));
			} else {
				if ($idriparazionestato > 0) {
					$db->query("UPDATE riparazionestato SET " . implode(',', $a) . " WHERE idriparazionestato='$idriparazionestato'");
				}
			}
		}
	}
}
	
$q = $db->query("SELECT * FROM riparazionestato ORDER BY ordine");

echo "\n<form action='sys_statoriparazione.php' method='post'>";
echo "\n<table border='0' align='center'>";
echo $b2->intestazioneTabella(array('Ordine', 'Stato', 'Nuovo', 'Lavorazione', 'Attesa', 'Chiusa', "Accesso", "Flag", "Canc"));

// nuovo record
$bg = $b2->bgcolor();
$id = 0;
echo "\n<tr $bg>";
echo "<td $bg align='center'>" . $b2->inputText("p[$id][ordine]", '100', 8, 8, '', '', B2_IT_RIGHT) . "</td>";
echo "<td $bg align='center'>" . $b2->inputText("p[$id][riparazionestato]", '', 30, 100) . "</td>";
echo "<td $bg align='center'>" . $b2->inputCheck("p[$id][isnew]", false) . "</td>";
echo "<td $bg align='center'>" . $b2->inputCheck("p[$id][isattesa]", false) . "</td>";
echo "<td $bg align='center'>" . $b2->inputCheck("p[$id][islavoro]", false) . "</td>";
echo "<td $bg align='center'>" . $b2->inputCheck("p[$id][ischiusa]", false) . "</td>";
echo "<td $bg align='center'>" . $b2->inputSelect("p[$id][collaboratore]", $acoll, 'X') . "</td>";
echo "<td $bg align='center'>" . $b2->inputText("p[$id][flag]", '', 50, 1000) . "</td>";
echo "<td $bg align='center'><b>New</b></td>";

while ($r = $q->fetch_array()) {
$bg = $b2->bgcolor();
	$id = $r['idriparazionestato'];
	if ('1' == $r['isnew'])  { $xn = 'checked'; $xl = ''; $xp = ''; $xc = ''; }
	if ('1' == $r['islavoro']) { $xn = ''; $xl = 'checked'; $xp = ''; $xc = ''; }
	if ('1' == $r['isattesa'])  { $xn = ''; $xl = ''; $xp = 'checked'; $xc = ''; }
	if ('1' == $r['ischiusa']) { $xn = ''; $xl = ''; $xp = ''; $xc = 'checked'; }
	echo "\n<tr $bg>";
	echo "<td $bg align='center'>" . $b2->inputText("p[$id][ordine]", $r['ordine'], 8, 8, '', '', B2_IT_RIGHT) . "</td>";
	echo "<td $bg align='center'>" . $b2->inputText("p[$id][riparazionestato]", $r['riparazionestato'], 30, 100) . "</td>";
	echo "<td $bg align='center'>" . $b2->inputCheck("p[$id][isnew]", $r['isnew'] == '1') . "</td>";
	echo "<td $bg align='center'>" . $b2->inputCheck("p[$id][isattesa]", $r['isattesa'] == '1') . "</td>";
	echo "<td $bg align='center'>" . $b2->inputCheck("p[$id][islavoro]", $r['islavoro'] == '1') . "</td>";
	echo "<td $bg align='center'>" . $b2->inputCheck("p[$id][ischiusa]", $r['ischiusa'] == '1') . "</td>";
	echo "<td $bg align='center'>" . $b2->inputSelect("p[$id][collaboratore]", $acoll, $r['collaboratore']) . "</td>";
	echo "<td $bg align='center'>" . $b2->inputText("p[$id][flag]", $r['flag'], 50, 1000) . "</td>";
	echo "<td $bg align='center'>";
	$qq = $db->query("SELECT idriparazionestato FROM riparazione WHERE idriparazionestato='$id'");
	if ($qq->num_rows > 0) {
		echo "&nbsp;";
	} else {
		echo $b2->inputCheck("p[$id][xxx]", false);
	}
	echo "</td>";
	echo "\n</tr>";
}

echo "\n</table>";
echo "\n<p align='center'><input type='submit' value='Conferma le modifiche' alt='Conferma le modifiche'></p>";
echo "\n</form>";

piede();

// ### END OF FILE ###