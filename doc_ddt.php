<?php

/**
 * 
 * Gain Studios - DDT, elecno
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141207 file creato
 * 20150615 ddt->fattura
 * 20190202 conversione datainitaliano(),coloreriga,normalizza() in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

intestazione("Documenti di trasporto");

$cercastringa = '';
$pagina = 0;
$righe = 50;
$anno = date('Y');

if (isset($_POST['righe']) and is_numeric($_POST['righe'])) {
	$righe = $_POST['righe'];
	$pagina = 0;
}
if (isset($_POST['cercastringa'])) {
	$cercastringa = $_POST['cercastringa'];
	$pagina = 0;
}

if (isset($_GET['pagina']) and is_numeric($_GET['pagina'])) {
	$pagina = $_GET['pagina'];
}
if (isset($_GET['cercastringa'])) {
	$cercastringa = $_GET['cercastringa'];
}
if (isset($_GET['righe']) and is_numeric($_GET['righe'])) {
	$righe = $_GET['righe'];
}

if (isset($_POST['anno']) and is_numeric($_POST['anno'])) {
	$righe = $_POST['anno'];
	$pagina = 0;
}
if (isset($_POST['anno'])) {
	$cercastringa = $_POST['anno'];
	$pagina = 0;
}


echo "\n<form action='doc_ddt.php' method='post'>";
echo "\n<table border='0' align='center'>";
echo "<tr><th colspan='2' align='center'><b>Filtri</b></th></tr>";
$x = $b2->normalizza($anno, B2_NORM_FORM);
echo "<tr><td align='right'>Anno di riferimento:</td><td><input type='text' value=\"$x\" name='anno' size='4'></td></tr>";
$x = $b2->normalizza($cercastringa, B2_NORM_FORM);
echo "<tr><td align='right'>Ricerca libera:</td><td><input type='text' value=\"$x\" name='cercastringa' size='100'></td></tr>";
$x = $b2->normalizza($righe, B2_NORM_FORM);
echo "<tr><td align='right'>Righe per pagina:</td><td><input type='text' value=\"$x\" name='righe' size='3' maxlength='2'></td></tr>";
echo "<tr><td align='center' colspan='2'><input type='submit' value='Applica queste regole'></td></tr>";
echo "\n</table></form>";

$awhere = array();
if ('' != $anno) {
	$x = $b2->normalizza($anno);
	$awhere[] = "ddt.anno='$x'";
}
if ('' != $cercastringa) {
	$x = "'%" . $b2->normalizza($cercastringa) . "%'";
	$awhere[] = "(ddt.cessionario1 LIKE $x OR ddt.cessionario2 LIKE $x OR ddt.cessionario3 LIKE $x OR ddt.cessionario4 LIKE $x OR ddt.destinazione1 LIKE $x OR ddt.destinazione2 LIKE $x OR ddt.destinazione3 LIKE $x OR ddt.destinazione4 LIKE $x )";
}
$awhere[] = "1=1";
$paginamysql = $pagina * $righe;
$q = $db->query("SELECT ddt.data,ddt.numero,ddt.anno,ddt.stato,clienti.ragsoc,clienti.cognome,clienti.nome,ddt.idddt
                FROM ddt
                LEFT JOIN clienti ON ddt.idcliente=clienti.idcliente
                WHERE " . implode(' AND ', $awhere) . "
                ORDER BY ddt.stato DESC,ddt.anno DESC,ddt.numero DESC
                LIMIT $righe OFFSET $paginamysql");
$parametri = "righe=$righe&cercastringa=" . urlencode($cercastringa);
if ('' != $anno) $parametri .= "&anno=$anno";

$r = $db->query("SELECT COUNT(*) FROM ddt WHERE " . implode(' AND ', $awhere))->fetch_array();
$quantiddt = $r[0];
$quantepagine = ceil($quantiddt / $righe);
// codice di paginazione, ripetuto due volte, in alto et in basso
$paginazione = "\n<table border='0'><tr>";
// indietro
if ($pagina > 0) {
	$xp = $pagina - 1;
	$parametripg = $parametri . "&pagina=$xp";
	$paginazione .= "\n<td align='left' width='20%'>&nbsp;&nbsp;&nbsp;<a href='doc_ddt.php?$parametripg'>&lt;&lt;</a></td>";
} else {
	$paginazione .= "\n<td align='left' width='20%'>&nbsp;&nbsp;</td>";
}
// numero pagine
if ($quantepagine > 1) {
	$xp = $pagina + 1;
	$paginazione .= "\n<td align='center' width='60%'>Pagina $xp di $quantepagine</td>";
} else {
	$paginazione .= "\n<td align='center' width='60%'>&nbsp;</td>";
}
// avanti
if ($pagina < ($quantepagine -1)) {
	$xp = $pagina + 1;
	$parametripg = $parametri . "&pagina=$xp";
	$paginazione .= "\n<td align='right' width='20%'><a href='doc_ddt.php?$parametripg'>&gt;&gt;</a>&nbsp;&nbsp;&nbsp;</td>";
} else {
	$paginazione .= "\n<td align='right' width='20%'>&nbsp;&nbsp;</td>";
}
$paginazione .= "\n<tr>\n</table>";

$parametri .= "&pagina=$pagina";

echo "\n<table border='0' align='center'>";
echo "<tr><td align='center' colspan='4'>$paginazione</td></tr>";
echo "<tr><td align='center' colspan='4'><b><a href='doc_ddtedit.php?idddt=0'>Nuovo DDT</a></b></td></tr>";
echo "\n<tr>
        <th align='center'><b>Numero</b></th>
        <th align='center'><b>Data</b></th>
        <th align='center'><b>Cliente</b></th>
        <th align='center'><b>Azione</b></th>
        </tr>";
while ($r = $q->fetch_array()) {
	$bg = $b2->bgcolor();
	echo "\n<tr $bg>";
	$temp = 'L' == $r['stato'] ? 'In lavorazione' : "$r[numero]/$r[anno]";
	echo "<td $bg align='right'>&nbsp;${temp}&nbsp;</td>";
	echo "<td $bg align='right'>&nbsp;" . $b2->dt2ita($r['data']) . "&nbsp;</td>";
	echo "<td $bg align='left'>&nbsp;" . trim("$r[ragsoc] $r[cognome] $r[nome]") . "&nbsp;</td>";
	$aazioni = array();
	if ('L' == $r['stato']) $aazioni[] = "<a href='doc_ddtedit.php?idddt=$r[idddt]'>Modifica testata</a>";
	if ('L' == $r['stato']) $aazioni[] = "<a href='doc_ddtrighe.php?idddt=$r[idddt]'>Righe</a>";
	if ('L' == $r['stato']) $aazioni[] = "<a href='doc_ddtazioni.php?idddt=$r[idddt]&chiudi=1'>Chiudi</a>";
	if ('E' == $r['stato']) $aazioni[] = "<a href='doc_ddtazioni.php?idddt=$r[idddt]&riapri=1'>Riapri</a>";
	if ('E' == $r['stato']) $aazioni[] = "<a href='doc_ddtstampa.php?idddt=$r[idddt]'>Stampa</a>";
	// creo fattura in base al DDT?
	if ('E' == $r['stato']) {
		$qq = $db->query("SELECT idddt FROM fattura WHERE idddt='$r[idddt]'");
		if ($qq->num_rows == 0) $aazioni[] = "<a href='doc_ddt2fattura.php?idddt=$r[idddt]'>Emetti fattura</a>";
	}
	echo "<td $bg align='left'>&nbsp;" . implode('&nbsp;&bull;&nbsp;', $aazioni) . "&nbsp;</td>";
	echo "\n</tr>";
}
echo "<tr><td align='center' colspan='4'><b><a href='doc_ddtedit.php?idddt=0'>Nuovo DDT</a></b></td></tr>";
echo "<tr><td align='center' colspan='4'>$paginazione</td></tr>";
echo "\n</table>";

echo "\n<p align='center'><b>$quantiddt</b> DDT registrati.</p>";

piede();

### END OF FILE ###