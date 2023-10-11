<?php

/**
 * 
 * Gain Studios - Anagrafica documenti collaboratori
 * Copyright (C) 2015 B2TEAM S.r.l. - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20150623 file creato
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Documenti dei collaboratori");

echo "<p align='center'>Cerca documento: <input type='text' id='cerca' name='cerca' size='50'><br>Almeno tre lettere, tre asterischi per visualizzare tutti i documenti.</p>";
echo "<p align='center'><b><a href='doc_collaboratoriedit.php?iddocumento=0'>Nuovo documento</a></p>";
echo "<span align='center' id='risultato'></span>";

?>
<script language="Javascript">

$("#cerca").on("input", function() {
	if (this.value.length > 2) {
		$("#risultato").load("doc_collaboratori.server.php?filtro=" + encodeURIComponent(this.value));  
	} else {
		$("#risultato").empty();
	}
});


</script>
<?php

piede();

### END OF FILE ###