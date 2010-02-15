<?php
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

if(is_dir(SH_CLASS_FOLDER.'sh_diff/')){
    $masterMenu['Section Développeur'][] = array(
        'link'=>'dev/prepareCommit/','text'=>'Commit','icon'=>'picto_tool.png'
    );
    $masterMenu['Section Développeur'][] = array(
        'link'=>'dev/showProject/','text'=>'Projets','icon'=>'picto_tool.png'
    );
}
