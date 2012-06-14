<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->default = array(
  'address' =>
  array (
  ),
  'phone' =>
  array (
    0 =>
    array (
      'name' => '',
      'value' => '',
    ),
  ),
  'mail' =>
  array (
    0 =>
    array (
      'name' => '',
      'value' => '',
    ),
  ),
  'sendMail' => true,
  'showMail' => false,
  'showAddress' => true,
  'showPhone' => false,
  'activated' => true,
);
