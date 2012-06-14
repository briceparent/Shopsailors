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
    'site' => 'websailors',
    'devUrl' => 'http://dev.websailors.fr/',
    'prodUrl' => 'http://www.websailors.fr/',
    'connectionPage' => 'user/tryToConnect.php',
    'createAccount' => 'user/createAccount.php',
    'getUserData' => 'user/getUserData.php',
    'getOneUserId' => 'user/getOneUserId.php',
    'set_connection_status' => 'user/master_set_connection_status.php',
    'get_connection_failures' => 'user/master_get_connection_failures.php',
    'clear_connection_failures' => 'user/master_clear_connection_failures.php',
    'get_last_connection' => 'user/master_get_last_connection.php',
    'passwordForgotten' => 'user/passwordForgotten_master.php',
    'allowedSites' => 
    array (
      0 => 'localhost',
      1 => '127.0.0.1',
      2 => '88.191.80.65',
    ),
  ),
  'version' => '1.09.118.1',
);
