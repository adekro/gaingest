<?php

/**
 * 
 * Gain Studios - fattura, elecno
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20150521 file creato
 * 20161001 rifacimento da Matrix
 *
 */

define('SOUNDPARK', true);
require('global.php');

// anno corrente delle fatture
if (!isset($_SESSION['annofatture'])) $_SESSION['annofatture'] = date('Y');

if (isset($_POST['azione'])) {
	switch ($_POST['azione']) {
		case 'mail':
			creafattura($b2->normalizza($_POST['idfattura']), '*mail');
		break;
	}
}

if (isset($_GET['azione'])) {
	switch ($_GET['azione']) {
		case 'duplica':
			$idfattura = $b2->normalizza($_GET['idfattura']);
			// testata
			$db->query("INSERT INTO fatture (tipofattura,cliente,indirizzo1,indirizzo2,indirizzo3,piva,cf,idpagamento,idcliente,tariffa,iva,costoorario,totale,dicestremi,dettaglioesenzione,isesenteivadic)
			            SELECT tipofattura,cliente,indirizzo1,indirizzo2,indirizzo3,piva,cf,idpagamento,idcliente,tariffa,iva,costoorario,totale,dicestremi,dettaglioesenzione,isesenteivadic
			            FROM fatture
			            WHERE idfattura='$idfattura'");
			$idnuovo = $db->insert_id;
			// numero
			$q = $db->query("SELECT numero FROM fattura WHERE anno='" . date('Y') . "' ORDER BY numero DESC LIMIT 1");
			if ($q->num_rows > 0) {
				$r = $q->fetch_array();
				$numerofattura = $r['numero'] + 1;
			} else {
				$numerofattura = 1;
			}
			$db->query("UPDATE fatture SET data=CURDATE(),anno=YEAR(CURDATE()),ispagata='0',pagato='0',speditastudio='0',numero='$numerofattura' WHERE idfattura='$idnuovo'");
			// dettaglio
			$db->query("INSERT INTO fattured (idfattura,ordine,descrizione,importo,iva,descrizioneeng,ore,idrapportino)
			            SELECT $idnuovo,ordine,descrizione,importo,iva,descrizioneeng,ore,0
			            FROM fattured
			            WHERE idfattura='$idfattura'");
		break;
	}
	
}

intestazione("Elenco fatture $_SESSION[annofatture]");

echo "\n<form method='post' action='doc_fattura.php'><table border='0' align='right'>";
echo "\n<tr><td align='right'><b>Anno:</b></td><td align='left'><input type='text' name='annofatture' value='$_SESSION[annofatture]' size='5' maxlenght='4'></td></tr>";
echo "\n<tr><td align='center' colspan='2'><input type='submit' value='Conferma'></td></tr>";
echo "\n<tr><td align='center' colspan='2'><a href='doc_fatturaedit.php?idfattura=0'>Nuova fattura</a></td></tr>";
echo "\n</table></form>";

$q = $db->query("SELECT fattura.idfattura,fattura.data,fattura.numero,fattura.ispagata,fattura.ragsoc
                 FROM fattura
                 JOIN clienti ON fattura.idcliente=clienti.idcliente
                 WHERE fattura.anno='$_SESSION[annofatture]'
                 ORDER BY numero DESC");

if ($q->num_rows > 0) {
	echo "\n<table align='center' border='0' cellpadding='3' cellspacing='0'>";
	/*
	echo "\n<tr>
	      <th align='center'><b>&#8364;</b></th>
	      <th align='center'><b>Cliente</b></th>
	      <th align='center'><b>Fattura</b></th>
	      <th align='center' colspan='4'><b>Comandi</b></th>
	      </tr>";
	*/
	while ($r = $q->fetch_array()) {
		$bg = $b2->bgcolor();
		echo "\n<tr $bg>";
		$x = '1' == $r['ispagata'] ? '&#9786;' : '&nbsp;';
		echo "<td $bg align='center'><span class='smile'>$x</span></td>";
		echo "<td $bg align='left'>$r[ragsoc]</td>";
		echo "<td $bg align='center'>$r[numero] del " . $b2->dt2ita($r['data']) . "</td>";
/*		//mail
		echo "<td $bg align='center'>";
		echo "<form action='bko_fatture.php' method='post'>";
		echo "<input type='hidden' name='azione' value='mail'><input type='hidden' name='idfattura' value='$r[idfattura]'>";
		echo "<input type='submit' value='Spedisci'></form>";
		echo "</td>";
		// visualizza
		echo "<td $bg align='center'>";
		echo "<a href='bko_fatturevis.php?idfattura=$r[idfattura]' target='_blank'>Visualizza</a>";
		echo "</td>";
*/
		// edit righe
		echo "<td $bg align='center'>";
		echo "<a href='doc_fatturarighe.php?idfattura=$r[idfattura]'>Modifica righe</a>";
		echo "</td>";
		// edit testa
		echo "<td $bg align='center'>";
		echo "<a href='doc_fatturaedit.php?idfattura=$r[idfattura]'>Modifica intestazione</a>";
		echo "</td>";
		//
		echo "</tr>";
	}
	echo "\n</table>";
} else {
	echo "\n<p align='center'>Nessuna fattura registrata</p>";
}

echo "\n<p>&nbsp;</p>";

piede();

### END OF FILE ###