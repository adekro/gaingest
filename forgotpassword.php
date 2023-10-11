<?php

/**
 * 
 * Gain Studios - Procedura di recupero password
 * Copyright (C) 2014-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20150807 prima versione
 * 20161226 aggiornato PHPMailer alla versione 5.2.18
 * 20180829 rimosso TLS
 * 20220903 phpmailer nuovo
 *
 */

// sicurezza X-Content-Type-Options: nosniff
header("X-Frame-Options: deny");
header("Frame-Options: deny");
header("X-XSS-Protection: \"1; mode=block\"");
header("X-Content-Type-Options: nosniff");

define("SOUNDPARK", true);
require('database.php');

// phpmailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
//Load Composer's autoloader
require 'vendor/autoload.php';

// numero di secondi di validita' del form
$timeoutform = 120;
$timeoutmail = 3600;

// per il CAPTCHA
$anum = array();
$anum[1] = 'uno';
$anum[2] = 'due';
$anum[3] = 'tre';
$anum[4] = 'quattro';
$anum[5] = 'cinque';
$anum[6] = 'sei';
$anum[7] = 'sette';
$anum[8] = 'otto';
$anum[9] = 'nove';
$anum[10] = 'dieci';
$anum[11] = 'undici';

$rs = $db->query("SELECT valore FROM setup WHERE item='MAILFROM'")->fetch_array();
$mailfrom = $rs[0];

// cancello i record piu' vecchi di una settimana
$oldtime = strtotime("-1 week");
$db->query("DLETE FROM forgotpassword WHERE timestamp<'$oldtime'");

// scrivo direttamente gli header e le inizializzaiozni varie
echo <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<link rel='STYLESHEET' href='static/login.css' type='text/css'>
<title>Recupero password</title>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
<meta http-equiv='CACHE-CONTROL' content='NO-CACHE'>
<meta http-equiv='PRAGMA' content='NO-CACHE'>
<meta name='robots' content='noindex'/>
</head>
<body>
EOT;

