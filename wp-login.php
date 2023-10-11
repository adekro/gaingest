<?php

/**
 * Gain Studios - Cos non da` errore quando qualcuno ci prova
 * Copyright (C) 2022 Luigi Rosa <io@luigirosa.com> - All rights reserved
 *
 * 20220903 file creato
 *
 */

session_start();
session_destroy();

header('Location: https://www.gainstudios.com/');


// ### END OF FILE ###