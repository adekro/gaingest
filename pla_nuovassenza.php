<?php

/**
 * 
 * Gain Studios - Nuova assenza
 * Copyright (C) 2016 B2TEAM S.r.l. - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20160527 file creato
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Nuova assenza");

echo "\n<form id='frm'>";
echo $b2->inputHidden('dispatch', 'form');
echo "\n<table border='0' align='center'>";

// idutente, un amministratore puo' aggiungere l'assenza di tutte, un operatore solo la propria
if ('1' == $_SESSION['utente']['isadmin']) {
	echo $b2->rigaEdit('Collaboratore:', $b2->inputSelect('idutente', $b2->creaArraySelect("SELECT idutente, CONCAT(nome, ' ', cognome) FROM utente WHERE sviluppatore<>'1' ORDER BY nome, cognome")));
} 
// datainizio
echo $b2->rigaEdit("Data inizio:", $b2->inputText('datainizio', date('j/n/Y'), 11, 10, 'datainizio', '', B2_IT_CENTER));
// datafine
echo $b2->rigaEdit("Data fine:", $b2->inputText('datafine', date('j/n/Y'), 11, 10, 'datafine', '', B2_IT_CENTER));
// orainizio
echo $b2->rigaEdit('Ora di inizio:', $b2->inputText('orainizioh', '0', 3, 2) . ':' . $b2->inputText('orainiziom', '0', 3, 2));
// orafine
echo $b2->rigaEdit('Ora di fine:', $b2->inputText('orafineh', '24', 3, 2) . ':' . $b2->inputText('orafinem', '0', 3, 2));
// note
echo $b2->rigaEdit('Note:', $b2->inputText('note', '', 50, 250));
// submit
echo "\n<tr><td align='center' colspan='2'><input type='button' id='submit' value='Segnsala questa assenza'></td></tr>";
echo "\n</table>";
echo "\n</form>";

?>
<script>
  $.datepicker.setDefaults( $.datepicker.regional[ "it" ] );
  $(function() {
    $( "#datainizio" ).datepicker({
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
    $( "#datafine" ).datepicker({
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
      $( "#datainizio" ).datepicker( "option", "dateFormat", $( this ).val() );
      $( "#datafine" ).datepicker( "option", "dateFormat", $( this ).val() );
    });
  });
  
	$(document).ready(function() {
		// submit
		$("#submit").click(function(e){ 
	    e.preventDefault(); 
			$('#submit').fadeOut(200);
	    $.ajax({
	  		type: "POST",
	  		url: "pla_nuovassenza.server.php",
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