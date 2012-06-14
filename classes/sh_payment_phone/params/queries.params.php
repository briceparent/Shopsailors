<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    
    'getWithInactive' => array(
        'query' => 'SELECT
            `id`,
            `isNews`,
            `title`,
            `summary`,
            `image`,
            DATE_FORMAT(`date`,"%d/%m/%Y") as date,
            `content`,
            `showDate`,
            `showTitle`,
            `active`,
            `seo_titleBar`,
            `seo_metaDescription`
            FROM
            ###content
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'delete' => array(
        'query' => 'DELETE
            FROM
            ###content
            WHERE
            `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'create' => array(
        'query' => 'INSERT INTO ###content
            (`active`)
            VALUES
            (1)',
        'type' =>'insert'
    ),
);
