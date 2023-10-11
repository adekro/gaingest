<?php

/**
 * 
 * Gain Studios - Anagrafica clienti, elecno
 * Copyright (C) 2014-2015 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2016-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141003 file creato
 * 20160922 ricerca per prossimita'
 * 20190128 allineamento tabella
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
		$wh = "WHERE ragsoc LIKE $filtrolike OR cognome LIKE $filtrolike OR nome LIKE $filtrolike OR piva LIKE $filtrolike OR cf LIKE $filtrolike";
	}
	$q = $db->query("SELECT ragsoc,citta,cognome,nome,idcliente,istemporaneo
	                 FROM clienti
	                 $wh
	                 ORDER BY ragsoc,cognome");
	if ($q->num_rows > 0) {
		echo "\n<table border='0' align='center'>";
		echo $b2->intestazioneTabella(array('Ragione sociale', 'Contatto', 'Citt&agrave;'));
		while ($r = $q->fetch_array()) {
			$bg = $b2->bgcolor();
			$temp = '1' == $r['istemporaneo'] ? 'TEMP' : '';
			$href = "<a href='ana_clientiedit.php?idcliente=$r[idcliente]'>";
			echo "\n<tr $bg>";
			echo "<td $bg align='left'>&nbsp;$href$temp$r[ragsoc]</a>&nbsp;</td>";
			echo "<td $bg align='left'>&nbsp;$href$r[cognome] $r[nome]</a>&nbsp;</td>";
			echo "<td $bg align='left'>&nbsp;$href$r[citta]</a>&nbsp;</td>";
			echo "\n</tr>";
		}
		echo "\n</table>";
	} else {
		echo "<b>Nessun cliente corrisponde alla ricerca indicata.</b>";
	}
	die();
}

intestazione("Anagrafica clienti");

echo "<p align='center'>Ricerca: <input type='text' id='cerca' name='cerca' size='50'><br>Almeno tre lettere, tre asterischi per visualizzare tutti i clienti.</p>";
echo "<p align='center'><b><a href='ana_clientiedit.php?idcliente=0'>Nuovo cliente</a></b></p>";
echo "<span align='center' id='risultato'></span>";

?>

<script language="Javascript">

$("#cerca").on("input", function() {
	if (this.value.length > 2) {
		$("#risultato").load("ana_clienti.php?filtro=" + encodeURIComponent(this.value));  
	} else {
		$("#risultato").empty();
	}
});

</script>

<?php

piede();

### END OF FILE ###