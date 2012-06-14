<?php

/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * Class that display and manages html page contents, like company presentation for example.
 */
class sh_forum extends sh_core {

    const CLASS_VERSION = '1.1.12.03.09';

    public $users_images_folder = '';
    public $users_images_path = '';
    protected $images_salt = '';
    protected $minimal = array(
        'get_users_name' => true, 'addUserToGroup' => true, 'addGroup' => true, 'removeUserFromGroup' => true
    );
    protected static $usesRightsManagement = true;
    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db', 'sh_renderer', 'sh_site', 'sh_template', 'sh_variation',
        'sh_admin', 'sh_browser', 'sh_css', 'sh_html', 'sh_events', 'sh_cron', 'sh_helper'
    );
    public $callWithoutId = array(
        'manage', 'usersList', 'get_users_name', 'addGroup'
    );
    public $callWithId = array(
        'edit', 'show', 'show_user', 'getUsersList', 'manageGroup', 'topic', 'alert', 'addUserToGroup',
        'removeUserFromGroup', 'moderate', 'message', 'topic_move', 'profile', 'lastTopics'
    );
    public $rights_methods = array(
        'topic', 'show', 'show_user'
    );
    protected $post_active_deepness = 0;
    protected $posts_list = array( );

    const RIGHTS_ADMINISTRATE = 'administrate';
    const RIGHTS_SEE = 'read';
    const RIGHTS_BANNISH = 'bannish';
    const RIGHTS_CREATE_CATEGORIES = 'create_categories';
    const RIGHTS_CREATE_TOPIC = 'create_topics';
    const RIGHTS_POST = 'post';
    const RIGHTS_MODERATE = 'moderate';

    const PARTS_CATEGORIES = 'categories';
    const PARTS_TOPICS = 'topics';
    const PARTS_POSTS = 'posts';

    protected $alias = false;
    protected $userId = null;
    protected $userGroups = array( );
    public $image_profile_female = '/images/shared/default/default_profile_female.png';
    public $image_profile_male = '/images/shared/default/default_profile_male.png';

    const GROUPS_ADMINISTRATORS = 1;
    const GROUPS_MODERATORS = 2;
    const GROUPS_CONNECTED = 3;
    const GROUPS_DISCONNECTED = 4;

    protected $dangerousWordsFound = array( );
    protected $forbiddenWordsFound = array( );
    protected $forbidden_rights = array(
        self::GROUPS_CONNECTED => array(
            self::RIGHTS_ADMINISTRATE => self::RIGHTS_ADMINISTRATE,
            self::RIGHTS_BANNISH => self::RIGHTS_BANNISH,
            self::RIGHTS_MODERATE => self::RIGHTS_MODERATE
        ),
        self::GROUPS_DISCONNECTED => array(
            self::RIGHTS_ADMINISTRATE => self::RIGHTS_ADMINISTRATE,
            self::RIGHTS_BANNISH => self::RIGHTS_BANNISH,
            self::RIGHTS_MODERATE => self::RIGHTS_MODERATE
        )
    );
    protected $minimal_rights = array(
        self::GROUPS_ADMINISTRATORS => array(
            self::RIGHTS_ADMINISTRATE => self::RIGHTS_ADMINISTRATE,
            self::RIGHTS_BANNISH => self::RIGHTS_BANNISH,
            self::RIGHTS_CREATE_CATEGORIES => self::RIGHTS_CREATE_CATEGORIES,
            self::RIGHTS_CREATE_TOPIC => self::RIGHTS_CREATE_TOPIC,
            self::RIGHTS_POST => self::RIGHTS_POST,
            self::RIGHTS_SEE => self::RIGHTS_SEE,
            self::RIGHTS_MODERATE => self::RIGHTS_MODERATE
        ),
        self::GROUPS_MODERATORS => array(
            self::RIGHTS_BANNISH => self::RIGHTS_BANNISH,
            self::RIGHTS_SEE => self::RIGHTS_SEE,
            self::RIGHTS_MODERATE => self::RIGHTS_MODERATE
        )
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();

        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->db->updateQueries( __CLASS__ );
            if( version_compare( $installedVersion, '1.1.11.03.29', '<' ) ) {
                // The class datas are not in the same version as this file, or don't exist (installation)
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_searcher', 'scopes', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_sitemap', '', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_path', '', $this->className );

                @mkdir( SH_SITE_FOLDER . __CLASS__ );
                @mkdir( SH_SITE_FOLDER . __CLASS__ . '/images/' );
                @mkdir( SH_SITE_FOLDER . __CLASS__ . '/images/users' );

                for( $a = 1; $a <= 7; $a++ ) {
                    $this->db_execute( 'create_table_' . $a, array( ) );
                }
            }
            if( version_compare( $installedVersion, '1.1.11.03.29', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_user', 'accountTabs', __CLASS__ );
            }
            if( version_compare( $installedVersion, '1.1.11.07.12.1', '<' ) ) {
                $this->db_execute( 'update_table_users_1', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.07.12.2', '<' ) ) {
                $this->db_execute( 'update_table_users_2', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.07.12.3', '<' ) ) {
                $this->db_execute( 'update_table_users_3', array( ) );
                $salt = md5( microtime() );
                $this->setParam( 'images_salt', $salt );
                $this->writeParams();
            }
            if( version_compare( $installedVersion, '1.1.11.07.13', '<' ) ) {
                $this->helper->deleteDir( SH_SITE_FOLDER . __CLASS__ . '/images/' );
                mkdir( SH_IMAGES_FOLDER . 'forum' );
                sh_browser::setOwner( SH_IMAGES_FOLDER . 'forum' );
                sh_browser::setRights( SH_IMAGES_FOLDER . 'forum', sh_browser::ALL );
                sh_browser::setDimensions( SH_IMAGES_FOLDER . 'forum', 300, 300 );
                sh_browser::setHidden( SH_IMAGES_FOLDER . 'forum' );
            }
            if( version_compare( $installedVersion, '1.1.11.07.13.2', '<' ) ) {
                $this->db_execute( 'update_table_users_4', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.07.14', '<' ) ) {
                $this->db_execute( 'rename_table_groups', array( ) );
                $this->db_execute( 'rename_table_groups_rights', array( ) );
                $this->db_execute( 'rename_table_users_groups', array( ) );
                $this->db_execute( 'update_table_users_groups', array( ) );
                $this->db_execute( 'update_table_groups_rights', array( ) );
                $this->db_execute( 'update_table_sections', array( ) );
                $this->db_execute( 'update_table_posts', array( ) );
                $this->db_execute( 'update_table_posts_2', array( ) );
                $this->helper->addClassesSharedMethods( 'sh_rights', 'pages', __CLASS__ );
            }
            if( version_compare( $installedVersion, '1.1.11.07.26', '<' ) ) {
                $this->linker->renderer->add_render_tag( 'render_forum_lastPosts', __CLASS__, 'render_lastPosts' );
                $this->linker->renderer->add_render_tag( 'render_forum_category', __CLASS__, 'render_category' );
                $this->linker->renderer->add_render_tag( 'render_forum_usersList', __CLASS__, 'render_usersList' );
            }
            if( version_compare( $installedVersion, '1.1.11.07.29', '<' ) ) {
                $this->db_execute(
                    'section_create_root_category', array(
                    'name' => 'Forum',
                    )
                );
            }
            if( version_compare( $installedVersion, '1.1.11.08.08', '<' ) ) {
                $this->db_execute( 'update_table_groups_rights_2', array( ) );
                $this->db_execute( 'update_table_groups_rights_3', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.08.09', '<' ) ) {
                $this->db_execute( 'rename_table_subjects_to_topics', array( ) );
                $this->db_execute( 'rename_subjects_to_topics_1', array( ) );
                $this->db_execute( 'rename_subjects_to_topics_2', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.08.12', '<' ) ) {
                $this->db_execute( 'update_groups_1', array( ) );
                $this->db_execute( 'update_groups_2', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.08.14', '<' ) ) {
                $this->db_execute( 'update_users_1', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.08.15.3', '<' ) ) {
                $this->db_execute( 'update_profile_1', array( ) );
                $this->db_execute( 'update_users_5', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.08.16', '<' ) ) {
                $this->db_execute( 'delete_old_rights_table', array( ) );
                $this->db_execute( 'create_rights_table', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.08.17', '<' ) ) {
                $this->db_execute( 'create_default_groups_1', array( ) );
                $this->db_execute( 'create_default_groups_2', array( ) );
                $this->db_execute( 'group_create_withId', array( 'id' => 1, 'name' => 'Administrateurs du forum' ) );
                $this->db_execute( 'group_create_withId', array( 'id' => 2, 'name' => 'Modérateurs du forum' ) );
                $this->db_execute( 'group_create_withId', array( 'id' => 3, 'name' => 'Utilisateurs connectés' ) );
                $this->db_execute( 'group_create_withId', array( 'id' => 4, 'name' => 'Utilisateurs non connectés' ) );
            }
            if( version_compare( $installedVersion, '1.1.11.08.17.2', '<' ) ) {
                $this->db_execute( 'delete_old_rights_table_2', array( ) );
                $this->db_execute( 'create_section_rights_table', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.08.17.3', '<' ) ) {
                $this->db_execute( 'add_moderation_id_to_topics', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.08.17.4', '<' ) ) {
                $this->db_execute( 'right_set',
                                   array( 'section_id' => 0, 'group_id' => 1, 'right' => self::RIGHTS_ADMINISTRATE ) );
                $this->db_execute( 'right_set',
                                   array( 'section_id' => 0, 'group_id' => 1, 'right' => self::RIGHTS_BANNISH ) );
                $this->db_execute( 'right_set',
                                   array( 'section_id' => 0, 'group_id' => 1, 'right' => self::RIGHTS_CREATE_CATEGORIES ) );
                $this->db_execute( 'right_set',
                                   array( 'section_id' => 0, 'group_id' => 1, 'right' => self::RIGHTS_CREATE_TOPIC ) );
                $this->db_execute( 'right_set',
                                   array( 'section_id' => 0, 'group_id' => 1, 'right' => self::RIGHTS_MODERATE ) );
                $this->db_execute( 'right_set',
                                   array( 'section_id' => 0, 'group_id' => 1, 'right' => self::RIGHTS_POST ) );
                $this->db_execute( 'right_set', array( 'section_id' => 0, 'group_id' => 1, 'right' => self::RIGHTS_SEE ) );
            }
            if( version_compare( $installedVersion, '1.1.11.08.19', '<' ) ) {
                // We remove all the topics and sections from the searcher database
                $this->search_removeEntry( '*' );
                $topics = $this->db_execute( 'topics_getAllForSearcher', array( ) );
                $sections = $this->db_execute( 'sections_getAllForSearcher', array( ) );

                if( is_array( $topics ) ) {
                    foreach( $topics as $topic ) {
                        $this->search_addEntry( 'topic', $topic[ 'id' ], $topic[ 'alias' ], $topic[ 'title' ],
                                                $topic[ 'content' ] );
                    }
                }
                if( is_array( $sections ) ) {
                    foreach( $sections as $section ) {
                        $this->search_addEntry( 'show', $section[ 'id' ], $section[ 'name' ], '', '' );
                    }
                }
            }
            if( version_compare( $installedVersion, '1.1.11.08.26', '<' ) ) {
                $topics = $this->db_execute( 'add_image_and_text_to_sections', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.09.19', '<' ) ) {
                $topics = $this->db_execute( 'add_moderation_to_posts', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.10.11', '<' ) ) {
                $topics = $this->db_execute( 'create_table_messages', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.10.11.2', '<' ) ) {
                $topics = $this->db_execute( 'update_table_messages_1', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.11.10', '<' ) ) {
                $topics = $this->db_execute( 'create_table_notifications', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.11.11.21', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_cron', '', __CLASS__ );
            }
            if( version_compare( $installedVersion, '1.1.11.11.21.2', '<' ) ) {
                $topics = $this->db_execute( 'topics_add_last_post_date', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.12.01.04', '<' ) ) {
                $topics = $this->db_execute( 'topics_remove_autoupdate_date', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.12.01.05', '<' ) ) {
                $topics = $this->db_execute( 'topics_getLasts', array( 'count' => 10000 ) );
                if( $topics ) {
                    foreach( $topics as $oneTopic ) {
                        list($firstPostDatas) = $this->db_execute( 'posts_get_first', array( 'id' => $oneTopic[ 'id' ] ) );
                        if( $firstPostDatas && strtotime( $oneTopic[ 'date' ] ) > strtotime( $firstPostDatas[ 'date' ] ) ) {
                            $this->db_execute( 'topic_force_creation_date',
                                               array( 'id' => $oneTopic[ 'id' ], 'date' => $firstPostDatas[ 'date' ] ) );
                        }
                        list($lastPostDatas) = $this->db_execute( 'posts_get_last', array( 'id' => $oneTopic[ 'id' ] ) );
                        if( $lastPostDatas && strtotime( $oneTopic[ 'last_post_date' ] ) < strtotime( $lastPostDatas[ 'date' ] ) ) {
                            $this->db_execute( 'topic_set_last_post_date_force',
                                               array( 'id' => $oneTopic[ 'id' ], 'last_post_date' => $lastPostDatas[ 'date' ] ) );
                        }
                    }
                }
            }
            if( version_compare( $installedVersion, '1.1.12.01.26', '<' ) ) {
                $topics = $this->db_execute( 'profile_change_text_to_real_text_field', array( ) );
            }
            if( version_compare( $installedVersion, '1.1.12.02.28', '<' ) ) {
                $this->linker->renderer->add_render_tag( 'render_forum_lastTopics', __CLASS__, 'render_lastTopics' );
            }
            if( version_compare( $installedVersion, '1.1.12.03.09', '<' ) ) {
                $this->linker->renderer->add_render_tag( 'render_forum_menuBar', __CLASS__, 'render_menuBar' );
            }

            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        $this->users_images_folder = SH_SITE_FOLDER . 'sh_images/forum/';
        $this->users_images_path = SH_IMAGES_SITE . 'forum/';

        if( isset( $_SESSION[ __CLASS__ ][ 'images_salt' ] ) ) {
            $this->images_salt = $_SESSION[ __CLASS__ ][ 'images_salt' ];
        } else {
            $this->images_salt = $this->getParam( 'images_salt', '' );
        }

        if( $this->isConnected() ) {
            $this->userId = $this->linker->user->get( 'id' );
            $groups = $this->db_execute( 'user_get_groups', array( 'user_id' => $this->userId ) );
            foreach( $groups as $group ) {
                $this->userGroups[ ] = $group[ 'group_id' ];
            }
            $this->userGroups[ ] = self::GROUPS_CONNECTED;
            $this->userGroups[ ] = self::GROUPS_DISCONNECTED;
            list($datas) = $this->db_execute( 'user_get_alias', array( 'id' => $this->userId ), $qry );
            if( !empty( $datas[ 'alias' ] ) ) {
                $this->alias = $datas[ 'alias' ];
            }
            $this->renderer_addConstants( array( 'userId' => $this->userId, 'alias' => $this->alias ) );
        } else {
            $this->userGroups[ ] = self::GROUPS_DISCONNECTED;
        }
    }

    public function cron_job( $type ) {
        sh_cache::disable();
        $start = time();
        if( $type == sh_cron::JOB_QUARTERHOUR ) {
            // Between 0:00 and 0:15, we delete the cached version because the date like "today at" are false now
            $this->cachedPart_remove( '*' );
        }
    }

    protected function set_rendering_rights( $action = null, $id=null ) {
        if( is_null( $action ) ) {
            // We get the action and the id from the url
            $class = $this->linker->path->page[ 'element' ];
            if( $class == __CLASS__ ) {
                $action = $this->linker->path->page[ 'action' ];
                $id = $this->linker->path->page[ 'id' ];
            } else {
                // As there is no page given (or it isn't a page from this class), we exit
                return false;
            }
        }
        $this->renderer_addConstants(
            array(
                'rights_may_add_topics'
            )
        );
    }

    public function getAlias( $me = true ) {
        if( $me === true ) {
            return $this->alias;
        }
        list($alias) = $this->db_execute( 'user_getAlias', array( 'id' => $me ) );
        return $alias[ 'alias' ];
    }

    protected function help( $function ) {
        if( $function == 'render_category' ) {
            return $this->linker->renderer->showRenderTagHelp(
                    'RENDER_FORUM_CATEGORY',
                    'This RENDER_TAG renders the content of or forum\'s category. This category may contain
                    either sub-categories or topics.',
                    array(
                    'id' => 'The id of the category to show. Defaults to 0, the main category.',
                    'rf' => 'The name of the renderFile to use (in the sh_forum class). Defaults to "render_category".'
                    ),
                    array(
                    'RENDER_FORUM_CATEGORY id="5" rf="my_custom_rf"' => 'Will use 
                        /classes/sh_forum/renderFiles/my_custom_rf.rf.xml (or any other that is replaced
                        by the template) instead of /classes/sh_forum/renderFiles/render_category.rf.xml to
                        render the category number 5.'
                    )
            );
        } elseif( $function == 'render_lastTopics' ) {
            return $this->linker->renderer->showRenderTagHelp(
                    'RENDER_FORUM_LASTTOPICS',
                    'This RENDER_FORUM_LASTTOPICS renders the N last topics created in the forum, or the last topics created 
                    by a special user.',
                    array(
                    'count' => 'Optional. The number of topics to show. Defaults to 8.',
                    'user' => 'Optional. If not set, will show the last topics created by anyone. If set,
                        will only show the last topics created by the user who has this value as user_id.',
                    'rf' => 'The name of the renderFile to use (in the sh_forum class). Defaults to "render_lastTopics".'
                    ),
                    array(
                    'RENDER_FORUM_LASTTOPICS count="15" user="201201010001" rf="my_custom_rf"' => 'Will use 
                        /classes/sh_forum/renderFiles/my_custom_rf.rf.xml (or any other that is replaced
                        by the template) instead of /classes/sh_forum/renderFiles/render_lastTopics.rf.xml to
                        render the 15 last topics created by the user whose id is 201201010001.'
                    )
            );
        }
    }

    public function render_lastTopics( $attributes, $content ) {
        if( isset( $attributes[ 'help_me' ] ) ) {
            $ret = $this->help( __FUNCTION__ );
            return $ret;
        }
        if( !isset( $attributes[ 'count' ] ) ) {
            $count = '8';
        } else {
            $count = $attributes[ 'count' ];
        }
        if( !isset( $attributes[ 'user' ] ) ) {
            $values[ 'topics' ] = $this->db_execute( 'topics_getLasts', array( 'count' => $count ) );
        } else {
            $values[ 'topics' ] = $this->db_execute(
                'topics_getLastsForUser', array( 'count' => $count, 'user' => $attributes[ 'user' ] )
            );
        }
        if( !isset( $attributes[ 'rf' ] ) ) {
            $rf = 'render_lastTopics';
        } else {
            $rf = $attributes[ 'rf' ];
        }

        if( !empty( $values[ 'topics' ] ) ) {
            foreach( $values[ 'topics' ] as $id => $topic ) {
                $values[ 'topics' ][ $id ][ 'content' ] = strip_tags( $topic[ 'content' ] );
                $values[ 'topics' ][ $id ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/topic/' . $topic[ 'id' ] );
                
                list($count) = $this->db_execute( 'topic_count_posts', array( 'topic_id' => $topic[ 'id' ] ) );
                $values[ 'topics' ][ $id ][ 'posts' ] = $count[ 'count' ];
                if( $count[ 'count' ] > 1 ) {
                    $values[ 'topics' ][ $id ][ 'posts_plural' ] = true;
                }
                $values['topics'][$id]['userProfile'] = $this->linker->path->getLink(__CLASS__.'/profile/'.$topic['opener_id']);
            }
        } else {
            $values[ 'error' ][ 'nothingToShow' ] = true;
        }
        return $this->render( $rf, $values, false, false );
    }

    public function render_menuBar( $attributes, $content ) {
        return $this->render( 'render_menuBar', $values, false, false );
    }
    
    public function render_lastPosts( $attributes, $content ) {
        if( !isset( $attributes[ 'count' ] ) ) {
            $count = '8';
        } else {
            $count = $attributes[ 'count' ];
        }
        if( !isset( $attributes[ 'user' ] ) ) {
            $values[ 'posts' ] = $this->db_execute( 'posts_getLasts', array( 'count' => $count ) );
        } else {
            $values[ 'posts' ] = $this->db_execute(
                'posts_getLastsForUser', array( 'count' => $count, 'user' => $attributes[ 'user' ] )
            );
        }

        foreach( $values[ 'posts' ] as $id => $topic ) {
            $values[ 'posts' ][ $id ][ 'content' ] = strip_tags( $topic[ 'content' ] );
            $values[ 'posts' ][ $id ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/topic/' . $topic[ id ] );
            $date = $this->linker->datePicker->dateAndTimeToLocal(
                $topic[ 'date' ], true
            );
            $values[ 'posts' ][ $id ][ 'date' ] = substr( $date, 0, -3 );
        }

        return $this->render( 'render_lastPosts', $values, false, false );
    }

    public function render_usersList( $attributes, $content ) {
        if( !isset( $attributes[ 'count' ] ) ) {
            $count = '8';
        } else {
            $count = $attributes[ 'count' ];
        }

        if( !isset( $attributes[ 'group' ] ) ) {
            $group = '*';
            $values[ 'users' ] = $this->db_execute( 'users_get_all', array( 'count' => $count ), $qry );
        } else {
            $group = $attributes[ 'group' ];
            $values[ 'users' ] = $this->db_execute( 'group_get_users', array( 'group_id' => $group, 'count' => $count ) );
        }

        $extendedFormClasses = $this->get_shared_methods( 'special_groups_forms' );

        $images = array( );
        foreach( $values[ 'users' ] as $id => $user ) {
            if( file_exists( $this->users_images_folder . $user[ 'image' ] . '.png' ) ) {
                $values[ 'users' ][ $id ][ 'image' ] = $this->users_images_path . $user[ 'image' ] . '.png';
            } else {
                if( $user[ 'gender' ] == 'female' ) {
                    $values[ 'users' ][ $id ][ 'image' ] = $this->image_profile_female;
                } else {
                    $values[ 'users' ][ $id ][ 'image' ] = $this->image_profile_male;
                }
            }
            foreach( $extendedFormClasses as $extendedFormClass ) {
                $userDatas = $this->linker->$extendedFormClass->forum_get_special_user_datas( $user[ 'id' ] );
                foreach( $userDatas as $userDataName => $userDataValue ) {
                    $values[ 'users' ][ $id ][ $userDataName ] = $userDataValue;
                }
            }
            $images[ ] = $values[ 'users' ][ $id ][ 'image' ] . '.resized.100.100.png';
        }
        shuffle( $values[ 'users' ] );
        // We allow to show only the first (js could make a diapo using this)
        $values[ 'users' ][ 0 ][ 'first' ] = true;


        $values[ 'group' ][ 'diaporama' ] = implode( '|', $images );


        return $this->render( 'render_usersList', $values, false, false );
    }

    protected function checkRights( $right_id, $part_type = self::PARTS_CATEGORIES, $id = null, $groups = null ) {
        if( is_null( $id ) ) {
            $id = ( int ) $this->linker->path->page[ 'id' ];
        }
        if( is_null( $groups ) ) {
            $groups = $this->userGroups;
        }
        if( isset( $this->rights[ $right_id ][ $part_type . '_' . $id ] ) ) {
            return $this->rights[ $right_id ][ $part_type . '_' . $id ];
        }

        $rights = $this->getAllRights();
        foreach( $groups as $group ) {
            if( isset( $rights[ $group ][ $right_id ] ) ) {
                // This user is allowed for this in the entire forum
                $this->rights[ $right_id ][ $part_type . '_' . $id ] = true;
                return true;
            }
        }

        // We check if the user is allowed for this in this specific context
        if( $part_type == self::PARTS_TOPICS ) {
            // As this is a topic, we should check the right on the parent categories
            list($parent) = $this->db_execute( 'topic_getSection', array( 'id' => $id ) );
            if( $this->checkRights( $right_id, self::PARTS_CATEGORIES, $parent[ 'parent' ], $groups ) ) {
                return true;
            }
            while( $parent[ 'parent' ] != 'null' ) {
                list($parent) = $this->db_execute( 'section_get', array( 'id' => $id ) );
                if( $this->checkRights( $right_id, self::PARTS_CATEGORIES, $parent[ 'parent' ], $groups ) ) {
                    return true;
                }
            }
        } elseif( $part_type == self::PARTS_CATEGORIES ) {
            if( $right_id == self::RIGHTS_CREATE_CATEGORIES ) {
                
            } elseif( $right_id == self::RIGHTS_MODERATE ) {
                
            }

            return false;
        }
    }

    protected function getAllRights() {
        static $rights = null;
        if( is_null( $rights ) ) {
            $rights = $this->getParam( 'rights', array( ) );
            $rights[ self::GROUPS_ADMINISTRATORS ] = array(
                self::RIGHTS_ADMINISTRATE => true,
                self::RIGHTS_BANNISH => true,
                self::RIGHTS_CREATE_CATEGORIES => true,
                self::RIGHTS_CREATE_TOPIC => true,
                self::RIGHTS_MODERATE => true,
                self::RIGHTS_POST => true,
                self::RIGHTS_SEE => true
            );
        }
        return $rights;
    }

    protected function setRight( $section, $right, $group_id ) {
        $this->db_execute( 'right_set', array( 'section_id' => $section, 'right' => $right, 'group_id' => $group_id ) );
    }

    protected function getRight( $section, $right, $groups = null ) {
        if( is_null( $groups ) ) {
            $groups = $this->userGroups;
        }
        if( is_array( $groups ) ) {
            // We should check for every group
            list($rep) = $this->db_execute(
                'right_get_from_groups',
                array(
                'section_id' => $section,
                'right' => $right,
                'groups' => '"' . implode( '","', $groups ) . '"'
                )
            );
        } else {
            list($rep) = $this->db_execute( 'right_get',
                                            array( 'section_id' => $section, 'right' => $right, 'group_id' => $groups ) );
        }
        if( ($rep[ 'count' ] > 0 ) ) {
            return true;
        }
        if( $section != 0 ) {
            // We ckeck its parent category
            list($section) = $this->db_execute( 'section_get', array( 'id' => $section ) );

            return $this->getRight( $section[ 'parent' ], $right, $groups );
        }
        return false;
    }

    public function modify_section() {
        sh_cache::disable();
        $id = ( int ) $this->linker->path->page[ 'id' ];
        if( !$this->getRight( $id, self::RIGHTS_ADMINISTRATE ) ) {
            $this->linker->path->error( 403 );
        }
        list($values[ 'old' ]) = $this->db_execute( 'section_get', array( 'id' => $id ) );
        if( $this->formSubmitted( 'modify_section' ) ) {
            $error = $_FILES[ 'new' ][ 'error' ][ 'image' ];
            $imageName = '';
            if( $error === UPLOAD_ERR_OK ) {
                // We check the image type
                $allowed = array( 'image/png', 'image/jpeg', 'image/jpg', 'image/gif' );
                if( in_array( $_FILES[ 'new' ][ 'type' ][ 'image' ], $allowed ) ) {
                    $imageName = substr( md5( microtime() ), 0, 8 );
                    // There is a new image, so we save it
                    $tmp_name = $_FILES[ 'new' ][ "tmp_name" ][ 'image' ];
                    $name = $_FILES[ 'new' ][ "name" ][ 'image' ];
                    $ext = strtolower( array_pop( explode( '.', $name ) ) );
                    move_uploaded_file( $tmp_name, SH_TEMPIMAGES_FOLDER . $imageName . '.' . $ext );
                    $this->linker->browser->addImage(
                        'forum', SH_TEMPIMAGES_FOLDER . $imageName . '.' . $ext
                    );
                    $this->db_execute(
                        'section_update_withImage',
                        array(
                        'name' => addslashes( $_POST[ 'name' ] ),
                        'image' => $imageName,
                        'text' => addslashes( trim( $_POST[ 'text' ] ) ),
                        'id' => $id
                        )
                    );
                    $done = true;
                } else {
                    $this->linker->html->addMessage( 'Le fichier doit être une image JPG, PNG ou GIF.' );
                    $errorInImage = true;
                }
            }
            if( !$done ) {
                $this->db_execute(
                    'section_update_withoutImage',
                    array(
                    'name' => $this->clearUserEntryForDatabase( $_POST[ 'name' ] ),
                    'text' => $this->clearUserEntryForDatabase( trim( $_POST[ 'text' ] ) ),
                    'id' => $id
                    )
                );
            }
            $this->cachedPart_remove( 'section_subsections_' . $values[ 'old' ][ 'parent' ] );
            if( !$errorInImage ) {
                $this->linker->path->redirect( __CLASS__, 'show', $values[ 'old' ][ 'parent' ] );
            }
        }
        $this->linker->html->setTitle( $this->getI18n( 'modify_section_title' ) );
        if( file_exists( $this->users_images_folder . $values[ 'old' ][ 'image' ] . '.png' ) ) {
            $values[ 'old' ][ 'image' ] = $this->users_images_path . $values[ 'old' ][ 'image' ] . '.png';
        }
        $this->render( 'modify_section', $values );
    }

    public function clearUserEntryForDatabase( $entry ) {
        $replacements = array(
            '&amp;' => '&',
            '&' => '&#38;'
        );
        $ret = str_replace( array_keys( $replacements ), array_values( $replacements ), $entry );
        return addslashes( $ret );
    }

    public function lastTopics() {
        $count = ( int ) $this->linker->path->page[ 'id' ];
        $this->linker->html->setTitle(
            str_replace(
                '[COUNT]', $count, $this->getI18n( 'lastTopics_title' )
            )
        );
        $values[ 'topics' ][ 'count' ] = $count;
        $this->render( 'last_topics', $values );
    }

    public function show() {
        sh_cache::disable();
        $id = ( int ) $this->linker->path->page[ 'id' ];

        if( !$this->getRight( $id, self::RIGHTS_SEE ) ) {
            $this->linker->path->error( 403 );
            return false;
        }

        // We create the breadcrumbs
        $breadcrumbs = array( );
        $nextSection = $id;
        do {
            $section = $nextSection;
            list($sectionDatas) = $this->db_execute( 'section_get', array( 'id' => $section ) );
            array_unshift(
                $breadcrumbs,
                array(
                'id' => $section,
                'name' => $sectionDatas[ 'name' ],
                'link' => $this->linker->path->getLink( __CLASS__ . '/show/' . $section )
                )
            );
            $nextSection = $sectionDatas[ 'parent' ];
        } while( $section != 0 && $cpt++ < 100 );
        $values[ 'nav_levels' ] = $breadcrumbs;

        if( $this->getParam( 'forceUserToCheckConditions', false ) ) {
            $values[ 'conditions' ][ 'file' ] = $this->getParam( 'conditions', '' );
        }

        if( $this->formSubmitted( 'forum_administrate_section' ) && $this->getRight( $id, self::RIGHTS_ADMINISTRATE ) ) {
            $this->db_execute( 'rights_delete_for_section', array( 'section_id' => $id ) );
            // We force the rights that should never be changed
            $_POST[ 'rights' ][ self::GROUPS_ADMINISTRATORS ][ self::RIGHTS_ADMINISTRATE ] = true;
            $_POST[ 'rights' ][ self::GROUPS_ADMINISTRATORS ][ self::RIGHTS_BANNISH ] = true;
            $_POST[ 'rights' ][ self::GROUPS_ADMINISTRATORS ][ self::RIGHTS_CREATE_CATEGORIES ] = true;
            $_POST[ 'rights' ][ self::GROUPS_ADMINISTRATORS ][ self::RIGHTS_CREATE_TOPIC ] = true;
            $_POST[ 'rights' ][ self::GROUPS_ADMINISTRATORS ][ self::RIGHTS_MODERATE ] = true;
            $_POST[ 'rights' ][ self::GROUPS_ADMINISTRATORS ][ self::RIGHTS_POST ] = true;
            $_POST[ 'rights' ][ self::GROUPS_ADMINISTRATORS ][ self::RIGHTS_SEE ] = true;

            $_POST[ 'rights' ][ self::GROUPS_MODERATORS ][ self::RIGHTS_SEE ] = true;
            $_POST[ 'rights' ][ self::GROUPS_MODERATORS ][ self::RIGHTS_BANNISH ] = true;
            $_POST[ 'rights' ][ self::GROUPS_MODERATORS ][ self::RIGHTS_MODERATE ] = true;

            $rights_types = array(
                self::RIGHTS_ADMINISTRATE, self::RIGHTS_BANNISH, self::RIGHTS_CREATE_CATEGORIES,
                self::RIGHTS_CREATE_TOPIC, self::RIGHTS_MODERATE, self::RIGHTS_POST, self::RIGHTS_SEE
            );
            // We save the default rights for the forum
            foreach( $_POST[ 'rights' ] as $group_id => $group ) {
                foreach( $rights_types as $right ) {
                    if( !isset( $this->forbidden_rights[ $group_id ][ $right ] ) ) {
                        if( isset( $group[ $right ] ) ) {
                            $this->setRight( $id, $right, $group_id );
                        }
                    }
                }
            }
            $this->setParam( 'rights', $_POST[ 'rights' ] );
            $this->writeParams();
        }

        if( $this->formSubmitted( 'new_section' ) && $this->getRight( $id, self::RIGHTS_CREATE_CATEGORIES ) ) {
            if( !$this->isConnected() ) {
                // We should check if the captcha has been filled successfully
                if( !$this->linker->captcha->verify( 'new_section' ) ) {
                    $this->linker->html->addMessage( $this->getI18n( 'captchaError_message' ) );
                }
            }

            $this->cachedPart_remove( 'section_subsections_' . $id );

            $error = $_FILES[ 'new' ][ 'error' ][ 'image' ];
            $imageName = '';
            if( $error === UPLOAD_ERR_OK ) {
                // We check the image type
                $allowed = array( 'image/png', 'image/jpeg', 'image/jpg', 'image/gif' );
                if( in_array( $_FILES[ 'new' ][ 'type' ][ 'image' ], $allowed ) ) {
                    $imageName = substr( md5( microtime() ), 0, 8 );
                    // There is a new image, so we save it
                    $tmp_name = $_FILES[ 'new' ][ "tmp_name" ][ 'image' ];
                    $name = $_FILES[ 'new' ][ "name" ][ 'image' ];
                    $ext = strtolower( array_pop( explode( '.', $name ) ) );
                    move_uploaded_file( $tmp_name, SH_TEMPIMAGES_FOLDER . $imageName . '.' . $ext );
                    $this->linker->browser->addImage(
                        'forum', SH_TEMPIMAGES_FOLDER . $imageName . '.' . $ext
                    );
                } else {
                    $this->linker->html->addMessage( 'Le fichier doit être une image JPG, PNG ou GIF.' );
                }
            } elseif( $error == UPLOAD_ERR_INI_SIZE || $error == UPLOAD_ERR_FORM_SIZE ) {
                $this->linker->html->addMessage( 'Le fichier est trop lourd.' );
            }

            $this->db_execute(
                'section_create',
                array(
                'name' => $this->clearUserEntryForDatabase( $_POST[ 'new' ][ 'name' ] ),
                'hasChildren' => ($_POST[ 'new' ][ 'type' ] == 'categories'),
                'parent' => $id,
                'image' => $imageName,
                'text' => $this->clearUserEntryForDatabase( $_POST[ 'new' ][ 'text' ] )
                )
            );
            $section_id = $this->db_insertId();

            $this->search_addEntry(
                'show', $section_id, $_POST[ 'new' ][ 'name' ], '', ''
            );
        }
        if( $this->formSubmitted( 'new_topic' ) && $this->getRight( $id, self::RIGHTS_CREATE_TOPIC ) ) {
            if( !$this->isConnected() ) {
                // We should check if the captcha has been filled successfully
                if( !$this->linker->captcha->verify( 'new_topic' ) ) {
                    $this->linker->html->addMessage( $this->getI18n( 'captchaError_message' ) );
                    $error = true;
                }
            }

            $this->cachedPart_remove( 'section_topics_' . $id );

            // We should create a new topic
            // We  check if there is no forbidden/dangerous words in his topic
            if( $this->isThereForbiddenWords( $_POST[ 'new' ][ 'title' ] . "\n" . $_POST[ 'new' ][ 'text' ] ) ) {
                $this->linker->html->addMessage( $this->getI18n( 'forbiddenWordsFound_message' ) );
                $error = true;
            }
            if( isset( $values[ 'conditions' ][ 'file' ] ) && !isset( $_POST[ 'accept_conditions' ] ) ) {
                $this->linker->html->addMessage( $this->getI18n( 'please_accept_conditions' ) );
                $error = true;
            }
            if( trim( $_POST[ 'new' ][ 'title' ] ) == '' ) {
                $this->linker->html->addMessage( $this->getI18n( 'a_title_is_required' ) );
                $error = true;
            }
            if( trim( strip_tags( $_POST[ 'new' ][ 'content' ] ) ) == '' ) {
                $this->linker->html->addMessage( $this->getI18n( 'a_content_is_required' ) );
                $error = true;
            }
            if( !$error ) {
                $this->db_execute(
                    'topic_create',
                    array(
                    'opener_id' => $this->userId,
                    'section_id' => $id,
                    'title' => $this->clearUserEntryForDatabase( $_POST[ 'new' ][ 'title' ] ),
                    'content' => $this->clearUserEntryForDatabase( $_POST[ 'new' ][ 'content' ] )
                    )
                );
                $topic_id = $this->db_insertId();
                if( $this->isThereDangerousWords( $_POST[ 'new' ][ 'title' ] . "\n" . $_POST[ 'new' ][ 'content' ] ) ) {
                    $this->alert_auto(
                        $_POST[ 'new' ][ 'title' ], $_POST[ 'new' ][ 'content' ],
                        $this->linker->path->getLink( __CLASS__ . '/topic/' . $topic_id )
                    );
                }

                $this->search_addEntry(
                    'topic', $topic_id, '', $_POST[ 'new' ][ 'title' ], $_POST[ 'new' ][ 'content' ]
                );
                list($datas) = $this->db_execute( 'user_get_notifications_my_topics', array( 'id' => $this->userId ) );
                if( !empty( $datas[ 'notifications_my_topics' ] ) ) {
                    if( $datas[ 'notifications_my_topics' ] == '1' ) {
                        $this->db_execute( 'notif_set', array( 'topic_id' => $topic_id, 'user_id' => $this->userId ) );
                        $this->linker->html->addMessage( $this->getI18n( 'added_to_notified_automatically' ), false );
                    }
                }
            } else {
                $values[ 'form' ] = $_POST[ 'new' ];
                if( isset( $_POST[ 'accept_conditions' ] ) ) {
                    $values[ 'form' ][ 'accept_conditions' ] = 'checked';
                }
            }
        }

        list($values[ 'section' ]) = $this->db_execute( 'section_get', array( 'id' => $id ) );
        $this->linker->html->setTitle( 'Forum - ' . $values[ 'section' ][ 'name' ] );
        if( $values[ 'section' ][ 'hasChildren' ] ) {
            // This section contains sub sections
            $values[ 'sections' ] = $this->db_execute( 'sections_get', array( 'parent' => $id ) );
            $values[ 'rights' ][ 'modify_section' ] = $this->getRight( $id, self::RIGHTS_ADMINISTRATE );
            if( is_array( $values[ 'sections' ] ) ) {
                foreach( $values[ 'sections' ] as $sectionNumber => $section ) {
                    if( file_exists( $this->users_images_folder . $section[ 'image' ] . '.png' ) ) {
                        $values[ 'sections' ][ $sectionNumber ][ 'image' ] = $this->users_images_path . $section[ 'image' ] . '.png';
                    } else {
                        unset( $values[ 'sections' ][ $sectionNumber ][ 'image' ] );
                    }
                    $values[ 'sections' ][ $sectionNumber ][ 'text' ] = nl2br( $values[ 'sections' ][ $sectionNumber ][ 'text' ] );
                    $values[ 'sections' ][ $sectionNumber ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/' . __FUNCTION__ . '/' . $section[ 'id' ] );
                    if( $section[ 'hasChildren' ] ) {
                        // The section contains sub sections
                        list($count) = $this->db_execute( 'sections_count_subSections',
                                                          array( 'parent' => $section[ 'id' ] ), $qry );
                        $values[ 'sections' ][ $sectionNumber ][ 'subsections' ] = $count[ 'count' ];
                    } else {
                        // The section contains topics
                        list($count) = $this->db_execute( 'sections_count_topics', array( 'parent' => $section[ 'id' ] ) );
                        $values[ 'sections' ][ $sectionNumber ][ 'topics' ] = $count[ 'count' ];
                    }

                    if( $values[ 'rights' ][ 'modify_section' ] ) {
                        $values[ 'cache' ][ 'disable_subsections' ] = true;
                        $values[ 'sections' ][ $sectionNumber ][ 'modify_section_link' ] = $this->linker->path->getLink( __CLASS__ . '/modify_section/' . $section[ 'id' ] );
                    }
                }
            }

            $values[ 'rights' ][ 'create_section' ] = $this->getRight( $id, self::RIGHTS_CREATE_CATEGORIES );
        } else {
            // This section contains topics
            $values[ 'topics' ] = $this->db_execute( 'section_get_topics', array( 'id' => $id ) );
            if( is_array( $values[ 'topics' ] ) ) {
                foreach( $values[ 'topics' ] as $topicId => $topic ) {
                    if( $values[ 'topics' ][ $topicId ][ 'last_post_date' ] != '0000-00-00 00:00:00' && $values[ 'topics' ][ $topicId ][ 'last_post_date' ] != $values[ 'topics' ][ $topicId ][ 'date' ] ) {
                        $values[ 'topics' ][ $topicId ][ 'last_post_date' ] = $this->linker->datePicker->dateAndTimeToLocal(
                            $values[ 'topics' ][ $topicId ][ 'last_post_date' ], true
                        );
                    } else {
                        unset( $values[ 'topics' ][ $topicId ][ 'last_post_date' ] );
                    }
                    $values[ 'topics' ][ $topicId ][ 'date' ] = $this->linker->datePicker->dateAndTimeToLocal(
                        $values[ 'topics' ][ $topicId ][ 'date' ], true
                    );

                    if( file_exists( $this->users_images_folder . $topic[ 'image' ] . '.png' ) ) {
                        $values[ 'topics' ][ $topicId ][ 'image' ] = $this->users_images_path . $topic[ 'image' ] . '.png';
                    } else {
                        // We should set the default image
                        $values[ 'topics' ][ $topicId ][ 'image' ] = '/images/shared/default/default_profile_' . $values[ 'topics' ][ $topicId ][ 'gender' ] . '.png';
                    }

                    list($count) = $this->db_execute( 'topic_count_posts', array( 'topic_id' => $topic[ 'id' ] ) );
                    $values[ 'topics' ][ $topicId ][ 'posts' ] = $count[ 'count' ];
                    if( $count[ 'count' ] > 1 ) {
                        $values[ 'topics' ][ $topicId ][ 'posts_plural' ] = true;
                    }
                    $values[ 'topics' ][ $topicId ][ 'link' ] = $this->linker->path->getLink(
                        __CLASS__ . '/topic/' . $topic[ 'id' ]
                    );

                    $values[ 'topics' ][ $topicId ][ 'userProfile' ] = $this->linker->path->getLink(
                        __CLASS__ . '/profile/' . $topic[ 'opener_id' ]
                    );
                }
            }
            $values[ 'rights' ][ 'create_topic' ] = $this->getRight( $id, self::RIGHTS_CREATE_TOPIC );
        }
        $values[ 'rights' ][ 'administrate' ] = $this->getRight( $id, self::RIGHTS_ADMINISTRATE );
        if( $values[ 'rights' ][ 'administrate' ] ) {
            $groupsRights = $this->getParam( 'rights', array( ) );
            $groups = $this->db_execute( 'groups_get_all', array( ) );

            foreach( $groups as $oneGroup ) {

                $rights_types = array(
                    self::RIGHTS_ADMINISTRATE, self::RIGHTS_BANNISH, self::RIGHTS_CREATE_CATEGORIES,
                    self::RIGHTS_CREATE_TOPIC, self::RIGHTS_MODERATE, self::RIGHTS_POST, self::RIGHTS_SEE
                );
                foreach( $rights_types as $right ) {
                    $values[ 'groups' ][ $oneGroup[ 'id' ] ][ 'id' ] = $oneGroup[ 'id' ];
                    $values[ 'groups' ][ $oneGroup[ 'id' ] ][ 'name' ] = $oneGroup[ 'name' ];
                    if( isset( $this->minimal_rights[ $oneGroup[ 'id' ] ][ $right ] ) ) {
                        $values[ 'groups' ][ $oneGroup[ 'id' ] ][ $right ] = 'checked+disabled';
                    } elseif( !isset( $this->forbidden_rights[ $oneGroup[ 'id' ] ][ $right ] ) ) {
                        if( $this->getRight( $id, $right, $oneGroup[ 'id' ] ) ) {
                            $values[ 'groups' ][ $oneGroup[ 'id' ] ][ $right ] = 'checked';
                        }
                    } else {
                        $values[ 'groups' ][ $oneGroup[ 'id' ] ][ $right ] = 'disabled';
                    }
                }
            }
        }
        if( $this->isConnected() && !$this->alias ) {
            $values[ 'user' ][ 'noProfile' ] = true;
            $values[ 'user' ][ 'editProfileLink' ] = $this->linker->path->getLink( 'sh_user/profile/' ) . '?selectedTab=tab_forum';
        } elseif( !$this->isConnected() ) {
            $values[ 'user' ][ 'requiresCaptcha' ] = true;
        }
        $values[ 'links' ][ 'connection' ] = $this->linker->path->getLink( 'user/connect/' ) . '?redirectionAfterConnection=' . $this->linker->path->getPage();

        $values[ 'user' ][ 'connected' ] = $this->isConnected();
        $this->render( 'show', $values );
    }

    public function render_category( $attributes, $content ) {
        if( isset( $attributes[ 'help_me' ] ) ) {
            $ret = $this->help( __FUNCTION__ );
            return $ret;
        }
        if( !isset( $attributes[ 'id' ] ) ) {
            $id = 0;
        } else {
            $id = $attributes[ 'id' ];
        }
        if( !isset( $attributes[ 'rf' ] ) ) {
            $rf = 'render_category';
        } else {
            $rf = $attributes[ 'rf' ];
        }

        list($values[ 'section' ]) = $this->db_execute( 'section_get', array( 'id' => $id ) );

        if( $values[ 'section' ][ 'hasChildren' ] ) {
            // This section contains sub sections
            $values[ 'sections' ] = $this->db_execute( 'sections_get', array( 'parent' => $id ) );
            if( is_array( $values[ 'sections' ] ) ) {
                foreach( $values[ 'sections' ] as $sectionNumber => $section ) {
                    if( file_exists( $this->users_images_folder . $section[ 'image' ] . '.png' ) ) {
                        $values[ 'sections' ][ $sectionNumber ][ 'image' ] = $this->users_images_path . $section[ 'image' ] . '.png';
                    } else {
                        unset( $values[ 'sections' ][ $sectionNumber ][ 'image' ] );
                    }
                    $values[ 'sections' ][ $sectionNumber ][ 'text' ] = nl2br( $values[ 'sections' ][ $sectionNumber ][ 'text' ] );
                    $values[ 'sections' ][ $sectionNumber ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/show/' . $section[ 'id' ] );
                    if( $section[ 'hasChildren' ] ) {
                        // The section contains sub sections
                        list($count) = $this->db_execute( 'sections_count_subSections',
                                                          array( 'parent' => $section[ 'id' ] ), $qry );
                        $values[ 'sections' ][ $sectionNumber ][ 'subsections' ] = $count[ 'count' ];
                    } else {
                        // The section contains topics
                        list($count) = $this->db_execute( 'sections_count_topics', array( 'parent' => $section[ 'id' ] ) );
                        $values[ 'sections' ][ $sectionNumber ][ 'topics' ] = $count[ 'count' ];
                    }
                }
            }
        } else {
            // This section contains topics
            $values[ 'topics' ] = $this->db_execute( 'section_get_topics', array( 'id' => $id ) );
            if( is_array( $values[ 'topics' ] ) ) {
                foreach( $values[ 'topics' ] as $topicId => $topic ) {
                    if( $values[ 'topics' ][ $topicId ][ 'last_post_date' ] != '0000-00-00 00:00:00' && $values[ 'topics' ][ $topicId ][ 'last_post_date' ] != $values[ 'topics' ][ $topicId ][ 'date' ] ) {
                        $values[ 'topics' ][ $topicId ][ 'last_post_date' ] = $this->linker->datePicker->dateAndTimeToLocal(
                            $values[ 'topics' ][ $topicId ][ 'last_post_date' ], true
                        );
                    } else {
                        unset( $values[ 'topics' ][ $topicId ][ 'last_post_date' ] );
                    }
                    $values[ 'topics' ][ $topicId ][ 'date' ] = $this->linker->datePicker->dateAndTimeToLocal(
                        $values[ 'topics' ][ $topicId ][ 'date' ], true
                    );

                    if( file_exists( $this->users_images_folder . $topic[ 'image' ] . '.png' ) ) {
                        $values[ 'topics' ][ $topicId ][ 'image' ] = $this->users_images_path . $topic[ 'image' ] . '.png';
                    } else {
                        // We should set the default image
                        $values[ 'topics' ][ $topicId ][ 'image' ] = '/images/shared/default/default_profile_' . $values[ 'topics' ][ $topicId ][ 'gender' ] . '.png';
                    }

                    list($count) = $this->db_execute( 'topic_count_posts', array( 'topic_id' => $topic[ 'id' ] ) );
                    $values[ 'topics' ][ $topicId ][ 'posts' ] = $count[ 'count' ];
                    if( $count[ 'count' ] > 1 ) {
                        $values[ 'topics' ][ $topicId ][ 'posts_plural' ] = true;
                    }
                    $values[ 'topics' ][ $topicId ][ 'link' ] = $this->linker->path->getLink(
                        __CLASS__ . '/topic/' . $topic[ 'id' ]
                    );

                    $values[ 'topics' ][ $topicId ][ 'userProfile' ] = $this->linker->path->getLink(
                        __CLASS__ . '/profile/' . $topic[ 'opener_id' ]
                    );
                }
            }
        }
        return $this->render( $rf, $values, false, false );
    }

    public function moderate() {
        $id = ( int ) $this->linker->path->page[ 'id' ];
        list($values[ 'topic' ]) = $this->db_execute( 'topic_get', array( 'id' => $id ) );
        if( !isset( $_GET[ 'post' ] ) ) {
            if( !$this->getRight( $values[ 'topic' ][ 'section_id' ], self::RIGHTS_SEE ) ) {
                $this->linker->path->error( 403 );
            }
            if( $this->formSubmitted( 'topic_moderate' ) ) {
                $this->cachedPart_remove( 'section_topics_' . $values[ 'topic' ][ 'section_id' ] );
                $this->cachedPart_remove( 'section_subsections_' . $values[ 'topic' ][ 'section_id' ] );
                $this->cachedPart_remove( 'topic_' . $values[ 'topic' ][ 'section_id' ] . '_*' );
                $this->cachedPart_remove( 'topic_' . $id );
                if( isset( $_POST[ 'cancel' ] ) ) {
                    $this->linker->path->redirect( __CLASS__, 'topic', $id );
                }
                $this->search_removeEntry( 'topic', $id );
                if( $_POST[ 'action' ] == 'delete' ) {
                    $this->db_execute( 'topic_moderation_delete', array( 'id' => $id, 'moderator' => $this->userId ) );

                    list($oldDatas) = $this->db_execute( 'topic_get', array( 'id' => $id ) );

                    $this->linker->html->addMessage( $this->getI18n( 'topic_delete_success' ) );

                    /* $this->linker->mailer->default_send(
                      $to,
                      $this->getI18n('moderation_deletion_title'),
                      $this->render('moderation_deletion_content',$value,false,false)
                      );/* */

                    $this->linker->path->redirect( __CLASS__, 'show', $oldDatas[ 'section_id' ] );
                } else {
                    // We should back the previous version up
                    list($oldDatas) = $this->db_execute( 'topic_get', array( 'id' => $id ) );
                    $topicBackup_id = substr( $this->images_salt . $id, 0, 16 );
                    $topicBackup_file = SH_SITE_FOLDER . __CLASS__ . '/moderation/topics/' . $topicBackup_id . '.php';
                    if( file_exists( $topicBackup_file ) ) {
                        include($topicBackup_file);
                    } else {
                        $previous_versions = array( );
                    }
                    $previous_versions[ ] = $oldDatas;
                    $this->helper->writeArrayInFile( $topicBackup_file, 'previous_versions', $previous_versions, false );

                    list($oldDatas) = $this->db_execute(
                        'topic_moderate',
                        array(
                        'id' => $id,
                        'title' => $this->clearUserEntryForDatabase( $_POST[ 'new' ][ 'title' ] ),
                        'content' => $this->clearUserEntryForDatabase( $_POST[ 'new' ][ 'content' ] ),
                        'moderation_id' => $topicBackup_id
                        )
                    );

                    $this->search_addEntry(
                        'topic', $id, '', $_POST[ 'new' ][ 'title' ], $_POST[ 'new' ][ 'content' ]
                    );

                    $this->linker->html->addMessage( $this->getI18n( 'topic_moderate_success' ) );

                    $this->linker->path->redirect( __CLASS__, 'topic', $id );
                }
            }
            $this->linker->html->setTitle( $this->getI18n( 'moderate_topic_title' ) );
            $values[ 'topic' ][ 'image' ] = $this->users_images_path . $values[ 'topic' ][ 'image' ] . '.png';

            $this->render( 'moderate_topic', $values );
        } else {
            if( $this->formSubmitted( 'moderate_post' ) ) {
                $this->cachedPart_remove( 'section_topics_' . $values[ 'topic' ][ 'section_id' ] );
                $this->cachedPart_remove( 'section_subsections_' . $values[ 'topic' ][ 'section_id' ] );
                $this->cachedPart_remove( 'topic_' . $values[ 'topic' ][ 'section_id' ] . '_*' );
                $this->cachedPart_remove( 'topic_' . $id );
                if( isset( $_POST[ 'cancel' ] ) ) {
                    $this->linker->path->redirect( __CLASS__, 'topic', $id );
                }
                $post = $_GET[ 'post' ];
                if( $_POST[ 'action' ] == 'delete' ) {
                    $this->db_execute( 'post_moderation_delete',
                                       array( 'topic_id' => $id, 'post_number' => $post, 'moderator' => $this->userId ) );

                    // We also delete all the posts that answer to this post
                    $this->db_execute( 'post_moderation_delete_answers',
                                       array( 'topic_id' => $id, 'parent' => $post, 'moderator' => $this->userId ) );

                    $this->linker->html->addMessage( $this->getI18n( 'post_delete_success' ) );
                    $this->linker->path->redirect( __CLASS__, 'topic', $id );
                } else {
                    // We should back the previous version up
                    list($oldDatas) = $this->db_execute( 'post_get', array( 'id' => $id, 'post_number' => $post ) );
                    $topicBackup_id = substr( $this->images_salt . $id . '_' . $post, 0, 16 );
                    $topicBackup_file = SH_SITE_FOLDER . __CLASS__ . '/moderation/topics/' . $topicBackup_id . '.php';
                    if( file_exists( $topicBackup_file ) ) {
                        include($topicBackup_file);
                    } else {
                        $previous_versions = array( );
                    }
                    $previous_versions[ ] = $oldDatas;
                    $this->helper->writeArrayInFile( $topicBackup_file, 'previous_versions', $previous_versions, false );

                    list($oldDatas) = $this->db_execute(
                        'post_moderate',
                        array(
                        'id' => $id,
                        'post_number' => $post,
                        'title' => $this->clearUserEntryForDatabase( $_POST[ 'new' ][ 'title' ] ),
                        'text' => $this->clearUserEntryForDatabase( $_POST[ 'new' ][ 'text' ] ),
                        'moderation_id' => $topicBackup_id
                        )
                    );

                    $this->linker->html->addMessage( $this->getI18n( 'post_moderate_success' ) );

                    $this->linker->path->redirect( __CLASS__, 'topic', $id );
                }
                $this->cachedPart_remove( 'section_topics_' . $values[ 'topic' ][ 'section_id' ] );
                $this->cachedPart_remove( 'section_subsections_' . $values[ 'topic' ][ 'section_id' ] );
                $this->cachedPart_remove( 'topic_' . $values[ 'topic' ][ 'section_id' ] . '_*' );
                $this->cachedPart_remove( 'topic_' . $id );
            }
            $this->linker->html->setTitle( $this->getI18n( 'moderate_post_title' ) );
            list($values[ 'post' ]) = $this->db_execute( 'post_get',
                                                         array( 'id' => $id, 'post_number' => $_GET[ 'post' ] ) );
            //$this->render( 'alert_topic', $values );
            $this->render( 'moderate_post', $values );
        }
    }

    public function topic() {
        sh_cache::disable();
        $id = $this->linker->path->page[ 'id' ];
        if( isset( $_GET[ 'notify' ] ) ) {
            if( !empty( $this->userId ) ) {
                if( $_GET[ 'notify' ] == 'true' ) {
                    $this->db_execute( 'notif_set', array( 'topic_id' => $id, 'user_id' => $this->userId ) );
                    $this->linker->html->addMessage( $this->getI18n( 'added_to_notified' ), false );
                } else {
                    $this->db_execute( 'notif_unset', array( 'topic_id' => $id, 'user_id' => $this->userId ) );
                    $this->linker->html->addMessage( $this->getI18n( 'removed_from_notified' ), false );
                }
                $this->linker->path->redirect( __CLASS__, __FUNCTION__, $id );
            }
        }

        list($values[ 'topic' ]) = $this->db_execute( 'topic_get', array( 'id' => $id ) );
        if( !$this->getRight( $values[ 'topic' ][ 'section_id' ], self::RIGHTS_SEE ) ) {
            $this->linker->path->error( 403 );
            return false;
        }

        if( $this->getParam( 'forceUserToCheckConditions', false ) ) {
            $values[ 'conditions' ][ 'file' ] = $this->getParam( 'conditions', '' );
        }

        // We create the breadcrumbs
        $section = $values[ 'topic' ][ 'section_id' ];
        $motherSection = $section;
        $breadcrumbs = array( );
        $nextSection = $section;
        do {
            $section = $nextSection;
            list($sectionDatas) = $this->db_execute( 'section_get', array( 'id' => $section ) );
            array_unshift(
                $breadcrumbs,
                array(
                'id' => $section,
                'name' => $sectionDatas[ 'name' ],
                'link' => $this->linker->path->getLink( __CLASS__ . '/show/' . $section )
                )
            );
            $nextSection = $sectionDatas[ 'parent' ];
        } while( $section != 0 && $cpt++ < 100 );
        $values[ 'nav_levels' ] = $breadcrumbs;

        $values[ 'rights' ][ 'post' ] = $this->getRight( $values[ 'topic' ][ 'section_id' ], self::RIGHTS_POST );
        if( $values[ 'rights' ][ 'post' ] && $this->formSubmitted( 'new_post' ) ) {
            if( !$this->isConnected() ) {
                // We should check if the captcha has been filled successfully
                if( !$this->linker->captcha->verify( 'new_post' ) ) {
                    $this->linker->html->addMessage( $this->getI18n( 'captchaError_message' ) );
                    $error = true;
                }
            } else {
                list($datas) = $this->db_execute( 'user_get_notifications_other_topics', array( 'id' => $this->userId ) );
                if( !empty( $datas[ 'notifications_other_topics' ] ) ) {
                    $alreadyNotified = $this->db_execute( 'notif_is_set',
                                                          array( 'topic_id' => $id, 'user_id' => $this->userId ) );
                    if( $datas[ 'notifications_other_topics' ] == '1' && empty( $alreadyNotified ) ) {
                        $this->db_execute( 'notif_set', array( 'topic_id' => $id, 'user_id' => $this->userId ) );
                        $this->linker->html->addMessage( $this->getI18n( 'added_to_notified_automatically' ), false );
                    }
                }
            }
            // We check if the user has validated the conditions
            if( isset( $values[ 'conditions' ][ 'file' ] ) && !isset( $_POST[ 'accept_conditions' ] ) ) {
                $this->linker->html->addMessage( $this->getI18n( 'please_accept_conditions' ) );
                $error = true;
            }
            // and if there is no forbidden/dangerous words in his topic
            if( $this->isThereForbiddenWords( $_POST[ 'new' ][ 'title' ] . "\n" . $_POST[ 'new' ][ 'text' ] ) ) {
                $this->linker->html->addMessage( $this->getI18n( 'forbiddenWordsFound_message' ) );
                $error = true;
            }
            if( trim( $_POST[ 'new' ][ 'title' ] ) == '' ) {
                $this->linker->html->addMessage( $this->getI18n( 'a_title_is_required' ) );
                $error = true;
            }
            if( trim( strip_tags( $_POST[ 'new' ][ 'text' ] ) ) == '' ) {
                $this->linker->html->addMessage( $this->getI18n( 'a_content_is_required' ) );
                $error = true;
            }
            if( !$error ) {
                list($max) = $this->db_execute( 'post_getMaxPostNumber', array( 'topic_id' => $id ), $qry );
                $post_id = $max[ 'max' ] + 1;
                if( $this->isThereDangerousWords( $_POST[ 'new' ][ 'title' ] . "\n" . $_POST[ 'new' ][ 'text' ] ) ) {
                    $this->alert_auto(
                        $_POST[ 'new' ][ 'title' ], $_POST[ 'new' ][ 'text' ],
                        $this->linker->path->getLink( __CLASS__ . '/topic/' . $id ) . '#post_' . $post_id
                    );
                }

                $this->db_execute(
                    'post_create',
                    array(
                    'title' => $this->clearUserEntryForDatabase( $_POST[ 'new' ][ 'title' ] ),
                    'text' => $this->clearUserEntryForDatabase( $_POST[ 'new' ][ 'text' ] ),
                    'topic_id' => $id,
                    'post_number' => $post_id,
                    'parent' => $_POST[ 'new' ][ 'parent' ],
                    'user_id' => $this->userId
                    )
                );

                $this->db_execute(
                    'post_setHasChildren',
                    array(
                    'topic_id' => $id,
                    'post_number' => $_POST[ 'new' ][ 'parent' ]
                    )
                );

                $this->db_execute(
                    'topic_set_last_post_date', array(
                    'id' => $id )
                );

                $this->cachedPart_remove( 'topic_' . $id . '_.*' );
                $this->cachedPart_remove( 'section_topics_' . $motherSection );

                // We may have to send a mail to the ones that wanted to be notified
                $usersForNotif = $this->db_execute( 'notif_get_users',
                                                    array( 'topic_id' => $id, 'user_id' => $this->userId ) );
                if( !empty( $usersForNotif ) ) {
                    $mailer = $this->linker->mailer->get();
                    $mail = $mailer->em_create();
                    foreach( $usersForNotif as $userForNotif ) {
                        // We get the mail address
                        $datas = $this->linker->user->getOneUserData( $userForNotif[ 'user_id' ], array( 'mail' ) );
                        $mailer->em_addBCC( $mail, $datas[ 'mail' ] );
                    }
                    $baseLink = $this->linker->path->getBaseUri();
                    $replacements = array(
                        '[TOPIC]' => $values[ 'topic' ][ 'title' ],
                        '[TOPIC_LINK]' => $baseLink . $this->linker->path->getLink( __CLASS__ . '/' . __FUNCTION__ . '/' . $id ),
                        '[SITE]' => $baseLink,
                        '[POST_ID]' => 'post_' . $post_id
                    );
                    $title = $this->getI18n( 'notif_mail_title' );
                    $title = str_replace(
                        array_keys( $replacements ), array_values( $replacements ), $title
                    );
                    $mailer->em_addSubject( $mail, $title );
                    $content = $this->getI18n( 'notif_mail_content' );
                    $content = str_replace(
                        array_keys( $replacements ), array_values( $replacements ), $content
                    );
                    $mailer->em_addContent( $mail, $content );
                    $mailer->em_send( $mail );
                }
            } else {
                $values[ 'form' ] = $_POST[ 'new' ];
                if( isset( $_POST[ 'accept_conditions' ] ) ) {
                    $values[ 'form' ][ 'accept_conditions' ] = 'checked';
                }
            }
        }
        $values[ 'topic' ][ 'moderate' ] = false;
        if( $this->getRight( $values[ 'topic' ][ 'section_id' ], self::RIGHTS_MODERATE ) ) {
            $values[ 'topic' ][ 'moderate' ] = true;
            $values[ 'topic' ][ 'moderateLink' ] = $this->linker->path->getLink( __CLASS__ . '/moderate/' . $id );
            $values[ 'topic' ][ 'moveLink' ] = $this->linker->path->getLink( __CLASS__ . '/topic_move/' . $id );
        }

        $values[ 'topic' ][ 'user_groups' ] = $this->getUserGroups( $values[ 'topic' ][ 'opener_id' ] );
        $values[ 'topic' ][ 'user_groups_css' ] = $this->helper->replaceSpecialChars(
            $values[ 'topic' ][ 'user_groups' ]
        );

        $this->linker->html->setTitle(
            'Forum - ' . $values[ 'topic' ][ 'title' ] . ' - ' . $this->linker->datePicker->dateAndTimeToLocal( $values[ 'topic' ][ 'date' ],
                                                                                                                true )
        );

        if( file_exists( $this->users_images_folder . $values[ 'topic' ][ 'image' ] . '.png' ) ) {
            $values[ 'topic' ][ 'image' ] = $this->users_images_path . $values[ 'topic' ][ 'image' ] . '.png';
        } else {
            // We should set the default image
            $values[ 'topic' ][ 'image' ] = '/images/shared/default/default_profile_' . $values[ 'topic' ][ 'gender' ] . '.png';
        }

        $values[ 'posts' ][ 'contents' ] = $this->topic_get_posts( $id, 0, $values[ 'topic' ][ 'moderate' ] );

        $values[ 'posts_list' ] = $this->posts_list;
        $values[ 'answerTo' ][ 'post_number' ] = isset( $_GET[ 'answerTo' ] ) ? $_GET[ 'answerTo' ] : 0;
        $alert_baseUri = $this->linker->path->getLink( __CLASS__ . '/alert/' . $id );
        $values[ 'topic' ][ 'alertLink' ] = $alert_baseUri;

        if( $this->isConnected() && !$this->alias ) {
            $values[ 'user' ][ 'noProfile' ] = true;
            $values[ 'user' ][ 'editProfileLink' ] = $this->linker->path->getLink( 'sh_user/profile/' ) . '?selectedTab=tab_forum';
        } elseif( !$this->isConnected() ) {
            $values[ 'user' ][ 'requiresCaptcha' ] = true;
        }
        $values[ 'links' ][ 'connection' ] = $this->linker->path->getLink( 'user/connect/' ) . '?redirectionAfterConnection=' . $this->linker->path->getPage();

        if( $this->isConnected() ) {
            $is_notified = $this->db_execute( 'notif_is_set', array( 'topic_id' => $id, 'user_id' => $this->userId ) );
            if( empty( $is_notified ) ) {
                $values[ 'user' ][ 'notify_me' ] = true;
                $values[ 'links' ][ 'notify_me' ] = $this->linker->path->uri . '?notify=true';
            } else {
                $values[ 'user' ][ 'dont_notify_me' ] = true;
                $values[ 'links' ][ 'notify_me' ] = $this->linker->path->uri . '?notify=false';
            }
        }
        $values[ 'user' ][ 'connected' ] = $this->isConnected();
        $this->render( 'topic', $values );
    }

    public function topic_move() {
        list($values[ 'topic' ]) = $this->db_execute( 'topic_get', array( 'id' => $id ) );
        if( !$this->getRight( $values[ 'topic' ][ 'section_id' ], self::RIGHTS_MODERATE ) ) {
            $this->linker->path->error( 403 );
        }
        sh_cache::disable();
        $id = ( int ) $this->linker->path->page[ 'id' ];
        if( $this->formSubmitted( 'topic_move' ) ) {
            $this->db_execute( 'topic_changeSection', array( 'section_id' => $_POST[ 'newSection' ], 'id' => $id ) );

            $this->linker->html->setTitle( $this->getI18n( 'topic_moved_successfully' ) );
            $this->linker->path->redirect( __CLASS__, 'show', $_POST[ 'newSection' ] );
        }

        $this->linker->html->setTitle( $this->getI18n( 'topic_move_title' ) );

        list($values[ 'topic' ]) = $this->db_execute( 'topic_get', array( 'id' => $id ) );
        // We list the categories
        $values[ 'categories' ] = $this->db_execute( 'sections_getContainingTopics' );
        if( !empty( $values[ 'categories' ] ) ) {
            foreach( $values[ 'categories' ] as $catId => $cat ) {
                if( $cat[ 'id' ] == $values[ 'topic' ][ 'section_id' ] ) {
                    $values[ 'categories' ][ $catId ][ 'state' ] = 'selected';
                }
            }
        }
        $this->render( 'topic_move', $values );
    }

    protected function topic_get_posts( $topic, $parent = 0, $moderate = false ) {
        // We get the posts
        $posts = $this->db_execute( 'posts_get', array( 'id' => $topic, 'parent' => $parent ) );

        // We create the chain of posts
        $ret = '';
        if( is_array( $posts ) ) {
            $moderate_baseUri = $this->linker->path->getLink( __CLASS__ . '/moderate/' . $topic );
            $alert_baseUri = $this->linker->path->getLink( __CLASS__ . '/alert/' . $topic ) . '?post=';
            foreach( $posts as $postNum => $post ) {
                $values[ 'post' ] = $post;

                if( file_exists( $this->users_images_folder . $values[ 'post' ][ 'image' ] . '.png' ) ) {
                    $values[ 'post' ][ 'image' ] = $this->users_images_path . $values[ 'post' ][ 'image' ] . '.png';
                } else {
                    // We should set the default image
                    $values[ 'post' ][ 'image' ] = '/images/shared/default/default_profile_' . $values[ 'post' ][ 'gender' ] . '.png';
                }
                $values[ 'post' ][ 'date' ] = $this->linker->datePicker->dateAndTimeToLocal( $values[ 'post' ][ 'date' ],
                                                                                             true );
                $values[ 'post' ][ 'answerLink' ] = $this->linker->path->uri . '?answerTo=' . $post[ 'post_number' ];
                $values[ 'post' ][ 'alertLink' ] = $alert_baseUri . $post[ 'post_number' ];
                if( $moderate ) {
                    $values[ 'post' ][ 'moderate' ] = true;
                    $values[ 'post' ][ 'moderateLink' ] = $moderate_baseUri . '?post=' . $post[ 'post_number' ];
                }

                $values[ 'post' ][ 'messageLink' ] = $this->linker->path->getLink(
                    __CLASS__ . '/message/' . $post[ 'user_id' ]
                );

                $values[ 'post' ][ 'signature' ] = $post[ 'signature' ];

                if( $post[ 'hasChildren' ] ) {
                    // We get the children
                    $this->post_active_deepness++;
                    $values[ 'children' ][ 'content' ] = $this->topic_get_posts( $topic, $post[ 'post_number' ],
                                                                                 $moderate );
                    $this->post_active_deepness--;
                } else {
                    $values[ 'children' ][ 'content' ] = '';
                }
                $values[ 'post' ][ 'user_groups' ] = $this->getUserGroups( $values[ 'post' ][ 'user_id' ] );
                $values[ 'post' ][ 'user_groups_css' ] = $this->helper->replaceSpecialChars(
                    $values[ 'post' ][ 'user_groups' ]
                );


                $ret .= $this->render( 'post', $values, false, false );
            }
        }
        return $ret;
    }

    public function message() {
        sh_cache::disable();
        $id = $this->linker->path->page[ 'id' ];

        $from = $this->userId;
        if( $id == 0 ) {
            // This should be a quickResponse
            $quickResponse = $_GET[ 'quickResponse' ];
            list($datas) = $this->db_execute( 'message_getFromQuickResponse', array( 'quickResponse' => $quickResponse ) );
            $from = $datas[ 'to' ];
            $id = $datas[ 'from' ];
            $allowDisconnected = true;
        }
        if( $this->formSubmitted( 'message_write' ) ) {
            // We should send the message
            $quickResponse = md5( microtime() );
            $quickResponse .= md5( microtime() . $quickResponse );

            $quickResponseLink = $this->linker->path->getBaseUri();
            $quickResponseLink .= $this->linker->path->getLink( __CLASS__ . '/' . __FUNCTION__ . '/0' );
            $quickResponseLink .= '?quickResponse=' . $quickResponse;

            $mail = $this->linker->mailer->get();
            $mailId = $mail->em_create();
            $address = $this->linker->user->getOneUserData( $id, array( 'mail' ) );
            $address = $address[ 'mail' ];
            $mail->em_addAddress( $mailId, $address );

            $alias = $this->getAlias( $from );
            $mail->em_addSubject(
                $mailId, $this->linker->path->getBaseUri() . ' - ' . $this->getI18n( 'message_titles' ) . $alias
            );
            $mailContent = $this->getI18n( 'message_intros' ) . nl2br( $_POST[ 'content' ] );
            $mailContent .= $this->getI18n( 'message_answer' ) . '<a href="' . $quickResponseLink . '">' . $quickResponseLink . '</a>';
            $mail->em_addContent(
                $mailId, $mailContent
            );
            if( $mail->em_send( $mailId ) ) {
                $this->linker->html->addMessage( 'Votre message a été envoyé avec succès!' );
                $this->db_execute(
                    'message_add',
                    array(
                    'from' => $from,
                    'to' => $id,
                    'message' => $this->clearUserEntryForDatabase( $_POST[ 'content' ] ),
                    'quickResponse' => $quickResponse
                    )
                );
            }
        }
        $this->linker->html->setTitle( $this->getI18n( 'message_title' ) );
        $values[ 'link' ][ 'back' ] = $this->linker->path->getHistory( 1 );
        if( !$this->isConnected() && !$allowDisconnected ) {
            $this->render( 'message_youshouldbeconnected', $values );
        } else {
            $values[ 'dest' ][ 'alias' ] = $this->getAlias( $id );
            $this->render( 'message_write', $values );
        }
    }

    protected function getUserGroups( $user ) {
        $groups_db = $this->db_execute( 'user_get_groups', array( 'user_id' => $user ) );
        foreach( $groups_db as $group ) {
            list($groupName) = $this->db_execute( 'group_getName', array( 'id' => $group[ 'group_id' ] ) );
            $groups .= ' ' . $groupName[ 'name' ];
        }
        return $groups;
    }

    protected function alert_auto( $title, $content, $link ) {
        $mailer = $this->linker->mailer->get();
        $mailObject = $mailer->em_create();

        $mails = $this->getParam( 'alertMails', array( ) );
        foreach( $mails as $oneMail ) {
            $mailer->em_addBCC( $mailObject, $oneMail );
        }

        $rootDir = $this->linker->path->getBaseUri();
        $mailer->em_addSubject(
            $mailObject, $this->getI18n( 'alert_auto_moderator_mail_title' ) . $rootDir
        );

        $values[ 'datas' ][ 'title' ] = strip_tags( $title );
        $values[ 'datas' ][ 'content' ] = strip_tags( $content );
        $values[ 'datas' ][ 'link' ] = $rootDir . $link;

        $dangerousWords = $this->dangerousWordsFound;
        $done = array( );
        foreach( $dangerousWords as $dangerousWord ) {
            if( !in_array( $dangerousWord, $done ) ) {
                $done[ ] = $dangerousWord;
                $values[ 'dangerousWords' ][ ][ 'word' ] = $dangerousWord;

                $searched = '/([ \.,;:!?])(' . trim( $dangerousWord ) . ')/i';
                $replaced = '<b>$1$2</b>';
                $values[ 'datas' ][ 'title' ] = preg_replace( $searched, $replaced, $values[ 'datas' ][ 'title' ] );
                $values[ 'datas' ][ 'content' ] = preg_replace( $searched, $replaced, $values[ 'datas' ][ 'content' ] );
            }
        }

        $content = $this->render( 'alert_auto_mail', $values, false, false );

        $mailer->em_addContent( $mailObject, $content );
        $mailer->em_send( $mailObject );
        return true;
    }

    public function alert() {
        $id = ( int ) $this->linker->path->page[ 'id' ];

        list($topic) = $this->db_execute( 'topic_get', array( 'id' => $id ) );
        if( !$this->getRight( $topic[ 'section_id' ], self::RIGHTS_SEE ) ) {
            $this->linker->path->error( 403 );
            return false;
        }

        if( $this->formSubmitted( 'alert_topic' ) ) {
            $mailer = $this->linker->mailer->get();
            $mailObject = $mailer->em_create();

            $mails = $this->getParam( 'alertMails', array( ) );
            foreach( $mails as $oneMail ) {
                $mailer->em_addBCC( $mailObject, $oneMail );
            }

            $rootDir = $this->linker->path->getBaseUri();
            $mailer->em_addSubject(
                $mailObject, $this->getI18n( 'alert_moderator_mail_title' ) . $rootDir
            );

            $values[ 'topic' ] = $topic;

            $values[ 'reason' ][ 'text' ] = $_POST[ 'reason' ];
            $values[ 'user' ][ 'ip' ] = $_SERVER[ 'REMOTE_ADDR' ];
            if( $this->isConnected() ) {
                $values[ 'user' ][ 'connected' ] = true;
                $values[ 'user' ][ 'id' ] = $this->userId;
                $values[ 'user' ][ 'alias' ] = $this->alias;
            }
            $values[ 'topic' ][ 'date' ] = $this->linker->datePicker->dateAndTimeToLocal( $topic[ 'date' ], true );
            $values[ 'topic' ][ 'link' ] = $rootDir . $this->linker->path->getLink( __CLASS__ . '/topic/' . $id );

            $content = $this->render( 'alert_topic_mail', $values, false, false );

            $mailer->em_addContent( $mailObject, $content );

            if( !$mailer->em_send( $mailObject ) ) {
                // Error sending the email
                echo 'Erreur dans l\'envoi du mail de validation...';
            } else {
                $this->linker->html->addMessage( 'Votre demande de modération a bien été prise en compte' );
                $this->linker->path->redirect( __CLASS__, 'topic', $id );
            }
        }
        if( $this->formSubmitted( 'alert_post' ) ) {
            $mailer = $this->linker->mailer->get();
            $mailObject = $mailer->em_create();

            $mails = $this->getParam( 'alertMails', array( ) );
            foreach( $mails as $oneMail ) {
                $mailer->em_addBCC( $mailObject, $oneMail );
            }

            $rootDir = $this->linker->path->getBaseUri();
            $mailer->em_addSubject(
                $mailObject, $this->getI18n( 'alert_moderator_mail_title' ) . $rootDir
            );

            list($values[ 'post' ]) = $this->db_execute( 'post_get',
                                                         array( 'id' => $id, 'post_number' => $_GET[ 'post' ] ) );

            $values[ 'reason' ][ 'text' ] = $_POST[ 'reason' ];
            $values[ 'user' ][ 'ip' ] = $_SERVER[ 'REMOTE_ADDR' ];
            if( $this->isConnected() ) {
                $values[ 'user' ][ 'connected' ] = true;
                $values[ 'user' ][ 'id' ] = $this->userId;
                $values[ 'user' ][ 'alias' ] = $this->alias;
            }

            $values[ 'post' ][ 'date' ] = $this->linker->datePicker->dateAndTimeToLocal( $values[ 'post' ][ 'date' ],
                                                                                         true );
            $values[ 'post' ][ 'link' ] = $rootDir . $this->linker->path->getLink( __CLASS__ . '/topic/' . $id );
            $values[ 'post' ][ 'link' ] .= '#post_' . $_GET[ 'post' ];

            $content = $this->render( 'alert_post_mail', $values, false, false );

            $mailer->em_addContent( $mailObject, $content );

            if( !$mailer->em_send( $mailObject ) ) {
                // Error sending the email
                echo 'Erreur dans l\'envoi du mail de validation...';
            } else {
                $this->linker->html->addMessage( 'Votre demande de modération a bien été prise en compte' );
                $this->linker->path->redirect( __CLASS__, 'topic', $id );
            }
        }


        $this->linker->html->setTitle( $this->getI18n( 'alert_title' ) );


        if( isset( $_GET[ 'post' ] ) ) {
            list($values[ 'post' ]) = $this->db_execute( 'post_get',
                                                         array( 'id' => $id, 'post_number' => $_GET[ 'post' ] ) );
            $this->render( 'alert_post', $values );
        } else {
            $values[ 'topic' ] = $topic;
            $this->render( 'alert_topic', $values );
        }
    }

    public function admin_getMenuContent() {
        $adminMenu[ 'Forum' ][ ] = array(
            'link' => 'forum/manage/', 'text' => 'Gérer le forum', 'icon' => 'picto_details.png'
        );
        $adminMenu[ 'Forum' ][ ] = array(
            'link' => 'forum/usersList/', 'text' => 'Liste des utilisateurs', 'icon' => 'picto_list.png'
        );

        return $adminMenu;
    }

    public function manage() {
        $this->onlyAdmin( true );
        if( $this->formSubmitted( 'forum_manage' ) ) {
            $this->setParam( 'active', isset( $_POST[ 'active' ] ) );

            $this->setParam( 'forceUserToCheckConditions', isset( $_POST[ 'forceUserToCheckConditions' ] ) );
            $this->setParam( 'conditions', $_POST[ 'conditions' ] );

            $alerts = array( );
            $_POST[ 'alertMails' ] = str_replace( array( ' ', ',', "\r" ), "\n", $_POST[ 'alertMails' ] );
            foreach( explode( "\n", $_POST[ 'alertMails' ] ) as $mail ) {
                if( trim( $mail ) != '' ) {
                    $alerts[ ] = trim( $mail );
                }
            }
            $this->setParam( 'alertMails', $alerts );

            $forbidden = explode( "\n", $_POST[ 'words' ][ 'forbidden' ] );

            $forbiddenWords = array( );
            foreach( $forbidden as $oneForbiddenWord ) {
                if( trim( $oneForbiddenWord ) != '' ) {
                    $word = ' `' . $this->removeSpecialChars( trim( $oneForbiddenWord ) ) . '` ';
                    $forbiddenWords[ ] = str_replace( '*', '.*', $word );
                }
            }
            $this->setParam( 'forbiddenWords', $forbiddenWords );

            $dangerous = explode( "\n", $_POST[ 'words' ][ 'dangerous' ] );
            $dangerousWords = array( );
            foreach( $dangerous as $oneDangerousWord ) {
                if( trim( $oneDangerousWord ) != '' ) {
                    $word = ' `' . $this->removeSpecialChars( trim( $oneDangerousWord ) ) . '` ';
                    $dangerousWords[ ] = str_replace( '*', '.*', $word );
                }
            }
            $this->setParam( 'dangerousWords', $dangerousWords );

            $this->writeParams();
        }

        if( $this->getParam( 'active' ) ) {
            $values[ 'active' ][ 'checked' ] = 'checked';
        }
        if( $this->getParam( 'forceUserToCheckConditions' ) ) {
            $values[ 'forceUserToCheckConditions' ][ 'checked' ] = 'checked';
        }
        $values[ 'conditions' ][ 'file' ] = $this->getParam( 'conditions' );
        if( is_array( $this->getParam( 'alertMails' ) ) ) {
            $values[ 'alert' ][ 'mails' ] = implode( "\n", $this->getParam( 'alertMails' ) );
        }
        if( is_array( $this->getParam( 'forbiddenWords' ) ) ) {
            $values[ 'words' ][ 'forbidden' ] = implode( "\n", $this->getParam( 'forbiddenWords' ) );
            $values[ 'words' ][ 'forbidden' ] = str_replace( array( ' `', '` ', '.*' ), array( '', '', '*' ),
                                                             $values[ 'words' ][ 'forbidden' ] );
        }
        if( is_array( $this->getParam( 'dangerousWords' ) ) ) {
            $values[ 'words' ][ 'dangerous' ] = implode( "\n", $this->getParam( 'dangerousWords' ) );
            $values[ 'words' ][ 'dangerous' ] = str_replace( array( ' `', '` ', '.*' ), array( '', '', '*' ),
                                                             $values[ 'words' ][ 'dangerous' ] );
        }

        $this->render( 'manage', $values );
    }

    protected function isThereForbiddenWords( $textToCheck ) {
        $forbiddenWords = $this->getParam( 'forbiddenWords', array( ) );
        return $this->checkForSpecialWords( $textToCheck, $forbiddenWords, false );
    }

    protected function isThereDangerousWords( $textToCheck ) {
        $dangerousWords = $this->getParam( 'dangerousWords', array( ) );
        return $this->checkForSpecialWords( $textToCheck, $dangerousWords, 'dangerousWordsFound' );
    }

    protected function checkForSpecialWords( $textToCheck, $specialWords, $collectIn = false ) {
        $textToCheck = ' ' . $this->removeSpecialChars( $textToCheck ) . ' ';
        $textToCheck = strip_tags( $textToCheck );
        $rep = false;
        foreach( $specialWords as $specialWord ) {
            if( preg_match( $specialWord, $textToCheck ) ) {
                if( !$collectIn ) {
                    return true;
                } else {
                    $rep = true;
                    $cleanedWord = str_replace( array( '.*', '`' ), '', $specialWord );
                    array_push( $this->$collectIn, $cleanedWord );
                }
            }
        }
        return $rep;
    }

    protected function removeSpecialChars( $text ) {
        return strtolower( preg_replace(
                    array( '`[ø]`', '`([àäâã])`', '`([éêèëẽ])`', '`([îïĩ])`', '`([ôöõ])`', '`([ûüùũ])`', '`([ÿ])`', '`([ñ])`', '`[ç]`', '`[æ]`', '`[œ]`', '`[ ]+\.\,\!\?\:\;`' ),
                    array( '', 'a', 'e', 'i', 'o', 'u', 'y', 'n', 'c', 'ae', 'oe', ' ' ), $text
                ) );
    }

    public function manageGroup() {
        $this->onlyAdmin( true );
        $id = ( int ) $this->linker->path->page[ 'id' ];

        if( $this->formSubmitted( 'manageGroup' ) ) {
            // We may have to add an user to this group (if the + button hasn't been pressed)
            if( !empty( $_POST[ 'autocomplete_parameter' ] ) ) {
                $this->addUserToGroup_byAlias( $id, $_POST[ 'autocomplete_parameter' ] );
            }
            if( trim( $_POST[ 'name' ] ) != '' ) {
                // We should save the group name
                $this->db_execute( 'group_save', array( 'name' => $_POST[ 'name' ], 'id' => $id ) );
            }
        }

        $values[ 'autocompleter' ][ 'url' ] = $this->linker->path->getLink(
            __CLASS__ . '/get_users_name/'
        );

        if( $id > 0 ) {
            list($values[ 'group' ]) = $this->db_execute(
                'group_getName', array( 'id' => $id )
            );

            // We also get the users who are part of this group
            $values[ 'users' ] = $this->db_execute(
                'group_get_users', array( 'group_id' => $id, 'count' => 50 ), $qry
            );
        } else {
            $values[ 'group' ][ 'name' ] = $this->getI18n( 'action_manageGroup_0' );
        }

        $values[ 'links' ][ 'addUserToGroup' ] = $this->linker->path->getLink( __CLASS__ . '/addUserToGroup/' . $id );
        $values[ 'links' ][ 'removeUserFromGroup' ] = $this->linker->path->getLink( __CLASS__ . '/removeUserFromGroup/' . $id );

        $this->render( 'manageGroup', $values );
    }

    public function addUserToGroup() {
        $this->onlyAdmin( true );
        $id = ( int ) $this->linker->path->page[ 'id' ];
        echo $this->addUserToGroup_byAlias( $id, $_POST[ 'user' ] );
    }

    protected function addUserToGroup_byAlias( $group, $user ) {
        list($user) = $this->db_execute( 'user_get_by_alias', array( 'alias' => $user ) );
        if( empty( $user ) ) {
            return 'NOT_FOUND';
        } else {
            $this->db_execute( 'group_insert_user', array( 'user_id' => $user[ 'id' ], 'group_id' => $group ), $qry );
            return $user[ 'id' ];
        }
    }

    public function removeUserFromGroup() {
        $this->onlyAdmin( true );
        $id = ( int ) $this->linker->path->page[ 'id' ];
        $this->db_execute( 'group_remove_user', array( 'user_id' => $_POST[ 'user' ], 'group_id' => $id ), $qry );

        echo 'OK';
    }

    public function get_users_name() {
        $this->onlyAdmin( true );
        $users = $this->db_execute( 'users_get_by_part', array( 'text' => addslashes( $_POST[ 'user_entry' ] ) ), $qry );
        if( is_array( $users ) ) {
            echo '<ul>';
            foreach( $users as $user ) {
                echo '<li>' . $user[ 'alias' ] . '<span class="informal"> (' . $user[ 'id' ] . ')</span></li>';
            }
            echo '</ul>';
        }
    }

    public function user_getAccountTabs() {
        $ret = array( );
        $profile = $this->profileEditor();
        if( $profile ) {
            $ret[ 'forum' ] = array(
                'title' => 'Forum',
                'content' => $profile,
                'uid' => 'tab_forum'
            );
        }
        return $ret;
    }

    public function profile() {
        if( !$this->getParam( 'active', false ) ) {
            return false;
        }
        $id = $this->linker->path->page[ 'id' ];
        list($userDatas) = $this->db_execute( 'profile_get', array( 'id' => $id ) );
        $image = $this->users_images_path . $userDatas[ 'image' ];

        $values[ 'profile' ] = $userDatas;
        if( file_exists( $this->users_images_folder . $values[ 'profile' ][ 'image' ] . '.png' ) ) {
            $values[ 'profile' ][ 'image' ] = $this->users_images_path . $values[ 'profile' ][ 'image' ] . '.png';
        } else {
            // We should set the default image
            $values[ 'profile' ][ 'image' ] = '/images/shared/default/default_profile_' . $values[ 'profile' ][ 'gender' ] . '.png';
        }

        if( $id == $this->userId ) {
            $this->linker->html->setTitle( $this->getI18n( 'profile_myprofile_title' ) );
            $values[ 'profile' ][ 'this_is_mine' ] = true;
        } else {
            $this->linker->html->setTitle(
                str_replace( '[ALIAS]', $userDatas[ 'alias' ], $this->getI18n( 'profile_someprofile_title' ) )
            );
        }
        $values[ 'groups' ] = $this->db_execute( 'user_get_groups', array( 'user_id' => $id ) );
        list($topicsCount) = $this->db_execute( 'user_count_topics', array( 'user_id' => $id ) );
        $values[ 'stats' ][ 'topics' ] = $topicsCount[ 'count' ];
        list($postsCount) = $this->db_execute( 'user_count_posts', array( 'user_id' => $id ) );
        $values[ 'stats' ][ 'posts' ] = $postsCount[ 'count' ];
        $values[ 'stats' ][ 'last' ] = max( $postsCount[ 'last' ], $topicsCount[ 'last' ] );
        $values[ 'profile' ][ 'gender_' . $values[ 'profile' ][ 'gender' ] ] = 'selected';
        $extendedFormClasses = $this->get_shared_methods( 'special_groups_forms' );
        $groups = array( );
        if( is_array( $values[ 'groups' ] ) ) {
            foreach( $values[ 'groups' ] as $oneGroup ) {
                $groups[ $oneGroup[ 'group_id' ] ] = $oneGroup[ 'group_id' ];
            }
        }

        $values[ 'links' ][ 'privateMessage' ] = $this->linker->path->getLink(
            __CLASS__ . '/message/' . $id
        );
        foreach( $extendedFormClasses as $extendedFormClass ) {
            $content = $this->linker->$extendedFormClass->forum_get_special_user_datas( $id, true, $groups );
            $values[ 'externalDatas' ][ $extendedFormClass ] = $content;
            $values[ 'externalDatasLoop' ][ $extendedFormClass ][ 'content' ] = $content;
        }

        $this->render( 'profile', $values );
    }

    protected function profileEditor( $userId = null ) {
        if( !$this->getParam( 'active', false ) ) {
            return false;
        }
        if( is_null( $userId ) && !$this->isConnected() ) {
            return false;
        } elseif( !is_null( $userId ) && !$this->isAdmin() ) {
            return false;
        } elseif( is_null( $userId ) ) {
            $userId = $this->linker->user->get( 'id' );
        }

        $imageName = substr( md5( $this->images_salt . $userId ), 5, 10 );

        $thereAreExternalClassesForms = false;
        // We have to check if any other class has to fill a special additionnal form for this user's groups
        $groups = $this->userGroups;
        $values[ 'external_classes' ][ 'form' ] = '';
        if( !empty( $groups ) ) {
            $extendedFormClasses = $this->get_shared_methods( 'special_groups_forms' );
            foreach( $extendedFormClasses as $extendedFormClass ) {
                $ret = $this->linker->$extendedFormClass->forum_get_special_groups_form( $groups );
                if( $ret ) {
                    $thereAreExternalClassesForms = true;
                    $values[ 'external_classes' ][ 'form' ] .= $ret;
                }
            }
        }

        if( $this->formSubmitted( 'forum_profile' ) ) {
            $error = $_FILES[ 'forum_image' ][ 'error' ];
            if( $error === UPLOAD_ERR_OK ) {
                // We check the image type
                $allowed = array( 'image/png', 'image/jpeg', 'image/jpg', 'image/gif' );
                if( in_array( $_FILES[ 'forum_image' ][ 'type' ], $allowed ) ) {
                    // There is a new image, so we save it
                    $tmp_name = $_FILES[ "forum_image" ][ "tmp_name" ];
                    $name = $_FILES[ "forum_image" ][ "name" ];
                    $ext = strtolower( array_pop( explode( '.', $name ) ) );
                    move_uploaded_file( $tmp_name, SH_TEMPIMAGES_FOLDER . $imageName . '.' . $ext );
                    $this->linker->browser->addImage(
                        'forum', SH_TEMPIMAGES_FOLDER . $imageName . '.' . $ext
                    );
                } else {
                    $this->linker->html->addMessage( 'Le fichier doit être une image JPG, PNG ou GIF.' );
                }
            } elseif( $error == UPLOAD_ERR_INI_SIZE || $error == UPLOAD_ERR_FORM_SIZE ) {
                $this->linker->html->addMessage( 'Le fichier est trop lourd.' );
            } elseif( $error != UPLOAD_ERR_NO_FILE ) {
                $this->linker->html->addMessage( 'Il y a eu un problème lors de l\'envoi de l\'image.' );
            }
            if( $thereAreExternalClassesForms ) {
                foreach( $extendedFormClasses as $extendedFormClass ) {
                    $this->linker->$extendedFormClass->forum_get_special_groups_form_results( $groups );
                }
            }
            if( trim( $_POST[ 'forum_alias' ] ) == '' ) {
                $this->linker->html->addMessage( 'Le champs Alias étant vide, votre nom sera utilisé.' );
                $_POST[ 'forum_alias' ] = $this->linker->user->get( 'name' ) . ' ' . $this->linker->user->get( 'lastName' );
            }
            $datas = array(
                'id' => $userId,
                'alias' => addslashes( $_POST[ 'forum_alias' ] ),
                'gender' => $_POST[ 'forum_gender' ],
                'image' => $imageName,
                'signature' => nl2br( str_replace( array( '<', '>' ), array( '&#60;', '&#62;' ),
                                                   addslashes( $_POST[ 'forum_signature' ] ) ) ),
                'profile_text' => addslashes( $_POST[ 'forum_profile_text' ] ),
                'notifications_my_topics' => isset( $_POST[ 'forum_notifications_my_topics' ] ) ? '1' : '0',
                'notifications_other_topics' => isset( $_POST[ 'forum_notifications_other_topics' ] ) ? '1' : '0'
            );
            // We save the datas
            $this->db_execute( 'profile_save', $datas, $qry );
        }
        list($userDatas) = $this->db_execute( 'profile_get', array( 'id' => $userId ) );
        if( empty( $userDatas ) || false ) {
            // The user has never filled his datas, so we create some by default
            $datas = array(
                'id' => $userId,
                'alias' => $this->linker->user->get( 'name' ) . ' ' . $this->linker->user->get( 'lastName' ),
                'image' => $imageName,
                'gender' => 'unset',
                'signature' => '',
                'subscription_date' => date( 'Y-m-d H:i:s' ),
                'notifications_my_topics' => '0',
                'notifications_other_topics' => '0'
            );
            $this->db_execute( 'profile_create', $datas );
            list($userDatas) = $this->db_execute( 'profile_get', array( 'id' => $userId ) );
        }

        $image = $this->users_images_path . $userDatas[ 'image' ];

        $values[ 'profile' ] = $userDatas;
        $values[ 'profile' ][ 'image' ] = $this->users_images_path . $values[ 'profile' ][ 'image' ] . '.png';

        if( $values[ 'profile' ][ 'notifications_my_topics' ] ) {
            $values[ 'profile' ][ 'notifications_my_topics' ] = 'checked';
        } else {
            $values[ 'profile' ][ 'notifications_my_topics' ] = '';
        }
        if( $values[ 'profile' ][ 'notifications_other_topics' ] ) {
            $values[ 'profile' ][ 'notifications_other_topics' ] = 'checked';
        } else {
            $values[ 'profile' ][ 'notifications_other_topics' ] = '';
        }
        $values[ 'profile' ][ 'gender_' . $values[ 'profile' ][ 'gender' ] ] = 'selected';
        $values[ 'profile' ][ 'signature' ] = str_replace( '<br />', '', $values[ 'profile' ][ 'signature' ] );

        return $this->render( 'profileEditor', $values, false, false );
    }

    public function master_getMenuContent() {
        $masterMenu = array( );
        return $masterMenu;
    }

    /**
     * This method allows other classes to create users groups.
     * @param str $name The name of the group to create.
     * @return int The id of the newly created group
     */
    public function create_external_group( $name ) {
        $this->db_execute( 'group_create', array( 'name' => addslashes( stripslashes( $name ) ) ) );
        $id = $this->db_insertId();
        return $id;
    }

    public function addGroup() {
        $this->onlyAdmin( true );
        $name = trim( $_POST[ 'name' ] );
        if( empty( $name ) ) {
            $rep = array( 'error' => 'EMPTY_STRING' );
        } else {
            $this->db_execute( 'group_create', array( 'name' => addslashes( $_POST[ 'name' ] ) ) );
            $error = $this->db_lastError();
            if( $error[ 'id' ] == 1062 ) {
                $rep = array( 'error' => 'DUPLICATE' );
            } else {
                $id = $this->db_insertId();
                $rep = array( 'id' => $id, 'name' => $name, 'link' => $this->linker->path->getLink( __CLASS__ . '/manageGroup/' . $id ) );
            }
        }
        echo 'rep = ' . json_encode( $rep ) . ';';
        return true;
    }

    public function usersList() {
        $this->onlyAdmin( true );

        $values[ 'groups' ] = $this->db_execute( 'groups_get_all', array( ) );
        if( is_array( $values[ 'groups' ] ) ) {
            foreach( $values[ 'groups' ] as $groupNum => $group ) {
                if( !in_array( $group[ 'id' ], array( self::GROUPS_CONNECTED, self::GROUPS_DISCONNECTED ) ) ) {
                    $values[ 'groups' ][ $groupNum ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/manageGroup/' . $group[ 'id' ] );
                } else {
                    unset( $values[ 'groups' ][ $groupNum ] );
                }
            }
        }
        $values[ 'links' ][ 'addGroup' ] = $this->linker->path->getLink( __CLASS__ . '/addGroup/' );
        $this->render( 'usersList', $values );
    }

    /**
     * Creates or gets a name for a page.<br />
     * This name can be used to describe it, like a kind of title.<br />
     * To build good names, the classes that create main contents should
     * extend this function, or put a reverse in the "uri" table of the database
     * @param string $action
     * Action of the page (second part of the page name, like show in shop/show/17)
     * @param integer $id
     * optional - defaults to null<br />
     * Id of the page (third part of the page name, like 17 in shop/show/17)
     * @return string New name of the page
     */
    public function getPageName( $action, $id = null, $forUrl = false ) {
        $name = $this->getI18n( 'action_' . $action );
        if( $action == 'manageGroup' ) {
            if( $id == 0 ) {
                return $this->getI18n( 'action_manageGroup_0' );
            }
            list($name) = $this->db_execute( 'group_getName', array( 'id' => $id ) );
            $name = $this->getI18n( $name[ 'name' ] );
            if( $forUrl ) {
                return $name;
            }
            $name = str_replace(
                array( '{id}', '{link}', '{name}' ), array( $id, $link, $name ), $name
            );
        }
        if( $action == 'topic_move' ) {
            $name = 'move';
        }
        if( $action == 'topic' ) {
            list($title) = $this->db_execute( 'topic_getTitle', array( 'id' => $id ) );
            $title = $title[ 'title' ];
            if( $forUrl ) {
                return $title;
            }
            $title = str_replace(
                array( '{id}', '{link}', '{title}' ), array( $id, $link, $title ), $name
            );
        }
        if( $action == 'show' ) {
            if( $id == 0 ) {
                return 'Racine du forum';
            }
        }

        if( $name != '' ) {
            return $name;
        }
        return '';
    }

    /**
     * Gets the list of the contents types that the searcher should search in.
     * @return array An array containing the list of search types.
     */
    public function searcher_getScope() {
        return array(
            'scope' => 'forum',
            'name' => $this->getI18n( 'search_forumTitle' )
        );
    }

    /**
     * Renders the results of a research (should be called by sh_searcher).
     * @param str $method The method that should be called to access the page
     * of the result
     * @param array $elements An array containing the list of the ids of the
     * elements that are to be shown in the results.
     * @return str The rendered xml for the results.
     */
    public function searcher_showResults( $method, $elements ) {
        $this->debug( __FUNCTION__ . '(' . $method . ',' . print_r( $elements, true ) . ');', 2, __LINE__ );

        arsort( $elements );
        if( $method == 'show' ) {
            // This is a category of subjects, or a category of sub-categories
            foreach( $elements as $num => $element ) {
                list($values[ 'sections' ][ $num ]) = $this->db_execute( 'section_get', array( 'id' => $element ) );
                $values[ 'sections' ][ $num ][ 'date' ] = $this->linker->datePicker->dateAndTimeToLocal(
                    $values[ 'sections' ][ $num ][ 'date' ], true
                );
                $values[ 'sections' ][ $num ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/show/' . $element );
            }
            $content = $this->render( 'searcher_result_sections', $values, false, false );
            return array( 'name' => 'Catégories du forum', 'content' => $content );
        } else {
            // This is a topic
            foreach( $elements as $num => $element ) {
                list($values[ 'topics' ][ $num ]) = $this->db_execute( 'topic_get', array( 'id' => $element ) );
                $values[ 'topics' ][ $num ][ 'date' ] = $this->linker->datePicker->dateAndTimeToLocal(
                    $values[ 'topics' ][ $num ][ 'date' ], true
                );
                $values[ 'topics' ][ $num ][ 'link' ] = $this->linker->path->getLink( __CLASS__ . '/topic/' . $element );
                list($count) = $this->db_execute( 'topic_count_posts', array( 'topic_id' => $element ) );
                $values[ 'topics' ][ $num ][ 'posts_count' ] = $count[ 'count' ];
            }
            $content = $this->render( 'searcher_result_topics', $values, false, false );
            return array( 'name' => 'Sujets du forum', 'content' => $content );
        }

        return 'ygyg';

        // We prepare the rendering
        foreach( $elements as $element ) {
            list($element) = $this->db_execute(
                'getShort', array( 'id' => $element )
            );
            $values[ 'contents' ][ ] = $element;
        }
        foreach( $values[ 'contents' ] as &$element ) {
            $element[ 'link' ] = $this->linker->path->getLink(
                $this->shortClassName . '/show/' . $element[ 'id' ]
            );
            $element[ 'title' ] = $this->getI18n( $element[ 'title' ] );
            $element[ 'summary' ] = $this->getI18n( $element[ 'summary' ] );
        }
        return array(
            'name' => $this->getI18n( 'search_contentsTitle' ),
            'content' => $this->render( 'searcher_results', $values, false, false )
        );
    }

    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew() {
        $this->addToSitemap( $this->shortClassName . '/show/0', 0.5 );
        return true;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}