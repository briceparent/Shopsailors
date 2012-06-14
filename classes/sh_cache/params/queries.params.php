<?php

/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if( !defined( 'SH_MARKER' ) )
    header( 'location: directCallForbidden.php' );

$this->queries = array(
    'part_get_split' => array(
        'query' => 'SELECT
            `content`,
            `date`
            FROM
            ###cache_parts
            WHERE `class` = "{class}"
            AND `part` LIKE "{part}_split_%"
            AND `lang` = "{lang}";',
        'type' => 'get'
    ),
    'part_get' => array(
        'query' => 'SELECT
            `content`,
            `date`
            FROM
            ###cache_parts
            WHERE `class` = "{class}"
            AND `part` = "{part}"
            AND `lang` = "{lang}"
            LIMIT 1;',
        'type' => 'get'
    ),
    'part_create' => array(
        'query' => 'INSERT INTO ###cache_parts
            (`class`,`part`,`lang`,`content`)
            VALUES
            ("{class}","{part}","{lang}","{content}")',
        'type' =>'insert'
    ),
    'parts_delete' => array(
        'query' => 'DELETE
            FROM
            ###cache_parts
            WHERE `class` = "{class}"
            AND `part` REGEXP "^{part}"
            AND `lang` IN ({langs});',
        'type' =>'set'
    ),
    'all_parts_delete' => array(
        'query' => 'DELETE
            FROM
            ###cache_parts
            WHERE `class` = "{class}"
            AND `lang` IN ({langs});',
        'type' =>'set'
    ),
    'parts_delete_all_langs' => array(
        'query' => 'DELETE
            FROM
            ###cache_parts
            WHERE `class` = "{class}"
            AND `part` REGEXP "^{part}";',
        'type' =>'set'
    ),
    'all_parts_delete_all_langs' => array(
        'query' => 'DELETE
            FROM
            ###cache_parts
            WHERE `class` = "{class}";',
        'type' =>'set'
    ),
    'create_table_cache_parts' => array(
        'query' => '
                CREATE TABLE `###cache_parts` (
`class` VARCHAR( 32 ) NOT NULL ,
`part` VARCHAR( 32 ) NOT NULL ,
`lang` VARCHAR( 8 ) NOT NULL ,
`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`content` TEXT NOT NULL
) ENGINE = MYISAM ;',
        'type' =>'insert'
    ),
    'modify_table_cache_parts_add_unique' => array(
        'query' => 'ALTER TABLE `###cache_parts` ADD UNIQUE `unic` ( `class` , `part` , `lang` )  ;',
        'type' =>'insert'
    ),
);
