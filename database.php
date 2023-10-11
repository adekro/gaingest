<?php

/**
 * Gain Studios - Chiamate alle routine di accesso ai database SQL
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015 B2TEAM
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20141002 prima versione
 * 20150615 setup.ini
 *
 */

if(!defined('SOUNDPARK')) {
	header ('Location: http://www.gainstudios.com/');
	die();
}

$a = parse_ini_file('setup.ini', true);

$db = new mysqli($a['sql']['host'], $a['sql']['user'], $a['sql']['password'], $a['sql']['database']);
if (mysqli_connect_errno()) {
	echo "<html><head><title>Procedura in manutenzione</title><body><p>La procedura &egrave; in manutenzione, torneremo appena possibile.\n<!-- " . mysqli_connect_error() . " -->\n</p></body></html>";
	die();
}

$db->set_charset('UTF8');

unset($a);

### END OF FILE ###