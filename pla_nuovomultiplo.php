<?php

/**
 * 
 * Gain Studios - Nuova voce multipla di planning
 * Copyright (C) 2016-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20160526 file creato
 * 20161130 luogo
 * 20170207 note amministrative
 * 20220811 rimossa riga inutile
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Nuovo servizio di pi&ugrave; giorni");

echo "\n<form id='frm'>";
echo $b2->inputHidden('dispatch', 'form');
echo "\n<table border='0' align='center'>";

// idplanningstato
echo $b2->rigaEdit('Stato:', $b2->inputSelect('idplanningstato', $b2->creaArraySelect("SELECT idplanningstato,planningstato FROM planningstato ORDER BY ordine")));
// idcliente
$a = array();
$q = $db->query("SELECT idcliente,ragsoc,cognome,nome FROM clienti");
while ($r = $q->fetch_array()) $a[$r['idcliente']] = trim($r['ragsoc'] == '') ? "$r[cognome] $r[nome]" : $r['ragsoc'];
asort($a);
echo $b2->rigaEdit('Cliente:', $b2->inputSelect('idcliente', $a));
// titolo
echo $b2->rigaEdit('Titolo:', $b2->inputText('titolo', '', 60, 250));
// titolo
echo $b2->rigaEdit('Luogo:', $b2->inputText('luogo', '', 60, 250));
// datainizio
echo $b2->rigaEdit("Data inizio:", $b2->inputText('datainizio', date('j/n/Y'), 11, 10, 'datainizio', '', B2_IT_CENTER));
// datafine
echo $b2->rigaEdit("Data fine:", $b2->inputText('datafine', date('j/n/Y'), 11, 10, 'datafine', '', B2_IT_CENTER));
// ripetiogni
echo $b2->rigaEdit('Ripeti ogni:', $b2->inputText('ripetiogni', '1', 6, 3) . " giorni");
// orainizio
echo $b2->rigaEdit('Ora di inizio:', $b2->inputText('orainizioh', '', 3, 2) . ':' . $b2->inputText('orainiziom', '', 3, 2));
// orafine
echo $b2->rigaEdit('Ora di fine:', $b2->inputText('orafineh', '', 3, 2) . ':' . $b2->inputText('orafinem', '', 3, 2));
// dettaglio
echo $b2->rigaEdit('Dettaglio:', $b2->inputTextarea('dettaglio', '', 50, 5), B2_ED_VTOP);
// noteadmin
echo $b2->rigaEdit('Note amministrative:', $b2->inputText('noteadmin', '', 60, 250));
// submit
echo "\n<tr><td align='center' colspan='2'><input type='button' id='submit' value='Aggiorna questa voce di planning'></td></tr>";
// spiegone
echo "\n<tr><td align='center' colspan='2'>Per creare un evento su pi&ugrave; giorni immettere la data di inizio e di fine e lasciare a <b>1</b> il valore di <i>ripeti ogni</i>.<br/>";
echo "Per creare un evento che si ripete ogni sabato immettere la data di inizio (deve essere un sabato) e di fine e impostare a <b>7</b> il valore di <i>ripeti ogni</i>.<br/>";
echo "Per creare un evento che si ripete a domeniche alterne immettere la data di inizio (deve essere una domenica) e di fine e impostare a <b>14</b> il valore di <i>ripeti ogni</i>.</td></tr>";
echo "\n</table>";
echo "\n</form>";

?>
<script>
	$("#luogo").autocomplete({
		source: "pla_nuovomultiplo.server.php",
      minLength: 2
	});
	function rimuovi(idutente) {
		$.post("pla_nuovomultiplo.server.php", 
			{dispatch: "delete", idutente: idutente})
			.done(function( data ) {
				$("#collaboratori").html(data);
			});
	};
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
		$("#aggiungi").click( function() {
			var idutente = $("#aggiungiutente").find('option:selected').val();
			$.post("pla_nuovomultiplo.server.php", 
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
	  		url: "pla_nuovomultiplo.server.php",
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