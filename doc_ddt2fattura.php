<?php

/**
 * 
 * Gain Studios - Fattura in base al DDT
 * Copyright (C) 2015-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20150615 file creato
 * 20190202 conversione normalizza() in B2TOOLS
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

if (isset($_GET['idddt']) and is_numeric($_GET['idddt'])) {
	$qt = $db->query("SELECT * FROM ddt WHERE idddt='" . $b2->normalizza($_GET['idddt']) . "'");
	if ($qt->num_rows > 0) {
		$rt = $qt->fetch_array();
		$a = array();
		$a[] = "data=CURDATE()";
		$a[] = "anno=YEAR(CURDATE())";
		$a[] = "stato='L'";
		$a[] = "idcliente='" . $b2->normalizza($rt['idcliente']) . "'";
		$a[] = "idddt='" . $b2->normalizza($rt['idddt']) . "'";
		if ($rt['idcliente'] > 0) {
			$rc = $db->query("SELECT ragsoc,cognome,nome,destfattura1,destfattura2,destfattura3,cf,piva,idiva FROM clienti WHERE idcliente='" . $b2->normalizza($rt['idcliente']) . "'")->fetch_array();
			$intestazione = '' == trim($rc['ragsoc']) ? "$rc[cognome] $rc[nome]" : $rc['ragsoc'];
			$a[] = "intestazione='" . $b2->normalizza($intestazione) . "'";
			$a[] = "indirizzo1='" . $b2->normalizza($rc['destfattura1']) . "'";
			$a[] = "indirizzo2='" . $b2->normalizza($rc['destfattura2']) . "'";
			$a[] = "indirizzo3='" . $b2->normalizza($rc['destfattura3']) . "'";
			$a[] = "cf='" . $b2->normalizza($rc['cf']) . "'";
			$a[] = "piva='" . $b2->normalizza($rc['piva']) . "'";
			$a[] = "idiva='" . $b2->normalizza($rc['idiva']) . "'";
		}
		$db->query("INSERT INTO fattura SET " . implode(',', $a));
		$idfattura = $db->insert_id;
		$qd = $db->query("SELECT * FROM ddtrighe WHERE idddt='" . $b2->normalizza($_GET['idddt']) . "'");
		while ($rd = $qd->fetch_array()) {
			$a = array();
			$a[] = "idfattura='" . $b2->normalizza($idfattura) . "'";
			$a[] = "riga='" . $b2->normalizza($rd['riga']) . "'";
			$a[] = "descrizione='" . $b2->normalizza($rd['descrizione']) . "'";
			$a[] = "qta='" . $b2->normalizza($rd['qta']) . "'";
			if ('' != trim($rd['codice'])) {
				$a[] = "codice='" . $b2->normalizza($rd['codice']) . "'";
				$qa = $db->query("SELECT vendita FROM articoli WHERE codice='" . $b2->normalizza($rd['codice']) . "'");
				if ($qa->num_rows > 0) {
					$ra = $qa->fetch_array();
					$a[] = "prezzo='" . $b2->normalizza($ra['vendita']) . "'";
				}
			}
			$db->query("INSERT INTO fatturarighe SET " . implode(',', $a));
		}
	}
	intestazione("Fattura creata");
	echo "\n<p align='center'><a href='doc_fatturaedit.php?idfattura=$idfattura'>Vai al dettaglio della fattura</a> oppure <a href='doc_fatturarighe.php?idfattura=$idfattura'>vai alle righe della fattura</a></p>";
	piede();
	die();
}
header('Location: doc_ddt.php');


### END OIF FILE ###