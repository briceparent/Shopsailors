<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$adminMenu['Contenu'][] = array(
    'link'=>'index/choose/',
    'text'=>'Choisir la page d\'accueil',
    'icon'=>'picto_modify.png'
);
