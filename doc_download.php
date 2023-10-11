<?php

/**
 * 
 * Gain Studios - Download documento
 * Copyright (C) 2015 B2TEAM S.r.l. - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20150623 file creato
 *
 */

define('SOUNDPARK', true);
require('global.php');

// si sa mai...
set_time_limit(600);


if (isset($_GET['iddocumento'])) {
	$iddocumento = $b2->normalizza($_GET['iddocumento']);
	$q = $db->query("SELECT nomefile,nomefilesystem,mime FROM documento WHERE iddocumento='$iddocumento'");
	if ($q->num_rows > 0) {
		$filedir = readsetup('DOCDIR');
		$r = $q->fetch_array();
		$dimensione = filesize($filedir . '/' . $r['nomefilesystem']);
		// output the file
		header("Content-type: $r[mime]");
		header("Content-length: $dimensione");
		header("Pragma: public");
		header("Content-Description: File transfer");
		header("Content-Disposition: attachment; filename=\"" . $b2->normalizza($r['nomefile'], B2_NORM_FORM) . "\"");
		header("Expires: 0");
		header("Cache-Control: must-revalidate");
		// questi tre header servono per Internet Explorer
		header("Cache-Control: no-store, max-age=0, no-cache, must-revalidate"); // HTTP/1.1
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Cache-Control: private");
		readfile($filedir . '/' . $r['nomefilesystem']);
	} else {
		header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
		echo "\n<h1>404 - Il documento non esiste</h1>";
	}
} else {
	header('location: home.php');
}


### END OF FILE ###