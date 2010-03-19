<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$myLinks = sh_linker::getInstance();

$menusNumber = $myLinks->template->menusNumber;
$menusDescription = $myLinks->template->menusDescription;

for($cpt = 0; $cpt<$menusNumber; $cpt++){
    $adminMenu['Contenu'][] = array(
        'link'=>'menu/edit/'.($cpt + 1),
        'text'=>'Modifier le menu '.$menusDescription[$cpt],
        'icon'=>'picto_modify.png'
    );
}
