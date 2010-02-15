<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$adminMenu['Contenu'][] = array(
    'link'=>'searcher/manage/',
    'text'=>'Recherche',
    'icon'=>'picto_search.png'
);
