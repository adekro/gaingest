<?php

/**
 * 
 * Gain Studios - Calendario mensile planning
 * Copyright (C) 2015-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20150617 file creato
 * 20152525 se l'utente non e' admin, vede solo i suoi impegni; singola data
 * 20170213 oggi colorato diverso
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

function cambiamese($dir, $m, $a) {
	switch ($dir) {
		case '-':
			if ($m > 1) {
				$aretval = array('m'=> ($m-1), 'a'=> $a);
			} else {
				$aretval = array('m'=> 12, 'a'=> ($a-1));
			}
		break;
		case '+':
			if ($m < 12) {
				$aretval = array('m'=> ($m+1), 'a'=> $a);
			} else {
				$aretval = array('m'=> 1, 'a'=> ($a+1));
			}
		break;
	}
	return $aretval;
}	

// default
if (isset($_SESSION['plageneralemese'])) {
	$mese = $_SESSION['plageneralemese'];
} else {
	$mese = date('m');
}
if (isset($_SESSION['plageneraleanno'])) {
	$anno = $_SESSION['plageneraleanno'];
} else {
	$anno = date('Y');
}
// GET
if (isset($_GET['m']) and is_numeric($_GET['m']) and $_GET['m'] >= 1 and $_GET['m'] <= 12) {
	$mese = $_GET['m'];
}
if (isset($_GET['a']) and is_numeric($_GET['a']) and $_GET['a'] >= 2015) {
	$anno = $_GET['a'];
}
$_SESSION['plageneralemese'] = $mese;
$_SESSION['plageneraleanno'] = $anno;
$mese = sprintf("%02d", $mese);

intestazione("Planning " . $b2->dtMeseIta($mese) . " $anno");

// codice di paginazione
$acomandi = array();
$paginazione = "\n<table border='0' align='center'><tr>";
$ad = cambiamese('-', $mese, $anno);
if ($ad['m'] == 1 and $ad['a'] == 2015) {
	$paginazione .= "<td>&nbsp;</td>";
} else {
	$paginazione .= "<td align='left'><a href='pla_calendario.php?m=$ad[m]&amp;a=$ad[a]'>&lt;&lt;&lt;" . $b2->dtMeseIta($ad['m']) . " $ad[a]</a> || </td>";
}
if ('1' == $_SESSION['utente']['isadmin']) {
	$acomandi[] = "<a href='pla_dettaglioplanning.php?idplanning=0'>Nuovo servizio singolo</a>";
	$acomandi[] = "<a href='pla_nuovomultiplo.php'>Nuovo servizio multiplo</a>";
	$acomandi[] = "<a href='pla_nuovassenza.php'>Nuova assenza</a>";
} else {
	$acomandi[] = "<a href='pla_nuovassenza.php'>Nuova assenza</a>";
}
$paginazione .= "<td>&nbsp;" . implode('&nbsp;&nbsp;&bull;&nbsp;&nbsp;', $acomandi) . "</td>";
$ad = cambiamese('+', $mese, $anno);
$paginazione .= "<td align='right'> || <a href='pla_calendario.php?m=$ad[m]&amp;a=$ad[a]'>" . $b2->dtMeseIta($ad['m']) . " $ad[a]&gt;&gt;&gt;</a></td>";
$paginazione .= "</tr></table>";

echo $paginazione;

/* draw table */
echo "\n<table cellpadding='0' cellspacing='0' class='calendar' align='center'>";

/* table headings */
$headings = array();
for ($n = 1 ; $n <=7 ; $n++) $headings[] = $b2->dtDowIta($n, B2_DT_DOWLUNGO);
echo "\n<tr class='calendar-row'><td class='calendar-day-head'>"  . implode('</td><td class="calendar-day-head">', $headings) . '</td></tr>';

/* days and weeks vars now ... */
$running_day = date('N', mktime(0, 0, 0, $mese, 1, $anno)) - 1;
$days_in_month = date('t', mktime(0, 0, 0, $mese, 1, $anno));
$days_in_this_week = 1;
$day_counter = 0;
$dates_array = array();

