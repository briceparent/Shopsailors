<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$adminMenu['Contenu'][] = array(
    'link'=>'content/edit/0','text'=>'Nouvel article','icon'=>'picto_add.png'
);
$adminMenu['Contenu'][] = array(
    'link'=>'content/showList/','text'=>'Répertoire des articles','icon'=>'picto_details.png'
);
$adminMenu['Contenu'][] = array(
    'link'=>'content/editShortList/0','text'=>'Listes d\'articles','icon'=>'picto_list.png'
);
