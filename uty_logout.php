<?php

/**
 * 
 * Gain Studios - Logout
 * Copyright (C) 2015 B2TEAM S.r.l. - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20150628 file creato
 *
 */

session_start();
session_destroy();

header('Location: index.php');


// ### END OF FILE ###