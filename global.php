<?php

/**
 * Gain Studios - Funzioni e definizioni globali
 * Copyright (C) 2014-2021 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * NB: Le intestazioni delle singole funzioni riportano il changelog delle stesse, qui c'e' il changelog generico
 * 20141002 prima versione
 * 20150807 include del mailer spsotata qui
 * 20150815 aggiornamento creazione istanza B2TOOLS
 * 20160922 cambio durata sessione, logriparazione()
 * 20160923 notificariparazione(), getRiparazioneStato(), checkRiparazioneStato()
 * 20161226 aggiornato PHPMailer alla versione 5.2.18
 * 20190204 conversione funzioni locali in B2TOOLS, rimozione xAjax
 * 20220815 nuovo phpmailer
 *
 */
 
//error_reporting ( E_ALL );
//ini_set ( "display_errors", 'on' );

if(!defined('SOUNDPARK')) {
	header ('Location: http://www.gainstudios.com/');
	die();
}

//sessione
//ini_set("session.gc_maxlifetime", "8000"); 
session_start();

//override di qls impostazione del server http
header("Content-Type: text/html; charset=UTF-8");

// sicurezza X-Content-Type-Options: nosniff
header("X-Frame-Options: deny");
header("Frame-Options: deny");
header("X-XSS-Protection: \"1; mode=block\"");
header("X-Content-Type-Options: nosniff");

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

// definizioni
define('HEAD_CALENDAR', 1);     // intestazione() : calendario
define('HEAD_EDITOR',   2);     // intestazione() : editor

// classi di comumenti
$aclassedoc = array('C'=>'Documenti dei collaboratori');

// prima di tutto: se non e' impostato l'array di sessione, e' inutile continuare (e generare warning nei log!)
if (!isset($_SESSION['utente'])) {
	session_destroy();
	header('Location: index.php');
	die();
}

// controllo di sicurezza 1/3 - attivo
$q = $db->query("SELECT isattivo FROM utente WHERE idutente='" . $db->escape_string($_SESSION['utente']['idutente']) . "'");
if ($q->num_rows < 1) {
	error_log("Utente " . $_SESSION['utente']['login'] . " non trovato per il controllo di stato attivo. Strano.");
	session_destroy();
	header('Location: index.php');
	die();
} else {
	$r = $q->fetch_array();
	if ('0' == $r['isattivo']) {
		error_log("Utente " . $_SESSION['utente']['login'] . " disattivato, lo caccio.");
		session_destroy();
		header('Location: index.php');
		die();
	}
}
// controllo di sicurezza 2/3 - password
$q = $db->query("SELECT idutente FROM utente WHERE idutente='" . $db->escape_string($_SESSION['utente']['idutente']) . "' AND password='" . $db->escape_string($_SESSION['utente']['password']) . "'");
if ($q->num_rows < 1) {
	error_log("Utente " . $_SESSION['utente']['login'] . ": password mismatch, lo caccio.");
	session_destroy();
	header('Location: index.php');
	die();
}
// controllo di sicurezza 3/3 - accesso allo script
$r = explode('/', $_SERVER["SCRIPT_NAME"]);
$nomefile = $r[count($r) - 1]; 
$q = $db->query("SELECT idmenu FROM menu WHERE nomefile='" . $db->escape_string($nomefile) . "'");
// se lo script non e' nel menu non e' una bella cosa
if ($q->num_rows < 1) {
	error_log("Script $nomefile non trovato nella tabella menu: caccio l'utente, ma bisognerebbe trovare una soluzione.");
	session_destroy();
	header('Location: index.php');
	die();
} else {
	$r = $q->fetch_array();
	if (!isabilitato($_SESSION['utente']['idlivello'], $r['idmenu'])) {
		error_log($_SESSION['utente']['login'] . " ha violato la sicurezza di $nomefile: lo caccio.");
		session_destroy();
		header('Location: index.php');
		die();
	}
}
unset($nomefile);
unset($r);
unset($q);
unset($qq);
// fine controlli di sicurezza


/**
 * intestazione($titolo, $opzioni=0, $altriheader = '')
 * 
 * Disegna l'intestazione e prepara l'ambiente
 * 
 * 20141002 prima versione
 * 20150310 nuovo layout
 * 20150616 jquery-ui per il calendario nuovo
 * 20160915 jQuery mask input
 * 20190204 rimosso xajax e opzioni mascherato a bit
 *
 */
