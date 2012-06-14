<?php

/**
 * Params file for the helper extension
 * Licensed under LGPL
 */
if( !defined( 'SH_MARKER' ) )
    header( 'location: directCallForbidden.php' );

$this->queries = array(
    /* GROUPS */
    'group_create' => array(
        'query' => 'INSERT INTO ###forum_groups
            (`name`)
            VALUES
            ("{name}")',
        'type' => 'insert'
    ),
    'group_create_withId' => array(
        'query' => 'INSERT INTO ###forum_groups
            (`id`,`name`)
            VALUES
            ({id},"{name}")',
        'type' => 'insert'
    ),
    'group_save' => array(
        'query' => 'UPDATE ###forum_groups
            SET
            `name`="{name}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'set'
    ),
    'group_getName' => array(
        'query' => 'SELECT
            `name`
            FROM
            ###forum_groups
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'get'
    ),
    'groups_get_all' => array(
        'query' => 'SELECT
            `id`,
            `name`
            FROM
            ###forum_groups
            ORDER BY `id` ASC;',
        'type' => 'get'
    ),
    'group_get_users' => array(
        'query' => 'SELECT
            fu.`id`,
            fu.`alias`,
            fu.`gender`,
            fu.`image`
            FROM
            ###forum_users_groups AS fug
            LEFT JOIN ###forum_users AS fu ON fu.`id` = fug.`user_id`
            WHERE fug.`group_id` = "{group_id}"
            LIMIT {count};',
        'type' => 'get'
    ),
    'group_insert_user' => array(
        'query' => 'INSERT INTO ###forum_users_groups
            (`user_id`,`group_id`)
            VALUES
            ("{user_id}","{group_id}");',
        'type' => 'insert'
    ),
    'group_remove_user' => array(
        'query' => 'DELETE FROM ###forum_users_groups
            WHERE `user_id`="{user_id}" AND `group_id`="{group_id}";',
        'type' => 'insert'
    ),
    'user_count_topics' => array(
        'query' => 'SELECT
            COUNT(*) AS count,
            MAX(`date`) AS last
            FROM
            ###forum_topics
            WHERE `opener_id` = "{user_id}"
            AND (
                (
                    `moderation_id` IS NULL
                ) OR (
                    LEFT( `moderation_id` , 7 ) != "delete:"
                )
            )
        ;',
        'type' => 'get'
    ),
    'user_count_posts' => array(
        'query' => 'SELECT
            COUNT(*) AS count,
            MAX(`date`) AS last
            FROM
            ###forum_posts
            WHERE `user_id` = "{user_id}"
            AND (
                (
                    `moderation_id` IS NULL
                ) OR (
                    LEFT( `moderation_id` , 7 ) != "delete:"
                )
            )
        ;',
        'type' => 'get'
    ),
    'user_get_groups' => array(
        'query' => 'SELECT
            `group_id`
            FROM
            ###forum_users_groups
            WHERE `user_id` = "{user_id}";',
        'type' => 'get'
    ),
    'user_get_alias' => array(
        'query' => 'SELECT
            `alias`
            FROM
            ###forum_users
            WHERE `id` = "{id}";',
        'type' => 'get'
    ),
    'user_get_notifications_my_topics' => array(
        'query' => 'SELECT
            `notifications_my_topics`
            FROM
            ###forum_users
            WHERE `id` = "{id}";',
        'type' => 'get'
    ),
    'user_get_notifications_other_topics' => array(
        'query' => 'SELECT
            `notifications_other_topics`
            FROM
            ###forum_users
            WHERE `id` = "{id}";',
        'type' => 'get'
    ),

    /* SECTIONS */
    'section_create_root_category' => array(
        'query' => 'INSERT INTO ###forum_sections
            (`id`,`name`,`hasChildren`,`parent`,`image`,`text`)
            VALUES
            (0,"{name}",TRUE,NULL,"{image}","{text}");',
        'type' => 'insert'
    ),
    'section_create' => array(
        'query' => 'INSERT INTO ###forum_sections
            (`name`,`hasChildren`,`parent`,`image`,`text`)
            VALUES
            ("{name}","{hasChildren}","{parent}","{image}","{text}");',
        'type' => 'insert'
    ),
    'section_update_withImage' => array(
        'query' => 'UPDATE ###forum_sections
            SET
            `name`="{name}",
            `image`="{image}",
            `text`="{text}"
            WHERE 
            `id` = {id};',
        'type' => 'insert'
    ),
    'section_update_withoutImage' => array(
        'query' => 'UPDATE ###forum_sections
            SET
            `name`="{name}",
            `text`="{text}"
            WHERE 
            `id` = {id};',
        'type' => 'insert'
    ),
    'section_get' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `hasChildren`,
            `parent`,
            `image`,
            `text`
            FROM
            ###forum_sections
            WHERE `id` = "{id}";',
        'type' => 'get'
    ),
    'section_get_topics' => array(
        'query' => 'SELECT
            ft.`id` ,
            ft.`date`,
            ft.`opener_id` ,
            ft.`section_id` ,
            ft.`title` ,
            ft.`content` ,
            ft.`closed`,
            ft.`last_post_date`,
            fu.`alias`,
            fu.`gender`,
            fu.`image`
            FROM
            ###forum_topics AS ft
            LEFT JOIN ###forum_users AS fu ON fu.`id` = ft.`opener_id`
            WHERE ft.`section_id` = "{id}"
            AND (
                (
                    ft.`moderation_id` IS NULL
                ) OR (
                    LEFT( ft.`moderation_id` , 7 ) != "delete:"
                )
            )
            ORDER BY `last_post_date` DESC,`date` DESC;',
        'type' => 'get'
    ),

    'sections_getAllForSearcher' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `hasChildren`,
            `image`,
            `text`
            FROM
            ###forum_sections;',
        'type' => 'get'
    ),
    'sections_getContainingTopics' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `image`,
            `text`
            FROM
            ###forum_sections
            WHERE `hasChildren` = "0";',
        'type' => 'get'
    ),
    'sections_get' => array(
        'query' => 'SELECT
            `id`,
            `name`,
            `hasChildren`,
            `image`,
            `text`
            FROM
            ###forum_sections
            WHERE `parent` = "{parent}"
            ;',
        'type' => 'get'
    ),
    'sections_count_subSections' => array(
        'query' => 'SELECT
            count(*) AS count
            FROM
            ###forum_sections
            WHERE `parent` = "{parent}";',
        'type' => 'get'
    ),
    'sections_count_topics' => array(
        'query' => 'SELECT
            count(*) AS count
            FROM
            ###forum_topics
            WHERE `section_id` = "{parent}"
            AND (
                (
                    `moderation_id` IS NULL
                ) OR (
                    LEFT( `moderation_id` , 7 ) != "delete:"
                )
                );',
        'type' => 'get'
    ),
    
    /* TOPICS */
    'topics_getAllForSearcher' => array(
        'query' => 'SELECT
            fs.`id`, 
            fs.`title`, 
            fs.`title`, 
            fs.`content`,
            fu.`alias`
            FROM
            ###forum_topics AS fs
            LEFT JOIN ###forum_users AS fu ON fu.`id` = fs.`opener_id`
            WHERE (
                (
                    fs.`moderation_id` IS NULL
                ) OR (
                    LEFT( fs.`moderation_id` , 7 ) != "delete:"
                )
            );',
        'type' => 'get'
    ),
    'topics_getLasts' => array(
        'query' => 'SELECT
            fs.`id`, 
            fs.`title`, 
            fs.`opener_id`, 
            fs.`section_id`, 
            fs.`title`, 
            fs.`content`, 
            fs.`closed`,
            fs.`date`,
            fs.`last_post_date`,
            fu.`alias`,
            fu.`image`,
            fu.`gender`
            FROM
            ###forum_topics AS fs
            LEFT JOIN ###forum_users AS fu ON fu.`id` = fs.`opener_id`
            WHERE (
                (
                    fs.`moderation_id` IS NULL
                ) OR (
                    LEFT( fs.`moderation_id` , 7 ) != "delete:"
                )
            )
            ORDER BY fs.`date` DESC
            LIMIT {count};',
        'type' => 'get'
    ),
    'topics_getLastsForUser' => array(
        'query' => 'SELECT
            fs.`id`, 
            fs.`title`, 
            fs.`opener_id`, 
            fs.`section_id`, 
            fs.`title`, 
            fs.`content`, 
            fs.`closed`,
            fs.`date`,
            fs.`last_post_date`,
            fu.`alias`,
            fu.`image`,
            fu.`gender`
            FROM
            ###forum_topics AS fs
            LEFT JOIN ###forum_users AS fu ON fu.`id` = fs.`opener_id`
            WHERE (
                (
                    fs.`moderation_id` IS NULL
                ) OR (
                    LEFT( fs.`moderation_id` , 7 ) != "delete:"
                )
            )
            AND fs.`opener_id` = "{user}"
            ORDER BY fs.`date` DESC
            LIMIT {count};',
        'type' => 'get'
    ),
    'topic_count_posts' => array(
        'query' => 'SELECT
            count(*) AS count
            FROM
            ###forum_posts
            WHERE `topic_id` = "{topic_id}"
            AND (
                (
                    `moderation_id` IS NULL
                ) OR (
                    LEFT( `moderation_id` , 7 ) != "delete:"
                )
            )
            ORDER BY `post_number` ASC;',
        'type' => 'get'
    ),
    'topic_getSection' => array(
        'query' => 'SELECT
            `section_id`
            FROM
            ###forum_topics
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'get'
    ),
    'topic_set_last_post_date' => array(
        'query' => 'UPDATE
            ###forum_topics
            SET
            `last_post_date` = NOW()
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'get'
    ),
    'topic_set_last_post_date_force' => array(
        'query' => 'UPDATE
            ###forum_topics
            SET
            `last_post_date` = "{last_post_date}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'get'
    ),
    'topic_force_creation_date' => array(
        'query' => 'UPDATE
            ###forum_topics
            SET
            `date` = "{date}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'get'
    ),
    'topic_changeSection' => array(
        'query' => 'UPDATE
            ###forum_topics
            SET
            `section_id` = "{section_id}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'get'
    ),
    'topic_getTitle' => array(
        'query' => 'SELECT
            `title`
            FROM
            ###forum_topics
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'get'
    ),
    'topic_create' => array(
        'query' => 'INSERT INTO ###forum_topics 
            (`opener_id`, `date`,`section_id`, `title`, `content`, `closed`,`last_post_date`)
            VALUES
            ("{opener_id}", NOW(), "{section_id}", "{title}", "{content}", FALSE, NOW());',
        'type' => 'insert'
    ),
    'topic_get' => array(
        'query' => 'SELECT
            fs.`id`, 
            fs.`title`, 
            fs.`opener_id`, 
            fs.`section_id`, 
            fs.`title`, 
            fs.`content`, 
            fs.`closed`,
            fs.`moderation_id`,
            fs.`date`,
            fu.`alias`,
            fu.`signature`,
            fu.`image`,
            fu.`gender`
            FROM
            ###forum_topics AS fs
            LEFT JOIN ###forum_users AS fu ON fu.`id` = fs.`opener_id`
            WHERE fs.`id` = "{id}"
            LIMIT 1;',
        'type' => 'get'
    ),
    'topic_moderation_delete'=> array(
        'query' => 'UPDATE ###forum_topics
            SET `moderation_id` = "delete:{moderator}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'update'
    ),
    'topic_moderate'=> array(
        'query' => 'UPDATE ###forum_topics
            SET 
            `title` = "{title}",
            `content` = "{content}",
            `moderation_id` = "{moderation_id}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'update'
    ),
    
    /* POSTS */
    'post_getMaxPostNumber' => array(
        'query' => 'SELECT
            MAX(`post_number`) AS max
            FROM
            ###forum_posts
            WHERE `topic_id` = "{topic_id}";',
        'type' => 'get'
    ),
    'posts_get_last' => array(
        'query' => 'SELECT
            fp.`topic_id`, 
            fp.`post_number`, 
            fp.`parent`, 
            fp.`hasChildren`, 
            fp.`user_id`, 
            fp.`date`, 
            fp.`title`, 
            fp.`text`
            FROM
            ###forum_posts AS fp
            LEFT JOIN ###forum_users AS fu ON fu.`id` = fp.`user_id` 
            WHERE fp.`topic_id` = "{id}"
            ORDER BY `post_number` DESC
            LIMIT 1;',
        'type' => 'get'
    ),
    'posts_get_first' => array(
        'query' => 'SELECT
            fp.`topic_id`, 
            fp.`post_number`, 
            fp.`parent`, 
            fp.`hasChildren`, 
            fp.`user_id`, 
            fp.`date`, 
            fp.`title`, 
            fp.`text`
            FROM
            ###forum_posts AS fp
            LEFT JOIN ###forum_users AS fu ON fu.`id` = fp.`user_id` 
            WHERE fp.`topic_id` = "{id}"
            AND fp.`post_number` = "1";',
        'type' => 'get'
    ),
    'post_get' => array(
        'query' => 'SELECT
            fp.`topic_id`, 
            fp.`post_number`, 
            fp.`parent`, 
            fp.`hasChildren`, 
            fp.`user_id`, 
            fp.`date`, 
            fp.`title`, 
            fp.`text`, 
            fp.`important`,
            fu.`alias`
            FROM
            ###forum_posts AS fp
            LEFT JOIN ###forum_users AS fu ON fu.`id` = fp.`user_id` 
            WHERE fp.`topic_id` = "{id}"
            AND fp.`post_number` = "{post_number}";',
        'type' => 'get'
    ),
    'posts_get' => array(
        'query' => 'SELECT
            fp.`topic_id`, 
            fp.`post_number`, 
            fp.`parent`, 
            fp.`hasChildren`, 
            fp.`user_id`, 
            fp.`date`, 
            fp.`title`, 
            fp.`text`, 
            fp.`important`,
            fu.`alias`,
            fu.`image`,
            fu.`signature`,
            fu.`gender`
            FROM
            ###forum_posts AS fp
            LEFT JOIN ###forum_users AS fu ON fu.`id` = fp.`user_id`
            WHERE fp.`topic_id` = "{id}"
            AND fp.`parent` = "{parent}"
            AND (
                (
                    fp.`moderation_id` IS NULL
                ) OR (
                    LEFT( fp.`moderation_id` , 7 ) != "delete:"
                )
            )
            ;',
        'type' => 'get'
    ),
    'post_create' => array(
        'query' => 'INSERT INTO ###forum_posts 
            (`topic_id`,`post_number`, `parent`, `hasChildren`, `user_id`, `date`, `title`, `text`, `important`)
            VALUES
            ({topic_id},{post_number}, {parent}, FALSE, {user_id}, NOW(),"{title}","{text}", FALSE);',
        'type' => 'get'
    ),
    'post_setHasChildren' => array(
        'query' => 'UPDATE ###forum_posts
            SET
            `hasChildren`=TRUE
            WHERE `topic_id` = "{topic_id}"
            AND `post_number` = "{post_number}"
            LIMIT 1;',
        'type' => 'set'
    ),
    'post_moderation_delete'=> array(
        'query' => 'UPDATE ###forum_posts
            SET `moderation_id` = "delete:{moderator}"
            WHERE `topic_id` = "{topic_id}"
            AND `post_number` = "{post_number}"
            LIMIT 1;',
        'type' => 'update'
    ),
    'post_moderation_delete_answers'=> array(
        'query' => 'UPDATE ###forum_posts
            SET `moderation_id` = "delete:{moderator}"
            WHERE `topic_id` = "{topic_id}"
            AND `parent` = "{parent}";',
        'type' => 'update'
    ),
    'post_moderate'=> array(
        'query' => 'UPDATE ###forum_posts
            SET 
            `title` = "{title}",
            `text` = "{text}",
            `moderation_id` = "{moderation_id}"
            WHERE `topic_id` = "{id}"
            AND `post_number`="{post_number}"
            LIMIT 1;',
        'type' => 'update'
    ),
    

    
    'users_get_by_part' => array(
        'query' => 'SELECT
            `id`,
            `alias`
            FROM
            ###forum_users
            WHERE `alias` LIKE "%{text}%";',
        'type' => 'get'
    ),
    'user_get_by_alias' => array(
        'query' => 'SELECT
            `id`,
            `alias`
            FROM
            ###forum_users
            WHERE `alias` = "{alias}";',
        'type' => 'get'
    ),
    'user_getAlias' => array(
        'query' => 'SELECT
            `id`,
            `alias`
            FROM
            ###forum_users
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'get'
    ),
    
    /* RIGHTS ON SECTIONS */
    'rights_delete_for_section' => array(
        'query' => 'DELETE FROM ###forum_rights
            WHERE `section_id`="{section_id}";',
        'type' => 'insert'
    ),
    'right_set' => array(
        'query' => 'INSERT INTO ###forum_rights
            (`section_id`,`group_id`,`right`)
            VALUES
            ("{section_id}", "{group_id}", "{right}")',
        'type' => 'insert'
    ),
    'right_get' => array(
        'query' => 'SELECT
            COUNT(*) as count
            FROM ###forum_rights
            WHERE `section_id` = "{section_id}" AND `group_id` = "{group_id}" AND `right` = "{right}";',
        'type' => 'get'
    ),
    'right_get_from_groups' => array(
        'query' => 'SELECT
            COUNT(*) as count
            FROM ###forum_rights
            WHERE `section_id` = "{section_id}" AND `group_id` IN ({groups}) AND `right` = "{right}";',
        'type' => 'get'
    ),
    
    /* PROFILES */
    'profile_get' => array(
        'query' => 'SELECT
            `id`,
            `alias`,
            `image`,
            `gender`,
            `profile_text`,
            `signature`,
            `subscription_date`,
            `notifications_my_topics`,
            `notifications_other_topics`
            FROM
            ###forum_users
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'get'
    ),
    'profile_create' => array(
        'query' => 'INSERT INTO ###forum_users
            (
            `id`,`alias`,`image`,`gender`,`signature`,
            `subscription_date`,`notifications_my_topics`,`notifications_other_topics`
            )
            VALUES
            (
            "{id}", "{alias}", "{image}", "{gender}", "{signature}",
            "{subscription_date}", "{notifications_my_topics}", "{notifications_other_topics}"
            )',
        'type' => 'insert'
    ),
    'profile_save' => array(
        'query' => 'UPDATE ###forum_users
            SET
            `alias`="{alias}",
            `gender`="{gender}",
            `signature`="{signature}",
            `image`="{image}",
            `profile_text` = "{profile_text}",
            `notifications_my_topics`="{notifications_my_topics}",
            `notifications_other_topics`="{notifications_other_topics}"
            WHERE `id` = "{id}"
            LIMIT 1;',
        'type' => 'set'
    ),
    
    /* MESSAGES */
    'message_add' => array(
        'query' => 'INSERT INTO ###forum_messages
            (
            `from`,`to`,`date`,`message`,`quickResponse`
            )
            VALUES
            (
            "{from}", "{to}", NOW(), "{message}", "{quickResponse}"
            )',
        'type' => 'insert'
    ),
    'message_getFromQuickResponse' => array(
        'query' => 'SELECT
            `id`,
            `from`,
            `to`,
            `date`,
            `message`
            FROM
            ###forum_messages
            WHERE `quickResponse` = "{quickResponse}"
            LIMIT 1;',
        'type' => 'get'
    ),
    
    /* NOTIFICATIONS */
    'notif_get_users' => array(
        'query' => 'SELECT
            `user_id`
            FROM
            ###forum_notifications
            WHERE `topic_id` = "{topic_id}"
            AND `user_id` != "{user_id}";',
        'type' => 'get'
    ),
    'notif_is_set' => array(
        'query' => 'SELECT
            `topic_id`
            FROM
            ###forum_notifications
            WHERE `topic_id` = "{topic_id}"
            AND `user_id` = "{user_id}"
            LIMIT 1;',
        'type' => 'get'
    ),
    'notif_set' => array(
        'query' => 'INSERT INTO ###forum_notifications
            (
            `topic_id`,`user_id`
            )
            VALUES
            (
            "{topic_id}", "{user_id}"
            )',
        'type' => 'insert'
    ),
    'notif_unset' => array(
        'query' => 'DELETE FROM ###forum_notifications
            WHERE `topic_id` = "{topic_id}"
            AND `user_id` = "{user_id}"
            LIMIT 1;',
        'type' => 'delete'
    ),
    
    
    /* UPDATER */
    'create_table_1' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###forum_posts` (
  `subject_id` int(10) unsigned NOT NULL,
  `post_number` int(10) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(256) NOT NULL,
  `text` text NOT NULL,
  `important` tinyint(1) NOT NULL DEFAULT \'0\',
  UNIQUE KEY `subject_id` (`subject_id`,`post_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'insert'
    ),
    'create_table_2' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###forum_sections` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) NOT NULL,
  `parent` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;',
        'type' => 'insert'
    ),
    'create_table_3' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###forum_subjects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `opener_id` bigint(20) unsigned NOT NULL,
  `section_id` int(10) unsigned NOT NULL,
  `title` varchar(256) NOT NULL,
  `content` text NOT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',
        'type' => 'insert'
    ),
    'create_table_4' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###forum_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(512) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;',
        'type' => 'insert'
    ),
    'create_table_5' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###forum_types_rights` (
  `type_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `write` tinyint(1) NOT NULL DEFAULT \'0\',
  `read` tinyint(1) NOT NULL DEFAULT \'0\',
  `modify` tinyint(1) NOT NULL DEFAULT \'0\',
  UNIQUE KEY `type_id` (`type_id`,`section_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'insert'
    ),
    'create_table_6' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###forum_users` (
  `id` bigint(20) NOT NULL,
  `name` int(11) NOT NULL,
  `lastName` int(11) NOT NULL,
  `image` int(11) NOT NULL,
  `phone` int(11) NOT NULL,
  `address` int(11) NOT NULL,
  `gender` int(11) NOT NULL,
  `signature` text NOT NULL,
  `subscription_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'insert'
    ),
    'create_table_7' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###forum_users_types` (
  `user_id` bigint(20) NOT NULL,
  `type_id` int(11) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`,`type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
        'type' => 'insert'
    ),
    'update_table_users_1' => array(
        'query' => 'ALTER TABLE `###forum_users`
ADD `notifications_my_subjects` BOOLEAN NOT NULL DEFAULT \'0\',
ADD `notifications_other_subjects` BOOLEAN NOT NULL DEFAULT \'0\'',
        'type' => 'insert'
    ),
    'update_table_users_2' => array(
        'query' => 'ALTER TABLE `###forum_users` ADD `alias` VARCHAR( 32 ) NOT NULL AFTER `id` ;',
        'type' => 'insert'
    ),
    'update_table_users_3' => array(
        'query' => 'ALTER TABLE `###forum_users` CHANGE `name` `name` VARCHAR( 32 ) NOT NULL ,
CHANGE `lastName` `lastName` VARCHAR( 32 ) NOT NULL ,
CHANGE `image` `image` VARCHAR( 10 ) NOT NULL ,
CHANGE `phone` `phone` VARCHAR( 11 ) NOT NULL ,
CHANGE `address` `address` VARCHAR( 1024 ) NOT NULL ,
CHANGE `gender` `gender` BOOLEAN NULL DEFAULT NULL ;',
        'type' => 'insert'
    ),
    'update_table_users_4' => array(
        'query' => 'ALTER TABLE `###forum_users`
  DROP `name`,
  DROP `lastName`,
  DROP `phone`,
  DROP `address`;',
        'type' => 'insert'
    ),
    'rename_table_groups' => array(
        'query' => 'RENAME TABLE `###forum_types` TO `###forum_groups` ;',
        'type' => 'insert'
    ),
    'rename_table_groups_rights' => array(
        'query' => 'RENAME TABLE `###forum_types_rights` TO `###forum_groups_rights` ;',
        'type' => 'insert'
    ),
    'rename_table_users_groups' => array(
        'query' => 'RENAME TABLE `###forum_users_types` TO `###forum_users_groups` ;',
        'type' => 'insert'
    ),
    'update_table_users_groups' => array(
        'query' => 'ALTER TABLE `###forum_users_groups` CHANGE `type_id` `group_id` INT( 11 ) NOT NULL  ;',
        'type' => 'insert'
    ),
    'update_table_groups_rights' => array(
        'query' => 'ALTER TABLE `###forum_groups_rights` CHANGE `type_id` `group_id` INT( 11 ) NOT NULL ;',
        'type' => 'insert'
    ),
    'update_table_sections' => array(
        'query' => 'ALTER TABLE `###forum_sections` ADD `hasChildren` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `parent` ;',
        'type' => 'insert'
    ),
    'update_table_posts' => array(
        'query' => 'ALTER TABLE `###forum_posts` ADD `parent` INT UNSIGNED NOT NULL DEFAULT \'0\' AFTER `post_number` ;',
        'type' => 'insert'
    ),
    'update_table_posts_2' => array(
        'query' => 'ALTER TABLE `###forum_posts` ADD `hasChildren` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `parent`  ;',
        'type' => 'insert'
    ),
    'update_table_groups_rights_2' => array(
        'query' => 'ALTER TABLE `###forum_groups_rights`
  DROP `write`,
  DROP `modify`;',
        'type' => 'insert'
    ),
    'update_table_groups_rights_3' => array(
        'query' => 'ALTER TABLE `###forum_groups_rights` ADD `insert` BOOLEAN NOT NULL DEFAULT \'0\',
ADD `post` BOOLEAN NOT NULL DEFAULT \'0\',
ADD `moderate` BOOLEAN NOT NULL DEFAULT \'0\'',
        'type' => 'insert'
    ),
    'rename_table_subjects_to_topics' => array(
        'query' => 'RENAME TABLE `###forum_subjects` TO `###forum_topics`',
        'type' => 'insert'
    ),
    'rename_subjects_to_topics_1' => array(
        'query' => 'ALTER TABLE `###forum_posts` CHANGE `subject_id` `topic_id` INT( 10 ) UNSIGNED NOT NULL ',
        'type' => 'insert'
    ),
    'rename_subjects_to_topics_2' => array(
        'query' => 'ALTER TABLE `###forum_users` CHANGE `notifications_my_subjects` `notifications_my_topics` TINYINT( 1 ) NOT NULL DEFAULT \'0\',
CHANGE `notifications_other_subjects` `notifications_other_topics` TINYINT( 1 ) NOT NULL DEFAULT \'0\'',
        'type' => 'insert'
    ),
    'update_groups_1' => array(
        'query' => 'ALTER TABLE `###forum_groups` CHANGE `name` `name` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ',
        'type' => 'insert'
    ),
    'update_groups_2' => array(
        'query' => 'ALTER TABLE `###forum_groups` ADD UNIQUE (`name`) ',
        'type' => 'insert'
    ),
    'update_profile_1' => array(
        'query' => 'ALTER TABLE `###forum_users` ADD `profile_text` VARCHAR( 1024 ) NOT NULL AFTER `gender` ',
        'type' => 'insert'
    ),
    'update_users_5' => array(
        'query' => 'ALTER TABLE `###forum_users` CHANGE `gender` `gender` ENUM( \'unset\', \'female\', \'male\' ) NOT NULL DEFAULT \'unset\' ',
        'type' => 'insert'
    ),
    'delete_old_rights_table' => array(
        'query' => 'DROP TABLE `###forum_groups_rights` ',
        'type' => 'insert'
    ),
    'create_rights_table' => array(
        'query' => 'CREATE TABLE `###forum_rights` (
`part_type` ENUM( \'categories\', \'topics\' ) NOT NULL ,
`page_id` INT NOT NULL ,
`group` INT NOT NULL ,
`read` BOOLEAN NOT NULL DEFAULT \'0\',
`bannish` BOOLEAN NOT NULL DEFAULT \'0\',
`create_categories` BOOLEAN NOT NULL DEFAULT \'0\',
`create_topics` BOOLEAN NOT NULL DEFAULT \'0\',
`post` BOOLEAN NOT NULL DEFAULT \'0\',
`moderate` BOOLEAN NOT NULL DEFAULT \'0\'
) ENGINE = MYISAM ; ',
        'type' => 'insert'
    ),
    'create_default_groups_1' => array(
        'query' => 'UPDATE `###forum_groups` SET `id` = `id`+4; ; ',
        'type' => 'insert'
    ),
    'create_default_groups_2' => array(
        'query' => 'UPDATE `###forum_users_groups` SET `group_id` = `group_id`+4 ; ',
        'type' => 'insert'
    ),
    'delete_old_rights_table_2' => array(
        'query' => 'DROP TABLE `###forum_rights` ',
        'type' => 'insert'
    ),
    'create_section_rights_table' => array(
        'query' => 'CREATE TABLE `###forum_rights` (
`section_id` INT NOT NULL ,
`group_id` INT NOT NULL ,
`right` VARCHAR(20) NOT NULL,
UNIQUE KEY `type_id` (`section_id`,`group_id`,`right`)
) ENGINE = MYISAM ; ',
        'type' => 'insert'
    ),
    'add_moderation_id_to_topics' => array(
        'query' => 'ALTER TABLE `###forum_topics` ADD `moderation_id` VARCHAR( 16 ) NULL ; ',
        'type' => 'insert'
    ),
    'add_image_and_text_to_sections' => array(
        'query' => 'ALTER TABLE `###forum_sections`  ADD `image` VARCHAR(8) NOT NULL AFTER `name`,  ADD `text` TEXT NOT NULL AFTER `image` ; ',
        'type' => 'insert'
    ),
    'add_moderation_to_posts' => array(
        'query' => 'ALTER TABLE `###forum_posts` ADD `moderation_id` VARCHAR( 16 ) NULL DEFAULT NULL  ; ',
        'type' => 'insert'
    ),
    'create_table_messages' => array(
        'query' => 'CREATE TABLE `###forum_messages` (
`id` BIGINT NOT NULL AUTO_INCREMENT ,
`from` BIGINT NOT NULL ,
`to` BIGINT NOT NULL ,
`date` TIMESTAMP NOT NULL ,
`title` VARCHAR( 512 ) NOT NULL ,
`message` TEXT NOT NULL ,
`read` BOOLEAN NOT NULL DEFAULT \'0\',
PRIMARY KEY ( `id` )
) ENGINE = MYISAM ; ',
        'type' => 'insert'
    ),
    'update_table_messages_1' => array(
        'query' => 'ALTER TABLE `###forum_messages` CHANGE `title` `quickResponse` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ',
        'type' => 'insert'
    ),
    'create_table_notifications' => array(
        'query' => 'CREATE TABLE IF NOT EXISTS `###forum_notifications` (
  `topic_id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  UNIQUE KEY `unique` (`topic_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; ',
        'type' => 'insert'
    ),
    'topics_add_last_post_date' => array(
        'query' => 'ALTER TABLE `forum_topics` ADD `###last_post_date` TIMESTAMP NOT NULL ',
        'type' => 'insert'
    ),
    'topics_remove_autoupdate_date' => array(
        'query' => 'ALTER TABLE `###forum_topics` CHANGE `date` `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ',
        'type' => 'insert'
    ),
    'profile_change_text_to_real_text_field' => array(
        'query' => 'ALTER TABLE `###forum_users` CHANGE `profile_text` `profile_text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;',
        'type' => 'insert'
    ),
        
);
