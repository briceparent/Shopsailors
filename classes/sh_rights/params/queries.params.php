<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    // Verification of the rights
    'is_group_allowed_to_read' => array(
        'query' => '
            SELECT
            count(*) AS cpt
            FROM
            ###rights_access_groups
            WHERE
            `page_id` = "{page_id}"
            AND `group_id` = "{group_id}";',
        'type' =>'get'
    ),
    'is_user_allowed_to_read' => array(
        'query' => '
            SELECT
            count(*) AS cpt
            FROM
            ###rights_access_users
            WHERE
            `page_id` = "{page_id}"
            AND `user_id` = "{user_id}";',
        'type' =>'get'
    ),

    // Listing of the users
    'addOneUser' => array(
        'query' => '
            INSERT INTO
            ###rights_allUsers
            (`user_id`)
            VALUES
            ("{user_id}");',
        'type' =>'insert'
        ),
    'addOneUserDatas' => array(
        'query' => '
            UPDATE ###rights_allUsers SET
            `lastName` = "{lastName}",
            `name` = "{name}"
            WHERE
            `user_id` = "{user_id}";',
        'type' =>'insert'
        ),
    'getAllUsers' => array(
        'query' => '
            SELECT
            `user_id`,
            `name`,
            `lastName`
            FROM
            ###rights_allUsers;',
        'type' =>'get'
    ),
    'getAllUsersForAutocompleter' => array(
        'query' => '
            SELECT
            `user_id`,
            `name`,
            `lastName`
            FROM
            ###rights_allUsers
            WHERE
            CONCAT_WS(
            " ", CAST(`user_id` AS CHAR),`name`,`lastName`) LIKE "%{search}%"
            LIMIT 20;',
        'type' =>'get'
    ),

    // Getting a page id
    'get_page_id' => array(
        'query' => '
            SELECT
            `page_id`
            FROM
            ###rights_pages
            WHERE
            `class` = "{class}"
            AND `method` = "{method}"
            AND `id` = "{id}";',
        'type' =>'get'
    ),
    'get_method_page_id' => array(
        'query' => '
            SELECT
            `page_id`
            FROM
            ###rights_pages
            WHERE
            `class` = "{class}"
            AND `method` = "{method}"
            AND `id` IS NULL;',
        'type' =>'get'
    ),

    //Pages management
    'add_page' => array(
        'query' => '
            INSERT INTO 
            ###rights_pages
            (`class`,`method`,`id`)
            VALUES
            ("{class}","{method}",IF("{id}" = "",NULL,"{id}"));',
        'type' =>'insert'
    ),
    'delete_page' => array(
        'query' => '
            DELETE
            FROM
            ###rights_pages AS rp
            INNER JOIN ###rights_access_users AS rvu ON rp.`page_id` = rvu.`page_id`
            INNER JOIN ###rights_access_groups AS rvg ON rp.`page_id` = rvg.`page_id`
            WHERE
            `page_id` = "{page_id}";',
        'type' =>'set'
    ),
    'is_manager_for_page' => array(
        'query' => '
            SELECT
            `page_id`
            FROM
            ###rights_managers
            WHERE `user_id` = "{user_id}"
            AND `page_id` = "{page_id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'get_manager_pages' => array(
        'query' => '
            SELECT
            rmu.`page_id`,
            rp. `class`,
            rp.`method`,
            rp.`id`
            FROM
            ###rights_managers AS rmu
            LEFT JOIN ###rights_pages AS rp ON rmu.`page_id` = rp.`page_id`
            WHERE rmu.`user_id` = "{user_id}"
            ORDER by rp.`class` ASC, rp.`method` ASC, rp.`id` ASC;',
        'type' =>'get'
    ),
    'get_all_manager_pages' => array(
        'query' => '
            SELECT
            rmu.`page_id`,
            rp. `class`,
            rp.`method`,
            rp.`id`
            FROM
            ###rights_managers AS rmu
            LEFT JOIN ###rights_pages AS rp ON rmu.`page_id` = rp.`page_id`
            ORDER by rp.`class` ASC, rp.`method` ASC, rp.`id` ASC;',
        'type' =>'get'
    ),
    'set_manager_page' => array(
        'query' => '
            INSERT INTO
            ###rights_managers
            (`page_id`,`user_id`)
            VALUES
            ("{page_id}","{user_id}");',
        'type' =>'insert'
    ),
    'is_manager' => array(
        'query' => '
            SELECT
            `page_id`
            FROM
            ###rights_managers
            WHERE `user_id` = "{user_id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'getAllowedGroups' => array(
        'query' => '
            SELECT
            rg.`group_id`,
            rg.`group_name`
            FROM
            ###rights_access_groups AS rag
            LEFT JOIN ###rights_groups AS rg ON rag.`group_id` = rg.`group_id`
            WHERE rag.`page_id` = "{page_id}";',
        'type' =>'get'
    ),
    'get_all_groups' => array(
        'query' => '
            SELECT
            `group_id`,
            `group_name`
            FROM
            ###rights_groups;',
        'type' =>'get'
    ),
    'getAllowedUsers' => array(
        'query' => '
            SELECT
            `user_id`
            FROM
            ###rights_access_users
            WHERE `page_id` = "{page_id}";',
        'type' =>'get'
    ),


    // Setting rights on pages
    'set_access_user' => array(
        'query' => '
            INSERT INTO
            ###rights_access_users
            (`page_id`,`user_id`)
            VALUES
            ("{page_id}","{user_id}");',
        'type' =>'insert'
    ),
    'set_access_group' => array(
        'query' => '
            INSERT INTO
            ###rights_access_groups
            (`page_id`,`group_id`)
            VALUES
            ("{page_id}","{group_id}");',
        'type' =>'insert'
    ),
    // Removing rights on pages
    'unset_access_user' => array(
        'query' => '
            DELETE
            FROM
            ###rights_access_users
            WHERE
            `page_id` = "{page_id}"
            AND `user_id` = "{user_id}";',
        'type' =>'set'
    ),
    'unset_access_group' => array(
        'query' => '
            DELETE
            FROM
            ###rights_access_groups
            WHERE
            `page_id` = "{page_id}"
            AND `group_id` = "{group_id}";',
        'type' =>'set'
    ),
    'unset_access_for_all_groups' => array(
        'query' => '
            DELETE
            FROM
            ###rights_access_groups
            WHERE
            `page_id` = "{page_id}";',
        'type' =>'insert'
    ),
    'unset_access_for_all_users' => array(
        'query' => '
            DELETE
            FROM
            ###rights_access_users
            WHERE
            `page_id` = "{page_id}";',
        'type' =>'insert'
    ),

    // Groups management
    'get_user_groups' => array(
        'query' => '
            SELECT
            `group_id`
            FROM
            ###rights_users
            WHERE
            `user_id` = "{user_id}";',
        'type' =>'get'
    ),
    'add_user_to_group' => array(
        'query' => '
            INSERT INTO
            ###rights_users
            (`user_id`,`group_id`)
            VALUES
            ("{user_id}","{group_id}");',
        'type' =>'insert'
    ),
    'remove_user_from_group' => array(
        'query' => '
            DELETE
            FROM
            ###rights_users
            WHERE
            `group_id` = "{group_id}"
            AND `user_id` = "{user_id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'get_group_name' => array(
        'query' => '
            SELECT
            `group_name`
            FROM
            ###rights_groups
            WHERE
            `group_id` = "{group_id}";',
        'type' =>'get'
    ),
    'set_group_name' => array(
        'query' => '
            UPDATE ###rights_groups SET
            `group_name` = "{group_name}"
            WHERE
            `group_id` = "{group_id}";',
        'type' =>'get'
    ),
    'set_group_owner' => array(
        'query' => '
            UPDATE ###rights_groups SET
            `group_owner_id` = "{group_owner_id}"
            WHERE
            `group_id` = "{group_id}";',
        'type' =>'get'
    ),
    'add_group' => array(
        'query' => '
            INSERT INTO
            ###rights_groups
            (`group_owner_id`,`group_name`)
            VALUES
            ("{group_owner_id}","{group_name}");',
        'type' =>'insert'
    ),
    'delete_group' => array(
        'query' => '
            DELETE
            FROM
            ###rights_groups
            WHERE
            `group_id` = "{group_id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    
    
    
    /* UPDATER */
    'create_table_1' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###rights_access_groups` (
  `page_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  UNIQUE KEY `page_id` (`page_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'insert'
    ),
    'create_table_2' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###rights_access_users` (
  `page_id` int(11) NOT NULL,
  `user_id` bigint(11) NOT NULL,
  UNIQUE KEY `page_id` (`page_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'insert'
    ),
    'create_table_3' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###rights_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_owner_id` bigint(11) NOT NULL,
  `group_name` int(11) NOT NULL COMMENT \'read in the i18n table\',
  UNIQUE KEY `group_id` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',
        'type' => 'insert'
    ),
    'create_table_4' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###rights_allUsers` (
  `user_id` bigint(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'insert'
    ),
    'create_table_5' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###rights_managers` (
  `user_id` bigint(20) NOT NULL,
  `page_id` int(11) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`,`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'insert'
    ),
    'create_table_6' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###rights_pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(64) NOT NULL,
  `method` varchar(64) NOT NULL,
  `id` int(11) DEFAULT NULL,
  `owner_id` bigint(20) NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',
        'type' => 'insert'
    ),
    'create_table_7' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###rights_users` (
  `group_id` int(11) NOT NULL,
  `user_id` bigint(11) NOT NULL,
  UNIQUE KEY `group` (`group_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'insert'
    ),
        
);