function intestazione($titolo, $opzioni=0, $altriheader = '') {
	global $db,$b2;
	$calendario = $opzioni & HEAD_CALENDAR;
	$editor = $opzioni & HEAD_EDITOR;

	if ($calendario) {
		require_once('calendar/tc_calendar.php');
	}
	echo "<!DOCTYPE html>	
	      <html>
	      <head>
	      <meta http-equiv='Content-Type' content='text/html; charset=UTF-8/'>
	      <link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Open+Sans|Open+Sans:b' />
	      <link rel='stylesheet' href='inc_jquery-ui/jquery-ui.css' type='text/css'/>
	      <link rel='stylesheet' href='static/gain.css' type='text/css'/>
	      <script src='https://code.jquery.com/jquery-2.2.4.min.js'></script>
	      <meta http-equiv='cache-control' content='no-cache'/>
	      <meta http-equiv='pragma' content='no-cache'/>
	      <link rel='shortcut icon' href='/static/favicon.ico'/>
	      <meta http-equiv='X-UA-Compatible' content='IE=edge'/>
	      <meta name='viewport' content='width=device-width, initial-scale=1'/>
	      <script src='https://code.jquery.com/ui/1.12.1/jquery-ui.min.js'></script>
	      <script type='text/javascript' src='inc_jquery-ui/datepicker-it.js'></script>
	      <script src='static/menu-script.js'></script>
	      <script src='inc_mask/jquery.mask.min.js'></script>";
	echo "\n<title>Gain Studios - $titolo</title>";
	if ($calendario) {
		echo "\n<link href='calendar/calendar.css' rel='stylesheet' type='text/css'/>";
		echo "\n<script language='javascript' src='calendar/calendar.js'></script>";
	}
	if ($editor) {
		echo "\n<script type='text/javascript' src='inc_editor/ckeditor.js'></script>";
	}
	// header con menu e logo
	echo "\n<table border='0' cellspacing='0' cellpadding='0' align='left'><tr>";
	//logo
	echo "<td valign='middle'><img src='static/logo.png' border='0'/>&nbsp;</td>";
	// menu
	echo "\n<td valign='middle'><div id='cssmenu'><ul>";
	$q = $db->query("SELECT menu,url,idmenu FROM menu WHERE isvisibile='1' AND idpadre='0' ORDER BY peso");
	while ($r = $q->fetch_array()) {
		// figli?
		$qf = $db->query("SELECT idmenu,menu,url FROM menu WHERE isvisibile='1' AND idpadre='$r[idmenu]' ORDER BY peso");
		if (isabilitato($_SESSION['utente']['idlivello'], $r['idmenu'])) {
			$url = '' == $r['url'] ? '#' : $r['url'];
			$class = $qf->num_rows == 0 ? 'active' : 'has-sub';
			echo "<li class='$class'><a href='$url'><span>$r[menu]</span></a>";
			// figli
			if ($qf->num_rows > 0) {
				echo "<ul>";
				while ($rf = $qf->fetch_array()) {
					$ai = array();
					$count = 0;
					if (isabilitato($_SESSION['utente']['idlivello'], $rf['idmenu'])) {
						$count++;
						$ai[$count]['url'] = '' == $rf['url'] ? '#' : $rf['url'];
						$ai[$count]['menu'] = $rf['menu'];
					}
					for ($n = 1; $n < $count; $n++) echo "<li><a href='" . $ai[$n]['url'] . "'><span>" . $ai[$n]['menu'] . "</span></a></li>";
					if ($count > 0) echo "<li class='last'><a href='" . $ai[$count]['url'] . "'><span>" . $ai[$count]['menu'] . "</span></a></li>";
				}
				echo "</ul>";
			}
			echo "</li>";
		}
	}
	echo "</ul></div></td></tr></table><br clear='all'/>";
	// titolo
	if ('' != $titolo) echo "\n<div class='titolopagina'>$titolo</div>";
}


/**
 * piede()
 * 
 * Disegna la fine della pagina
 * 
 * 20141002 funzione creata
 * 20150310 nuovo layout
 * 20150523 nome e cognome al posto della descrizione
 * 20160525 visualizza se e' admin
 *
 */
