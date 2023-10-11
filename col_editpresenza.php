<?php

/**
 * 
 * Gain Studios - Modifica presenza collaboratore
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20150807 file creato
 * 20190128 conversione ora da int a char
 *
 */

define('SOUNDPARK', true);
require('global.php');

if (isset($_POST['idplanning']) and is_numeric($_POST['idplanning'])) {
	$idplanning = $b2->normalizza($_POST['idplanning']);
	$a = array();
	$a[] = $b2->campoSQL('idplanningstatocol', $_POST['idplanningstatocol']);
	$a[] = $b2->campoSQL('note', $_POST['note']);
	$db->query("UPDATE planningutente SET " . implode(',', $a) . " WHERE idplanning='$idplanning' AND idutente='" . $_SESSION['utente']['idutente'] . "'");
	header('Location: home.php');
	die();
}
$idplanning = 0;
if (isset($_GET['idplanning']) and is_numeric($_GET['idplanning'])) {
	$idplanning = $b2->normalizza($_GET['idplanning']);
}
$qp = $db->query("SELECT planningutente.modificato,planningutente.note,planningutente.idplanningstatocol,
                         planning.titolo,planning.datainizio,planning.datafine,planning.orainizio,planning.orafine,
                         clienti.ragsoc,
                         planningstato.planningstato
                  FROM planningutente 
                  JOIN planning ON planningutente.idplanning=planning.idplanning
                  JOIN clienti ON planning.idcliente=clienti.idcliente
                  JOIN planningstato ON planning.idplanningstato=planningstato.idplanningstato
                  WHERE planningutente.idplanning='$idplanning' AND planningutente.idutente='" . $_SESSION['utente']['idutente'] . "'");
if (0 == $idplanning or 0 == $qp->num_rows) {
	header('Location: home.php');
	die();
}

$rp = $qp->fetch_array();

intestazione("Modifica presenza $rp[titolo]");

echo "\n<form method='post' action='col_editpresenza.php'>";
echo $b2->inputHidden('idplanning', $idplanning);
echo "\n<table border='0' align='center'>";
$x = int2ora($rp['orainizio']);
echo $b2->rigaedit('Inizio:', $b2->dt2ita($rp['datainizio']) . ' ' . $x['c']);
$x = int2ora($rp['orafine']);
echo $b2->rigaedit('Fine:', $b2->dt2ita($rp['datafine']) . ' ' . $x['c']);
echo $b2->rigaedit('Stato del servizio:', $rp['planningstato']);
echo $b2->rigaedit('Tuo stato:', $b2->inputSelect('idplanningstatocol', $b2->creaArraySelect("SELECT idplanningstatocol,planningstatocol FROM planningstatocol ORDER BY ordine"), $rp['idplanningstatocol']));
echo $b2->rigaedit('Note:', $b2->inputText('note', $rp['note'], 50, 250));
echo "\n<tr><td colspan='2' align='center'><input type='submit' value='Aggiorna le informazioni'/><td></tr>";
echo "\n</table>";
echo "\n</form>";


piede();

// ### END OF FILE ###