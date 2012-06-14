<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'coupon_add' => array(
        'query' => 'INSERT INTO ###coupons
            (`active`,`text`,`from`,`to`,`minimum`,`max_uses`,`reduction`,`reduction_type`)
            VALUES
            ("{active}","{text}","{from}","{to}","{minimum}","{max_uses}","{reduction}","{reduction_type}");',
        'type' =>'insert'
    ),
    'coupon_update' => array(
        'query' => 'UPDATE ###coupons SET
            `active` = "{active}",
            `text` = "{text}",
            `from` = "{from}",
            `to` = "{to}",
            `minimum` = "{minimum}",
            `max_uses` = "{max_uses}",
            `reduction` = "{reduction}",
            `reduction_type` = "{reduction_type}"
            WHERE
            `id`="{id}";',
        'type' =>'update'
    ),
    'coupon_addUsage' => array(
        'query' => 'UPDATE ###coupons SET
            `uses` = `uses` + 1
            WHERE
            `id`="{id}";',
        'type' =>'update'
    ),
    'coupon_get' => array(
        'query' => 'SELECT 
            `id`,`active`,`text`,`from`,`to`,`minimum`,`max_uses`,`uses`,`reduction`,`reduction_type`
            FROM ###coupons
            WHERE `id`="{id}" 
            AND `active` = 1
            AND `from` <= "{now}"
            AND `to` >= "{now}"
            AND `minimum` <= "{minimum}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'coupon_get_with_inactive' => array(
        'query' => 'SELECT 
            `id`,`active`,`text`,`from`,`to`,`minimum`,`max_uses`,`uses`,`reduction`,`reduction_type`
            FROM ###coupons
            WHERE `id`="{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    
    
    'create_table_coupons' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT \'0\',
  `text` varchar(32) NOT NULL,
  `from` date NOT NULL,
  `to` date NOT NULL,
  `minimum` decimal(10,0) NOT NULL,
  `max_uses` int(11) NOT NULL DEFAULT \'0\',
  `uses` int(11) NOT NULL DEFAULT \'0\',
  `reduction` decimal(10,0) NOT NULL,
  `reduction_type` enum(\'fixed\',\'percents\') NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `active` (`active`,`from`,`to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;;',
        'type' =>'set'
    ),
);