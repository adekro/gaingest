<?php

/**
 * 
 * Gain Studios - Modifica abilitazioni
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 20152016 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141002 file creato
 * 20161130 conversione a jQuery
 *
 */

define('SOUNDPARK', true);
require('global.php');

// gestione post, inutile fare due file per 10 righe
if (isset($_POST['questoid'])) {
	//error_log(print_r($_POST, TRUE));
	list($nonserve1,$nonserve2,$idlivello,$idmenu) = explode('-', $_POST['questoid']);
	if ('true' == $_POST['ischecked']) {
		$db->query("INSERT INTO abilitazioni SET idlivello='$idlivello',idmenu='$idmenu'");
	} else {
		$db->query("DELETE FROM abilitazioni WHERE idlivello='$idlivello' AND idmenu='$idmenu'");
	}
	die();
}

// precarico i livelli
$alivelli = array();
$q = $db->query("SELECT * FROM livello ORDER BY livello");
while ($r = $q->fetch_array()) $alivelli[$r['idlivello']] = $r['livello'];

intestazione("Abilitazioni");

echo "\n<table border='0' align='center'>";

echo "\n<tr><th valign='bottom' aling='center'><b>Voce di menu</b></th>";
foreach ($alivelli as $livello) echo "<th valign='bottom' aling='center'><b>$livello</b></th>";
echo "</tr>";

$contaspan = 1;
$q = $db->query("SELECT idmenu,menu FROM menu WHERE idpadre='0' ORDER BY peso");

while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	echo "\n<tr $bg>";
	echo "<td $bg align='left'><b>$r[menu]</b></td>";
	foreach ($alivelli as $idlivello=>$livello) {
		$qq = $db->query("SELECT idlivello FROM abilitazioni WHERE idlivello='$idlivello' AND idmenu='$r[idmenu]'");
		$x = $qq->num_rows > 0 ? 'checked' : '';
		echo "<td $bg align='center'><input type='checkbox' $x id='campo-$contaspan-$idlivello-$r[idmenu]' class='abilita'/></td>";
		$contaspan++;
	}
	echo "</tr>";
	// tiene figli?
	$qf = $db->query("SELECT idmenu,menu FROM menu WHERE idpadre='$r[idmenu]' ORDER BY peso");
	if ($qf->num_rows > 0) {
		while ($rf = $qf->fetch_array()) {
			echo "\n<tr $bg>";
			$bg = $b2->bgcolor();
			echo "<td $bg align='left'>&nbsp;&nbsp;$rf[menu]</td>";
			foreach ($alivelli as $idlivello=>$livello) {
				$qq = $db->query("SELECT idlivello FROM abilitazioni WHERE idlivello='$idlivello' AND idmenu='$rf[idmenu]'");
				$x = $qq->num_rows > 0 ? 'checked' : '';
				echo "<td $bg align='center'><input type='checkbox' $x id='campo-$contaspan-$idlivello-$rf[idmenu]' class='abilita'/></td>";
				$contaspan++;
			}
			echo "</tr>";
		}
	}
}

echo "\n<tr><th valign='bottom' aling='center'><b>Voce di menu</b></th>";
foreach ($alivelli as $livello) echo "<th valign='top' aling='center'><b>$livello</b></th>";
echo "</tr>";

echo "\n</table>";

?>
<script language="Javascript">
$(document).ready(function() {
	$(".abilita").click(function() { 
		var questoid = $(this).attr('id');
		var ischecked = $(this).is(':checked');
		$.post("sys_abilitazioni.php", {questoid: questoid, ischecked: ischecked});
	}) // class
}) // ready

</script>
<?php

piede();

// ### END OF FILE ###