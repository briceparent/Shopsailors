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
 * This class manages the users rigths.<br /><br />
 * <b>To check if the page myClass/myMethod/1 may be accessed by user 123456, do this way : </b><br />
 * <ul><li>From the myClass class (which extends sh_core) :<br />
 * $rights = $this->rights_check('myMethod',1,123456,sh_rights::RIGHT_READ);
 * </li><li>From any other class :<br />
 * $rights = getUserRights(123456,'myClass/myMethod/1');<br />
 * $access = ($rights & sh_rights::RIGHT_READ);
 * </li></ul>
 * <b>To allow the page myClass/myMethod/1 for user 123456, do it this way :</b><br />
 * <ul><li>From the myClass class (which extends sh_core) :<br />
 * $this->rights_setForUser('myMethod',1,sh_rights::RIGHT_READ,123456);
 * </li><li>
 * $this->linker->rights->setGroupRights(<br />
 * &nbsp;&nbsp;'myClass/myMethod/1',<br />
 * &nbsp;&nbsp;sh_rights::RIGHT_READ,<br />
 * &nbsp;&nbsp;123456 // use sh_rights::GLOBAL_GROUP_ID to allow it to everyone<br />
 * );
 * </li></ul>
 */
class sh_rights extends sh_core {
    const CLASS_VERSION = '1.1.11.07.21';

    protected static $usesRightsManagement = true; // of course...

    // Rights types
    const TYPES_CLASS = 'class'; // Rights for an entire class
    const TYPES_METHOD = 'method'; // Rights for an entire method of a class
    const TYPES_PAGE = 'page';

    protected $user = '';

    const RIGHT_NONE = 0;
    const RIGHT_READ = 1;
    const RIGHT_ALL = 1;

    const ERROR_PAGE_NOT_FOUND = 'page not found';

    const ERROR_PAGE = 'error/show/403';

    const UNCONNECTED_USER_ID = 0;
    const GLOBAL_GROUP_ID = 0;

