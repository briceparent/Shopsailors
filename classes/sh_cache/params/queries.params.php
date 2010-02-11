<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'getAllAuthors' => array(
        'query' => 'SELECT
            id,
            name
            FROM
            ###book_authors
            WHERE `id` != "0"
            ORDER BY `insertDate` DESC
            LIMIT {limit};',
        'type' => 'get'),
);
