<?php

/**
 * 
 * Gain Studios - Modifica stato collaboratore per impegni multipli (pubblico)
 * Copyright (C) 2016-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20160622 file creato
 * 20161226 aggiornato PHPMailer alla versione 5.2.18
 * 20180829 rimosso TLS
 * 20190128 conversione ora da int a char
 * 20220903 phpmailer nuovo
 *
 */

define('SOUNDPARK', true);

//override di qls impostazione del server http
header("Content-Type: text/html; charset=UTF-8");

// cliente
define('CLIENTE', 'GAIN');

// per la classe calendar
define("L_LANG", "it_IT");

require('database.php');

// B2TOOLS
require('inc_b2tools/b2tools.inc.php');
$b2 = new objB2($db);

// phpmailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
//Load Composer's autoloader
require 'vendor/autoload.php';

function readsetup($item) {
	global $db;
	$r = $db->query("SELECT valore FROM setup WHERE item='" . $db->escape_string(strtoupper($item)) . "'")->fetch_array();
	return $r['valore'];
}

if (isset($_POST['d'])) {
	$uidutente = substr($_POST['a'], 0, 36);
	$uuid_gruppo = substr($_POST['c'], 0, 36);
	$qu = $db->query("SELECT cognome,nome,idutente,email FROM utente WHERE uuid='" . $b2->normalizza($uidutente) . "'");
	$qp = $db->query("SELECT titolo,idplanning,data FROM planning WHERE uuid_gruppo='" . $b2->normalizza($uuid_gruppo) . "'");
	if ($qu->num_rows > 0 and $qp->num_rows > 0) {
		$ru = $qu->fetch_array();
		$a =array();
		$a[] = $b2->campoSQL('idplanningstatocol', $_POST['stato']);
		$a[] = $b2->campoSQL('note', $_POST['note']);
		$a[] = $b2->campoSQL('modificato', time());
		while ($rp = $qp->fetch_array()) {
			$db->query("UPDATE planningutente SET " . implode(',', $a) . " WHERE idplanning='$rp[idplanning]' AND idutente='$ru[idutente]'");
		}
		// requery per i dettagli nella mail
		$rp = $db->query("SELECT titolo,idplanning FROM planning WHERE uuid_gruppo='" . $b2->normalizza($uuid_gruppo) . "'")->fetch_array();
		$rs = $db->query("SELECT planningstatocol FROM planningstatocol WHERE idplanningstatocol='" . $b2->normalizza($_POST['stato']) . "'")->fetch_array();
		$mail = new PHPMailer;
		$mail->SMTPSecure = false;
		$mail->SMTPAutoTLS = false;
		$mail->isSMTP(); 
		$mail->isHTML(true); 
		$mail->Host = readsetup('SMTPSERVER');
		$mail->From = readsetup('MAILFROM');
		$mail->FromName = 'Notifica servizi Gain Studios';
		$mail->addAddress($ru['email'], "$ru[nome] $ru[cognome]");
		$abcc = explode(' ', readsetup('MAILSUPERVISORE'));
		foreach ($abcc as $bcc) $mail->addBcc($bcc);
		$mail->Subject = "Presenza aggiornata di $ru[nome] $ru[cognome] per $rp[titolo]";
		$bo = "<p>La presenza di <b>$ru[nome] $ru[cognome]</b> per il servizio <b>$rp[titolo]</b>  del " . $b2->dt2ita($rp['data']) . " &egrave; ora <b>$rs[planningstatocol]</b>.</p>";
		if ('' != trim($_POST['note'])) $bo .= "<p>Il collaboratore ha aggiunto queste note: " . $b2->normalizza($_POST['note'], B2_NORM_FORM) . "</p>";
		$mail->Body = $bo;
		$mail->send();
		echo "Situazione aggiornata, controlla la tua mail.";
	}
	die();
}

