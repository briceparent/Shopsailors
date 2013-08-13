<?php
/**
 * Params file for the \"".$this->object->__tostring()."\" extension
 *
 * Params file version : 0.2
 * Licensed under LGPL
 */

if(!defined('SH_MARKER')){
    header('location: directCallForbidden.php');
}

$this->general = array (
  'master' => 
  array (
    'site' => 'websailors',// probably deprecated
    'devUrl' => 'http://dev.websailors.fr/',// probably deprecated
    'prodUrl' => 'http://www.websailors.fr/',// probably deprecated
    'connectionPage' => 'user/tryToConnect.php',// Do not touch this
    'createAccount' => 'user/createAccount.php',// Do not touch this
    'getUserData' => 'user/getUserData.php',// Do not touch this
    'getOneUserId' => 'user/getOneUserId.php',// Do not touch this
    'set_connection_status' => 'user/master_set_connection_status.php',// Do not touch this
    'get_connection_failures' => 'user/master_get_connection_failures.php',// Do not touch this
    'clear_connection_failures' => 'user/master_clear_connection_failures.php',// Do not touch this
    'get_last_connection' => 'user/master_get_last_connection.php',// Do not touch this
    'passwordForgotten' => 'user/passwordForgotten_master.php',// Do not touch this
    'allowedSites' => 
    array (
        // Inserted here the list of host that may call the master servers on this installation
      //0 => 'localhost',
      //1 => '127.0.0.1',
      2 => $_SERVER['SERVER_ADDR'],
    ),
  ),
  'version' => '1.09.118.1',
);
