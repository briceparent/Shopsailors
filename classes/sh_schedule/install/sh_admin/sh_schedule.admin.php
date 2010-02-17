<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$adminMenu['Contenu'][] = array(
    'link'=>'schedule/edit/0','text'=>'Ajouter une date dans l\'agenda','icon'=>'picto_add.png'
);
$adminMenu['Contenu'][] = array(
    'link'=>'schedule/manage/','text'=>'Paramètres de l\'agenda','icon'=>'picto_list.png'
);