    const METHOD_NOT_LISTED = 'Method not listed';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db', 'sh_user'
    );
    public $callWithoutId = array(
        'edit', 'manageGroupsForPage', 'allUsersAutocompleter'
    );
    public $callWithId = array(
        'editOne'
    );
    protected $minimal = array(
        'allUsersAutocompleter' => true
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->db->updateQueries( __CLASS__ );
            if( version_compare( $installedVersion, '1.1.11.03.29', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_cron', '', __CLASS__ );
                $this->linker->renderer->add_render_tag( 'render_credentials', __CLASS__, 'render_credentials' );
            }
            if( version_compare( $installedVersion, '1.1.11.07.21', '<' ) ) {
                for($a = 1;$a<=7;$a++){
                    $this->db_execute('create_table_'.$a);
                }
            }
            
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        $this->user = $this->linker->user->getUserId();
        return true;
    }

    public function master_getMenuContent() {
        return array( );
    }

    public function admin_getMenuContent() {
        /*$adminMenu['Contenu'][] = array(
            'link' => 'rights/edit/', 'text' => 'Gérer les droits d\'accès', 'icon' => 'picto_security.png'
        );*/

        return $adminMenu;
    }

    public function cron_job( $time ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( $time == sh_cron::JOB_DAY ) {
            // Updating of the users' names and last names, enabling users selection using these datas
            $users = $this->db_execute( 'getAllUsers', array( ), $qry );
            if( is_array( $users ) ) {
                foreach( $users as $oneUser ) {
                    $userDatas = $this->getOneUserData( $oneUser['user_id'] );
                    if( $userDatas['name'] != $oneUser['name'] || $userDatas['lastName'] != $oneUser['lastName'] ) {
                        //We should update the entry
                        $this->db_execute(
                            'addOneUserDatas',
                            array(
                                'user_id' => $oneUser['user_id'],
                                'name' => $userDatas['name'],
                                'lastName' => $userDatas['lastName'],
                            )
                        );
                    }
                }
            }
        }
        return true;
    }

    public function render_credentials( $attributes = array( ) ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( !isset( $attributes['class'] ) ) {
            $this->debug( 'No class name found for ' . __FUNCTION__, 0, __LINE__ );
            return false;
        } else {
            $class = $attributes['class'];
        }
        if( !isset( $attributes['methods'] ) ) {
            $this->debug( 'No method names found for ' . __FUNCTION__, 0, __LINE__ );
            return false;
        } else {
            $methods = explode( '|', $attributes['methods'] );
        }
        if( !isset( $attributes['id'] ) ) {
            $this->debug( 'No id found for ' . __FUNCTION__, 0, __LINE__ );
            return false;
        } else {
            if( is_numeric( $attributes['id'] ) ) {
                $id = $attributes['id'];
            } elseif( strtolower( $attributes['id'] ) == 'method' ) {
                $render_for_complete_method = true;
            } else {
                $this->debug( 'Wrong data type for id. Should be either an integer or the string "method" in ' . __FUNCTION__, 0, __LINE__ );
                return false;
            }
        }

        $credentials_session = md5( $class . $methods . $id );
        $_SESSION[__CLASS__][$credentials_session] = array(
            'class' => $class,
            'methods' => $methods,
            'id' => $id
        );
        $values['rights_session']['id'] = $credentials_session;

        $allGroups = $this->db_execute( 'get_all_groups', array( ) );
        foreach( $allGroups as $groupId => $group ) {
            $allGroups[$groupId]['group_name'] = $this->getI18n( $group['group_name'] );
        }
        $allUsers = $this->db_execute( 'getAllUsers', array( ) );
        foreach( $methods as $methodId => $method ) {
            $groupsSelectedForMethod = array( );
            $usersSelectedForMethod = array( );
            if( $render_for_complete_method ) {
                $pageId = $this->getMethodPageId( $class . '/' . $method . '/' );
                if( $pageId == self::ERROR_PAGE_NOT_FOUND ) {
                    // We should register the page
                    $pageId = $this->getMethodPageId( $class . '/' . $method . '/' );
                }
                $pageName = $this->linker->i18n->get( $class, 'rights_' . $method . '_all' );
                if( empty( $methodName ) ) {
                    $pageName = $method;
                }
            } else {
                $newPage = ($id == 0);
                $pageId = $this->getPageId( $class . '/' . $method . '/' . $id );
                if( !$newPage && $pageId == self::ERROR_PAGE_NOT_FOUND ) {
                    // We should register the page
                    $pageId = $this->addPage( $class . '/' . $method . '/' . $id );
                }
                $methodName = $this->linker->i18n->get( $class, 'rights_' . $method . '_' . $id );
                if( !empty( $methodName ) ) {
                    $pageName = $methodName;
                } else {
                    $methodName = $this->linker->i18n->get( $class, 'rights_' . $method . '_one' );
                    $pageName = $this->linker->$class->getPageName( $method, $id, true );
                    $pageName = str_replace(
                            '[PAGE_NAME]',
                            $pageName,
                            $methodName
                    );
                }
                // We also should get the default rights for the method (if any)
                $methodPageId = $this->getMethodPageId( $class . '/' . $method . '/' );
                if( $methodPageId != self::ERROR_PAGE_NOT_FOUND ) {
                    $methodPageName = $this->linker->i18n->get( $class, 'rights_' . $method . '_all' );
                    if( empty( $methodPageName ) ) {
                        $methodPageName = $method;
                    }
                    $values['rights'][$methodId]['methodPage_exists'] = true;
                    $groups = $this->getGroupsForPage( $methodPageId );
                    $users = $this->getUsersForPage( $methodPageId );
                    $groupsSelectedForMethod = $groups;
                    $usersSelectedForMethod = $users;
                    $values['rights'][$methodId]['methodPage_name'] = $methodPageName;
                }
            }

            if( !$newPage ) {
                $groups = $this->getGroupsForPage( $pageId );
                $users = $this->getUsersForPage( $pageId );
                $owner = $this->getOwnerForPage( $pageId );
                $isManager = $this->isManagerForPage( $pageId );
            } else {
                $groups = array( );
                $users = array( );
            }

            $allGroupsCopy = $allGroups;
            $allUsersCopy = $allUsers;

            foreach( $allGroupsCopy as $groupId => $group ) {
                if( in_array( $group['group_id'], $groups ) ) {
                    $allGroupsCopy[$groupId]['state'] = 'checked';
                }
                if( in_array( $group['group_id'], $groupsSelectedForMethod ) ) {
                    $allGroupsCopy[$groupId]['decorationClass'] = 'underline';
                }
            }
            foreach( $allUsersCopy as $userId => $user ) {
                if( in_array( $user['user_id'], $users ) ) {
                    $allUsersCopy[$userId]['state'] = 'checked';
                }
            }
            $values['rights'][$methodId]['groups'] = $allGroupsCopy;
            $values['rights'][$methodId]['users'] = $allUsersCopy;
            $values['rights'][$methodId]['owner'] = $owner;
            $values['rights'][$methodId]['manager'] = true;
            $values['rights'][$methodId]['page'] = $pageId;
            $values['rights'][$methodId]['pageName'] = $pageName;
        }

        return $this->render( 'render_credentials', $values, false, false );
    }

    public function render_credentials_save( $newId = null ) {
        $session = $_POST['rights_session'];
        $details = $_SESSION[__CLASS__][$session];
        if( !is_array( $details['methods'] ) ) {
            return true;
        }
        $originalId = $details['id'];
        foreach( $details['methods'] as $method ) {
            if( is_numeric( $details['id'] ) ) {
                if( $details['id'] == 0 && is_numeric( $newId ) ) {
                    $details['id'] = $newId;
                    $pageId = $this->addPage( $details['class'] . '/' . $method . '/' . $details['id'] );
                    $originalId = 0;
                } else {
                    $pageId = $this->getPageId( $details['class'] . '/' . $method . '/' . $details['id'] );
                    if( $pageId == self::ERROR_PAGE_NOT_FOUND ) {
                        $pageId = $this->addPage( $details['class'] . '/' . $method . '/' . $details['id'] );
                    }
                }
            } else {
                $pageId = $this->getMethodPageId( $details['class'] . '/' . $method . '/' );
            }
            // We first remove old rights
            $this->unsetGroupsRights( $pageId );
            $this->unsetUsersRights( $pageId );
            if( is_array( $_POST['rights_groups'][$pageId] ) ) {
                foreach( array_keys( $_POST['rights_groups'][$pageId] ) as $group ) {
                    $this->setGroupRights( $pageId, self::RIGHT_READ, $group );
                }
            }
            if( is_array( $_POST['rights_users'][$pageId] ) ) {
                foreach( array_keys( $_POST['rights_users'][$pageId] ) as $user ) {
                    $this->setUserRights( $pageId, self::RIGHT_READ, $user );
                }
            }
        }
        return true;
    }

    public function getOwnerForPage( $pageId ) {

    }

    public function allUsersAutocompleter() {
        if( !$_SESSION[__CLASS__]['authorization_for_users_listing'] ) {
            return false;
        }
        $allUsers = $this->db_execute( 'getAllUsersForAutocompleter', array( 'search' => $_POST['search'] ) );
        echo '<ul>';
        if( is_array( $allUsers ) ) {
            foreach( $allUsers as $user ) {
                echo '<li>' . $user['lastName'] . ' ' . $user['name'] . ' (' . $user['user_id'] . ')</li>';
            }
        } else {
            echo '<li>...</li>';
        }
        echo '</ul>';
    }

    public function manageUsersForPage() {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        sh_cache::disable();
        if( !$this->linker->user->isConnected() ) {
            $this->linker->path->error( 403 );
            exit;
        }
        $class = $_GET['class'];
        $method = $_GET['method'];
        $id = $_GET['id'];
        if( isset( $_GET['id'] ) && !empty( $_GET['id'] ) ) {
            $id = $_GET['id'];
            $isPage = true;
            $page = $class . '/' . $method . '/' . $id;
            $pageId = $this->getPageId( $page );
        } else {
            $isMethod = true;
            $page = $class . '/' . $method . '/';
            $pageId = $this->getMethodPageId( $page );
        }

        $this->debug( 'Page is ' . $page . ' -> pageId = ' . $pageId, 3, __LINE__ );
        if( $pageId == self::METHOD_NOT_LISTED || $pageId == self::ERROR_PAGE_NOT_FOUND ) {
            $this->debug( 'ERROR : No way', 0, __LINE__ );
            die( 'The page you want to set the rights on doesn\'t exist or there is no rights
                management set for it.' );
        }
        list($isManager) = $this->db_execute( 'is_manager', array( 'user_id' => $this->user ) );
        if( !isset( $isManager['page_id'] ) ) {
            $this->debug( 'ERROR : Not allowed', 0, __LINE__ );
            die( 'You are not allowed to manage the rights on this page.' );
        }
        $_SESSION[__CLASS__]['authorization_for_users_listing'] = true;
        if( $this->formSubmitted( 'addUsersToPage' ) ) {
            if( is_array( $_POST['users'] ) ) {
                $users = array_keys( $_POST['users'] );
            } else {
                $users = array( );
            }
            if( isset( $_POST['add_user'] ) ) {
                if( preg_match( '`.+\(([1-9]?[0-9]{10}+)\).*`', $_POST['add_user'], $matches ) ) {
                    $users[] = $matches[1];
                };
            }
            // We disable every users rights on this page
            $this->db_execute( 'unset_access_for_all_users', array( 'page_id' => $pageId ) );
            // And add all the selected groups
            foreach( $users as $oneUser ) {
                $this->setUserRights( $page, self::RIGHT_READ, $oneUser );
            }
            $this->linker->path->redirect( __CLASS__, 'edit' );
        }

        $values['page']['category'] = $this->linker->i18n->get( $class, 'rights_className' );

        $methodName = $this->linker->i18n->get( $class, 'rights_' . $method . '_' . $id );
        if( !empty( $methodName ) ) {
            $pageName = $methodName;
        } else {
            $methodName = $this->linker->i18n->get( $class, 'rights_' . $method . '_one' );
            $pageName = $this->linker->$class->getPageName( $method, $id, true );
            $pageName = str_replace(
                    '[PAGE_NAME]',
                    $pageName,
                    $methodName
            );
        }
        $values['page']['name'] = $pageName;

        $values['users'] = $this->db_execute( 'getAllUsers', array( ), $qry );
        $selectedUsers = $this->getUsersForPage( $pageId );

        foreach( $values['users'] as $id => $oneUser ) {
            if( !in_array( $oneUser['user_id'], $selectedUsers ) ) {
                unset( $values['users'][$id] );
                continue;
            }

            $userDatas = $this->getOneUserData( $oneUser['user_id'] );
            $oneUser['name'] = $userDatas['name'];
            $oneUser['lastName'] = $userDatas['lastName'];
        }
        $this->render( 'manageUsersForPage', $values );
    }

    protected function getUsersForPage( $pageId ) {
        $users = $this->db_execute( 'getAllowedUsers', array( 'page_id' => $pageId ) );
        $ret = array( );
        if( is_array( $users ) ) {
            foreach( $users as $users ) {
                $ret[] = $users['user_id'];
            }
        }
        return $ret;
    }

    public function manageGroupsForPage() {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        sh_cache::disable();
        if( !$this->linker->user->isConnected() ) {
            $this->linker->path->error( 403 );
            exit;
        }
        $class = $_GET['class'];
        $method = $_GET['method'];
        $id = $_GET['id'];
        if( isset( $_GET['id'] ) && !empty( $_GET['id'] ) ) {
            $id = $_GET['id'];
            $isPage = true;
            $page = $class . '/' . $method . '/' . $id;
            $pageId = $this->getPageId( $page );
        } else {
            $isMethod = true;
            $page = $class . '/' . $method . '/';
            $pageId = $this->getMethodPageId( $page );
        }

        $this->debug( 'Page is ' . $page . ' -> pageId = ' . $pageId, 3, __LINE__ );
        if( $pageId == self::METHOD_NOT_LISTED || $pageId == self::ERROR_PAGE_NOT_FOUND ) {
            $this->debug( 'ERROR : No way', 0, __LINE__ );
            die( 'The page you want to set the rights on doesn\'t exist or there is no rights
                management set for it.' );
        }
        list($isManager) = $this->db_execute( 'is_manager', array( 'user_id' => $this->user ) );
        if( !isset( $isManager['page_id'] ) ) {
            $this->debug( 'ERROR : Not allowed', 0, __LINE__ );
            die( 'You are not allowed to manage the rights on this page.' );
        }
        if( $this->formSubmitted( 'addGroupToPage' ) ) {
            // We disable every groups rights on this page
            $this->db_execute( 'unset_access_for_all_groups', array( 'page_id' => $pageId ), $qry );
            // And add all the selected groups
            foreach( array_keys( $_POST['groups'] ) as $oneGroup ) {
                $this->setGroupRights( $page, self::RIGHT_READ, $oneGroup );
            }
            $this->linker->path->redirect( __CLASS__, 'edit' );
        }
        $values['page']['category'] = $this->linker->i18n->get( $class, 'rights_className' );

        $methodName = $this->linker->i18n->get( $class, 'rights_' . $method . '_' . $id );
        if( !empty( $methodName ) ) {
            $pageName = $methodName;
        } else {
            $methodName = $this->linker->i18n->get( $class, 'rights_' . $method . '_one' );
            $pageName = $this->linker->$class->getPageName( $method, $id, true );
            $pageName = str_replace(
                    '[PAGE_NAME]',
                    $pageName,
                    $methodName
            );
        }
        $values['page']['name'] = $pageName;

        $values['groups'] = $this->db_execute( 'get_all_groups', array( ), $qry );
        $selectedGroups = $this->getGroupsForPage( $pageId );

        foreach( $values['groups'] as &$oneGroup ) {
            $oneGroup['group_name'] = $this->getI18n( $oneGroup['group_name'] );
            if( in_array( $oneGroup['group_id'], $selectedGroups ) ) {
                $oneGroup['state'] = 'checked';
            }
        }
        $this->render( 'manageGroupsForPage', $values );
    }

    protected function getGroupsForPage( $pageId ) {
        $groups = $this->db_execute( 'getAllowedGroups', array( 'page_id' => $pageId ) );
        $ret = array( );
        if( is_array( $groups ) ) {
            foreach( $groups as $group ) {
                $ret[] = $group['group_id'];
            }
        }
        return $ret;
    }

    public function edit() {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        sh_cache::disable();
        if( !$this->linker->user->isConnected() ) {
            $this->linker->path->error( 403 );
            exit;
        }
        // There are 3 possibilities :
        //  - the user is an admin/master
        //      He can set rights on any documents
        //      He can give rights_managements rights to others for the documents he can manage
        //  - the user has rights_managements right
        //      He can view his own rights on the documents he has access to
        //      He can allow other users/groups to access the documents
        //  - the user is a simple user
        //      He can view his rights on the documents he has access to
        // We list all the rights this user has access to
        $user = $this->user;

        // We get the user's groups
        $groups_for_query = $this->getUserGroups( $user );
        foreach( $groups_for_query as $group ) {
            $groups[] = $group['group_id'];
        }

        if( $this->isAdmin() ) {
            $pages = $this->db_execute( 'get_all_manager_pages', array( ) );
            $this->showManagerPages( $pages );
        } elseif( $this->isManager( $user ) ) {
            $pages = $this->db_execute( 'get_manager_pages', array( 'user_id' => $user ) );
            $this->showManagerPages( $pages );
        } else {
            echo 'The user has no management rights<br />';
        }
    }

    protected function showManagerPages( $pages ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        $class = '';
        if( is_array( $pages ) ) {
            foreach( $pages as $page ) {
                if( $page['class'] != $class ) {
                    $class = $page['class'];
                    $className = $this->linker->i18n->get( $class, 'rights_className' );
                    if( empty( $className ) ) {
                        $className = $class;
                    }
                    $values['classes'][$class] = array(
                        'name' => $className
                    );
                }
                if( $page['id'] == '' ) {
                    // We have access to a full method
                    $methodName = $this->linker->i18n->get( $class, 'rights_' . $page['method'] . '_all' );
                    if( empty( $methodName ) ) {
                        $methodName = $page['method'];
                    }
                    $accessForGroups = $this->db_execute( 'getAllowedGroups', array( 'page_id' => $page['page_id'] ) );
                    $accessForUsers = $this->db_execute( 'getAllowedUsers', array( 'page_id' => $page['page_id'] ) );

                    $values['classes'][$class]['methods'][$page['method']] = array(
                        'name' => $methodName,
                        'uid' => md5( microtime() ),
                        'groupsCount' => count( $accessForGroups ),
                        'usersCount' => count( $accessForUsers ),
                        'class' => $class,
                        'method' => $page['method'],
                        'id' => $page['id']
                    );

                    if( !empty( $accessForGroups ) ) {
                        foreach( $accessForGroups as $group ) {
                            $values['classes'][$class]['methods'][$page['method']]['groups'][] = array(
                                'id' => $group['group_id'],
                                'name' => $group['group_name'],
                            );
                        }
                    }
                    if( !empty( $accessForUsers ) ) {
                        foreach( $accessForUsers as $user ) {
                            $userDatas = $this->getOneUserData( $user['user_id'] );
                            $values['classes'][$class]['methods'][$page['method']]['users'][] = array(
                                'name' => $userDatas['lastName'] . ' ' . $userDatas['name']
                            );
                        }
                    }
                } else {
                    $methodName = $this->linker->i18n->get( $class, 'rights_' . $page['method'] . '_' . $page['id'] );
                    if( !empty( $methodName ) ) {
                        $pageName = $methodName;
                    } else {
                        $methodName = $this->linker->i18n->get( $class, 'rights_' . $page['method'] . '_one' );
                        $pageName = $this->linker->$class->getPageName( $page['method'], $page['id'], true );
                        $pageName = str_replace(
                                '[PAGE_NAME]',
                                $pageName,
                                $methodName
                        );
                    }
                    $accessForGroups = $this->db_execute( 'getAllowedGroups', array( 'page_id' => $page['page_id'] ) );
                    $accessForUsers = $this->db_execute( 'getAllowedUsers', array( 'page_id' => $page['page_id'] ) );

                    $values['classes'][$class]['methods'][$page['method']]['pages'][$page['method'] . $page['id']] = array(
                        'name' => $pageName,
                        'uid' => md5( microtime() ),
                        'groupsCount' => count( $accessForGroups ),
                        'usersCount' => count( $accessForUsers ),
                        'class' => $class,
                        'method' => $page['method'],
                        'id' => $page['id']
                    );
                    if( !empty( $accessForGroups ) ) {
                        foreach( $accessForGroups as $group ) {
                            $values['classes'][$class]['methods'][$page['method']]['pages'][$page['method'] . $page['id']]['groups'][] = array(
                                'id' => $group['group_id'],
                                'name' => $group['group_name']
                            );
                        }
                    }
                    if( !empty( $accessForUsers ) ) {
                        foreach( $accessForUsers as $user ) {
                            $userDatas = $this->getOneUserData( $user['user_id'] );
                            $values['classes'][$class]['methods'][$page['method']]['pages'][$page['method'] . $page['id']]['users'][] = array(
                                'name' => $userDatas['lastName'] . ' ' . $userDatas['name']
                            );
                        }
                    }
                }
            }
            $this->render( 'showManagerPages', $values );
        } else {
            echo 'NO PAGES';
        }
    }

    protected function getOneUserData( $userId ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        return $this->linker->user->getOneUserData(
            $userId,
            array( 'name', 'lastName' )
        );
    }

    public function setManagerRights( $page = null, $user = null ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        
        if( is_null( $page ) ) {
            $page = $this->linker->path->getPage();
        }
        $page_id = $this->getPageId( $page );
        if( $page_id == self::METHOD_NOT_LISTED ) {
            // Can't set rights for this page
            return false;
        }
        if( $page_id == self::ERROR_PAGE_NOT_FOUND ) {
            // We should add the page
            $page_id = $this->addPage( $page );
        }
        if( is_null( $user ) ) {
            $user = $this->user;
        }
        $this->db_execute( 'addOneUser', array( 'user_id' => $user ) );
        $this->db_execute( 'set_manager_page', array( 'user_id' => $user, 'page_id' => $page_id ) );
    }

    public function removeManagerRights( $page = null, $user = null ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        if( is_null( $page ) ) {
            $page = $this->linker->path->getPage();
        }
        $page_id = $this->getPageId( $page );
        if( $page_id == self::METHOD_NOT_LISTED || $page_id == self::ERROR_PAGE_NOT_FOUND ) {
            // Can't remove inexisting rights
            return false;
        }
        if( is_null( $user ) ) {
            $user = $this->user;
        }
        $this->db_execute( 'remove_manager_page', array( 'user_id' => $user, 'page_id' => $page_id ) );
    }

    protected function isManager( $user = null ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        if( is_null( $user ) ) {
            $user = $this->user;
        }
        list($ret) = $this->db_execute( 'is_manager', array( 'user_id' => $user ) );
        return (isset( $ret['page_id'] ));
    }

    protected function isManagerForPage( $pageId, $user = null ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        if( is_null( $user ) ) {
            $user = $this->user;
        }
        list($ret) = $this->db_execute( 'is_manager_for_page', array( 'page_id' => $pageId, 'user_id' => $user ) );
        return (isset( $ret['page_id'] ));
    }

    public function showGroups() {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        $this->onlyAdmin();
    }

    public function showGroup() {

    }

    public function editGroup() {

    }

    /**
     * This function returns the user $user_id's rights for the page $page.
     */
    public function getUserRights( $user_id = null, $page = null ) {
        $this->debug( __FUNCTION__ . '(' . $user_id . ', ' . $page . ');', 2, __LINE__ );
        if( is_null( $page ) ) {
            $page = $this->linker->path->getPage();
            $class = $this->linker->path->page['element'];
        } else {
            $class = substr( $page, 0, strpos( $page, '/' ) );
        }
        $class = $this->helper->getRealClassName( $class );

        if( !$class::isUsingRightsManagement() ) {
            $this->debug( 'All rights because no rights management on class', 3, __LINE__ );
            return self::RIGHT_ALL;
        }

        if( $this->isAdmin() || $this->isMaster() ) {
            // Admins and masters may always access the pages, with R+O mode.
            // To disallow the admin for some pages, please use $this->onlyMaster
            // (This way, it is hard coded)
            //return self::RIGHT_ALL;
        }
        if( $page == self::ERROR_PAGE ) {
            $this->debug( 'All rights because page not found', 3, __LINE__ );
            return self::RIGHT_ALL;
        }
        if( is_null( $user_id ) ) {
            $user_id = $this->user;
        }
        if( empty( $user_id ) ) {
            // The user is not connected
            $user_id = self::UNCONNECTED_USER_ID;
        }
        $page_id = $this->getPageId( $page, true );
        if( $page_id == self::METHOD_NOT_LISTED ) {
            // The method doesn't use this class, so we allow the access
            $this->debug( 'All rights because no rights management on method', 3, __LINE__ );
            return self::RIGHT_ALL;
        }
        $method_id = $this->getMethodPageId( $page );
        if( $page_id != self::ERROR_PAGE_NOT_FOUND || $method_id != self::ERROR_PAGE_NOT_FOUND ) {
            if( $page_id > 0 ) {
                $read = $this->db_execute( 'is_user_allowed_to_read', array( 'user_id' => $user_id, 'page_id' => $page_id ), $qry );
            }
            if( $read[0]['cpt'] == 0 && $method_id != $page_id ) {
                $read = $this->db_execute( 'is_user_allowed_to_read', array( 'user_id' => $user_id, 'page_id' => $method_id ) );
            }
            if( $read[0]['cpt'] > 0 ) {
                $this->debug( 'Reading rights', 3, __LINE__ );
                return self::RIGHT_READ;
            }
        }

        // We verify if the groups' rights are more open
        $groups = $this->getUserGroups( $user_id );
        if( is_array( $groups ) ) {
            foreach( $groups as $group ) {
                $rights = $this->getGroupRights( $group['group_id'], $page );
                if( $rights & self::RIGHT_READ ) {
                    // We only need 1 group which allows the access to allow it
                    $this->debug( 'Reading rights thanks to group', 3, __LINE__ );
                    return self::RIGHT_READ;
                }
            }
        }
        $this->debug( 'No rights', 3, __LINE__ );
        return self::RIGHT_NONE;
    }

    public function getGroupRights( $group_id, $page = null ) {
        $this->debug( __FUNCTION__ . '(' . $group_id . ', ' . $page . ');', 2, __LINE__ );
        if( is_null( $page ) ) {
            $page = $this->linker->path->getPage();
            $class = $this->linker->path->page['element'];
        } else {
            $class = substr( $page, 0, strpos( $page, '/' ) );
        }
        $class = $this->helper->getRealClassName( $class );

        if( !$class::isUsingRightsManagement() ) {
            return self::RIGHT_READ;
        }

        if( $page == self::ERROR_PAGE ) {
            return self::RIGHT_READ;
        }
        if( is_null( $group_id ) ) {
            // there need to be a group id to check out.
            return false;
        }
        $page_id = $this->getPageId( $page, true );
        $method_id = $this->getMethodPageId( $page );
        $ret = 0;
        if( $page_id > 0 ) {
            $read = $this->db_execute( 'is_group_allowed_to_read', array( 'group_id' => $group_id, 'page_id' => $page_id ) );
        }
        if( $method_id > 0 && $read[0]['cpt'] == 0 && $method_id != $page_id ) {
            $read = $this->db_execute( 'is_group_allowed_to_read', array( 'group_id' => $group_id, 'page_id' => $method_id ) );
        }
        if( $read[0]['cpt'] > 0 ) {
            return self::RIGHT_READ;
        }
        return self::RIGHT_NONE;
    }

    public function setUserRights( $page, $rights, $user_id = null ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        $page_id = $this->getPageId( $page );
        if( $page_id != self::METHOD_NOT_LISTED ) {
            if( $page_id == self::ERROR_PAGE_NOT_FOUND ) {
                // We have to insert the page
                $page_id = $this->addPage( $page );
            }
            if( is_null( $user_id ) ) {
                $user_id = $this->user;
            }
            if( $rights & self::RIGHT_READ ) {
                $this->db_execute( 'addOneUser', array( 'user_id' => $user_id ) );
                $this->db_execute( 'set_access_user', array( 'user_id' => $user_id, 'page_id' => $page_id ), $qry );
            } else {
                $this->db_execute( 'unset_access_user', array( 'user_id' => $user_id, 'page_id' => $page_id ), $qry );
            }
        }
    }

    public function setGroupRights( $page, $rights, $group_id ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        $page_id = $this->getPageId( $page );
        if( $page_id != self::METHOD_NOT_LISTED ) {
            if( $page_id == self::ERROR_PAGE_NOT_FOUND ) {
                // We have to insert the page
                $page_id = $this->addPage( $page );
            }
            if( is_null( $group_id ) ) {
                $group_id = $this->getUserGroups();
            }
            if( $rights & self::RIGHT_READ ) {
                $this->db_execute( 'set_access_group', array( 'group_id' => $group_id, 'page_id' => $page_id ) );
            } else {
                $this->db_execute( 'unset_access_group', array( 'group_id' => $group_id, 'page_id' => $page_id ) );
            }
        }
    }

    public function unsetGroupsRights( $page ) {
        $this->db_execute( 'unset_access_for_all_groups', array( 'page_id' => $page ), $qry );
        echo $qry . '<br />';
        return true;
    }

    public function unsetUsersRights( $page ) {
        $this->db_execute( 'unset_access_for_all_users', array( 'page_id' => $page ), $qry );
        echo $qry . '<br />';
        return true;
    }

    public function createGroup( $group_name, $group_owner = null ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        if( !is_array( $group_name ) ) {
            // A group needs a name
            return false;
        }
        if( is_null( $group_owner ) ) {
            $group_owner = $this->user;
        }
        if( !($group_owner > 0) ) {
            // A group has to belong to someone
            return false;
        }
        $group_i18nName = $this->setI18n( 0, $group_name );

        $this->db_execute( 'addOneUser', array( 'user_id' => $group_owner ), $qry );
        $this->debug( $qry, 3, __LINE__ );
        $this->db_execute(
            'add_group',
            array(
                'group_owner_id' => $group_owner,
                'group_name' => $group_i18nName
            ), $qry
        );
        $this->debug( $qry, 3, __LINE__ );
        return $this->db_insertId();
    }

    public function getGroupName( $group_id, $asI18nId = false ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        list($i18nName) = $this->db_execute( 'get_group_name', array( 'group_id' => $group_id ) );
        if( $asI18nId ) {
            return $i18nName['group_name'];
        }
        return $this->getI18n( $i18nName['group_name'] );
    }

    public function addUserToGroup( $group_id, $user_id = null ) {
        $this->debug( __FUNCTION__ . '("' . $group_id . '","' . $user_id . '");', 2, __LINE__ );
        if( is_null( $user_id ) ) {
            $user_id = $this->user;
        }
        $this->db_execute( 'addOneUser', array( 'user_id' => $user_id ), $qry );
        $this->debug( $qry, 3, __LINE__ );
        $ret = $this->db_execute( 'add_user_to_group', array( 'user_id' => $user_id, 'group_id' => $group_id ), $qry );
        $this->debug( $qry, 3, __LINE__ );
        return $ret;
    }

    public function getUserGroups( $user = null ) {
        $this->debug( __FUNCTION__ . '();', 2, __LINE__ );
        if( is_null( $user ) ) {
            $user = $this->user;
        }
        $groups = $this->db_execute( 'get_user_groups', array( 'user_id' => $user ) );
        if( !is_array( $groups ) ) {
            $groups = array( );
        }
        $groups[]['group_id'] = 0;
        $this->debug( 'Groups : ' . print_r( $groups, true ), 3, __LINE__ );
        return $groups;
    }

    protected function addPage( $page ) {
        $this->debug( __FUNCTION__ . '(' . $page . ');', 2, __LINE__ );
        if( !is_array( $page ) ) {
            $completePageName = $page;
            $page = array( );
            list($page['class'], $page['method'], $page['id']) = explode( '/', $completePageName );
            $page['class'] = $this->linker->cleanObjectName( $page['class'] );
        }
        $this->db_execute( 'add_page', $page );
        $ret = $this->db_insertId();
        $this->debug( 'Insert id is ' . $ret, 3, __LINE__ );
        return $ret;
    }

    /**
     * Gets a page id using a page (class/method/id).
     * @param str $page The page
     * @param bool $fromMethodIfNotFound If set to <b>false</b> (default behaviour), the method
     * only returns a page id if it exists with the exact page $page.<br />
     * If set to <b>false</b>, if the page class/method/id, will look for the page class/method/ .
     * @return int|str Returns the page id (integer) or an error constant if there is an error.
     */
    protected function getPageId( &$page, $fromMethodIfNotFound = false ) {
        static $pages_ids = array( );
        static $pages_ids_fromMethodEventually = array( );
        $this->debug( __FUNCTION__ . '(' . $page . ', ' . $fromMethodIfNotFound . ');', 2, __LINE__ );
        if( is_numeric( $page ) ) {
            // It already is a page id
            return $page;
        }
        if( is_array( $page ) ) {
            $shortPageName = implode( '/', $page );
        } else {
            $shortPageName = $page;
        }
        $this->debug( 'Short page name is ' . $shortPageName, 3, __LINE__ );
        if( isset( $pages_ids[$shortPageName] ) ) {
            $this->debug( 'Taken from pages cache', 3, __LINE__ );
            return $pages_ids[$shortPageName];
        }
        if( $fromMethodIfNotFound && isset( $pages_ids_fromMethodEventually[$shortPageName] ) ) {
            $this->debug( 'Taken from methods cache', 3, __LINE__ );
            return $pages_ids_fromMethodEventually[$shortPageName];
        }

        if( !is_array( $page ) ) {
            $page = explode( '/', $page );
        }
        if( !isset( $page['class'] ) ) {
            $page = array(
                'class' => $page[0],
                'method' => $page[1],
                'id' => $page[2]
            );
        }
        $page['class'] = $this->linker->cleanObjectName( $page['class'] );
        $class = $page['class'];
        $method = $page['method'];
        $methods = $this->linker->$class->rights_methods;
        if( !in_array( $method, $methods ) ) {
            return self::METHOD_NOT_LISTED;
        }
        if( $page['id'] != '' ) {
            $ret = $this->db_execute( 'get_page_id', $page, $qry );
        } else {
            $ret = $this->db_execute( 'get_method_page_id', $page, $qry );
        }
        $page_id = $ret[0]['page_id'];
        if( empty( $page_id ) ) {
            // Id 0 is not seen as part of the normal ids, because the classes
            //use it as New Element
            if( $fromMethodIfNotFound && $page['id'] != 0 ) {
                $ret = $this->getMethodPageId( $page );
                $pages_ids_fromMethodEventually[$shortPageName] = $ret;
                return $ret;
            }
            $this->debug( 'Page not found', 3, __LINE__ );
            return self::ERROR_PAGE_NOT_FOUND;
        }
        $this->debug( 'Page is ' . $page_id, 3, __LINE__ );
        $pages_ids[$shortPageName] = $page_id;
        return $page_id;
    }

    protected function getMethodPageId( $page ) {
        $this->debug( __FUNCTION__ . '(' . $page . ');', 2, __LINE__ );
        if( is_numeric( $page ) ) {
            // It already is a page id
            return $page;
        }
        if( !is_array( $page ) ) {
            $page = explode( '/', $page );
        }
        if( !isset( $page['class'] ) ) {
            $page = array(
                'class' => $page[0],
                'method' => $page[1],
                'id' => $page[2]
            );
        }
        $class = $page['class'];
        $method = $page['method'];
        if( !in_array( $method, $this->linker->$class->rights_methods ) ) {
            return self::METHOD_NOT_LISTED;
        }
        $ret = $this->db_execute( 'get_method_page_id', $page );
        $page_id = $ret[0]['page_id'];

        if( empty( $page_id ) ) {
            $this->debug( 'Page not found', 3, __LINE__ );
            return self::ERROR_PAGE_NOT_FOUND;
        }
        $this->debug( 'Page is ' . $page_id, 3, __LINE__ );
        return $page_id;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}
