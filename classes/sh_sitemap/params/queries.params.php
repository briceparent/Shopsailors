<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'getList' => array(
        'query' => 'SELECT
            `page`, `uri`, `reverse`
            FROM
            ###uri;',
        'type' =>'get'),
    'getFromReverse' => array(
        'query' => 'SELECT
            `{id}`, `{title}`,`{date}`
            FROM
            ###{table}
            WHERE
            `{active}`={true};',
        'type' =>'get'),
    );
