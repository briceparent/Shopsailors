<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'get_key' => array(
        'query' => '
            SELECT
            `key`
            FROM
            ###allowedServers
            WHERE
            `siteCode` = "{siteCode}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'add_key' => array(
        'query' => '
            INSERT INTO 
            ###allowedServers
            (`siteName`,`siteCode`,`key`)
            VALUES
            ("{siteName}","{siteCode}","{key}");',
        'type' =>'insert'
    ),
);
