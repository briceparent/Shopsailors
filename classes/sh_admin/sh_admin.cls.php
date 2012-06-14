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
 * Class that creates the Command Panel, and creates the connection form.
 */
class sh_admin extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $admin = false;
    protected $master = false;
    protected $elements = array( );
    protected $usingCompatibleBrowser = false;

    const CONNECT_AS_ADMIN = 0;
    const CONNECT_AS_MASTER = 1;
    const CONNECT_AS_USER = 2;

    const ADMINMENUENTRIES = 'admin_menu_entries';

    public function construct() {
        if( !$this->linker->user->isConnected() ) {
            return true;
        }
        $userId = $this->linker->user->userId;
        $this->admin = $this->isAdmin( $userId );
        $this->master = $this->isMaster( $userId );

        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        if( !$this->admin && !$this->master ) {
            return true;
        }

        if( $this->admin ) {
            $this->linker->html->addMetaProperty( 'shopsailors_admin', 'true' );
        }
        if( $this->master ) {
            $this->linker->html->addMetaProperty( 'shopsailors_master', 'true' );
        }

        // We should verify if the user is using firefox:
        if( strpos( strtolower( $_SERVER[ 'HTTP_USER_AGENT' ] ), 'firefox' ) === false ) {
            // He is not, so he can't administrate the website
            if( !isset( $_SESSION[ __CLASS__ ][ 'notUsingFirefoxMessageShown' ] ) ) {
                $this->linker->cache->disable();
                $this->linker->html->addToBody(
                    'onload', 'sh_popup.alert(\'' . $this->getI18n( 'youShouldUseFirefox' ) . '\');'
                );
                $_SESSION[ __CLASS__ ][ 'notUsingFirefoxMessageShown' ] = true;
            }
            return true;
        }

        $this->usingCompatibleBrowser = true;

        sh_cache::removeCache();
        $this->linker->cache->disable();

        if( isset( $_SESSION[ __CLASS__ ][ 'adminBoxPosX' ] ) ) {
            $x = $_SESSION[ __CLASS__ ][ 'adminBoxPosX' ];
            $y = $_SESSION[ __CLASS__ ][ 'adminBoxPosY' ];
        } else {
            $x = '50px';
            $y = '50px';
        }
        $this->linker->html->addCSS( '/templates/global/admin.css', 'ADMINBOX' );
        $this->linker->html->addScript( '/' . __CLASS__ . '/singles/admin.js' );
        $this->linker->html->addToBody( 'onload', 'dragAdminBox(\'' . $x . '\',\'' . $y . '\');' );
        return true;
    }

    public function addMessage( $type, $message, $links = '' ) {
        $paramsFile = SH_SITEPARAMS_FOLDER . __CLASS__ . '_messages.params.php';
        $this->linker->params->addElement( $paramsFile, true );
        $date = date( 'Y-m-d' );
        if( is_array( $this->linker->params->get( $paramsFile, $type ) ) ) {
            $id = $this->linker->params->count( $paramsFile, $type . '>' . $date ) + 1;
        } else {
            $this->linker->params->set(
                $paramsFile, $type . '>link', ''
            );
            $îd = 1;
        }
        $this->linker->params->set(
            $paramsFile, $type . '>' . $date . '>' . $id,
            array(
            'message' => $message,
            'links' => $links
            )
        );
        $this->linker->params->write( $paramsFile );
        return true;
    }

    public function getAdmins( $andMasters = true ) {
        if( $this->getParam( 'thisIsADemoSite', false ) ) {
            return '*';
        }
        $admins = $this->getParam( 'admins', array( ) );
        if( $andMasters ) {
            $admins = array_merge( $admins, $this->getMasters() );
        }
        return $admins;
    }

    public function getMasters() {
        $masters = $this->getParam( 'masters', array( ) );
        return $this->getParam( 'masters', array( ) );
    }

    public function getMasterByMail() {
        return $this->getParam( 'master_by_mail', '' );
    }

    public function isAdmin( $id = null, $alsoVerifyIfIsMaster = true ) {
        if( is_null( $id ) ) {
            return parent::isAdmin();
        }
        $admins = $this->getAdmins( $alsoVerifyIfIsMaster );
        if( $admins == '*' ) {
            return true;
        }
        return in_array( $id, $admins );
    }

    public function isMaster( $id = null ) {
        $this->linker->params->addElement( __CLASS__, false, true );
        if( is_null( $id ) ) {
            return parent::isMaster();
        }
        return in_array( $id, $this->getMasters() );
    }

    public function isNewMaster( $userId, $mail ) {
        if( !empty( $mail ) && $this->getParam( 'master_by_mail', '' ) === $mail ) {
            // We should delete the master_by_mail entry and add the user id to the params file,
            //to let him connect with it.
            $this->helper->writeArrayInFile(
                dirname( __FILE__ ) . '/params/general.params.php', 'this->general',
                         array( 'masters' => array( ( int ) $userId ) ), false
            );
            $this->linker->params->reload( __CLASS__ );
            $this->master = true;
            return true;
        }
        return false;
    }

    /**
     * Redirects to an error page if the user is neither an admin nor a master
     */
    public function onlyAdmin( $alsoVerifyIfIsMaster = true ) {
        $userId = $this->linker->user->userId;
        if( !$this->isAdmin( $userId, $alsoVerifyIfIsMaster ) ) {
            $this->linker->path->error( 403 );
        }
    }

    /**
     * Redirects to an error page if the user is not a master
     */
    public function onlyMaster( $alsoVerifyIfIsMaster = true ) {
        $userId = $this->linker->user->userId;
        if( !$this->isMaster( $userId ) ) {
            $this->linker->path->error( 403 );
        }
    }

    public function connect( $as = self::CONNECT_AS_USER ) {
        sh_cache::removeCache();
        if( $as == self::CONNECT_AS_MASTER ) {
            $_SESSION[ __CLASS__ ][ 'admin' ] = true;
            $_SESSION[ __CLASS__ ][ 'master' ] = true;
            $_SESSION[ __CLASS__ ][ 'user' ] = true;
            $_SESSION[ __CLASS__ ][ 'newConnexion' ] = true;
            $this->admin = true;
            $this->master = true;
            $this->linker->events->onMasterConnection();
            return true;
        }
        if( $as == self::CONNECT_AS_ADMIN ) {
            $_SESSION[ __CLASS__ ][ 'master' ] = false;
            $_SESSION[ __CLASS__ ][ 'admin' ] = true;
            $_SESSION[ __CLASS__ ][ 'user' ] = true;
            $_SESSION[ __CLASS__ ][ 'newConnexion' ] = true;
            $this->master = false;
            $this->admin = true;
            $this->linker->events->onAdminConnection();
            return true;
        }
        $_SESSION[ __CLASS__ ][ 'admin' ] = false;
        $_SESSION[ __CLASS__ ][ 'master' ] = false;
        $_SESSION[ __CLASS__ ][ 'user' ] = true;
        $_SESSION[ __CLASS__ ][ 'newConnexion' ] = true;
        $this->master = false;
        $this->admin = false;
        $this->linker->events->onUserConnection();
        return true;
    }

    public function disconnect() {
        $_SESSION[ __CLASS__ ][ 'newConnexion' ] = false;
        unset( $_SESSION[ __CLASS__ ][ 'notUsingFirefoxMessageShown' ] );

        if( $_SESSION[ __CLASS__ ][ 'master' ] ) {
            $this->linker->events->onMasterDisconnection();
            $_SESSION[ __CLASS__ ][ 'master' ] = false;
        }
        if( $_SESSION[ __CLASS__ ][ 'admin' ] ) {
            $this->linker->events->onAdminDisconnection();
            $_SESSION[ __CLASS__ ][ 'admin' ] = false;
        }
        if( $_SESSION[ __CLASS__ ][ 'user' ] ) {
            $this->linker->events->onUserDisconnection();
            $_SESSION[ __CLASS__ ][ 'user' ] = false;
        }

        $this->master = false;
        $this->admin = false;
        return true;
    }

    /**
     * Creates the admin menu itself
     * @return str
     * Returns the html source of the admin menu
     */
    public function get() {
        if( !$this->isAdmin() || !$this->usingCompatibleBrowser ) {
            return '';
        }
        $this->linker->session->sessionKeeper();

        $admin[ 'admin' ][ 'paneltitle' ] = SH_TEMPLATE_PATH . '/global/admin/pannel_title.png';
        $admin[ 'admin' ][ 'closeimage' ] = SH_TEMPLATE_PATH . '/global/admin/pannel_close.png';
        $admin[ 'admin' ][ 'closehref' ] = $this->linker->path->getLink( 'user/disconnect/' );

        $adminMenuClasses = $this->get_shared_methods( self::ADMINMENUENTRIES );
        foreach( $adminMenuClasses as $class ) {
            // We collect the entries for both admin and master (if needed for master)
            $admin = $this->linker->$class->admin_getMenuContent();
            if( is_array( $admin ) ) {
                foreach( $admin as $title => $content ) {
                    foreach( $content as $position => $entry ) {
                        $this->insertElement( $title, $entry, $position );
                    }
                }
            }
            if( $this->isMaster() ) {
                $master = $this->linker->$class->master_getMenuContent();
                foreach( $master as $title => $content ) {
                    foreach( $content as $position => $entry ) {
                        $this->insertElement( '[' . $title . ']', $entry, $position );
                    }
                }
            }
        }

        $masterCpt = 0;
        $adminCpt = 1000;

        foreach( $this->elements as $category => $contents ) {
            if( substr( $category, 0, 1 ) == '[' ) {
                $masterCpt++;
                $cpt = $masterCpt;
                $categoryOrder = '0' . $category;
                $admin[ 'sections' ][ $categoryOrder ][ 'number' ] = 'master';
            } else {
                $categoryOrder = '1' . $category;
                $adminCpt++;
                $cpt = $adminCpt;
            }
            $admin[ 'sections' ][ $categoryOrder ][ 'elements' ] = $contents;
            $admin[ 'sections' ][ $categoryOrder ][ 'name' ] = $category;
            $admin[ 'sections' ][ $categoryOrder ][ 'id' ] = $cpt;
        }

        ksort( $admin[ 'sections' ] );
        $sectionNumber = 1;
        foreach( $admin[ 'sections' ] as &$category ) {
            if( $category[ 'number' ] != 'master' ) {
                $category[ 'number' ] = $sectionNumber++;
                if( $sectionNumber > 5 ) {
                    $sectionNumber = 1;
                }
            }
        }
        if( $this->isMaster() ) {
            $admin[ 'master' ][ 'on' ] = true;
        }
        // We also have to add the message entry
        $ret = $this->render( 'interface', $admin, false, false );

        $root = $this->linker->path->getBaseUri();
        $ret = str_replace(
            array( ' href="/', 'window.open(\'/' ), array( ' href="' . $root . '/', 'window.open(\'' . $root . '/' ),
            $ret
        );
        return $ret;
    }

    /**
     * Inserts a page in the admin bar<br />
     * Manages with long lines
     * @param str $page
     * The page we want to insert
     * @param str $category
     * The category in which to insert the page $page
     * @param str $image
     * (optionnal)<br />
     * Path to the icon
     * @param str $pageName
     * (optionnal)<br />
     * The text that will appear in the menu, or empty for automatic text (taken from the url)
     * @return bool
     * status of the operation<br />
     * (For now, it only returns true)
     */
    public function insertPage( $page, $category, $image = '', $pageName = '' ) {
        if( $image != '' ) {
            $root = $this->linker->path->getBaseUri() . '/';
            $image = '<img src="' . $root . 'templates/global/admin/icons/' . $image . '" alt="logo"/> ';
        }
        if( empty( $pageName ) ) {
            $pageName = basename( $this->linker->path->getLink( $page ) );
            if( strlen( $pageName ) < 18 ) {
                $pageName = 'Modifier la page "' . $pageName . '"';
            } elseif( strlen( $pageName ) > 28 ) {
                $pageName = 'Modifier la page <br />"' . substr( $pageName, 0, 25 ) . '..."';
            } else {
                $pageName = 'Modifier la page <br />"' . $pageName . '"';
            }
        }
        $this->elements[ $category ][ ][ 'element' ] = $image . '<span>' .
            $this->linker->html->createLink( $page, $pageName ) . '</span>' . "\n";

        return true;
    }

    /**
     * Inserts a page in the admin bar
     * @deprecated
     * @param str $page
     * The page we want to insert
     * @param str $category
     * The category in which to insert the page $page
     * @param str $image
     * (optionnal)<br />
     * Path to the icon
     * @return bool
     * status of the operation<br />
     * (For now, it only returns true)
     */
    public function insert( $element, $category, $image = '', $position = "bottom" ) {
        if( $image != '' ) {
            $root = $this->linker->path->getBaseUri() . '/';
            $image = '<img src="' . $root . 'templates/global/admin/icons/' . $image . '" alt="logo"/> ';
        }
        if( !is_array( $this->elements[ $category ] ) ) {
            $this->elements[ $category ] = array( );
        }
        $count = str_pad( count( $this->elements[ $category ] ), 3, '0', STR_PAD_LEFT );
        if( substr( $position, 0, 3 ) == 'top' ) {
            $position = 'a' . $count;
        } elseif( substr( $position, 0, 6 ) == 'bottom' ) {
            $position = 'c' . $count;
        } else {
            $position = 'b' . $count;
        }
        $this->elements[ $category ][ $position ][ 'element' ] = $image . '<span>' . $element . "</span>\n";
        ksort( $this->elements[ $category ] );
        return true;
    }

    protected function insertElement( $name, $menuElement, $position = 'bottom' ) {
        if( $menuElement[ 'type' ] != 'popup' ) {
            $options = array( );
            if( isset( $menuElement[ 'target' ] ) ) {
                $options[ 'target' ] = $menuElement[ 'target' ];
            }
            $this->insert(
                $this->linker->html->createLink(
                    $menuElement[ 'link' ], $menuElement[ 'text' ], $options
                ), $name, $menuElement[ 'icon' ], $position
            );
        } else {
            $this->insert(
                $this->linker->html->createPopupLink(
                    $menuElement[ 'link' ], $menuElement[ 'text' ], $menuElement[ 'width' ], $menuElement[ 'height' ]
                ), $name, $menuElement[ 'icon' ], $position
            );
        }
    }

    /**
     * Adds the $file's menu entries to the bar
     * @param str $file
     * File to take the menu entries from
     * @return bool
     * status of the operation<br />
     * False if the file doesn't exist<br />
     * else: True
     */
    protected function insertFromFile( $file ) {
        if( !file_exists( $file ) ) {
            // The file doesn't exist, so we return false
            return false;
        }
        include($file);
        if( is_array( $adminMenu ) ) {
            foreach( $adminMenu as $name => $menu ) {
                foreach( $menu as $position => $menuElement ) {
                    if( $menuElement[ 'type' ] != 'popup' ) {
                        $options = array( );
                        if( isset( $menuElement[ 'target' ] ) ) {
                            $options[ 'target' ] = $menuElement[ 'target' ];
                        }
                        $this->insert(
                            $this->linker->html->createLink(
                                $menuElement[ 'link' ], $menuElement[ 'text' ], $options
                            ), $name, $menuElement[ 'icon' ], $position
                        );
                    } else {
                        $this->insert(
                            $this->linker->html->createPopupLink(
                                $menuElement[ 'link' ], $menuElement[ 'text' ], $menuElement[ 'width' ],
                                $menuElement[ 'height' ]
                            ), $name, $menuElement[ 'icon' ], $position
                        );
                    }
                }
            }
        }
        if( is_array( $masterMenu ) && $this->master ) {
            foreach( $masterMenu as $name => $menu ) {
                foreach( $menu as $position => $menuElement ) {
                    if( $menuElement[ 'type' ] != 'popup' ) {
                        $this->insert(
                            $this->linker->html->createLink(
                                $menuElement[ 'link' ], $menuElement[ 'text' ]
                            ), '[' . $name . ']', $menuElement[ 'icon' ], $position
                        );
                    } else {
                        $this->insert(
                            $this->linker->html->createPopupLink(
                                $menuElement[ 'link' ], $menuElement[ 'text' ], $menuElement[ 'width' ],
                                $menuElement[ 'height' ]
                            ), '[' . $name . ']', $menuElement[ 'icon' ], $position
                        );
                    }
                }
            }
        }
        return true;
    }

    /**
     * Gets the source code from the connection form
     * @return str
     * The connection form's html
     */
    public function getConnectionForm() {
        $datas[ 'form' ][ 'id' ] = 'admin_connection';
        return $this->render( 'connection', $datas, false, false );
    }

    /**
     * public function connection
     *
     */
    public function connection() {
        if( $this->formSubmitted( 'admin_connection', true ) === true ) {
            return true;
        }
        return false;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        list($class, $method, $id) = explode( '/', $page );
        $withoutId = array(
            'showMessages'
        );
        $withId = array(
        );
        if( $id === '' && in_array( $method, $withoutId ) ) {
            return '/' . $this->shortClassName . '/' . $method . '.php';
        } elseif( in_array( $method, $withId ) ) {
            return '/' . $this->shortClassName . '/' . $method . '/' . $id . '.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( preg_match( '`/' . $this->shortClassName . '/([^/]+)(/([0-9]+)(-[^/]+)?)?\.php`', $uri, $matches ) ) {
            $method = $matches[ 1 ];
            $id = $matches[ 3 ];
            $methods = array(
                'showMessages'
            );
            if( in_array( $method, $methods ) ) {
                return $this->shortClassName . '/' . $method . '/' . $id;
            }
        }
        return false;
    }

    public function __tostring() {
        return get_class();
    }

}

