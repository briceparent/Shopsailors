<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$adminMenu['Newsletters'][] = array(
    'link'=>'newsletters/manage/',
    'text'=>'Gérer la newsletter',
    'icon'=>'picto_tool.png'
);
$newsletterClass = sh_linker::getInstance()->newsletters;
if($newsletterClass->isActivated()){
    $adminMenu['Newsletters'][] = array(
        'link'=>'newsletters/createNewsletter/0',
        'text'=>'Créer une newsletter',
        'icon'=>'picto_add.png'
    );
    $adminMenu['Newsletters'][] = array(
        'link'=>'newsletters/showInvisible/',
        'text'=>'Liste des newsletters',
        'icon'=>'picto_list.png'
    );
    $adminMenu['Newsletters'][] = array(
        'link'=>'newsletters/manageLists/0',
        'text'=>'Gérer les listes de diffusion',
        'icon'=>'picto_list.png'
    );
}