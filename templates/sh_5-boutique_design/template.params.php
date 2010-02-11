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
  'menusDescription'=>array(
        0=>'principal'
    ),
  'globalImage' => true,
  'menuButtons' =>
    array (
        1 =>
        array (
      'maxWidth' => false,
      'totalWidth' => 250,
      'textHeight' => 18,
      'expand' => false,
      'type' => 'btn_transp',
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
            'categoriesNumber' => 12,
            'groupedBy' => 4,
            'fillWith' => '&#160;',
        ),
        'productsListing' => array(
            'grid' => array(
                'productsNumber' => 12,
                'groupedBy' => 4,
                'fillWith' => '&#160;',
            ),
            'default' => 'grid'
        ),
        'product' => array(
            'productsNumber' => 12,
            'groupedBy' => 4,
            'fillWith' => '&#160;',
        )
    ),
  'showCategoriesOnProductPages'=>true,
  'defaultBuilder' => 'btn_transp',
  'defaultFont' => 'FreeFontBold.ttf',
  
);
