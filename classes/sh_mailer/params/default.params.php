<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->default = array(
    'mailers'=>array(
        'internal'=>array(
            'name'=>'phpMailer',
            'file'=>'cm_phpMailer.cls.php'
        ),
    ),

    'cm_graphicMail'=>array(
        'from' =>'robot@websailors.fr',
        'fromName'=>'Websailors_Robot',
        'replyTo' =>'robot@websailors.fr',
        'replyToName'=>'Websailors_Robot',
    ),

    'cm_phpMailer'=>array(
        'SMTPAuth'=>true,
        'SMTPSecure'=>'ssl',
        'host'=>'smtp.gmail.com',
        'port'=>465,
        'username'=>'websailors.postmaster@gmail.com',
        'password'=>'Aq7vB33q3s6pG5f9',
        'from' =>'robot@websailors.fr',
        'fromName'=>'Websailors_Robot',
        'replyTo' =>'robot@websailors.fr',
        'replyToName'=>'Websailors_Robot',
    ),
);
