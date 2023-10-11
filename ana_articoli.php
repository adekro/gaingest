<?php

/**
 * 
 * Gain Studios - Anagrafica articoli, elecno
 * Copyright (C) 2014-2015 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2016 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141012 file creato
 * 20160922 ricerca per prossimita'
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
		$filtrolike = "'%" . $filtro . "%'";
		$wh = "WHERE codice LIKE $filtrolike OR articolo LIKE $filtrolike";
	}
	$q = $db->query("SELECT codice,articolo,giacenza,idarticolo
	                 FROM articoli
	                 $wh
	                 ORDER BY articolo");
	if ($q->num_rows > 0) {
		echo "\n<table border='0' align='center'>";
		echo $b2->intestazioneTabella(array('Descrizione', 'Giacenza', 'Codice', '&nbsp;'));
		while ($r = $q->fetch_array()) {
			$bg = $b2->bgcolor();
			$href = "<a href='ana_articoliedit.php?idarticolo=$r[idarticolo]'>";
			echo "\n<tr $bg>";
			echo "<td $bg align='left'>&nbsp;$href$r[articolo]</a>&nbsp;</td>";
			echo "<td $bg align='right'>&nbsp;$href$r[giacenza]</a>&nbsp;</td>";
			echo "<td $bg align='right'>&nbsp;$href$r[codice]</a>&nbsp;</td>";
			$rd = $db->query("SELECT COUNT(*) FROM distinta WHERE idpadre='$r[idarticolo]'")->fetch_array();
			$x = $rd[0] == 0 ? '' : ", $rd[0] figli";
			echo "<td $bg align='left'>&nbsp;<a href='ana_distinta.php?idarticolo=$r[idarticolo]'>Distinta base$x</a>&nbsp;</td>";
			echo "\n</tr>";
		}
		echo "\n</table>";
	} else {
		echo "<b>Nessun articolo corrisponde alla ricerca indicata</b>";
	}
	die();
}

intestazione("Anagrafica articoli");

echo "<p align='center'>Descrizione o codice: <input type='text' id='cerca' name='cerca' size='50'><br>Almeno tre lettere, tre asterischi per visualizzare tutti gli articoli.</p>";
echo "<p align='center'><b><a href='ana_articoliedit.php?idarticolo=0'>Nuovo articolo</a></b></p>";
echo "<span align='center' id='risultato'></span>";

?>

<script language="Javascript">

$("#cerca").on("input", function() {
	if (this.value.length > 2) {
		$("#risultato").load("ana_articoli.php?filtro=" + encodeURIComponent(this.value));  
	} else {
		$("#risultato").empty();
	}
});

</script>

<?php

piede();

### END OF FILE ###