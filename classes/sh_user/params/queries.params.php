<?php
/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

$this->queries = array(
    'cookies_setDate' => array(
        'query' => '
            UPDATE
            ###user_cookies
            SET
            `date` = "{date}"
            WHERE 
            `user` = "{user}"
            AND `cookie` = "{cookie}";',
        'type' =>'insert'
    ),
    'cookies_get' => array(
        'query' => '
            SELECT
            `user`
            FROM
            ###user_cookies
            WHERE `cookie` = "{cookie}";',
        'type' =>'get'
    ),
    'cookies_create' => array(
        'query' => '
            INSERT INTO 
            ###user_cookies
            (`user`,`cookie`,`expire`)
            VALUES
            ("{user}","{cookie}","{expire}");',
        'type' =>'insert'
    ),
    'cookies_delete' => array(
        'query' => '
            DELETE FROM
            ###user_cookies
            WHERE
            `user` = "{user}"
            AND `cookie` = "{cookie}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'cookies_deleteAllForUser' => array(
        'query' => '
            DELETE FROM
            ###user_cookies
            WHERE
            `user` = "{user}";',
        'type' =>'set'
    ),
    'cookies_deleteOlderThan' => array(
        'query' => '
            DELETE FROM
            ###user_cookies
            WHERE
            `date` < "{date}";',
        'type' =>'set'
    ),
    
    /* MASTER SERVER QUERIES */
    'getOneUserId' => array(
        'query' => '
            SELECT
            `id`
            FROM
            ###users
            WHERE
            `{field}` = "{value}"
            ;',
        'type' =>'get'
    ),
    'checkUser' => array(
        'query' => '
            SELECT
            `id`,
            `mail`
            FROM
            ###users
            WHERE
            `login` = "{login}"
            AND
            `password` = "{password}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'connectOneUser_single_step' => array(
        'query' => '
            SELECT
            `id`
            FROM
            ###users
            WHERE
            `login` = "{userName}"
            AND
            `password` = "{password}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'connectOneUser' => array(
        'query' => '
            SELECT
            `id`
            FROM
            ###users
            WHERE
            `login` = "{userName}"
            AND
            `password` = "{password}"
            AND
            `verification` = "{verification}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'connectOneUserWithChallenge' => array(
        'query' => '
            SELECT
            `id`
            FROM
            ###users
            WHERE
            `login` = "{userName}"
            AND
            MD5("{challenge}-".`password`) = "{challengePassword}"
            AND
            `verification` = "{verification}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'connectOneUserWithNewPassword' => array(
        'query' => '
            SELECT
            `id`
            FROM
            ###users
            WHERE
            `login` = "{userName}"
            AND
            `verification` = "{verification}"
            AND
            `temporaryPassword` = "{temporaryPassword}"
            AND
            TIMESTAMPDIFF(HOUR,`temporaryPasswordTimestamp`,NOW()) <= 48
            LIMIT 1;',
        'type' =>'get'
    ),
    'getOneUserVerification' => array(
        'query' => '
            SELECT
            `verification`,
            `active`,
            `id`
            FROM
            ###users
            WHERE
            `login` = "{userName}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'getAllowedUsers' => array(
        'query' => '
            SELECT
            `login`,
            `name`,
            `lastName`
            FROM
            ###users
            WHERE
            1;',
        'type' =>'get'
    ),
    'verify' => array(
        'query' => '
            SELECT
            `id`,
            `active`
            FROM
            ###users
            WHERE
            `login` = "{name}"
            AND 
            `password` = "{password}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'changePassword' => array(
        'query' => '
            UPDATE ###users SET
            `password` = "{newPassword}",
            `temporaryPasswordTimestamp` = ""
            WHERE
            `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'changeVerification' => array(
        'query' => '
            UPDATE ###users SET
            `verification` = "{verification}"
            WHERE
            `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'createAccount_setIncrement' => array(
        'query' => '
            ALTER TABLE `###users`
            AUTO_INCREMENT = {increment};',
        'type' =>'set'
    ),
    'addTemporaryPassword' => array(
        'query' => '
            UPDATE ###users SET
            `temporaryPassword` = "{temporaryPassword}",
            `temporaryPasswordTimestamp` = NOW()
            WHERE
            `id` = "{id}"
            LIMIT 1;',
        'type' =>'set'
    ),
    'getUserData' => array(
        'query' => '
            SELECT
            *
            FROM
            ###users
            WHERE
            `id` = "{id}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'activateAccount' => array(
        'query' => 'UPDATE
            ###users
            SET
            `active`="1"
            WHERE
            `mail` = "{mail}"
             LIMIT 1;',
        'type' =>'set'
    ),
    'createAccount' => array(
        'query' => '
            INSERT INTO 
            ###users
            (`login`,`active`,`name`,`lastName`,`mail`,`phone`,`password`,`address`,`zip`,`city`,`verification`)
            VALUES
            ("{login}","0","{name}","{lastName}","{mail}","{phone}","{password}","{address}","{zip}","{city}","{verification}");',
        'type' =>'insert'
    ),
    'updateAccount' => array(
        'query' => '
            UPDATE ###users SET
            /*`verification` = "{verification}",*/
            `name` = "{name}",
            `lastName` = "{lastName}",
            `mail` = "{mail}",
            `phone` = "{phone}",
            `address` = "{address}",
            `zip` = "{zip}",
            `city` = "{city}"
            WHERE
            `login` = "{login}"
            LIMIT 1;',
        'type' =>'insert'
    ),
    // Connections status logger
    'update_connection_successfull' => array(
        'query' => '
            UPDATE ###connections_successes
            SET
            `date` = CURRENT_TIMESTAMP(),
            `site` = "{site}"
            WHERE
            `user` = "{user}";',
        'type' =>'insert'
    ),
    'add_connection_successfull' => array(
        'query' => '
            INSERT INTO
            ###connections_successes
            (`user`,`site`,`date`)
            VALUES
            ("{user}","{site}",CURRENT_TIMESTAMP());',
        'type' =>'insert'
    ),
    'get_connection_successfull' => array(
        'query' => '
            SELECT
            `date`,`site`
            FROM
            ###connections_successes
            WHERE
            `user` = "{user}"
            LIMIT 1;',
        'type' =>'get'
    ),
    'add_connection_failure' => array(
        'query' => '
            INSERT INTO
            ###connections_failures
            (`user`,`site`,`date`,`ip`)
            VALUES
            ("{user}","{site}",CURRENT_TIMESTAMP(),"{ip}");',
        'type' =>'insert'
    ),
    'get_connection_failures_number' => array(
        'query' => '
            SELECT
            COUNT(*) as count
            FROM
            ###connections_failures
            WHERE
            `user` = "{user}" AND
            `shown` = "0";',
        'type' =>'get'
    ),
    'get_connection_failures' => array(
        'query' => '
            SELECT
            `date`,`site`,`ip`
            FROM
            ###connections_failures
            WHERE
            `user` = "{user}" AND
            `shown` = "0"
            ORDER BY `date` DESC
            LIMIT 10;',
        'type' =>'get'
    ),
    'clear_connections_failures' => array(
        'query' => '
            UPDATE ###connections_failures
            SET
            `shown` = 1
            WHERE
            `user` = "{user}";',
        'type' =>'set'
    ),
    'clear_older_connections_failures' => array(
        'query' => '
            DELETE
            FROM
            ###connections_failures
            WHERE
            DATEDIFF(CURDATE() , `date`) > 30;',
        'type' =>'set'
    ),

    // Creation of the tables
    'create_table_users' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT \'YYMMDD[5 digits counter]\',
  `active` tinyint(1) NOT NULL DEFAULT \'0\',
  `login` varchar(50) COLLATE utf8_bin NOT NULL,
  `name` varchar(50) COLLATE utf8_bin NOT NULL,
  `lastName` varchar(50) COLLATE utf8_bin NOT NULL,
  `mail` varchar(100) COLLATE utf8_bin NOT NULL,
  `password` varchar(32) COLLATE utf8_bin NOT NULL,
  `phone` varchar(20) COLLATE utf8_bin NOT NULL,
  `address` varchar(300) COLLATE utf8_bin NOT NULL,
  `zip` int(11) NOT NULL,
  `city` varchar(64) COLLATE utf8_bin NOT NULL,
  `temporaryPassword` varchar(32) COLLATE utf8_bin NOT NULL,
  `temporaryPasswordTimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verification` varchar(150) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mail` (`mail`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT=\'used by sh_user\' AUTO_INCREMENT=10010100001;',
        'type' =>'set'
    ),
    'create_table_connections_failures' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###connections_failures` (
  `user` bigint(20) NOT NULL,
  `site` varchar(50) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `shown` tinyint(1) NOT NULL,
  `ip` varchar(23) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=\'used by sh_user\';',
        'type' =>'set'
    ),
    'create_table_connections_successes' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###connections_successes` (
  `user` bigint(20) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `site` varchar(50) NOT NULL,
  UNIQUE KEY `user` (`user`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT=\'used by sh_user\';',
        'type' =>'set'
    ),
    'remove_case_in_logins' => array(
        'query' => 'ALTER TABLE `###users` CHANGE `login` `login` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;',
        'type' =>'set'
    ),
    'create_cookies_table' => array(
        'query' => '
CREATE TABLE IF NOT EXISTS `###user_cookies` (
  `user` bigint(20) NOT NULL,
  `cookie` varchar(64) DEFAULT NULL,
  `expire` date NOT NULL,
  UNIQUE KEY `cookie` (`cookie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' =>'set'
    ),

);
