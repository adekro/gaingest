<?php

/**
 * 
 * Gain Studios - fattura, modifica testata
 * Copyright (C) 2015-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20150521 file creato
 * 20161001 rifacimento da Matrix
 * 20190128 migrazione a jQuery e rimozione doc_fatturaedit.server.php
 * 20220821 fix implode()
 *
 */

define('SOUNDPARK', true);
require('global.php');

// ajax
if (isset($_POST['dispatch']) and 'idcliente' == $_POST['dispatch']) {
	$a = array();
	$idcliente = $b2->normalizza($_POST['idcliente']);
	$r = $db->query("SELECT ragsoc,cognome,nome,indirizzo1,indirizzo2,cap,citta,provincia,stato,idpagamento,piva,cf,idiva,isesenteivadic,dicestremi,dettaglioesenzione
	                FROM clienti 
	                WHERE idcliente='$idcliente'")->fetch_array();
	$ragsoc = '' == trim($r['ragsoc']) ? "$r[cognome] $r[nome]" : $r['ragsoc'];
	$a['ragsoc'] = $r['ragsoc'];
	$a['indirizzo1'] = $r['indirizzo1'];
	$a['indirizzo2'] = $r['indirizzo2'];
	$a['indirizzo3'] = trim("$r[cap] $r[citta] $r[provincia] $r[stato]");
	$a['idpagamento'] = $r['idpagamento'];
	$a['piva'] = $r['piva'];
	$a['cf'] = $r['cf'];
	$a['idiva'] = $r['idiva'];
	$a['isesenteivadic'] = $r['isesenteivadic'];
	$a['dicestremi'] = $r['dicestremi'];
	$a['dettaglioesenzione'] = $r['dettaglioesenzione'];
	echo json_encode($a);
	die();
}


if (isset($_POST['idfattura'])) {
	$idfattura = $b2->normalizza($_POST['idfattura']);
	$a = array();
	if (isset($_POST['xxx'])) {
		$db->query("DELETE FROM fatturarighe WHERE idfattura='$idfattura'");
		$db->query("DELETE FROM fattura WHERE idfattura='$idfattura'");
	} else {
		// anno
		list($dd,$mm,$yy) = explode('/', $_POST['data']);
		if ($yy < 2000) $yy += 2000;
		$a[] = $b2->campoSQL("anno", $yy);
		$a[] = $b2->campoSQL("data", $b2->dt2iso($_POST['data']));
		$a[] = $b2->campoSQL("numero", $_POST['numero']);
		$a[] = $b2->campoSQL("idcliente", $_POST['idcliente']);
		$a[] = $b2->campoSQL("idddt", $_POST['idddt']);
		$a[] = $b2->campoSQL("idpagamento", $_POST['idpagamento']);
		$a[] = $b2->campoSQL("ragsoc", $_POST['ragsoc']);
		$a[] = $b2->campoSQL("indirizzo1", $_POST['indirizzo1']);
		$a[] = $b2->campoSQL("indirizzo2", $_POST['indirizzo2']);
		$a[] = $b2->campoSQL("indirizzo3", $_POST['indirizzo3']);
		$a[] = $b2->campoSQL("cf", $_POST['cf']);
		$a[] = $b2->campoSQL("piva", $_POST['piva']);
		$a[] = $b2->campoSQL("idiva", $_POST['idiva']);
		if ($_POST['idfattura'] > 0 ) {
			$db->query("UPDATE fattura SET " . implode(',', $a) . " WHERE idfattura='$idfattura'");
		} else {
			if ('' == trim($_POST['ragsoc'])) {
				header("Location: doc_fattura.php");
				die();
			} else {
				$db->query("INSERT INTO fattura SET " . implode(',', $a));
				header("Location: doc_fatturarighe.php?idfattura=" . $db->insert_id);
				die();
			}
		}
		header("Location: doc_fattura.php");
		die();
	}
	header("Location: doc_fattura.php");
	die();
}


if (isset($_GET['idfattura'])) {
	if (is_numeric($_GET['idfattura'])) {
		if (0 == $_GET['idfattura']) {
			$r = array();
			$r['idfattura'] = 0;
			$r['data'] = date('Y-n-j');
			$r['anno'] = date('Y');
			$r['idcliente'] = 0;
			$r['idddt'] = 0;
			$r['idpagamento'] = 0;
			$r['ragsoc'] = '';
			$r['indirizzo1'] = '';
			$r['indirizzo2'] = '';
			$r['indirizzo3'] = '';
			$r['cf'] = '';
			$r['piva'] = '';
			$r['idiva'] = 0;
			$r['dicestremi'] = '';
			$r['dettaglioesenzione'] = '';
			$r['isesenteivadic'] = 0;
			$head = "Inserimento di una nuova fattura";
			// ultima fattura
			$qq = $db->query("SELECT numero FROM fattura WHERE anno='$r[anno]' ORDER BY numero DESC LIMIT 1");
			if ($qq->num_rows > 0) {
				$rr = $q->fetch_array();
				$r['numero'] = $rr['numero'] + 1;
			} else {
				$r['numero'] = 1;
			}
		} else {
			$q = $db->query("SELECT * FROM fattura WHERE idfattura='" . $b2->normalizza($_GET['idfattura']) . "'");
			if ($q->num_rows > 0) {
				$r = $q->fetch_array();
				$head = "Modifica fattura $r[numero]/$r[anno]";
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

echo "\n<form method='post' action='doc_fatturaedit.php'>";
//echo $b2->inputHidden('dispatch', 'form');
echo $b2->inputHidden('idfattura', $r['idfattura']);

echo "\n<table border='0' align='center'>";

// idcliente
$xa = array(0=>'Seleziona un cliente');
$qq = $db->query("SELECT idcliente,ragsoc FROM clienti ORDER BY ragsoc");
while ($rr = $qq->fetch_array()) $xa[$rr['idcliente']] = "$rr[ragsoc]";
echo $b2->rigaEdit('Cliente:', $b2->inputSelect('idcliente', $xa, $r['idcliente']));
// numero
echo $b2->rigaEdit('Numero fattura:', $b2->inputText('numero', $r['numero'], 6));
// data
echo $b2->rigaEdit('Data fattura:', $b2->inputText('data', $b2->dt2ita($r['data']), 12));
// idddt
$aa = array(0=>'Nessuno');
$qq = $db->query("SELECT idddt,numero,data FROM ddt WHERE anno='$r[anno]' ORDER BY numero");
while ($rr = $qq->fetch_array()) $aa[$rr['idddt']] = "$rr[numero] del " . $b2->dt2ita($rr['data']);
echo $b2->rigaEdit("DDT:", $b2->inputSelect('idddt', $aa, $r['idddt']));
// idpagamento
$aa = array(0=>'Non definito');
$qq = $db->query("SELECT idpagamento,pagamento FROM pagamenti ORDER BY pagamento");
while ($rr = $qq->fetch_array()) $aa[$rr['idpagamento']] = "$rr[pagamento]";
echo $b2->rigaEdit("Pagamento:", $b2->inputSelect('idpagamento', $aa, $r['idpagamento']));
// intestazione
echo $b2->rigaEdit("Intestazione:", $b2->inputText('ragsoc', $r['ragsoc'], 50, 250) . '<br/>' .
                               $b2->inputText('indirizzo1', $r['indirizzo1'], 50, 250) . '<br/>' .
                               $b2->inputText('indirizzo2', $r['indirizzo2'], 50, 250) . '<br/>' .
                               $b2->inputText('indirizzo3', $r['indirizzo3'], 50, 250), B2_ED_VTOP);
// cf
echo $b2->rigaEdit("Codice fiscale:", $b2->inputText('cf', $r['cf'], 20));
// piva
echo $b2->rigaEdit("Partita IVA:", $b2->inputText('piva', $r['piva'], 30));
// idiva
$aa = array(0=>'Nessuna');
$qq = $db->query("SELECT idiva,iva FROM iva ORDER BY iva");
while ($rr = $qq->fetch_array()) $aa[$rr['idiva']] = "$rr[iva]";
echo $b2->rigaEdit("IVA:", $b2->inputSelect('idiva', $aa, $r['idiva']));
// isesenteivadic
echo $b2->rigaEdit('Esente IVA:', $b2->inputCheck('isesenteivadic', $r['isesenteivadic'] == '1'));
// dicnumero dicann
echo $b2->rigaEdit("Estremi dichiarazione d'intento", $b2->inputText('dicestremi', $r['dicestremi'], 50, 250));
// dettaglioesenzione
echo $b2->rigaEdit('Dettaglio esenzione:', $b2->inputText('dettaglioesenzione', $r['dettaglioesenzione'], 50, 250));
// note
echo $b2->rigaEdit("Note", $b2->inputText('note', '', 50, 250));
if ($r['idfattura'] > 0) {
	// delete
	echo $b2->rigaEdit("Elimina dall'archivio:", $b2->inputCheck('xxx'));
}

// submit
$x = 0 == $r['idfattura'] ? 'Aggiungi questa fattura' : 'Aggiorna questa fattura';
echo "\n<tr><td align='center' colspan='2'><input type='submit' value=\"$x\"/></td></tr>";

echo "\n</table>";
echo "\n</form>";
echo "\n<p>&nbsp;</p>";

?>
<script type="text/javascript">
  $(document).ready(function() {
  	// cambio cliente
  	$("#idcliente").change(function(){
  		// valore della combo
	    var idcliente = $(this).find('option:selected').val();
			$.post("doc_fatturaedit.php", 
				{dispatch: "idcliente", idcliente: idcliente})
				.done(function( data ) {
					var json = $.parseJSON(data);
					$("#ragsoc").val(json.ragsoc);
					$("#indirizzo1").val(json.indirizzo1);
					$("#indirizzo2").val(json.indirizzo2);
					$("#indirizzo3").val(json.indirizzo3);
					$("#idpagamento").val(json.idpagamento);
					$("#piva").val(json.piva);
					$("#cf").val(json.cf);
					$("#idiva").val(json.idiva);
					$("#dicestremi").val(json.dicestremi);
					$("#dettaglioesenzione").val(json.dettaglioesenzione);
					if ('1' == json.isesenteivadic) {
						$("#isesenteivadic").prop('checked', true);
					} else {
						$("#isesenteivadic").prop('checked', false);
					}
  			});
  	});
  }); //document.ready
 	// date
  $.datepicker.setDefaults( $.datepicker.regional[ "it" ] );
  $(function() {
    $( "#data" ).datepicker({
    	changeYear: true,
    	yearRange: "-1:+2",
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
  }); // function
</script>

<?php

piede();

### END OIF FILE ###