<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$adminMenu['Assistance'][] = array(
    'target'=>'_blank',
    'link'=>'http://wiki.shopsailors.org',
    'text'=>'Aide en ligne Shopsailors Wiki',
    'icon'=>'picto_contactus.png'
);
$adminMenu['Assistance'][] = array(
    'link'=>'mailto:briceparent@free.fr',
    'text'=>'Contacter le service technique',
    'icon'=>'picto_contactus.png'
);

$adminMenu['Contenu']['top'] = array(
    'link'=>'site/changeParams/','text'=>'Gérer le site','icon'=>'picto_tool.png'
);

