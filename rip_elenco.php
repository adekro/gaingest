<?php

/**
 * 
 * Gain Studios - Riparazioni, elenco per i riparatori
 * Copyright (C) 2016 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20160923 file creato
 *
 */

define('SOUNDPARK', true);
require('global.php');

if (isset($_GET['azione'])) {
	$idriparazione = $b2->normalizza($_GET['idriparazione']);
	// doppio check se l'utente puo' modificare
	$q = $db->query("SELECT riparazione.idriparazione,riparazione.idriparazionestato
                   FROM riparazione
                   JOIN riparazionestato ON riparazionestato.idriparazionestato=riparazione.idriparazionestato
                   WHERE riparazione.idriparazione='$idriparazione' AND riparazione.idutente='" . $_SESSION['utente']['idutente'] . "' AND riparazionestato.collaboratore='M'");
	if ($q->num_rows > 0) {
		switch ($_GET['azione']) {
			case 'ritirata':
				$a = getRiparazioneStato('COLL_RITIRO_TO');
				$db->query("UPDATE riparazione SET idriparazionestato='$a[idriparazionestato]' WHERE idriparazione='$idriparazione'");
				logriparazione($idriparazione, "Riparatore conferma ritiro");
			break;
			case 'rifiutori':
				$a = getRiparazioneStato('COLL_RIFIUTORI_TO');
				$db->query("UPDATE riparazione SET idriparazionestato='$a[idriparazionestato]' WHERE idriparazione='$idriparazione'");
				logriparazione($idriparazione, "Riparatore rifiuta la riparazione");
			break;
			case 'fineriparazione':
				$a = getRiparazioneStato('COLL_FINE_TO');
				$db->query("UPDATE riparazione SET idriparazionestato='$a[idriparazionestato]' WHERE idriparazione='$idriparazione'");
				logriparazione($idriparazione, "Riparazione terminata");
			break;
		}
		notificariparazione($idriparazione, true);
	} else {
		error_log("Utente " . $_SESSION['utente']['login'] . " (" . $_SESSION['utente']['idutente'] . ") ha tentato di violare la sicurezza per la riparazione $idriparazione azione $_POST[azione]");
	}
}

if (isset($_POST['preventivo'])) {
	$idriparazione = $b2->normalizza($_POST['idriparazione']);
	// doppio check se l'utente puo' modificare
	$q = $db->query("SELECT riparazione.idriparazione,riparazione.idriparazionestato,
	                        clienti.nomarkupriparazione
                   FROM riparazione
                   JOIN riparazionestato ON riparazionestato.idriparazionestato=riparazione.idriparazionestato
                   JOIN clienti ON clienti.idcliente=riparazione.idcliente
                   WHERE riparazione.idriparazione='$idriparazione' AND riparazione.idutente='" . $_SESSION['utente']['idutente'] . "' AND riparazionestato.collaboratore='M'");
	if ($q->num_rows > 0) {
		$r = $q->fetch_array();
		// l'input formattato lascia i caratteri di formattazione
		$preventivo = str_replace(',', '', $_POST['preventivo']);
		$preventivo = str_replace('.', '', $preventivo);
		if (is_numeric($preventivo)) {
			$a = getRiparazioneStato('COLL_PREVE_TO');
			$aa = array();
			$aa[] = $b2->campoSQL('idriparazionestato', $a['idriparazionestato']);
			$aa[] = $b2->campoSQL('compenso', $preventivo);
			if (0 == $_SESSION['utente']['pctriparazione'] or '1' == $r['nomarkupriparazione']) {
				$prezzo = $preventivo;
			} else {
				$prezzo = round($preventivo + ($preventivo * ($_SESSION['utente']['pctriparazione'] / 100)), -2);
				$minimo = readsetup('RIPARAZIONEMINIMO');
				$prezzo = $prezzo < $minimo ? $minimo : $prezzo;
			}
			$aa[] = $b2->campoSQL('prezzo', $prezzo);
			$db->query("UPDATE riparazione SET " . implode(',', $aa) . " WHERE idriparazione='$idriparazione'");
			logriparazione($idriparazione, "Riparatore inserisce preventivo " . $_POST['preventivo']);
			notificariparazione($idriparazione, true);
		}
	}
}

if (isset($_POST['note'])) {
	$idriparazione = $b2->normalizza($_POST['idriparazione']);
	// doppio check se l'utente puo' modificare
	$q = $db->query("SELECT riparazione.idriparazione,riparazione.idriparazionestato,riparazione.note
                   FROM riparazione
                   JOIN riparazionestato ON riparazionestato.idriparazionestato=riparazione.idriparazionestato
                   WHERE riparazione.idriparazione='$idriparazione' AND riparazione.idutente='" . $_SESSION['utente']['idutente'] . "' AND riparazionestato.collaboratore='M'");
	if ($q->num_rows > 0) {
		$r = $q->fetch_array();
		$note = $r['note'];
		$a = getRiparazioneStato('COLL_FINE_TO');
		$aa = array();
		$note = trim($note . "\r\n\r\n---Note di chiusura del riparatore " . date('j/n/Y') . "---\r\n" . $b2->normalizza($_POST['note'], B2_NORM_FORM));
		$aa[] = $b2->campoSQL('idriparazionestato', $a['idriparazionestato']);
		$aa[] = $b2->campoSQL('note', $note);
		$db->query("UPDATE riparazione SET " . implode(',', $aa) . " WHERE idriparazione='$idriparazione'");
		logriparazione($idriparazione, "Riparazione terminata con note del riparatore");
		notificariparazione($idriparazione, true);
	}
}

if (isset($_POST['notelibere'])) {
	$idriparazione = $b2->normalizza($_POST['idriparazione']);
	$q = $db->query("SELECT riparazione.idriparazione,riparazione.note
                   FROM riparazione
                   WHERE riparazione.idriparazione='$idriparazione' AND riparazione.idutente='" . $_SESSION['utente']['idutente'] . "'");
	if ($q->num_rows > 0) {
		$r = $q->fetch_array();
		$aa = array();
		$note = trim($r['note'] . "\r\n\r\n---Nota del riparatore " . date('j/n/Y') . "---\r\n" . $b2->normalizza($_POST['notelibere'], B2_NORM_FORM));
		$aa[] = $b2->campoSQL('note', $note);
		error_log("UPDATE riparazione SET " . implode(',', $aa) . " WHERE idriparazione='$idriparazione'");
		$db->query("UPDATE riparazione SET " . implode(',', $aa) . " WHERE idriparazione='$idriparazione'");
		logriparazione($idriparazione, "Aggiunta nota del riparatore");
		notificariparazione($idriparazione, true);
	}
}
		 
intestazione("Elenco riparazioni");

$q = $db->query("SELECT riparazione.prodotto,riparazione.problema,riparazione.daggiornamento,riparazione.idriparazionestato,riparazione.idriparazione,riparazione.note,
                      riparazionestato.riparazionestato
                 FROM riparazione
                 JOIN riparazionestato ON riparazionestato.idriparazionestato=riparazione.idriparazionestato
                 WHERE riparazione.idutente='" . $_SESSION['utente']['idutente'] . "' AND riparazionestato.collaboratore<>'X' 
                 ORDER BY riparazionestato.ordine,riparazione.daggiornamento DESC");
if ($q->num_rows > 0) {
	while ($r = $q->fetch_array()) {
		echo "\n<p>";
		echo $b2->dt2ita($r['daggiornamento']) . " - $r[riparazionestato]<br/>";
		echo "<b>$r[prodotto]</b><br/>";
		echo "$r[problema]</p>";
		$azioni = array();
		$id = 0;
		// conferma ritiro
		if (checkRiparazioneStato('COLL_RITIRO_FR', $r['idriparazionestato'])) {
			$azioni[] = "<a href='rip_elenco.php?idriparazione=$r[idriparazione]&amp;azione=ritirata'>Conferma ritiro</a>";
		}
		// inserimento preventivo
		if (checkRiparazioneStato('COLL_PREVE_FR', $r['idriparazionestato'])) {
			$azioni[] = "<form method='post' action='rip_elenco.php' style='display: inline;'>
			             <input type='hidden' name='idriparazione' value='$r[idriparazione]'>
			             <input type='text' name='preventivo' id='preventivo' value='' size='8' maxlength='8' style='text-align:right;'>&#8364; (inserire anche i decimali)
			             <input type='submit' name='conferma' value='Conferma preventivo'>
			             </form>";
			$azioni[] = "<a href='rip_elenco.php?idriparazione=$r[idriparazione]&amp;azione=rifiutori'>Rifiuto riparazione</a>";
		}
		// termine lavorazione
		if (checkRiparazioneStato('COLL_FINE_FR', $r['idriparazionestato'])) {
			$azioni[] = "<form method='post' action='rip_elenco.php' style='display: inline;'>
			             <input type='hidden' name='idriparazione' value='$r[idriparazione]'>
			             <input type='text' name='note' id='note' value='' size='40' maxlength='200'>
			             <input type='submit' name='conferma' value='Fine riparazione e note'>
			             </form>";
			$azioni[] = "<a href='rip_elenco.php?idriparazione=$r[idriparazione]&amp;azione=fineriparazione'>Fine riparazione</a>";
		}
		$azioni[] = "<form method='post' action='rip_elenco.php' style='display: inline;'>
             <input type='hidden' name='idriparazione' value='$r[idriparazione]'>
             <input type='text' name='notelibere' id='notelibere' value='' size='50' maxlength='200'>
             <input type='submit' name='conferma' value='Aggiungi questo commento'>
             </form>";
		echo "\n<p>" . implode('<br/>', $azioni) . "</p>";
		$note = str_replace("\r\n", '<br/>', trim($r['note']));
		echo "\n<p>$note</p>";
		echo "\n<hr style='height: 10px;border: 0;box-shadow: 0 10px 10px -10px #ff5600 inset;'/>";
	}
}

?>

<script language="Javascript">
	$(document).ready(function() {
		$('#preventivo').mask("#.##0,00", {reverse: true});
  });
</script>

<?php

piede();

### END OF FILE ###