function piede() {
	global $b2;
	$amsg = array();
	if ('1' == $_SESSION['utente']['sviluppatore']) $amsg[] = " <b>MODALIT&Agrave; SVILUPPATORE</b>";
	$amsg[] = $_SESSION['utente']['nome'] . ' ' . $_SESSION['utente']['cognome'];
	if ('1' == $_SESSION['utente']['isadmin']) $amsg[] = " <b>Amministratore</b>";
	if ('' != $_SESSION['utente']['lastloginok']) {
		$x = "Ultima connessione valida: " . $b2->ts2ita($_SESSION['utente']['lastloginok']);
		if ('' != $_SESSION['utente']['lastipok']) $x .= " da " . $_SESSION['utente']['lastipok'];
		$amsg[] = $x;
	}
	if ('' != $_SESSION['utente']['lastloginko']) {
		$x = "Ultima connessione errata: " . $b2->ts2ita($_SESSION['utente']['lastloginko']);
		if ('' != $_SESSION['utente']['lastipko']) $x .= " da " . $_SESSION['utente']['lastipko'];
		$amsg[] = $x;
	}
	$amsg[] = "by <a href='https://b2team.com' target='_blank'>B2TEAM S.r.l.</a>";
	echo "<div class='piedepagina' id='piedepagina'>" . implode('<br/>', $amsg) . "</div>";
	echo "\n</body>\n</html>";
}


/**
 * readsetup($item)
 * Ritorna un valore di setup 
 * 
 * @param string $item tag dell'item di cui si desidera il valore
 *
 * 20141002: funzione creata
 *
 */
function readsetup($item) {
	global $db, $b2;
	$r = $db->query("SELECT valore FROM setup WHERE item='" . $b2->normalizza(strtoupper($item)) . "'")->fetch_array();
	return $r['valore'];
}


/**
 * euro2sql($intero, $decimale)
 * 
 * Ritorna un intero in centesimi di euro per lo stoccaggio nel db
 * 
 * 20141002 prima versione
 *
 */
function euro2sql($intero, $decimale) {
	$retval = $intero * 100 + $decimale;
	return $retval;
}


/**
 * sql2euro($euro)
 * 
 * Ritorna un array con la parte intera e i centesimi di euro
 * 
 * 20141002 prima versione
 *
 */
function sql2euro($centesimi) {
	$retval = array();
	$retval['dec'] = sprintf("%02d", $centesimi % 100);  // per fare zero-fill;
	$retval['int'] = (int)floor($centesimi / 100);
	return $retval;
}


/**
 * isabilitato($idlivello, $idmenu)
 * 
 * controlla se il libello utente $idlivello e' abilitato al menu $idmenu
 * 
 * 20141002 prima versione
 *
 */
function isabilitato($idlivello, $idmenu) {
	global $db;
	// lo sviluppatore e' sempre abilitato a tutto (e fa risparmiare una query)
	if ('1' == $_SESSION['utente']['sviluppatore']) {
		return TRUE;
	} else {
		$q = $db->query("SELECT idlivello FROM abilitazioni WHERE idlivello='$idlivello' AND idmenu='$idmenu'");
		return $q->num_rows > 0;
	}
}


/**
 * planningcollaboratori($idplanning)
 * 
 * funzione di appoggio per il planning
 *  
 * 20150624 prima versione
 * 20160623 impegni multipli
 * 20161112 tabella MEMORY per la gestione degli invitati
 *
 */
function planningcollaboratori($idplanning) {
	global $db, $b2;
	$b = '';
	$q = $db->query("SELECT * FROM tmp_planningedit WHERE idtemp='$_SESSION[idtemp] ORDER BY cognome,nome'");
	if ($q->num_rows > 0) {
		$b .= "<table border='0' align='left'>";
		$b .= $b2->intestazioneTabella(array("Collaboratore",'Stato',"Mail",'Modifica', "Note",""));
		while ($r = $q->fetch_array()) {
			$b .= "<tr style='color:$r[colore];'>";
			$b .= "<td style='color:$r[colore];' align='left'>$r[nome] $r[cognome]</td>"; 
			$b .= "<td align='center'>" . $b2->inputSelect("stato-$idplanning-$r[idutente]", $b2->creaArraySelect("SELECT idplanningstatocol,planningstatocol FROM planningstatocol ORDER BY ordine"), $r['idplanningstatocol'], '', "onChange=\"return cambiastato($r[idplanningedit], this.value);\"") . "</td>";
			$x = $r['ismail'] == '1' ? 'checked' : '';
			$b .= "<td align='center'><input type='checkbox' $x name='mail-$r[idplanningedit]' onClick=\"return togglemail($r[idplanningedit]);\"/></td>";
			$b .= "<td style='color:$r[colore];' align='left'>" . date(' j/n G:i', $r['modificato']) . "</td>";
			$b .= "<td style='color:$r[colore];'align='left'>$r[note]</td>"; 
			$b .= "<td align='center'><input type='button' class='cancella' id='canc-$r[idplanningedit]' value='Cancella' onClick=\"return rimuovi($r[idplanningedit]);\"></td>";
			$b .= "</tr>";
		}
		$b .= "</table><br/>";
	}
	return $b;
}

