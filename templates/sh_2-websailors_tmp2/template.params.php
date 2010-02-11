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
  'menuButtons' =>
    array (
        1 =>
        array (
      'maxWidth' => true,
      'totalWidth' => 820,
      'textHeight' => 18,
      'expand' => false,
      'type' => 'websailors_btn2',
      'textColor' => '',
      'font' => 'FreeFontBold.ttf',
        ),
    ),
  'fonts' =>
    array (
        'Aarvark_Cafe.ttf',
        'Abduction2002.ttf',
        'Alpine_Regular.ttf',
        'FreeFontBold.ttf',
        'FreeFontBoldOblique.ttf',
        'FreeFontSerifBold.ttf',
        'FreeFontSerifBoldOblique.ttf',
        'Hall_Fetica_Decompose.ttf',
        'Hall_Fetica_Decompose_Italic.ttf',
        'LatiniaBlack.ttf',
        'MiddleSaxonyText.ttf',
        'VeraBold.ttf',
        'VeraBoldOblique.ttf',
        'VeraSeBd.ttf',
        'designer.ttf',
    ),
  'sh_shop'=>array(
        'categoriesListing' => array(
            'categoriesNumber' => 10
        ),
        'productsListing' => array(
            'list' => array(
                'productsNumber' => 12
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
  'defaultBuilder' => 'websailors_btn2',
  'defaultFont' => 'FreeFontBold.ttf',
  'contact'=>array('renderFile'=>'show_bgNotif')
);
