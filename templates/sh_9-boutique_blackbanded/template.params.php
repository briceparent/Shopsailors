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
      'totalWidth' => 600,
      'textHeight' => 18,
      'expand' => false,
      'type' => 'btn_rounded',
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
  'sh_shop'=>array(
        'categoriesListing' => array(
            'categoriesNumber' => 10
        ),
        'productsListing' => array(
            'list' => array(
                'productsNumber' => 10
            ),
            'table' => array(
                'productsNumber' => 20
            ),
            'miniature' => array(
                'productsNumber' => 12
            ),
            'default' => 'list'
        ),
        'product' => array(
            'productsNumber' => 4
        )
    ),
  'loadClasses' => array(
        'diaporama'
    ),
  'defaultBuilder' => 'btn_rounded',
  'defaultFont' => 'FreeFontBold.ttf',
  'contact'=>array('renderFile'=>'show_bgNotif')
);