// subito alcuni test prima di cominciare
if (isset($_GET['a']) and isset($_GET['b']) and isset($_GET['c'])) {
	$uidutente = substr($_GET['a'], 0, 36);
	$uidfuffa = substr($_GET['b'], 0, 36);
	$uuid_gruppo = substr($_GET['c'], 0, 36);
	if (strlen($uidutente) != 36 or strlen($uuid_gruppo) != 36 or strlen($uidfuffa) != 36) {
		header('Location: http://www.gainstudios.com/');
		error_log('Die: 1');
		die();
	}
} else {
	header('Location: http://www.gainstudios.com/');
	error_log('Die: 2');
	die();
}
$qu = $db->query("SELECT cognome,nome,idutente,email FROM utente WHERE uuid='" . $b2->normalizza($uidutente) . "'");
if ($qu->num_rows == 0) {
	header('Location: http://www.gainstudios.com/');
	error_log('Die: 3');
	die();
} else {
	$ru = $qu->fetch_array();
}
$qp = $db->query("SELECT planning.data,planning.orainizio,planning.orafine,planning.titolo,planning.dettaglio,planning.idplanning,
	                       planningstato.planningstato,
	                       clienti.ragsoc
	               FROM planning 
	               JOIN planningstato ON planning.idplanningstato=planningstato.idplanningstato
	               JOIN clienti ON planning.idcliente=clienti.idcliente
	               WHERE uuid_gruppo='" . $b2->normalizza($uuid_gruppo) . "'");
if ($qp->num_rows == 0) {
	header('Location: http://www.gainstudios.com/');
	error_log('Die: 4');
	die();
} else {
	$rp = $qp->fetch_array();
}

echo "<!DOCTYPE html>	
	    <html>
	    <head>
	    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8/'>
	    <link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Open+Sans|Open+Sans:b' />
	    <link rel='stylesheet' href='inc_jquery-ui/jquery-ui.css' type='text/css'/>
	    <link rel='stylesheet' href='static/gain.css' type='text/css'/>
	    <meta http-equiv='cache-control' content='no-cache'/>
	    <meta http-equiv='pragma' content='no-cache'/>
	    <link rel='shortcut icon' href='/static/favicon.ico'/>
	    <meta http-equiv='X-UA-Compatible' content='IE=edge'/>
	    <meta name='viewport' content='width=device-width, initial-scale=1'/>
	    <title>Gain Studios - Modifica stato collaboratore per impegni multipli</title>
	    </head>
      <body>";

$rc = $db->query("SELECT idplanningstatocol,note FROM planningutente WHERE idplanning='$rp[idplanning]' AND idutente='$ru[idutente]'")->fetch_array();
echo "\n<form method='post' action='pub_editstatomultiplo.php'>";
echo $b2->inputHidden('a', $uidutente);
echo $b2->inputHidden('b', $uidfuffa);
echo $b2->inputHidden('c', $uuid_gruppo);
echo $b2->inputHidden('d', $b2->uuid());
echo "\n<p><b>GAIN STUDIOS - MODIFICA PLANNING</b><br>";
echo "\n<table border='0' align='left'>
      <tr><td align='right'><b>Collaboratore:</b></td><td align='left'>$ru[nome] $ru[cognome]</td></tr>
      <tr><td align='right'><b>Servizio:</b></td><td align='left'>$rp[titolo]</td></tr>
      <tr><td align='right'><b>Cliente:</b></td><td align='left'>$rp[ragsoc]</td></tr>";
$q = $db->query("SELECT planning.data,planning.orainizio,planning.orafine,planning.idplanning
	               FROM planning 
	               WHERE uuid_gruppo='" . $b2->normalizza($uuid_gruppo) . "'
	               ORDER BY data");
echo "\n<tr><td align='right' valign='top'><b>Date:</b></td><td align='left' valign='top'>";
while ($r = $q->fetch_array()) {
	// umanizzazione ore
	$orainizio = substr($r['orainizio'], 0, 2) . ':' . substr($r['orainizio'], -2);
	$orafine = substr($r['orafine'], 0, 2) . ':' . substr($r['orafine'], -2);
	echo $b2->dt2ita($r['data']) . " $orainizio-$orafine";
}
echo "</td></tr>";
echo "\n<tr><td align='right' valign='top'><b>Dettagli:</b></td><td align='left' valign='top'>$rp[dettaglio]</td></tr>
      <tr><td align='right'><b>Stato del servizio:</b></td><td align='left'>$rp[planningstato]</td></tr>
      <tr><td align='right' valign='top'><b>Tuo stato:</b></td><td align='left' valign='top'>";
$q = $db->query("SELECT * FROM planningstatocol ORDER BY ordine");
while ($r = $q->fetch_array()) {
	$x = $rc['idplanningstatocol'] == $r['idplanningstatocol'] ? 'checked' : '';
	echo "<label><input $x type='radio' name='stato' value='$r[idplanningstatocol]'> $r[planningstatocol]</label><br/>";
}
echo "</td></tr>";
echo $b2->rigaEdit('Note:', $b2->inputText('note', $rc['note'], 30, 250));
echo "<tr><td align='center' colspan='2' id='messaggio'><input type='submit' value='Conferma'></td></tr>";
echo "\n</table>\n</p>\n</form>";

echo "\n</body>\n</html>";


### END OF FILE ###