/* row for week one */
echo "\n<tr class='calendar-row'>";

/* print "blank" days until the first of the current week */
for ($x = 0 ; $x < $running_day ; $x++) {
	echo '<td class="calendar-day-np"> </td>';
	$days_in_this_week++;
}

/* keep going with days.... */
for ($list_day = 1 ; $list_day <= $days_in_month ; $list_day++) {
	echo '<td class="calendar-day">';
	/* add in the day number */
	// se e' oggi o no
	if ($anno == date('Y') and $mese == date('m') and $list_day == date('j')) {
		echo "<div class='day-number-today'>";
	} else {
		echo "<div class='day-number'>";
	}
	echo "<a href='pla_dettaglioplanning.php?idplanning=0&amp;data=$anno-$mese-$list_day' style='color: white;'>$list_day</a></div>";
		
	$dataoggiiso = "$anno-$mese-" . sprintf('%02d', $list_day);
	$quanti = 0;
	$q = $db->query("SELECT planning.idplanning,planning.titolo,planning.uuid_gruppo,
	                        planningstato.colore
	                 FROM planning 
	                 JOIN planningstato ON planning.idplanningstato=planningstato.idplanningstato
	                 WHERE data='$dataoggiiso'");
	while ($r = $q->fetch_array()) {
		if (isvoceplanning($r['idplanning'])) {
			$rx = $db->query("SELECT COUNT(*) FROM planning WHERE uuid_gruppo='$r[uuid_gruppo]'")->fetch_array();
			$editfile = $rx[0] > 1 ? 'pla_dettaglioplanningmultiplo.php' : 'pla_dettaglioplanning.php';
			echo "&#10004;<a href='" . $editfile . "?idplanning=$r[idplanning]' style='color: $r[colore];'>$r[titolo]";
			
			$qq = $db->query("SELECT planningstatocol.colore,COUNT(planningstatocol.planningstatocol) AS quanti
			                  FROM planningutente
			                  JOIN planningstatocol ON planningutente.idplanningstatocol=planningstatocol.idplanningstatocol
			                  WHERE planningutente.idplanning=$r[idplanning]
			                  GROUP BY planningstatocol.planningstatocol,planningstatocol.colore");
			$a = array();
			
			//while ($rr = $qq->fetch_array()) $a[] = "<span style='color:$rr[colore]'>$rr[quanti]</span>";
			echo ' (' . implode(', ', $a) . ')';
			echo "</a><br/>";
			$quanti++;
		}
	}
	// assenze
	$ass = array();
	$q = $db->query("SELECT assenza.idassenza,utente.cognome,utente.nome
	                 FROM assenza
	                 JOIN utente ON utente.idutente=assenza.idutente
	                 WHERE assenza.data='$dataoggiiso'");
	while ($r = $q->fetch_array()) {
		if (isvoceassenza($r['idassenza'])) {
			$ass[] = "&#10006;<a href='pla_dettaglioassenza.php?idassenza=$r[idassenza]' class='assenzacalendario'>$r[nome] $r[cognome]</a>";
			$quanti++;
		}
	}
	echo implode(", ", $ass);
	// per mantenere l'altezza minima
	for ($n = $quanti ; $n < 4 ; $n++) echo '&nbsp;<br/>';
		
	echo '</td>';
	if ($running_day == 6) {
		echo '</tr>';
		if (($day_counter + 1) != $days_in_month) echo "\n<tr class='calendar-row'>";
		$running_day = -1;
		$days_in_this_week = 0;
	}
	$days_in_this_week++; 
	$running_day++; 
	$day_counter++;
}

/* finish the rest of the days in the week */
if ($days_in_this_week < 8) {
	for ($x = 1 ; $x <= (8 - $days_in_this_week) ; $x++) echo '<td class="calendar-day-np"> </td>';
}

/* final row */
echo '</tr>';

/* end the table */
echo "\n</table>";

echo $paginazione;

echo "\n<p>&nbsp;</p>";

piede();

// ### END OF FILE ###