<?php

/**
 * 
 * Gain Studios - Riparazioni, elenco
 * Copyright (C) 2016 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20160922 file creato
 *
 */

define('SOUNDPARK', true);
require('global.php');

// ajax
if (isset($_GET['filtro'])) {
	$filtro = $b2->normalizza($_GET['filtro'], B2_NORM_SQL | B2_NORM_TRIM);
	if ('***' == $filtro) {
		$wh = "";
	} else {
		$fl = "'%" . $filtro . "%'";
		$wh = "WHERE riparazione.prodotto LIKE $fl OR riparazione.problema LIKE $fl OR clienti.ragsoc LIKE $fl OR utente.login LIKE $fl";
	}
	$q = $db->query("SELECT riparazione.prodotto,riparazione.problema,riparazione.daggiornamento,riparazione.idriparazione,
	                        clienti.ragsoc,
	                        utente.login,
	                        riparazionestato.riparazionestato 
	                 FROM riparazione
	                 JOIN clienti ON riparazione.idcliente=clienti.idcliente
	                 JOIN utente ON riparazione.idutente=utente.idutente
	                 JOIN riparazionestato ON riparazione.idriparazionestato=riparazionestato.idriparazionestato
	                 $wh
	                 ORDER BY riparazionestato.ordine,riparazione.daggiornamento,utente.login");
	if ($q->num_rows > 0) {
		echo "\n<table border='0' align='center'>";
		echo $b2->intestazioneTabella(array("Aggiornata","Cliente","Prodotto","Assegnata","Stato"));
		while ($r = $q->fetch_array()) {
			$bg = $b2->bgcolor();
			$href = "<a href='rip_riparazioniedit.php?idriparazione=$r[idriparazione]'>";
			echo "\n<tr $bg>";
			echo "<td $bg align='right'>&nbsp;$href" . $b2->dt2ita($r['daggiornamento']) . "</a>&nbsp;</td>";
			echo "<td $bg align='left'>&nbsp;$href$r[ragsoc]</a>&nbsp;</td>";
			echo "<td $bg align='left'>&nbsp;$href$r[prodotto]</a>&nbsp;</td>";
			echo "<td $bg align='left'>&nbsp;$href$r[login]</a>&nbsp;</td>";
			echo "<td $bg align='left'>&nbsp;$href$r[riparazionestato]</a>&nbsp;</td>";
			echo "\n</tr>";
		}
		echo "\n</table>";
	} else {
		echo "<b>Nessuna riparazione corrisponde alla ricerca indicata.</b>";
	}
	die();
}

intestazione("Riparazioni");

echo "<p align='center'>Ricerca: <input type='text' id='cerca' name='cerca' size='50'><br>Almeno tre lettere, tre asterischi per visualizzare tutte le riparazioni.</p>";
echo "<p align='center'><b><a href='rip_riparazioniedit.php?idriparazione=0'>Nuova riparazione</a></b></p>";
echo "<span align='center' id='risultato'></span>";

?>

<script language="Javascript">

$("#cerca").on("input", function() {
	if (this.value.length > 2) {
		$("#risultato").load("rip_riparazioni.php?filtro=" + encodeURIComponent(this.value));  
	} else {
		$("#risultato").empty();
	}
});

</script>

<?php

piede();

### END OF FILE ###