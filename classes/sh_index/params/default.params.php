<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
header('location: directCallForbidden.php');

$this->default = array(
    'class'=>'contact',
    'action'=>'show',
    'id'=>null,
    'link'=>'contact/show.php',
    'sitemap'=>array('priority'=>1),
);
