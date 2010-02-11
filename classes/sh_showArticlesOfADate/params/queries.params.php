<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'getByDate' => array(
        'query' => 'SELECT
            `id`,
            `title`
            FROM `###content`
            WHERE
            DATE_FORMAT(`date`,"%d/%m/%Y") = "{date}"
            AND
            `active` = "1"
            LIMIT 10;',
        'type' => 'get'),
);
