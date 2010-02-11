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
  'frequencies' => 
  array (
    0 => 'always',
    1 => 'hourly',
    2 => 'daily',
    3 => 'weekly',
    4 => 'monthly',
    5 => 'yearly',
    6 => 'never',
  ),
  'defaultFrequency' => 'weekly',
  'defaultPriority' => '0.5',
  'version' => '1.09.119.1',
);