// risposta al form di richiesta
if (isset($_POST['soluzione'])) {
	echo "\n<table border='0' cellspacing='2' cellpadding='2' align='center'>";
	// logo
	echo "\n<tr><td colspan='2' align='center'><img src='static/login.jpg' title='Gain Studios' alt='Gain Studios'/></td></tr>";
	$q = $db->query("SELECT * FROM forgotpassword WHERE uid='" . $db->escape_string($_POST['uid']) . "'");
	if ($q->num_rows > 0) {
		$r = $q->fetch_array();
		if ($_POST['soluzione'] == $r['soluzione'] and $r['timestamp']+$timeoutform > time()) {
			$qq = $db->query("SELECT idutente,email,login FROM utente WHERE login='" . $db->escape_string($_POST['utente']) . "' AND isattivo='1'");
			if ($qq->num_rows > 0) {
				echo "\n<tr><td  height='30' align='center'>Controlla la tua casella di posta elettronica e cerca un messaggio da <b>$mailfrom</b><br/>Se la mail non &egrave; presente nella posta in arrivo, controlla anche nella casella <b>Spam</b> o <b>Posta indesiderata</b> della tua casella E-mail.</td>";
				$rr = $qq->fetch_array();
				$mailuid = sha1("mail $r[uid]" . time() . rand());
				$db->query("UPDATE forgotpassword SET idcollaboratore='$rr[idutente]',mailuid='$mailuid' WHERE uid='$r[uid]'");
				$mail = new PHPMailer;
				$mail->SMTPSecure = false;
				$mail->SMTPAutoTLS = false;
				$mail->isSMTP(); 
				$rs = $db->query("SELECT valore FROM setup WHERE item='SMTPSERVER'")->fetch_array();
				$mail->Host = $rs[0];
				$mail->From = $mailfrom;
				$mail->FromName = 'Recupero password Gain Studios';
				$mail->addAddress($rr['email']);
				$mail->isHTML(true); 
				$mail->Subject = "Recupero password utente $rr[login]";
				$mail->Body = "<p>Per recuperare la tua password vai alla pagina <a href='http://$_SERVER[HTTP_HOST]/forgotpassword.php?id=$mailuid'>$_SERVER[HTTP_HOST]/forgotpassword.php?id=$mailuid</a></p>";
				$mail->AltBody = "Per recuperare la tua password vai alla pagina http://$_SERVER[HTTP_HOST]/forgotpassword.php?id=$mailuid'";
				$mail->send();
			} else {
				echo "\n<tr><td  height='30' align='center'>Hai inserito dei dati errati.</td>";
			}
		} else {
			echo "\n<tr><td  height='30' align='center'>La password <b>non</b> &egrave; stata reimpostata.</td></tr>";
		}
	} else {
		echo "\n<tr><td  height='30' align='center'>La password <b>non</b> &egrave; stata reimpostata.</td></tr>";		
	}
	echo "\n</table>";
} elseif (isset($_GET['id'])) {
	echo "\n<table border='0' cellspacing='2' cellpadding='2' align='center'>";
	// logo
	echo "\n<tr><td colspan='2' align='center'><img src='static/login.jpg' title='Gain Studios' alt='Gain Studios'/></td></tr>";
	$q = $db->query("SELECT * FROM forgotpassword WHERE mailuid='" . $db->escape_string($_GET['id']) . "'");
	if ($q->num_rows > 0) {
		$r = $q->fetch_array();
		if ($r['timestamp']+$timeoutmail > time()) { 
			$caratteri = 'abcdefghijklmnopqrstuvwxyz';
	    $nuovapassword = '';
			$rs = $db->query("SELECT valore FROM setup WHERE item='MINPWLEN'")->fetch_array();
	    for ($i = 0; $i < $rs[0]; $i++) {
	        $nuovapassword .= $caratteri[rand(0, strlen($caratteri) - 1)];
	    }
	    $db->query("UPDATE utente SET password='" . $db->escape_string(sha1($nuovapassword)) ."' WHERE idutente='$r[idcollaboratore]'");
	    $db->query("DELETE FROM forgotpassword WHERE idcollaboratore='$r[idcollaboratore]'");
	    echo "\n<tr><td  height='30' align='center'>La nuova password &egrave; <b>$nuovapassword</b></td></tr>";		
	    echo "\n<tr><td  height='30' align='center'>Ti invitiamo a cambiarla appena possibile.</td></tr>";		
	  } else {
			echo "\n<tr><td  height='30' align='center'>La password <b>non</b> &egrave; stata reimpostata.</td></tr>";		
		}
	} else {
		echo "\n<tr><td  height='30' align='center'>La password <b>non</b> &egrave; stata reimpostata.</td></tr>";		
	}
	echo "\n</table>";
} else {
	// form per la prima fase
	$n1 = mt_rand(1,11);
	$n2 = mt_rand(1,11);
	$somma = $n1 + $n2;
	$uid = uniqid('', TRUE);
	$db->query("INSERT INTO forgotpassword SET timestamp='" . time() . "',soluzione='$somma',uid='$uid'");
	echo "\n<p>&nbsp;</p>";
	echo "\n<p>&nbsp;</p>";
	echo "\n<form method='post' action='forgotpassword.php'>";
	echo "\n<input type='hidden' name='uid' value='$uid'>";
	echo "\n<table border='0' cellspacing='2' cellpadding='2' align='center'>";
	// logo
	echo "\n<tr><td colspan='2' align='center'><img src='static/login.jpg' title='Gain Studios' alt='Gain Studios'/></td></tr>";
	// utente
	echo "\n<tr align='right'><td>Utente:</td>";
	echo "\n<td align='left'><input type='text' name='utente' size='30' maxlength='30'></td></tr>";
	// Captcha
	echo "\n<tr><td colspan='2' height='30' align='center'>Quanto fa " . $anum[$n1] . " pi&ugrave; " . $anum[$n2] . "?</td></tr>";
	echo "\n<tr align='right'><td>Risposta:</td>";
	echo "\n<td align='left'>";
	echo "\n<input type='text' name='soluzione' size='30' maxlength='2'></td></tr>";
	echo "\n<tr><td colspan='2' height='30' align='center'><input type='submit' value='Reimposta la password'></td></tr>";
	echo "\n</table></form>";
	
	echo "\n</body></html>";
}	

### END OF FILE ###
