<?php

/**
 * 
 * Gain Studios - Anagrafica collaboratori
 * Copyright (C) 2015-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
*
 * 20150619 file creato
 * 20160922 rimozione commenti inutili e aggiunta isriparatore
 * 20161111 tolto isriparatore e spostato a livello di livello
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

if (isset($_POST['idutente'])) {
	try {
			//code...
		
		$a = array();
		// se devo cancellare, non faccio molto cinema
		if (isset($_POST['xxx1']) and isset($_POST['xxx2']) and isset($_POST['xxx3'])) {
			$db->query("DELETE FROM utente WHERE idutente='" . $b2->normalizza($_POST['idutente']) . "'");
			header('Location: ana_collaboratori.php');
			die();
		} else {
			// login
			$wh = $_POST['idutente'] != 0 ? " AND idutente<>'" . $b2->normalizza($_POST['idutente']) . "'" : '';
			$q = $db->query("SELECT login FROM utente WHERE login='" . $b2->normalizza($_POST['login']) . "' $wh");
			if ($q->num_rows == 0 and trim($_POST['login']) != '' ) {
				$a[] = $b2->campoSQL('login', $_POST['login'], B2_NORM_SQL || B2_NORM_TRIM);
				$loginok = true;
			} else {
				$loginok = false;
			}
			if (trim($_POST['password']) != '' ) $a[] = $b2->campoSQL('password', sha1($_POST['password']));
			$isattivo = isset($_POST['isattivo']) ? '1' : '0';
			try {
				$a[] = $b2->campoSQL('isattivo', $isattivo);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('idlivello', $_POST['idlivello']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('cognome', $_POST['cognome']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('nome', $_POST['nome']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('iniziali', $_POST['iniziali']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('email', $_POST['email']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('cellulare', $_POST['cellulare']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('indirizzo1', $_POST['indirizzo1']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('indirizzo2', $_POST['indirizzo2']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('indirizzo3', $_POST['indirizzo3']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('cf', $_POST['cf']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('piva', $_POST['piva']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				//$a[] = $b2->campoSQL('dnascita', $b2->dt2iso($_POST['dnascita']));
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('dnascita', '2020-01-01');
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('luonascita', $_POST['luonascita']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('banca', $_POST['banca']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('iban', $_POST['iban']);
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('lastloginok','2020-01-01');
			} catch (\Throwable $th) {
				//throw $th;
			}
			try {
				$a[] = $b2->campoSQL('lastloginko','2020-01-01');
			} catch (\Throwable $th) {
				//throw $th;
			}
				
			
			// l'input formattato lascia i caratteri di formattazione
			$retrordinaria = str_replace(',', '', $_POST['retrordinaria']);
			$retrordinaria = str_replace('.', '', $retrordinaria);
			$a[] = $b2->campoSQL('retrordinaria', $retrordinaria);
			$retrservizio = str_replace(',', '', $_POST['retrservizio']);
			$retrservizio = str_replace('.', '', $retrservizio);
			$a[] = $b2->campoSQL('retrservizio', $retrservizio);
			$pctriparazione = str_replace('%', '', $_POST['pctriparazione']);
			$a[] = $b2->campoSQL('pctriparazione', $pctriparazione);
			// controlli solo per i nuovi record
			if (0 == $_POST['idutente'] and $loginok) {
				// uuid
				$a[] = $b2->campoSQL('uuid', $b2->uuid());
				$db->query("INSERT INTO utente SET " . implode(',', $a));
			} else {
				$db->query("UPDATE utente SET " . implode(',', $a) . " WHERE idutente='" . $b2->normalizza($_POST['idutente']) . "'");
			}
		}
		header('Location: ana_collaboratori.php');
		die();

	} catch (\Throwable $th) {
		echo $th;
		die();
	}
}

if (isset($_GET['idutente'])) {
	if (is_numeric($_GET['idutente'])) {
		if (0 == $_GET['idutente']) {
			$r = array();
			$r['idutente'] = 0;
			$r['idlivello'] = 0;
			$r['login'] = '';
			$r['password'] = '';
			$r['isattivo'] = '1';
			$r['nome'] = '';
			$r['cognome'] = '';
			$r['iniziali'] = '';
			$r['email'] = '';
			$r['cellulare'] = '';
			$r['indirizzo1'] = '';
			$r['indirizzo2'] = '';
			$r['indirizzo3'] = '';
			$r['cf'] = '';
			$r['dnascita'] = '';
			$r['luonascita'] = '';
			$r['iban'] = '';
			$r['piva'] = '';
			$r['banca'] = '';
			$r['retrordinaria'] = 0;
			$r['retrservizio'] = 0;
			$r['pctriparazione'] = 0;
			$head = "Inserimento di un nuovo collaboratore";
		} else {
			$q = $db->query("SELECT * FROM utente WHERE idutente='" . $b2->normalizza($_GET['idutente']) . "'");
			if ($q->num_rows > 0) {
				$r = $q->fetch_array();
				$head = "Modifica $r[cognome] $r[nome]";
			} else {
				header('Location: home.php');
				die();
			}
		}
	} else {
		header('Location: home.php');
		die();
	}
} else {
	header('Location: home.php');
	die();
}

intestazione($head);

echo "\n<form id='coll' method='post' action='ana_collaboratoriedit.php'>";
echo $b2->inputHidden('idutente', $r['idutente']);

echo "\n<table border='0' align='center'>";

echo $b2->rigaEdit('Login:', $b2->inputText('login', $r['login'], 30) . " <span id='loginmsg'></span>");
echo $b2->rigaEdit('Password:', $b2->inputText('password', '', 30, 50));
echo $b2->rigaEdit('Attivo:', $b2->inputCheck('isattivo', $r['isattivo'] == '1'));
echo $b2->rigaEdit('Livello:', $b2->inputSelect('idlivello', $b2->creaArraySelect("SELECT idlivello,livello FROM livello ORDER BY livello"), $r['idlivello']));
echo $b2->rigaEdit("Cognome:", $b2->inputText('cognome', $r['cognome'], 30, 60));
echo $b2->rigaEdit("Nome:", $b2->inputText('nome', $r['nome'], 30, 60));
echo $b2->rigaEdit("Iniziali:", $b2->inputText('iniziali', $r['iniziali'], 3, 4));
echo $b2->rigaEdit("Email:", $b2->inputText('email', $r['email'], 50, 250));
echo $b2->rigaEdit("Cellulare:", $b2->inputText('cellulare', $r['cellulare'], 50, 250));
echo $b2->rigaEdit("Indirizzo:", $b2->inputText('indirizzo1', $r['indirizzo1'], 50, 250) . '<br/>' . $b2->inputText('indirizzo2', $r['indirizzo2'], 50, 250) . '<br/>' . $b2->inputText('indirizzo3', $r['indirizzo3'], 50, 250), B2_ED_VTOP);
echo $b2->rigaEdit("Codice fiscale:", $b2->inputText('cf', $r['cf'], 16, 18) . " <span id='cfmsg'></span>");
echo $b2->rigaEdit("Partita IVA:", $b2->inputText('piva', $r['piva'], 20));
echo $b2->rigaEdit("Data di nascita:", $b2->inputText('dnascita', $b2->dt2ita($r['dnascita'], B2_DT_ZEROFILL), 11, 10, 'dnascita', '', B2_IT_CENTER));
echo $b2->rigaEdit("Luogo di nascita:", $b2->inputText('luonascita', $r['luonascita'], 50, 100));
echo $b2->rigaEdit("Banca:", $b2->inputText('banca', $r['banca'], 50, 250));
echo $b2->rigaEdit("IBAN:", $b2->inputText('iban', $r['iban'], 40));
echo $b2->rigaEdit("Retribuzione oraria:", $b2->inputText('retrordinaria', $r['retrordinaria'], 12, 12, '', '', B2_IT_RIGHT));
echo $b2->rigaEdit("Retribuzione per servizio:", $b2->inputText('retrservizio', $r['retrservizio'], 12, 12, '', '', B2_IT_RIGHT));
echo $b2->rigaEdit("Percentuale markup riparazione:", $b2->inputText('pctriparazione', $r['pctriparazione'], 5, 5, '', '', B2_IT_RIGHT));

if ($r['idutente'] > 0) {
	// dati di login
	if ('' != trim($r['lastipok'])) {
		echo $b2->rigaEdit("Ultimo login:", $b2->ts2ita($r['lastloginok']) . " da " . $b2->risolviIP($r['lastipok']));
	}
	if ('' != trim($r['lastloginko'])) {
		echo $b2->rigaEdit("Ultimo login fallito:", $b2->ts2ita($r['lastloginko']) . " da " . $b2->risolviIP($r['lastipko']));
	}
	// permetto di cancellare solo se il collaboratore non e' utilizzato
	$rr1 = $db->query("SELECT COUNT(*) FROM planningutente WHERE idutente='$r[idutente]'")->fetch_array();
	if ($rr1[0] == 0 and isabilitato($_SESSION['utente']['idlivello'], "sonolamministratore.php")) {
		// delete
		echo "\n<tr><td align='right' valign='top'><b>Elimina dall'archivio:</b></td>";
		echo "<td align='left' valign='top'><input type='checkbox' name='xxx1'/> <input type='checkbox' name='xxx2'/> <input type='checkbox' name='xxx3'/><br />";
		echo "Per cancellare definitivamente un collaboratore spuntare tutte e tre le caselle.<br/><b>Non verr&agrave; chiesta alcuna ulteriore conferma!</b></td>";
	}
}

// submit
echo "\n<tr><td align='center' colspan='2'><input type='submit' value=\"Aggiorna i dati\"></td></tr>";
echo "\n</table>";
echo "\n</form>";
echo "\n<p>&nbsp;</p>";

?>

<script>
  $.datepicker.setDefaults( $.datepicker.regional[ "it" ] );
  $(function() {
    $( "#dnascita" ).datepicker({
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
      $( "#dnascita" ).datepicker( "option", "dateFormat", $( this ).val() );
    });
  });
	$(document).ready(function() {
		// cambio login
  	$("#login").change(function(){
	    var login = $(this).val();
	    var idutente = $("#idutente").val();
			$.post("ana_collaboratoriedit.server.php", 
				{dispatch: "login", login: login, idutente: idutente})
				.done(function( data ) {
					$("#loginmsg").html(data);
  			});
  	});
		// cambio cf
  	$("#cf").change(function(){
	    var cf = $(this).val();
			$.post("ana_collaboratoriedit.server.php", 
				{dispatch: "cf", cf: cf})
				.done(function( data ) {
					$("#cfmsg").html(data);
  			});
  	});
		$('#retrordinaria').mask("#.##0,00", {reverse: true});
		$('#retrservizio').mask("#.##0,00", {reverse: true});
		$('#pctriparazione').mask("##0%", {reverse: true});
  });
</script>

<?php

piede();

### END OIF FILE ###