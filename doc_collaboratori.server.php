<?php

/**
 * 
 * Gain Studios - Anagrafica documenti collaboratori elenco lato server
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20150623 file creato
 * 20190202 conversione normalizza() in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

if (isset($_GET['filtro'])) {
	$filtro = $b2->normalizza(trim($_GET['filtro']));
	$awh = array();
	$awh[] = "tipodocumento.classe='C'"; // documento dei collaboratori. Si`, e` hardcoded
	if ('***' != $filtro) {
		$filtrolike = "'%" . $filtro . "%'";
		$awh[] = "(utente.cognome LIKE $filtrolike OR utente.nome LIKE $filtrolike OR tipodocumento.tipodocumento LIKE $filtrolike OR documento.note LIKE $filtrolike)";
	}
	$q = $db->query("SELECT documento.iddocumento,documento.nomefile,documento.note,documento.scadenza,
	                        utente.cognome,utente.nome,
	                        tipodocumento.tipodocumento
	                 FROM documento
	                 LEFT JOIN utente ON documento.id=utente.idutente
	                 LEFT JOIN tipodocumento ON documento.idtipodocumento=tipodocumento.idtipodocumento
	                 WHERE " . implode(' AND ', $awh) . "
	                 ORDER BY cognome,nome");
	                 
	if ($q->num_rows > 0) {
		echo "\n<table border='0' align='center'>";
		echo $b2->intestazioneTabella(array("Cognome e nome","Documento","Scadenza","Note","Comandi"));
		while ($r = $q->fetch_array()) {
			$bg = $b2->bgcolor();
			echo "\n<tr $bg>";
			echo "<td align='left' $bg>&nbsp;<b>$r[cognome] $r[nome]</b>&nbsp;</td>";
			echo "<td align='left' $bg>&nbsp;$r[tipodocumento]&nbsp;</td>";
			echo "<td align='right' $bg>&nbsp;" . $b2->dt2ita($r['scadenza']) . "&nbsp;</td>";
			echo "<td align='left' $bg>&nbsp;$r[note]&nbsp;</td>";
			echo "<td align='center' $bg>&nbsp;<b><a href='doc_download.php?iddocumento=$r[iddocumento]' target='_blank'>Download</a></b>&nbsp;&bull;&nbsp;<b><a href='doc_collaboratoriedit.php?iddocumento=$r[iddocumento]'>Modifica</a></b>&nbsp;</td>";
			echo "\n</tr>";
		}
		echo "\n</table>";
	} else {
		echo "<p align='center'><b>Nessun documento corrisponde alla ricerca indicata</b></p>";
	}
}


### END OF FILE ###