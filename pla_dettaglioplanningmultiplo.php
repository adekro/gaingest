<?php

/**
 * 
 * Gain Studios - Dettaglio di una voce di planning multipla
 * Copyright (C) 2016-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20160622 file creato
 * 20161112 tabella MEMORY per la gestione degli invitati
 * 20161130 luogo
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
$_SESSION['idplanning'] = $_GET['idplanning'];
$db->query("DELETE FROM tmp_planningedit WHERE idtemp='$idtemp'");  // just in case

// popolo la tabella temporanea
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

$qp = $db->query("SELECT * FROM planning WHERE idplanning='$idplanning'");
$rp = $qp->fetch_array();
$titolo = $rp['titolo'];

intestazione($titolo);

// per la parte server di jscript
$_SESSION['uuid_gruppo'] = $rp['uuid_gruppo'];

echo "\n<form id='frm'>";
echo $b2->inputHidden('uuid_gruppo', $rp['uuid_gruppo']);
echo $b2->inputHidden('dispatch', 'form');

echo "\n<table border='0' align='center'><tr>"; // tab esterna
echo "\n<td align='right' valign='top'><table border='0' align='right'>"; // tab anagrafica

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
// personale
echo "\n<tr><td align='right' valign='top'><b>Personale:</b></td><td align='left' valign='top'><span id='collaboratori'>";
echo planningcollaboratori($idplanning);
echo "</span>";
$a = $b2->creaArraySelect("SELECT idutente,CONCAT(cognome,' ',nome) FROM utente WHERE isattivo='1' AND sviluppatore='0' ORDER BY cognome,nome");
echo $b2->inputSelect('aggiungiutente', $a) . " <input type='button' id='aggiungi' value='Aggiungi'>";
echo "</td></tr>";
// dettaglio
if ($isadmin) {
	echo $b2->rigaEdit('Dettaglio:', $b2->inputTextarea('dettaglio', $rp['dettaglio'], 50, 5), B2_ED_VTOP);
} else {
	if ('' != trim($rp['dettaglio'])) echo $b2->rigaEdit('Dettaglio:', $rp['dettaglio'], B2_ED_VTOP);
}	
if ($isadmin) {
	// cancella tutto
	$rr = $db->query("SELECT COUNT(*) FROM planning WHERE uuid_gruppo='$rp[uuid_gruppo]'")->fetch_array();
	if ($rr[0] > 1) {
		echo $b2->rigaEdit('Elimina tutti i servizi di questo gruppo:', $b2->inputCheck('yyy1', false) . $b2->inputCheck('yyy2', false) . $b2->inputCheck('yyy3', false));
	}
}
// submit
echo "\n<tr><td align='center' colspan='2'><input type='button' id='submit' value='Aggiorna questa voce di planning'></td></tr>";

echo "\n</table></td>"; // anagrafica
echo "\n<td align='right' valign='top'><table border='0' cellpadding='4' align='right'>"; // tab giorni
echo "\n<tr><th colspan='3'><b>Giorni del servizio</b></th></tr>"; // giorni
$q = $db->query("SELECT data,orainizio,orafine,idplanning FROM planning WHERE uuid_gruppo='$rp[uuid_gruppo]' ORDER BY data");
while ($r = $q->fetch_array()) {
	echo "\n<tr>";
	// data
	echo "<td align='right'><a href='pla_dettaglioplanning.php?idplanning=$r[idplanning]'>" . $b2->dt2ita($r['data'], B2_DT_ZEROFILL) . "</a></td>";
	// orainizio
	$x = int2ora($r['orainizio']);
	echo "<td align='right'><a href='pla_dettaglioplanning.php?idplanning=$r[idplanning]'>$x[c]</a></td>";
	// orafine
	$x = int2ora($r['orafine']);
	echo "<td align='right'><a href='pla_dettaglioplanning.php?idplanning=$r[idplanning]'>$x[c]</a></td>";
	echo "</tr>";
}
echo "\n</table></td>"; // giorni
echo "\n</tr></table>"; // tabellona

echo "\n</form>";

?>
<script>
	$("#luogo").autocomplete({
		source: "pla_dettaglioplanningmultiplo.server.php",
      minLength: 2
	});
	function rimuovi(idplanningedit) {
		$.post("pla_dettaglioplanningmultiplo.server.php", 
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
			$.post("pla_dettaglioplanningmultiplo.server.php", 
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
	  		url: "pla_dettaglioplanningmultiplo.server.php",
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
