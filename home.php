<?php

/**
 * 
 * Gain Studios - Menu principale
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2016 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141002 file creato
 * 20150807 modifica presenza
 * 20161111 tolto isriparatore dall'anagrafica utente e spostato a livello di livello
 *
 */

define('SOUNDPARK', true);
require('global.php');

// se e' un operatore, lo spedisco al planning
if ('1' == $_SESSION['utente']['isoperatore'] and '0' == $_SESSION['utente']['isriparatore']) {
	header('Location: pla_calendario.php');
	die();
}

// se l'utente e' un riparatore, mostro le cose che ha da riparare
if ('1' == $_SESSION['utente']['isriparatore']) {
	header('Location: rip_elenco.php');
	die();
}

intestazione('Menu principale');

/*
// impegni
$q = $db->query("SELECT planning.datainizio,planning.datafine,planning.titolo,planning.orainizio,planning.orafine,planning.dettaglio,planning.idplanning,
                        planningstato.planningstato,planningstato.colore AS coloreplanning,
                        clienti.ragsoc,
                        planningstatocol.planningstatocol,planningstatocol.colore AS coloreutente
                 FROM planning
                 JOIN planningutente ON planning.idplanning=planningutente.idplanning
                 JOIN planningstato ON planning.idplanningstato=planningstato.idplanningstato
                 JOIN planningstatocol ON planningutente.idplanningstatocol=planningstatocol.idplanningstatocol
                 LEFT JOIN clienti ON planning.idcliente=clienti.idcliente
                 WHERE planningutente.idutente='" . $_SESSION['utente']['idutente'] . "' AND planning.datainizio>=CURDATE()
                 ORDER BY planning.datainizio");
if ($q->num_rows > 0) {
	echo "\n<p>Prossimi impegni:<ul>";
	while ($r = $q->fetch_array()) {
		echo "<li><span style='color:$r[coloreplanning];'>";
		if ($r['datainizio'] == $r['datafine']) {
			echo $b2->dt2ita($r['datainizio']);
		} else {
			echo "dal " . $b2->dt2ita($r['datainizio']) . " al " . $b2->dt2ita($r['datafine']);
		}
		echo " $r[ragsoc] $r[titolo]:</span><br/>";
		echo "Stato del servizio: $r[planningstato]<br/>";
		echo "Tuo stato: <span style='color:$r[coloreutente];'>$r[planningstatocol]</span><br/>";
		if (trim("$r[orainizio]$r[orafine]") != '') echo "$r[orainizio] - $r[orafine]<br/>";
		if (trim("$r[dettaglio]") != '') echo "$r[dettaglio]<br/>";
		echo "<a href='col_editpresenza.php?idplanning=$r[idplanning]'><b>Modifica</b></a>";
		echo "</li>";
	}
	echo "</ul></p>";
} else {
	echo "\n<p>Nessun impegno previsto.</p>";
}                

*/


piede();

### END OF FILE ###