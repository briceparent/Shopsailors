<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'backup_show_tables' => array(
        'query' => 'SHOW TABLES;',
        'type' =>'get'
    ),
    'backup_select_everything' => array(
        'query' => 'SELECT * FROM {table};',
        'type' =>'get'
    ),
    'backup_show_create_table' => array(
        'query' => 'SHOW CREATE TABLE {table};',
        'type' =>'get'
    ),
);
