<?php

/**
 * 
 * Gain Studios - DDT, azioni headless
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141207 file creato
 * 20190202 conversione normalizza() in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');

if (isset($_GET['idddt']) and is_numeric($_GET['idddt'])) {
	$qt = $db->query("SELECT idddt,anno,numero FROM ddt WHERE idddt='" . $b2->normalizza($_GET['idddt']) . "'");
	if ($qt->num_rows > 0) {
		$rt = $qt->fetch_array();
		// chiudo un DDT inlavorazione	
		if (isset($_GET['chiudi'])) {
			$a = array();
			$a[] = "stato='E'";
			// se non sto chiudendo un ddt riaperto
			if ($rt['numero'] < 1) {
				$q = $db->query("SELECT numero FROM ddt WHERE anno='$rt[anno]' ORDER BY numero DESC LIMIT 1");
				if ($q->num_rows == 0) {
					$numero = 1;
				} else {
					$r = $q->fetch_array();
					$numero = $r['numero'] + 1;
				}
				$a[] = "numero='$numero'";
			}
			$db->query("UPDATE ddt SET " . implode(',', $a) . " WHERE idddt='$rt[idddt]'");
		} elseif (isset($_GET['riapri'])) { // riapro un ddt chiuso
			$a = array();
			$a[] = "stato='L'";
			$db->query("UPDATE ddt SET " . implode(',', $a) . " WHERE idddt='$rt[idddt]'");
		}
	}
}
header('Location: doc_ddt.php');


### END OIF FILE ###