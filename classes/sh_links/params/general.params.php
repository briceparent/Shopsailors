<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->general = array(
    'build' => array(
        'minimal' => array(
            'session',
            'site',
            'i18n',
            'path',
            'cache',
            'user',
            'menu'),
        'needed' => array(
            'session',
            'path',
            'html',
            'breadcrumbs',
            'cache',
            'menu',
            'admin')
        ));
