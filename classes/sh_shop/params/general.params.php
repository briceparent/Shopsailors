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

// We have to be sure that the sh_banks has been loaded before the rest of this
$payment = $this->linker->payment;
$this->general = array (
  'version' => '1.09.118.1',
  'monney_formats_listing'=>array(
      '123 456,78'=>array(
          'decimals'=>2,
          'decSeparator'=>',',
          'thousSeparator'=>' '
      ),
      '123456,78'=>array(
          'decimals'=>2,
          'decSeparator'=>',',
          'thousSeparator'=>''
      ),
      '123 456.78'=>array(
          'decimals'=>2,
          'decSeparator'=>'.',
          'thousSeparator'=>' '
      ),
      '123456.78'=>array(
          'decimals'=>2,
          'decSeparator'=>'.',
          'thousSeparator'=>''
      ),
      '123,456.78'=>array(
          'decimals'=>2,
          'decSeparator'=>'.',
          'thousSeparator'=>','
      ),
      '123456.78'=>array(
          'decimals'=>2,
          'decSeparator'=>'.',
          'thousSeparator'=>''
      ),
  ),
  'currencies' => array(
      sh_banks::CUR_EUR => array(
          'symbol' => '€',
          'before' => '',
          'after' => '€',
          'name' => 'Euro',
          'toDecimals' => 100
      ),
      sh_banks::CUR_USD=>array(
          'symbol' => '$',
          'before' => '$',
          'after' => '',
          'name' => 'Dollar',
          'toDecimals' => 100
      ),
      sh_banks::CUR_GBP=>array(
          'symbol' => '£',
          'before' => '£',
          'after' => '',
          'name' => 'Pound',
          'toDecimals' => 100
      ),
  ),
  'billColors'=>array(
      0=>array(
          204,
          102,
          102
      ),
      1=>array(
          255,
          255,
          153
      ),
      2=>array(
          204,
          255,
          153
      ),
      3=>array(
          102,
          204,
          102
      ),
      4=>array(
          153,
          204,
          204
      ),
      5=>array(
          102,
          153,
          204
      ),
      6=>array(
          204,
          153,
          255
      ),
      7=>array(
          153,
          102,
          153
      ),
      8=>array(
          204,
          102,
          153
      ),
      9=>array(
          204,
          204,
          204
      ),
  ),
  'productsListing' => array(
        'list' => array(
            'productsNumber' => 16
        ),
        'table' => array(
            'productsNumber' => 10
        ),
        'miniature' => array(
            'productsNumber' => 12
        ),
        'default' => 'list'
    ),
    'categoriesListing' => array(
        'categoriesNumber' => 20
    ),
    'product' => array(
        'productsNumber' => 4
    )
);
