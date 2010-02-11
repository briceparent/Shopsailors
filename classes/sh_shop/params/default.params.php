<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->default = array(
    'enabled' => true,
    'index' => 'Accueil',
    'monney_format'=> '123 456,78',
    'currency'=> 'Euro',
    'taxes'=> 'TTC',
    'taxRate'=> 19.6,
    'showTaxSymbol'=> false,
);
