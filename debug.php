<?php

define("SH_DEBUG_ERROR_REPORTING",E_ALL ^ E_DEPRECATED ^ E_NOTICE ^ E_USER_NOTICE ^ E_USER_DEPRECATED);
define("SH_DEBUG_VERIFY_FOLDER","../dev");

include(dirname(__FILE__)."/classes/sh_dev/debug_functions.php");