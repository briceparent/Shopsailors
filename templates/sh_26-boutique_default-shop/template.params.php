<?php
/**
 * Params file 
 * 
 * Params file version : 0.2
 * Licensed under LGPL
 */

if(!defined('SH_MARKER')){
    header('location: directCallForbidden.php');
}

$this->version = '0.2';

$this->values = array (
  'width' => 900,
  'defaultVariation'=>'blue',
  'menusNumber' => 1,
  'menusDescription'=>array(
        0=>'principal'
    ),
  'menuButtons' =>
  array (
    1 => 
    array (
      'maxWidth' => true,
      'totalWidth' => 875,
      'textHeight' => 18,
      'expand' => false,
      'type' => 'onglets_shadow',
      'textColor' => '',
      'font' => 'FreeFontBold.ttf',
    ),
    'font' => NULL,
  ),
  'fonts' => 
    array (
        0 => 'Aarvark_Cafe.ttf',
        1 => 'Hall_Fetica_Decompose.ttf',
        2 => 'Hall_Fetica_Decompose_Italic.ttf',
        3 => 'FreeFontBold.ttf',
        4 => 'VeraBold.ttf',
        5 => 'VeraSeBd.ttf',
    ),
  'defaultBuilder' => 'onglets_shadow',
  'defaultFont' => 'FreeFontBold.ttf',
  'contact'=>array('renderFile'=>'show_data_inline')
);
