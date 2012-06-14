<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'isIndex' => array(
        'query' => 'SELECT
            `page`
            FROM ###uri
            WHERE `uri`="/"
            AND `page`="{page}"
            LIMIT 1;',
         'type' => 'get')
        );
