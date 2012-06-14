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
            `id`,
            `isNews`,
            `title`,
            `date` AS timestamp,
            DATE_FORMAT(`date`,"%d/%m/%Y") as date,
            `active`
            FROM
            ###content
            WHERE (`active` = "1"){orAny};',
        'type' =>'get'
    ),
    'getNews' => array(
        'query' => 'SELECT
            `id`,
            `title`,
            `summary`,
            `image`,
            `date` AS timestamp,
            DATE_FORMAT(`date`,"%d/%m/%Y") as date,
            `active`
            FROM
            ###content
            WHERE `active` = "1"
            AND `isNews` = TRUE
            ORDER BY `timestamp` DESC
            LIMIT {count};',
        'type' =>'get'
    ),
    'get' => array(
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
            `seo_titleBar`,
            `seo_metaDescription`
            FROM
            ###content
            WHERE `active` = "1"
            AND `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
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
    'getTitle' => array(
        'query' => 'SELECT
            `title`
            FROM
            ###content
            WHERE `active` = "1"
            AND `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'getTitleWithInactive' => array(
        'query' => 'SELECT
            `title`
            FROM
            ###content
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'getContentTitle' => array(
        'query' => 'SELECT
            `title`
            FROM
            ###content
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'getShort' => array(
        'query' => 'SELECT
            `id`,
            `isNews`,
            `title`,
            `summary`,
            `image`,
            DATE_FORMAT(`date`,"%d/%m/%Y") as date,
            showDate,
            showTitle
            FROM
            ###content
            WHERE `active` = "1"
            AND `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'getNewId' => array(
        'query' => 'SELECT
            (MAX(`id`) + 1) as new
            FROM
            ###content
            WHERE 1;',
        'type' =>'get'
    ),
    'create' => array(
        'query' => 'INSERT INTO ###content
            (`active`)
            VALUES
            (1)',
        'type' =>'insert'
    ),
    'save' => array(
        'query' => 'UPDATE ###content
            SET
            `isNews`="{isNews}",
            `image`="{image}",
            `active`="{active}",
            `showDate`="{showDate}",
            `showTitle`="{showTitle}",
            `title`="{title}",
            `content`="{content}",
            `summary`="{summary}",
            `seo_titleBar`="{seo_titleBar}",
            `seo_metaDescription`="{seo_metaDescription}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'exists' => array(
        'query' => '
            SELECT 1 FROM 
            ###content
            WHERE
            exists(
            SELECT * FROM ###content WHERE `id`="{id}"
            );',
        'type' => 'get'
    ),


    'create_table' => array(
        'query' => '
                CREATE TABLE IF NOT EXISTS `###content` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `title` int(11) NOT NULL,
                  `summary` int(11) NOT NULL,
                  `image` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
                  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  `active` tinyint(1) NOT NULL,
                  `content` int(11) NOT NULL,
                  `showDate` tinyint(1) NOT NULL DEFAULT \'0\',
                  `showTitle` tinyint(1) NOT NULL DEFAULT \'1\',
                  `seo_titleBar` int(11) NOT NULL,
                  `seo_metaDescription` int(11) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;',
        'type' =>'insert'
    ),
    'modify_table_1' => array(
        'query' => '
                ALTER TABLE `###content` ADD `isNews` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `id` ;',
        'type' =>'insert'
    ),
);