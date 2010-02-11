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
  'menusNumber' => 1,
  'variations' => 
  array (
    'blue' => 
    array (
      'color' => 'blue',
      'colorname' => 'Blue',
      'linkscolor' => '#123456',
      'textcolor' => '#123456',
      'titlescolor' => '#234567',
      'backgroundcolor' => '#1E90FF',
      'importantcolor' => 'red',
    ),
  ),
  'menuButtons' => 
  array (
      1 => array(
    'maxWidth' => true,
    'totalWidth' => 875,
    'textHeight' => 15,
    'expand' => true,
    'type' => 'websailors_button',
      'font' => 'FreeFontBold.ttf',
    ),
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
  'defaultBuilder' => 'websailors_button',
  'defaultFont' => 'FreeFontBold.ttf',
);
