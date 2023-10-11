<?php

/**
 * 
 * Gain Studios - Dettaglio di una voce di planning
 * Copyright (C) 2015-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20150617 file creato
 * 20150807 notifica mail
 * 20160525 data singola, ora decimale
 * 20161112 tabella MEMORY per la gestione degli invitati
 * 20161130 luogo
 * 20170207 note amministrative
 * 20190128 conversione ora da int a char
 * 20220821 fix query
 *
 */

define('SOUNDPARK', true);
require('global.php');

// check livello utente
$isadmin = isabilitato($_SESSION['utente']['idlivello'], 103);

// GET
if (isset($_GET['idplanning']) and is_numeric($_GET['idplanning'])) {
	$idplanning = $b2->normalizza($_GET['idplanning']);
}
// post
if (isset($_POST['idplanning']) and is_numeric($_POST['idplanning'])) {
	$idplanning = $b2->normalizza($_POST['idplanning']);
}

// id del record del db temporaneo
$idtemp = time() - rand(0,100000);
$_SESSION['idtemp'] = $idtemp;
$db->query("DELETE FROM tmp_planningedit WHERE idtemp='$idtemp'");  // just in case

// popolo la tabella temporanea
try {
	//code...

$q = $db->query("INSERT INTO tmp_planningedit
                 (idtemp,modificato,note,idplanningstatocol,idutente,idplanning,nome,cognome,colore)
	               SELECT $idtemp,
                        planningutente.modificato,planningutente.note,planningutente.idplanningstatocol,planningutente.idutente,planningutente.idplanning,
                        utente.nome,utente.cognome,
                        planningstatocol.colore
                 FROM planningutente
                 JOIN utente on planningutente.idutente=utente.idutente
                 JOIN planningstatocol on planningutente.idplanningstatocol=planningstatocol.idplanningstatocol
                 WHERE planningutente.idplanning='$idplanning'");
} catch (\Throwable $th) {
	echo "errore" . $th;
}

$qp = $db->query("SELECT * FROM planning WHERE idplanning='$idplanning'");
if ($qp->num_rows == 0) {
	$idplanning = 0;
	$rp = array();
	$rp['idplanning'] = 0;
	$rp['idcliente'] = 0;
	$rp['idplanningstato'] = 0;
	$rp['titolo'] = '';
	$rp['luogo'] = '';
	if (isset($_GET['data'])) {
		$rp['data'] = $_GET['data'];
	} else {
		$rp['data'] = date('Y-m-d');
	}
	$rp['orainizio'] = 0;
	$rp['orafine'] = 0;
	$rp['dettaglio'] = '';
	$rp['noteadmin'] = '';
	$titolo = "Nuovo servizio";
	$db->query("DELETE FROM planningutente WHERE idplanning='0'");
} else {
	$rp = $qp->fetch_array();
	$titolo = $rp['titolo'];
}

intestazione($titolo);

// per la parte server di jscript
$_SESSION['idplanning'] = $idplanning;

echo "\n<form id='frm'>";
echo $b2->inputHidden('idplanning', $idplanning);
echo $b2->inputHidden('dispatch', 'form');
echo "\n<table border='0' align='center'>";

// idplanningstato
$a = array();
$q = $db->query("SELECT idplanningstato,planningstato FROM planningstato ORDER BY ordine");
while ($r = $q->fetch_array()) $a[$r['idplanningstato']] = $r['planningstato'];
if ($isadmin) {
	echo $b2->rigaEdit('Stato:', $b2->inputSelect('idplanningstato', $a, $rp['idplanningstato']));
} else {
	echo $b2->rigaEdit('Stato:', $a[$rp['idplanningstato']]);
}
// idcliente
$a = array();
$q = $db->query("SELECT idcliente,ragsoc,cognome,nome FROM clienti");
while ($r = $q->fetch_array()) $a[$r['idcliente']] = trim($r['ragsoc'] == '') ? "$r[cognome] $r[nome]" : $r['ragsoc'];
asort($a);
if ($isadmin) echo $b2->rigaEdit('Cliente:', $b2->inputSelect('idcliente', $a, $rp['idcliente']));
// titolo
if ($isadmin) {
	echo $b2->rigaEdit('Titolo:', $b2->inputText('titolo', $rp['titolo'], 60, 250));
} else {
	echo $b2->rigaEdit('Titolo:', $rp['titolo']);
}
// luogo
if ($isadmin) {
	echo $b2->rigaEdit('Luogo:', $b2->inputText('luogo', $rp['luogo'], 60, 250));
} else {
	if ('' != trim($rp['luogo'])) echo $b2->rigaEdit('Luogo:', $rp['luogo']);
}
// data
if ($isadmin) {
	echo $b2->rigaEdit("Data:", $b2->inputText('data', $b2->dt2ita($rp['data'], B2_DT_ZEROFILL), 11, 10, 'data', '', B2_IT_CENTER));
} else {
	echo $b2->rigaEdit("Data:", $b2->dt2ita($rp['data']));
}	
// orainizio
$x = int2ora($rp['orainizio']);
if ($isadmin) {
	echo $b2->rigaEdit('Ora di inizio:', $b2->inputText('orainizioh', $x['h'], 3, 2) . ':' . $b2->inputText('orainiziom', $x['m'], 3, 2));
} else {
	echo $b2->rigaEdit('Ora di inizio:', $x['c']);
}	
// orafine
$x = int2ora($rp['orafine']);
if ($isadmin) {
	echo $b2->rigaEdit('Ora di fine:', $b2->inputText('orafineh', $x['h'], 3, 2) . ':' . $b2->inputText('orafinem', $x['m'], 3, 2));
} else {
	echo $b2->rigaEdit('Ora di fine:', $x['c']);
}	
// personale
echo "\n<tr><td align='right' valign='top'><b>Personale:</b></td><td align='left' valign='top'><span id='collaboratori'>";
echo planningcollaboratori($idplanning, true);
echo "</span>";
//$a = $b2->creaArraySelect("SELECT idutente,CONCAT(cognome,' ',nome) FROM utente WHERE isattivo='1' AND sviluppatore='0' ORDER BY cognome,nome");
$a = array();
$qq = $db->query("SELECT idutente,cognome,nome FROM utente WHERE isattivo='1' AND sviluppatore='0' ORDER BY cognome,nome");
while ($rr = $qq->fetch_array()) {
	$a[$rr['idutente']] = "$rr[cognome] $rr[nome] " . implode(' * ', impegnigiorno($rr['idutente'], $rp['data'], $idplanning));
}
echo $b2->inputSelect('aggiungiutente', $a) . " <input type='button' id='aggiungi' value='Aggiungi'>";
echo "</td></tr>";
// dettaglio
if ($isadmin) {
	echo $b2->rigaEdit('Dettaglio:', $b2->inputTextarea('dettaglio', $rp['dettaglio'], 50, 5), B2_ED_VTOP);
} else {
	if ('' != trim($rp['dettaglio'])) echo $b2->rigaEdit('Dettaglio:', $rp['dettaglio'], B2_ED_VTOP);
}	
// noteadmin
if ($isadmin) echo $b2->rigaEdit('Note amministrative:', $b2->inputText('noteadmin', $rp['noteadmin'], 60, 250));
// cancella singolo
$ismultiplo = false;
if ($isadmin) {
	if ($rp['idplanning'] > 0) {
		echo $b2->rigaEdit('Elimina questo servizio:', $b2->inputCheck('xxx1', false) . $b2->inputCheck('xxx2', false) . $b2->inputCheck('xxx3', false));
		// cancella tutto
		$rr = $db->query("SELECT COUNT(*) FROM planning WHERE uuid_gruppo='$rp[uuid_gruppo]'")->fetch_array();
		if ($rr[0] > 1) {
			echo $b2->rigaEdit('Elimina questo servizio e tutti quelli successivi correlati:', $b2->inputCheck('yyy1', false) . $b2->inputCheck('yyy2', false) . $b2->inputCheck('yyy3', false));
			$ismultiplo = true;
		}
	}
}
// submit
echo "\n<tr><td align='center' colspan='2'><input type='button' id='submit' value='Aggiorna questa voce di planning'></td></tr>";
if ($ismultiplo and $isadmin) echo "\n<tr><td align='center' colspan='2'><a href='pla_dettaglioplanningmultiplo.php?idplanning=$idplanning'>Modifica i dati di tutti gli eventi <b>senza salvare questa schermata</b></a></td></tr>";

echo "\n</table>";
echo "\n</form>";

?>
<script>
	$("#luogo").autocomplete({
		source: "pla_dettaglioplanning.server.php",
      minLength: 2
	});
	function rimuovi(idplanningedit) {
		$.post("pla_dettaglioplanning.server.php", 
			{dispatch: "delete", idplanningedit: idplanningedit})
			.done(function( data ) {
				$("#collaboratori").html(data);
			});
	};
	function togglemail(idplanningedit) {
		$.post("pla_dettaglioplanning.server.php", 
			{dispatch: "togglemail", idplanningedit: idplanningedit})
			.done(function( data ) {
				$("#collaboratori").html(data);
			});
	};
	function cambiastato(idplanningedit, idplanningstatocol) {
		$.post("pla_dettaglioplanning.server.php", 
			{dispatch: "cambiastato", idplanningedit: idplanningedit, idplanningstatocol: idplanningstatocol})
			.done(function( data ) {
				$("#collaboratori").html(data);
			});
	};
  $.datepicker.setDefaults( $.datepicker.regional[ "it" ] );
  $(function() {
    $( "#data" ).datepicker({
      showButtonPanel: true,
      showOtherMonths: true,
      selectOtherMonths: true,
      showButtonPanel: true,
      dateFormat: "dd/mm/yy",
      showOn: "button",
      buttonImage: "static/iconcalendar.gif",
      buttonImageOnly: true,
      buttonText: "Scegli la data attraverso il calendario"
    });
    $( "#format" ).change(function() {
      $( "#data" ).datepicker( "option", "dateFormat", $( this ).val() );
    });
  });
  
	$(document).ready(function() {
		$("#aggiungi").click( function() {
			var idutente = $("#aggiungiutente").find('option:selected').val();
			$.post("pla_dettaglioplanning.server.php", 
				{dispatch: "idutente", idutente: idutente})
				.done(function( data ) {
					$("#collaboratori").html(data);
  		});
		});
		// submit
		$("#submit").click(function(e){ 
	    e.preventDefault(); 
			$('#submit').fadeOut(200);
	    $.ajax({
	  		type: "POST",
	  		url: "pla_dettaglioplanning.server.php",
	  		data: $("#frm").serialize(),
	      success: function(data){
					if('OK' == data) {
						window.location.href = "pla_calendario.php";
			    } else {
	         	$("#errore").html(data);
						$('#errore').show(); // perche' potrebbe essere dopo un fadeout
						$('#submit').fadeIn(200);
						$('#errore').fadeOut(4000);
	         }
	      },
	      error: function(xhr, desc, err) {
	    		console.log(xhr);
	    		console.log("Details: " + desc + "\nError:" + err);
	    	}
	    }); // Ajax Call
			$('#submit').fadeIn(200);
		}); 
	}); //document.ready

</script>

<?php

piede();

// ### END OF FILE ###
