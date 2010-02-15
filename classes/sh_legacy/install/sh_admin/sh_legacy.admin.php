<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$adminMenu['Contenu'][] = array(
    'link'=>'legacy/edit/',
    'text'=>'Modifier les mentions légales',
    'icon'=>'picto_modify.png'
);
