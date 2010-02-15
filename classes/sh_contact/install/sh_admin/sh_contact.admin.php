<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$adminMenu['Contenu'][] = array(
    'link'=>'contact/edit/','text'=>'Page de contact','icon'=>'picto_modify.png'
);
