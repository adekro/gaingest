<?php

/**
 * 
 * Gain Studios - Anagrafica collaboratori
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20150619 file creato
 * 20160525 un solo file per la parte AJAX
 * 20190202 conversione normalizza() in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

if (isset($_GET['filtro'])) {
	$filtro = $b2->normalizza(trim($_GET['filtro']));
	$awh = array();
	$awh[] = "utente.sviluppatore='0'";
	if ('***' != $filtro) {
		$filtrolike = "'%" . $filtro . "%'";
		$awh[] = "(login LIKE $filtrolike OR nome LIKE $filtrolike OR cognome LIKE $filtrolike)";
	}
	$q = $db->query("SELECT utente.login,utente.nome,utente.cognome,utente.idutente,utente.isattivo,
	                        livello.livello
	                 FROM utente
	                 LEFT JOIN livello ON utente.idlivello=livello.idlivello
	                 WHERE " . implode(' AND ', $awh) . "
	                 ORDER BY cognome,nome");
	if ($q->num_rows > 0) {
		echo "\n<table border='0' align='center'>";
		echo $b2->intestazioneTabella(array("Cognome e nome","Login","Livello"));
		while ($r = $q->fetch_array()) {
			$bg = $b2->bgcolor();
			echo "\n<tr $bg>";
			echo "<td $bg align='left'>&nbsp;<a href='ana_collaboratoriedit.php?idutente=$r[idutente]'>$r[cognome] $r[nome]</a>&nbsp;</td>";
			echo "<td $bg align='left'>&nbsp;<a href='ana_collaboratoriedit.php?idutente=$r[idutente]'>$r[login]</a>&nbsp;</td>";
			echo "<td $bg align='left'>&nbsp;<a href='ana_collaboratoriedit.php?idutente=$r[idutente]'>$r[livello]";
			if ('0' == $r['isattivo']) echo ' disabilitato';
			echo "</a>&nbsp;</td>";
			echo "\n</tr>";
		}
		echo "\n</table>";
	} else {
		echo "<p align='center'><b>Nessun collaboratore corrisponde alla ricerca indicata.</b></p>";
	}
	die();
}

intestazione("Anagrafica collaboratori");

echo "<p align='center'>Cerca collaboratore: <input type='text' id='cerca' name='cerca' size='50'><br>Almeno tre lettere, tre asterischi per visualizzare tutti i collaboratori.</p>";
echo "<p align='center'><b><a href='ana_collaboratoriedit.php?idutente=0'>Nuovo collaboratore</a></b></p>";
echo "<span align='center' id='risultato'></span>";

?>
<script language="Javascript">

$("#cerca").on("input", function() {
	if (this.value.length > 2) {
		$("#risultato").load("ana_collaboratori.php?filtro=" + encodeURIComponent(this.value));  
	} else {
		$("#risultato").empty();
	}
});

</script>
<?php

piede();

### END OF FILE ###