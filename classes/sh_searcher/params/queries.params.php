<?php
/**
 * Params file for the images extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'search' => array(
        'query' => 'SELECT
            `class`,
            `method`,
            `id`,
            {weight} as "weight",
            MATCH (`level_{level}`) AGAINST (\'{search}\') as "match"
            FROM ###searcher
            WHERE `lang`="{lang}"
            AND
            MATCH (`level_{level}`) AGAINST (\'{search}\' IN BOOLEAN MODE)
            ;',
         'type' => 'get'
    ),
    'searchAllWords' => array(
        'query' => 'SELECT
            `class`,
            `method`,
            `id`,
            MATCH (`level_1`,`level_2`,`level_3`) AGAINST (\'{search}\' IN BOOLEAN MODE) as "keywords"
            FROM ###searcher
            WHERE `lang`="{lang}"
            AND
            MATCH (`level_1`,`level_2`,`level_3`) AGAINST (\'{search}\' IN BOOLEAN MODE)
            ;',
         'type' => 'get'
    ),
    'getText' => array(
        'query' => 'SELECT
            `level_1`,
            `level_2`,
            `level_3`
            FROM ###searcher
            WHERE `lang`="{lang}"
            AND `class` = "{class}"
            AND `method` = "{method}"
            AND `id` = "{id}"
            LIMIT 1;',
         'type' => 'get'
    ),
    'addElement' => array(
        'query' => 'INSERT INTO ###searcher
            (`lang`,`class`,`method`,`id`,`level_1`,`level_2`,`level_3`)
            VALUES
            ("{lang}","{class}","{method}","{id}","{level_1}","{level_2}","{level_3}");',
        'type' => 'get'
    ),
    'removeElementAllLangs'=> array(
        'query' => 'DELETE FROM ###searcher
            WHERE
            `class` = "{class}"
            AND
            `method` = "{method}"
            AND
            `id` = "{id}";',
        'type' => 'set'
    ),
    'removeElement'=> array(
        'query' => 'DELETE FROM ###searcher
            WHERE 
            `class` = "{class}"
            AND
            `method` = "{method}"
            AND
            `id` = "{id}"
            AND
            `lang` = "{lang}"
            LIMIT 1;',
        'type' => 'set'
    ),
    'removeAllElementsForOneClass' => array(
        'query' => 'DELETE FROM ###searcher
            WHERE
            `class` = "{class}";',
        'type' => 'set'
    ),

    'create_table' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###searcher` (
  `lang` varchar(5) NOT NULL,
  `class` varchar(25) NOT NULL,
  `method` varchar(30) NOT NULL,
  `id` int(11) NOT NULL,
  `level_1` varchar(250) NOT NULL,
  `level_2` varchar(250) NOT NULL,
  `level_3` text NOT NULL,
  UNIQUE KEY `lang` (`lang`,`class`,`method`,`id`),
  FULLTEXT KEY `level_1_fulltext` (`level_1`),
  FULLTEXT KEY `level_2_fulltext` (`level_2`),
  FULLTEXT KEY `level_3_fulltext` (`level_3`),
  FULLTEXT KEY `allLevels_fulltext` (`level_1`,`level_2`,`level_3`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
',
        'type' => 'set'
    ),
);
