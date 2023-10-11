<?php

/**
 * 
 * Gain Studios - Dettaglio di una voce di assenza
 * Copyright (C) 2016 B2TEAM S.r.l. - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20160527 file creato
 *
 */

define('SOUNDPARK', true);
require('global.php');

// GET
if (isset($_GET['idassenza']) and is_numeric($_GET['idassenza'])) {
	$idassenza = $b2->normalizza($_GET['idassenza']);
}
// post
if (isset($_POST['idassenza']) and is_numeric($_POST['idassenza'])) {
	$idassenza = $b2->normalizza($_POST['idassenza']);
}

$qa = $db->query("SELECT * FROM assenza WHERE idassenza='$idassenza'");
if ($qa->num_rows < 1) {
	header("Location: pla_calendario.php");
	die();
} else {
	$ra = $qa->fetch_array();
}

// vediamo se e' abilitato
if (!isvoceassenza($ra['idassenza'])) {
	header("Location: pla_calendario.php");
	die();
}

intestazione("Assenza del " . $b2->dt2ita($ra['data']));

echo "\n<form id='frm'>";
echo $b2->inputHidden('idassenza', $idassenza);
echo $b2->inputHidden('dispatch', 'form');
echo "\n<table border='0' align='center'>";

// idutente, un amministratore puo' aggiungere l'assenza di tutte, un operatore solo la propria
if ('1' == $_SESSION['utente']['isadmin']) {
	echo $b2->rigaEdit('Collaboratore:', $b2->inputSelect('idutente', $b2->creaArraySelect("SELECT idutente, CONCAT(nome, ' ', cognome) FROM utente WHERE sviluppatore<>'1' ORDER BY nome, cognome"), $ra['idutente']));
} 
// data
echo $b2->rigaEdit("Data:", $b2->inputText('data', $b2->dt2ita($ra['data'], B2_DT_ZEROFILL), 11, 10, 'data', '', B2_IT_CENTER));
// orainizio
$x = int2ora($ra['orainizio']);
echo $b2->rigaEdit('Ora di inizio:', $b2->inputText('orainizioh', $x['h'], 3, 2) . ':' . $b2->inputText('orainiziom', $x['m'], 3, 2));
// orafine
$x = int2ora($ra['orafine']);
echo $b2->rigaEdit('Ora di fine:', $b2->inputText('orafineh', $x['h'], 3, 2) . ':' . $b2->inputText('orafinem', $x['m'], 3, 2));
// note
echo $b2->rigaEdit('Note:', $b2->inputText('note', $ra['note'], 50, 250));
echo $b2->rigaEdit('Elimina questa assenza:', $b2->inputCheck('xxx', false));
// cancella tutto
$rr = $db->query("SELECT COUNT(*) FROM assenza WHERE uuid_gruppo='$ra[uuid_gruppo]'")->fetch_array();
if ($rr[0] > 1) {
	echo $b2->rigaEdit('Elimina questa assenza e tutte quelle successive correlate:', $b2->inputCheck('yyy', false));
}

// submit
echo "\n<tr><td align='center' colspan='2'><input type='button' id='submit' value='Aggiorna questa voce di assenza'></td></tr>";

echo "\n</table>";
echo "\n</form>";

?>
<script>
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
		// submit
		$("#submit").click(function(e){ 
	    e.preventDefault(); 
			$('#submit').fadeOut(200);
	    $.ajax({
	  		type: "POST",
	  		url: "pla_dettaglioassenza.server.php",
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