/**
 * notificaplanning($idutente, $idplanning, $iscancellato = false)
 * 
 * invia una mail di notifica per il planning
 * 
 * 20150708 prima versione
 * 20161130 luogo
 * 20180829 rimosso TLS
 * 20190128 conversione ora da int a char
 *
 */
function notificaplanning($idutente, $idplanning, $iscancellato = false) {
	global $db, $b2;
	$ru = $db->query("SELECT cognome,nome,email,uuid FROM utente WHERE idutente='$idutente'")->fetch_array();
	$rp = $db->query("SELECT planning.data,planning.orainizio,planning.orafine,planning.titolo,planning.dettaglio,planning.uuid,planning.luogo,
	                         planningstato.planningstato,
	                         clienti.ragsoc
	                  FROM planning 
	                  JOIN planningstato ON planning.idplanningstato=planningstato.idplanningstato
	                  JOIN clienti ON planning.idcliente=clienti.idcliente
	                  WHERE idplanning='$idplanning'")->fetch_array(); 

	$mail = new PHPMailer(true);
	$mail->SMTPSecure = false;
	$mail->SMTPAutoTLS = false;
	$mail->isSMTP(); 
	$mail->isHTML(true); 
	$mail->CharSet = 'UTF-8';
	$mail->Host = readsetup('SMTPSERVER');
	$mail->From = readsetup('MAILFROM');
	$mail->FromName = 'Notifica servizi Gain Studios';
	$mail->addAddress($ru['email'], "$ru[nome] $ru[cognome]");
	$mail->Subject = "Servizio $rp[titolo]";
	
	if ($iscancellato) {
		$mail->Body = "<p>Non sei pu&ugrave; assegnato al servizio <b>$rp[titolo]</b> del " . $b2->dt2ita($rp['data']) . ".</p>";
	} else {
		$hi = int2ora($rp['orainizio']);
		$hf = int2ora($rp['orafine']);
		$url = sprintf("%s://%s",
                   isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                   $_SERVER['SERVER_NAME']
                  );
		$url .= "/pub_editstato.php?a=$ru[uuid]&amp;b=" . $b2->uuid() . "&amp;c=$rp[uuid]";
		$rc = $db->query("SELECT planningutente.modificato,
		                         planningstatocol.planningstatocol
		                  FROM planningutente 
		                  JOIN planningstatocol ON planningutente.idplanningstatocol=planningstatocol.idplanningstatocol
		                  WHERE planningutente.idplanning='$idplanning' AND planningutente.idutente='$idutente'")->fetch_array();
		$mail->Body = "<p>Dettaglio del servizio:<table border='0' align='center'>
		               <tr><td align='right'><b>Servizio:</b></td><td align='left'>$rp[titolo]</td></tr>
		               <tr><td align='right'><b>Luogo:</b></td><td align='left'>$rp[luogo]</td></tr>
		               <tr><td align='right'><b>Cliente:</b></td><td align='left'>$rp[ragsoc]</td></tr>
		               <tr><td align='right'><b>Data:</b></td><td align='left'>" . $b2->dt2ita($rp['data']) . " $hi[c]-$hf[c]</td></tr>
		               <tr><td align='right' valign='top'><b>Dettagli:</b></td><td align='left' valign='top'>$rp[dettaglio]</td></tr>
		               <tr><td align='right'><b>Stato del servizio:</b></td><td align='left'>$rp[planningstato]</td></tr>
		               </table></p>
		               <p align='center'>Lo stato attuale della tua pianificazione &egrave; <b>$rc[planningstatocol]</b>, per modificare lo stato 
		               <a href='$url'>clicca qui</a> oppure segui questo link:<br>
		               <a href='$url'>$url</a>";
	}
	$mail->send();
}


/**
 * notificaplanningmultiplo($idutente, $uuid_gruppo, $iscancellato = false)
 * 
 * invia una mail di notifica per il planning multigiorni
 * 
 * 20150623 prima versione
 * 20161130 luogo
 * 20180829 rimosso TLS
 * 20190128 conversione ora da int a char
 *
 */
function notificaplanningmultiplo($idutente, $uuid_gruppo, $iscancellato = false) {
	global $db, $b2;
	$r = $db->query("SELECT idplanning FROM planning WHERE uuid_gruppo='$uuid_gruppo'")->fetch_array();
	$idplanning = $r['idplanning'];	
	$ru = $db->query("SELECT cognome,nome,email,uuid FROM utente WHERE idutente='$idutente'")->fetch_array();
	$rp = $db->query("SELECT planning.titolo,planning.dettaglio,planning.data,planning.luogo,
	                         planningstato.planningstato,
	                         clienti.ragsoc
	                  FROM planning 
	                  JOIN planningstato ON planning.idplanningstato=planningstato.idplanningstato
	                  JOIN clienti ON planning.idcliente=clienti.idcliente
	                  WHERE uuid_gruppo='$uuid_gruppo'")->fetch_array(); 
	$mail = new PHPMailer;
	$mail->SMTPSecure = false;
	$mail->SMTPAutoTLS = false;
	$mail->isSMTP(); 
	$mail->isHTML(true); 
	$mail->CharSet = 'UTF-8';
	$mail->Host = readsetup('SMTPSERVER');
	$mail->From = readsetup('MAILFROM');
	$mail->FromName = 'Notifica servizi Gain Studios';
	$mail->addAddress($ru['email'], "$ru[nome] $ru[cognome]");
	$mail->Subject = "Servizio $rp[titolo]";
	
	if ($iscancellato) {
		$b = "<p>Non sei pu&ugrave; assegnato al servizio <b>$rp[titolo]</b> del " . $b2->dt2ita($rp['data']) . ".</p>";
	} else {
		$url = sprintf("%s://%s",
                   isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                   $_SERVER['SERVER_NAME']
                  );
		$url .= "/pub_editstatomultiplo.php?a=$ru[uuid]&amp;b=" . $b2->uuid() . "&amp;c=$uuid_gruppo";
		$rc = $db->query("SELECT planningutente.modificato,
		                         planningstatocol.planningstatocol
		                  FROM planningutente 
		                  JOIN planningstatocol ON planningutente.idplanningstatocol=planningstatocol.idplanningstatocol
		                  WHERE planningutente.idplanning='$idplanning' AND planningutente.idutente='$idutente'")->fetch_array();
		$b = "<p>Dettaglio del servizio:<table border='0' align='center'>
		      <tr><td align='right'><b>Servizio:</b></td><td align='left'>$rp[titolo]</td></tr>
		      <tr><td align='right'><b>Luogo:</b></td><td align='left'>$rp[luogo]</td></tr>
		      <tr><td align='right'><b>Cliente:</b></td><td align='left'>$rp[ragsoc]</td></tr>
		      <tr><td align='right' valign='top'><b>Date:</b></td><td align='left'  valign='top'>";
		$qx = $db->query("SELECT data,orainizio,orafine FROM planning WHERE uuid_gruppo='$uuid_gruppo' ORDER BY data,orainizio"); 
		while ($rx = $qx->fetch_array()) {
			// umanizzazione ore
			$hi = int2ora($rx['orainizio']);
			$hf = int2ora($rx['orafine']);
			$b .= $b2->dt2ita($rx['data']) . " $hi[c]-$hf[c]<br/>";
		}
		$b .= "</td></tr>";
		$b .= "<tr><td align='right' valign='top'><b>Dettagli:</b></td><td align='left' valign='top'>$rp[dettaglio]</td></tr>
		       <tr><td align='right'><b>Stato del servizio:</b></td><td align='left'>$rp[planningstato]</td></tr>
		       </table></p>
		       <p align='center'>Lo stato attuale della tua pianificazione &egrave; <b>$rc[planningstatocol]</b>, per modificare lo stato 
		       <a href='$url'>clicca qui</a> oppure segui questo link:<br>
		       <a href='$url'>$url</a>";
	}
	$mail->Body = $b;
	$mail->send();
}


/**
 * isvoceplanning($idplanning)
 * 
 * true se l'utente corrente puo' vedere la voce di planning
 * 
 * 20160525 prima versione
 *
 */
function isvoceplanning($idplanning) {
	global $db;
	// lo sviluppatore e' sempre abilitato a tutto (e fa risparmiare una query)
	if ('1' == $_SESSION['utente']['sviluppatore']) {
		return true;
	} elseif ('1' == $_SESSION['utente']['isadmin']) {
		// anche l'amministratore e' sempre abilitato a tutto (e anche lui fa risparmiare una query)	
		return true;
	} else {
		$q = $db->query("SELECT idplanning FROM planningutente WHERE idplanning='$idplanning' AND idutente='" . $_SESSION['utente']['idutente'] . "'");
		return $q->num_rows > 0;
	}
}


/**
 * isvoceassenza($idassenza)
 * 
 * true se l'utente corrente puo' vedere la voce di assenza
 * 
 * 20160527 prima versione
 *
 */
function isvoceassenza($idassenza) {
	global $db;
	// lo sviluppatore e' sempre abilitato a tutto (e fa risparmiare una query)
	if ('1' == $_SESSION['utente']['sviluppatore']) {
		return true;
	} elseif ('1' == $_SESSION['utente']['isadmin']) {
		// anche l'amministratore e' sempre abilitato a tutto (e anche lui fa risparmiare una query)	
		return true;
	} else {
		$q = $db->query("SELECT idassenza FROM assenza WHERE idassenza='$idassenza' AND idutente='" . $_SESSION['utente']['idutente'] . "'");
		return $q->num_rows > 0;
	}
}


/**
 * ora2int($h, $m)
 * 
 * ritorna un'ora in formato db
 * 
 * 20160525 prima versione
 * 20190128 conversione da int a char
 *
 */
function ora2int($h, $m) {
	$retval = sprintf("%02d", $h) . sprintf("%02d", $m);
	return ($retval);
}


/**
 * int2ora($i)
 * 
 * ritorna un array ore minuti prendendo l'ora dal DB
 * 
 * 20160525 prima versione
 * 20190128 conversione da int a char e aggiunto campo combinato nell'array 
 *
 */
function int2ora($i) {
	$h = substr($i, 0, 2);
	$m = substr($i, -2);
	return array('h'=>$h, 'm'=>$m, 'c'=>"$h:$m");
}


/**
 * impegnigiorno($idutente, $dataiso, $idplanning = 0)
 * 
 * ritorna un array con gli impegni di un giorno di un utente
 * 
 * 20160915 prima versione
 * 20161111 esclusione un singolo ID
 * 20190128 conversione ora da int a char
 * 20220821 fix $r/$hf nel while() 
 *
 */
function impegnigiorno($idutente, $dataiso, $idplanning = 0) {
	global $db, $b2;
	$ret = array();
	$wh = $idplanning == 0 ? '' : " AND planning.idplanning<>'$idplanning'";
	$q = $db->query("SELECT planning.orainizio,planning.orafine,planning.titolo,planningstatocol.planningstatocol
	                 FROM planning
	                 JOIN planningutente ON planning.idplanning=planningutente.idplanning
	                 JOIN planningstatocol ON planningutente.idplanningstatocol=planningstatocol.idplanningstatocol
	                 WHERE planning.data='$dataiso' AND planningutente.idutente='$idutente' $wh
	                 ORDER BY planning.orainizio");
	while ($r = $q->fetch_array()) {
		$hi = int2ora($r['orainizio']);
		$hf = int2ora($r['orafine']);
		$ret[] = "$hi[c]-$hf[c]: $r[titolo] ($r[planningstatocol]) ";
	}
	return ($ret);
}


/**
 * logriparazione($idriparazione, $messaggio, $idutente = '0')
 * 
 * aggiunge una riga al log delle riparazioni
 * 
 * 20160922 prima versione
 *
 */
function logriparazione($idriparazione, $messaggio, $idutente = '0') {
	global $db, $b2;
	$idutentesql = $idutente == 0 ? $_SESSION['utente']['idutente'] : $idutente;
	$r = $db->query("SELECT idriparazionestato FROM riparazione WHERE idriparazione='$idriparazione'")->fetch_array();
	$a = array();
	$a[] = "dataora=NOW()";
	$a[] = $b2->campoSQL('idriparazionestato', $r['idriparazionestato']);
	$a[] = $b2->campoSQL('idutente', $idutentesql);
	$a[] = $b2->campoSQL('idriparazione', $idriparazione);
	$a[] = $b2->campoSQL('messaggio', $messaggio);
	$a[] = $b2->campoSQL('ip', $_SERVER['REMOTE_ADDR']);
	$db->query("INSERT INTO riparazionelog SET " . implode(',', $a));
	return (true);
}


/**
 * notificariparazione($idriparazione, $anchesupervisri = false)
 * 
 * invia una mail di notifica per la riparazione
 * 
 * 20160923 prima versione
 * 20161130 utf8
 * 20180829 rimosso TLS
 *
 */
function notificariparazione($idriparazione, $anchesupervisori = false) {
	global $db, $b2;
	$r = $db->query("SELECT riparazione.prodotto,riparazione.problema,riparazione.daggiornamento,riparazione.note,
                          utente.nome,utente.cognome,utente.email,
                          riparazionestato.riparazionestato
                   FROM riparazione
                   JOIN riparazionestato ON riparazionestato.idriparazionestato=riparazione.idriparazionestato
                   JOIN utente ON utente.idutente=riparazione.idutente
                   WHERE riparazione.idriparazione='$idriparazione'")->fetch_array(); 
	$mail = new PHPMailer;
	$mail->SMTPSecure = false;
	$mail->SMTPAutoTLS = false;
	$mail->isSMTP(); 
	$mail->isHTML(true); 
	$mail->CharSet = 'UTF-8';
	$mail->Host = readsetup('SMTPSERVER');
	$mail->From = readsetup('MAILFROM');
	$mail->FromName = 'Notifica riparazioni Gain Studios';
	$mail->addAddress($r['email'], "$r[nome] $r[cognome]");
	if ($anchesupervisori) {
		$mail->AddCC(readsetup('MAILSUPERVISORE'));
	}
	$mail->Subject = "Riparazione $r[prodotto]";
	$b = "<table border='0'>";
	$b .= $b2->rigaEdit("Stato:", $r['riparazionestato']);
	$b .= $b2->rigaEdit("Aggiornamento:", $b2->dt2ita($r['daggiornamento']));
	$b .= $b2->rigaEdit("Prodotto:", $r['prodotto']);
	$b .= $b2->rigaEdit("Guasto:", nl2br($r['problema']), B2_ED_VTOP);
	$b .= $b2->rigaEdit("Note:", nl2br($r['note']), B2_ED_VTOP);
	$b .= "</table>";
	$mail->Body = $b;
	if ($mail->send()) {
		logriparazione($idriparazione, "Inviata notifica mail a $r[email]");
	} else {
		logriparazione($idriparazione, "Errore invio notifica mail a $r[email]: " . $mail->ErrorInfo);
	}
}


/**
 * getRiparazioneStato($flag)
 * 
 * restituisce un array con id e descrizione dello stato di riparazione in base al flag
 * 
 * 20160923 prima versione
 *
 */
function getRiparazioneStato($flag) {
	global $db;
	$a = array('idriparazionestato'=> 0, 'riparazionestato'=>'');
	$q = $db->query("SELECT idriparazionestato,riparazionestato FROM riparazionestato WHERE flag LIKE '%$flag%'"); 
	if ($q->num_rows > 0) {
		$r = $q->fetch_array();
		$a['idriparazionestato'] = $r['idriparazionestato'];
		$a['riparazionestato'] = $r['riparazionestato'];
	}
	return ($a);
}


/**
 * checkRiparazioneStato($flag, $idstatoriparazione)
 * 
 * verifica se una riparazione e' nello stato del flag
 * 
 * 20160923 prima versione
 *
 */
function checkRiparazioneStato($flag, $idriparazionestato) {
	global $db, $b2;
	$q = $db->query("SELECT idriparazionestato FROM riparazionestato WHERE idriparazionestato='$idriparazionestato' AND flag LIKE '%$flag%'"); 
	return ($q->num_rows > 0);
}


### END OF FILE ###