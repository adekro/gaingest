<?php

/**
 * 
 * Gain Studios - Anagrafica categorie articoli
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141112 file creato
 * 20190202 conversione normalizza(),coloreriga() in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Anagrafica categorie articoli");

if (isset($_POST['p'])) {
	foreach ($_POST['p'] as $idcategoria=>$x) {
		if (isset($x['xxx'])) {
			$db->query("DELETE FROM categoria WHERE idcategoria='$idcategoria'");
		} else {
			if (is_numeric($x['codice'])) {
				$a = array();
				$a[] = "codice='" . $b2->normalizza($x['codice']) . "'";
				$a[] = "categoria='" . $b2->normalizza($x['categoria']) . "'";
				$a[] = "tipo='" . $b2->normalizza($x['tipo']) . "'";
				if (0 == $idcategoria and '' != trim($x['categoria'])) {
					$db->query("INSERT INTO categoria SET " . implode(',', $a));
				} else {
					$db->query("UPDATE categoria SET " . implode(',', $a) . " WHERE idcategoria='$idcategoria'");
				}
			}
		}
	}
}

$q = $db->query("SELECT * FROM categoria ORDER BY codice");

echo "\n<form action='ana_categorie.php' method='post'>";
echo "\n<table border='0' cellpadding='2' cellspacing='0' align='center'>";
echo "\n<tr>
          <th><b>Codice</b></th>
          <th><b>Categoria</b></th>
          <th><b>Tipo</b></th>
          <th><b>Del</b></th>
        </tr>";

// nuovo record
$bg = $b2->bgcolor();
echo "\n<tr $bg>";
// codice
echo "<td $bg align='center'><input type='text' name='p[0][codice]' value='' size='5' maxlength='4' /></td>";
// categoria
echo "<td $bg align='center'><input type='text' name='p[0][categoria]' value='' size='30' maxlength='250' /></td>";
// tipo
echo "<td align='center'>";
echo "<label><input type='radio' name='p[0][tipo]' value='N' checked/> Noleggio</label>&nbsp;&nbsp;&nbsp;";
echo "<label><input type='radio' name='p[0][tipo]' value='V'/> Vendita</label>&nbsp;&nbsp;&nbsp;";
echo "<label><input type='radio' name='p[0][tipo]' value='S'/> Senza magazzino</label>&nbsp;&nbsp;&nbsp;";
echo "</td>";
// xxx
echo "<td $bg align='center'><b>Nuova</b></td>";
echo "\n</tr>";

while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	$id = $r['idcategoria'];
	echo "\n<tr $bg>";
	// codice
	$x = $b2->normalizza($r['codice'], B2_NORM_FORM);
	echo "<td $bg align='center'><input type='text' name='p[$id][codice]' value=\"$x\" size='5' maxlength='4' /></td>";
	// categoria
	$x = $b2->normalizza($r['categoria'], B2_NORM_FORM);
	echo "<td $bg align='center'><input type='text' name='p[$id][categoria]' value=\"$x\" size='30' maxlength='250' /></td>";
	// tipo
	echo "<td align='center'>";
	$x = 'N' == $r['tipo'] ? 'checked' : ''; // noleggio
	echo "<label><input type='radio' name='p[$id][tipo]' value='N' $x/> Noleggio</label>&nbsp;&nbsp;&nbsp;";
	$x = 'V' == $r['tipo'] ? 'checked' : ''; // vendita
	echo "<label><input type='radio' name='p[$id][tipo]' value='V' $x/> Vendita</label>&nbsp;&nbsp;&nbsp;";
	$x = 'S' == $r['tipo'] ? 'checked' : ''; // no magazzino
	echo "<label><input type='radio' name='p[$id][tipo]' value='S' $x/> Senza magazzino</label>&nbsp;&nbsp;&nbsp;";
	echo "</td>";
	// xxx
	$r1 = $db->query("SELECT COUNT(*) FROM articoli WHERE idcategoria='$id'")->fetch_array();
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