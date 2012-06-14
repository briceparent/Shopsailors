<?php
/**
 * Params file for the renderer extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
    header('location: directCallForbidden.php');

$this->queries = array(
    'render_tags_create_table' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###render_tags` (
                      `tag` varchar(32) NOT NULL,
                      `class` varchar(32) NOT NULL,
                      `method` varchar(32) NOT NULL,
                      UNIQUE KEY `tag` (`tag`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' =>'set'
    ),
    'add_render_tag' => array(
        'query' => 'INSERT INTO ###render_tags
            (`tag`,`class`,`method`)
            VALUES
            ("{tag}","{class}","{method}");',
        'type' =>'insert'
    ),
    'get_render_tags' => array(
        'query' => 'SELECT
            `tag`, `class`,`method`
            FROM ###render_tags;',
        'type' =>'get'
    ),
);
