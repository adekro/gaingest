<?php

/**
 * 
 * Gain Studios - Anagrafica pagamenti
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141003 file creato
 * 20190202 conversione normalizza(),coloreriga() in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Anagrafica pagamenti");

if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $idpagamento=>$x) {
		if (isset($x['xxx'])) {
			$db->query("DELETE FROM pagamenti WHERE idpagamento='$idpagamento'");
		} else {
			$a = array();
			$a[] = "pagamento='" . $b2->normalizza($x['pagamento']) . "'";
			$a[] = "giorni='" . $b2->normalizza($x['giorni']) . "'";
			$isfinemese = isset($x['isfinemese']) ? '1' : '0';
			$a[] = "isfinemese='$isfinemese'";
			$a[] = "banca1='" . $b2->normalizza($x['banca1']) . "'";
			$a[] = "banca2='" . $b2->normalizza($x['banca2']) . "'";
			$a[] = "iban='" . $b2->normalizza($x['iban']) . "'";
			if (0 == $idpagamento and '' != trim($x['pagamento'])) {
				$db->query("INSERT INTO pagamenti SET " . implode(',', $a));
			} else {
				$db->query("UPDATE pagamenti SET " . implode(',', $a) . " WHERE idpagamento='$idpagamento'");
			}
		}
	}
}

$q = $db->query("SELECT * FROM pagamenti ORDER BY pagamento");

echo "\n<form action='ana_pagamenti.php' method='post'>";
echo "\n<table border='0' cellpadding='2' cellspacing='0' align='center'>";
echo "\n<tr>
          <th><b>Descrizione</b></th>
          <th><b>Giorni</b></th>
          <th><b>Finemese</b></th>
          <th><b>Banca</b></th>
          <th><b>IBAN</b></th>
          <th><b>Del</b></th>
        </tr>";

// nuovo record
$bg = $b2->bgcolor();
echo "\n<tr $bg>";
// pagamento
echo "<td $bg align='center'><input type='text' name='p[0][pagamento]' value='' size='20' maxlength='100'/></td>";
// giorni
echo "<td $bg align='center'><input type='text' name='p[0][giorni]' value='' size='4' maxlength='3'/></td>";
// isfinemese
echo "<td $bg align='center'><input type='checkbox' name='p[0][isfinemese]'/></td>";
// banca
echo "<td $bg align='center'><input type='text' name='p[0][banca1]' value='' size='40' maxlength='250'/><br/><input type='text' name='p[0][banca2]' value='' size='40' maxlength='250'/></td>";
// iban
echo "<td $bg align='center'><input type='text' name='p[0][iban]' value='' size='20' maxlength='50'/></td>";
// xxx
echo "<td $bg align='center'><b>Nuovo</b></td>";
echo "\n</tr>";

while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	$id = $r['idpagamento'];
	echo "\n<tr $bg>";
	// pagamento
	$x = $b2->normalizza($r['pagamento'], B2_NORM_FORM);
	echo "<td $bg align='center'><input type='text' name='p[$id][pagamento]' value=\"$x\" size='20' maxlength='100'/></td>";
	// giorni
	$x = $b2->normalizza($r['giorni']);
	echo "<td $bg align='center'><input type='text' name='p[$id][giorni]' value=\"$x\" size='4' maxlength='3'/></td>";
	// isfinemese
	$x = '1' == $r['isfinemese'] ? 'checked' : '';
	echo "<td $bg align='center'><input type='checkbox' name='p[$id][isfinemese]' $x/></td>";
	// banca
	$x1 = $b2->normalizza($r['banca1'], B2_NORM_FORM);
	$x2 = $b2->normalizza($r['banca2'], B2_NORM_FORM);
	echo "<td $bg align='center'><input type='text' name='p[$id][banca1]' value=\"$x1\" size='40' maxlength='250'/><br/><input type='text' name='p[$id][banca2]' value=\"$x2\" size='40' maxlength='250'/></td>";
	// iban
	$x = $b2->normalizza($r['iban'], B2_NORM_FORM);
	echo "<td $bg align='center'><input type='text' name='p[$id][iban]' value=\"$x\" size='20' maxlength='50'/></td>";
	// xxx
	$r1 = $db->query("SELECT COUNT(*) FROM clienti WHERE idpagamento='$id'")->fetch_array();
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