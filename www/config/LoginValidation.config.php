<?php

define('LOGGED', (isset($_SESSION[SPRE.'status']) && ($_SESSION[SPRE.'status'] == \Sys\Session::STT_ACTIVE)));