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

class sh_user extends sh_core {

    const CLASS_VERSION = '1.1.12.02.02';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $minimal = array(
        'tryToConnect' => true, 'getUserData' => true, 'getOneUserId' => true, 'passwordForgotten_master' => true,
        'connection_step1_master' => true, 'connection_step2_master' => true, 'master_get_last_connection' => true,
        'master_set_connection_status' => true, 'master_get_connection_failures' => true,
        'master_clear_connection_failures' => true, 'editProfile_master' => true, 'editPassword_master' => true,
        'editPassphrase_master' => true, 'createConnectionTicket_master' => true, 'useConnectionTicket_master' => true,
        'master_verifyUsernameAvailability' => true, 'master_createAccount' => true, 'master_confirmAccountCreation' => true,
        'connection_single_step_master' => true, 'single_step_connection' => true
    );
    protected $allowedActionsWhenNotOpen = array(
        'useConnectionTicket', 'confirmAccountCreation'
    );
    protected $masterServer_methods = array(
        'master_clear_connection_failures', 'master_get_connection_failures', 'master_get_last_connection',
        'master_set_connection_status', 'editPassphrase_master', 'editPassword_master', 'editProfile_master',
        'passwordForgotten_master', 'getAllUsers_master', 'createConnectionTicket_master', 'useConnectionTicket_master',
        'getOneUserId', 'getUserData', 'isMasterServer', 'tryToConnect',
        'connection_step1_master', 'connection_step2_master', 'connection_step3_master',
        'master_verifyUsernameAvailability', 'master_createAccount', 'master_confirmAccountCreation',
        'connection_single_step_master'
    );
    protected $connected = false;
    protected $allowed = '*';
    const WRONG_DATA_TEXT = 'WRONG_DATA';
    const SITE_NOT_ALLOWED_TEXT = 'SITE_NOT_ALLOWED';
    const ERROR_USING_FORM_TEXT = 'ERROR_USING_FORM';
    const ERROR_LOGIN_ALREADY_IN_USE = 'ERROR_LOGIN_ALREADY_IN_USE';
    const ERROR_MAIL_ALREADY_IN_USE = 'ERROR_MAIL_ALREADY_IN_USE';
    const SHOPSAILORS_USERNAME_NOT_ALLOWED_TEXT = 'SHOPSAILORS_USERNAME_NOT_ALLOWED';
    const ACCOUNT_NOT_ACTIVATED_TEXT = 'ACCOUNT_NOT_ACTIVATED';
    const OK = 'OK';
    const USER_NAME = 'name';
    const USER_LOGIN = 'login';
    const NOT_CONNECTED = 'The user is not connected.';
    const USER_DATA_NOT_FOUND = 'This user data does not exist.';
    const PASSWORD_BASE_MD5 = 'ws_user';
    const DELAY_ELAPSED = 'DELAY_ELAPSED';
    const WRONG_PASSWORD = 'WRONG_PASSWORD';
    const WRONG_PASSWORD_FORMAT = 'WRONG_PASSWORD_FORMAT';
    const WRONG_PASSWORD_COPY = 'WRONG_PASSWORD_COPY';
    const PASSWORD_CHANGED_SUCCESSFULLY = 'PASSWORD_CHANGED_SUCCESSFULLY';
    const PASSPHRASE_CHANGED_SUCCESSFULLY = 'PASSWORD_CHANGED_SUCCESSFULLY';
    const WRONG_PASSPHRASE_FORMAT = 'WRONG_PASSWORD_FORMAT';

    const COOKIE_VALIDITY = 7776000; // 60*60*24*90 -> 90 days

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->linker->db->updateQueries( __CLASS__ );

            if( version_compare( $installedVersion, '1.1.11.03.28', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_cron', '', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_masterServer', '', __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, $this->className );
                // We also add the db table, even if we are not on a master server. May may have to use it lately...
                if( $this->isMasterServer( false ) ) {
                    $this->db_execute( 'create_table_users', array( ) );
                    $this->db_execute( 'create_table_connections_failures', array( ) );
                    $this->db_execute( 'create_table_connections_successes', array( ) );
                }
            }
            if( version_compare( $installedVersion, '1.1.11.12.05', '<' ) ) {
                if( $this->isMasterServer( false ) ) {
                    $this->db_execute( 'remove_case_in_logins', array( ) );
                }

                if( !is_dir( SH_SITE_FOLDER . __CLASS__ . '/tickets/' ) ) {
                    mkdir( SH_SITE_FOLDER . __CLASS__ . '/tickets/', 0775, true );
                }
            }
            if( version_compare( $installedVersion, '1.1.12.02.01', '<' ) ) {
                if( !$this->isMasterServer( false ) ) {
                    $this->db_execute( 'create_cookies_table', array( ) );
                }
            }
            if( version_compare( $installedVersion, '1.1.12.02.02', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_events', 'onAfterBaseConstruction', __CLASS__ );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        $this->getConnection();
        $this->allowed = $this->getParam( 'allowed' );

        $params = $this->getParam();

        $this->masterSite = SH_MASTERSERVER_SITE;
        $this->masterUrl = $this->linker->masterServer->getMasterServerUrl();

        $_SESSION[ __CLASS__ ][ 'accountJustCreated' ] = false;
        return true;
    }
    
    public function onAfterBaseConstruction(){
        if(!$this->isConnected()){
            // We check if there is a valid cookie
            $cookie_id = $this->linker->cookie->get('shopsailors_auto_connection','none');
            if($cookie_id != 'none'){
                list($user) = $this->db_execute(
                    'cookies_get', array( 'user' => $this->getUserId(), 'cookie' => $cookie_id )
                );
                $user = $user['user'];
                if($user){
                    $this->connectUser($user, false);
                    $this->linker->html->addMessage(
                        str_replace(
                            '[NAME]',
                            $_SESSION[ __CLASS__ ][ 'connected' ][ 'completeName' ],
                            $this->getI18n('connected_using_cookie')
                        ), 
                        false
                    );
                    // We re-set the cookie to extend its validity to 90 new days
                    $this->linker->cookie->set( 'shopsailors_auto_connection', $cookie_id, self::COOKIE_VALIDITY );
                }
                sleep(0.8);
            }
        }
    }

    public function master_getMenuContent() {
        return array( );
    }

    public function admin_getMenuContent() {
        $adminMenu = array( );
        /* $adminMenu['Contenu'][] = array(
          'link'=>'user/manage/','text'=>'Restrictions d\'accès','icon'=>'picto_security.png'
          ); */

        return $adminMenu;
    }

    ////////////////////////////////////////////////////////////////////////////////
    //                        USED ON EVERY CLIENT                                //
    ////////////////////////////////////////////////////////////////////////////////
    public function cron_job( $time ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( $time == sh_cron::JOB_DAY ) {
            // Everyday, we should clean the connection table from the database
            $this->db_execute( 'clear_older_connections', array( ) );
            $this->db_execute( 'cookies_deleteOlderThan', array( 'date' => time() + 60 * 60 * 24 * 90 ) );
        }
        return true;
    }

    public function masterServer_getMethods() {
        return $this->masterServer_methods;
    }

    public function getMasterUrl( $withEndSlash = true ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( $withEndSlash ) {
            return $this->masterUrl;
        } else {
            return substr( $this->masterUrl, 0, -1 );
        }
    }

    public function isConnected() {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        return $this->connected;
    }

    public function getUserId() {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( $this->isConnected() ) {
            return $_SESSION[ __CLASS__ ][ 'connected' ][ 'userId' ];
        }
        return false;
    }

    public function renderConnectionLink() {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        $values[ 'link' ][ 'connection' ] = $this->linker->path->getLink( __CLASS__ . '/connect/' );
        return $this->render( 'connectionLink', $values, false, false );
    }

    /**
     * Returns a boolean telling if the access to the page is granted to connected users or not.
     * @see set_needs_connection()
     * @return boolean
     * True if the page needs a connected user.<br />
     * False if not.
     */
    public static function needs_connection() {
        if( !is_dir( SH_SITE_FOLDER . __CLASS__ ) ) {
            mkdir( SH_SITE_FOLDER . __CLASS__ );
        }
        if( file_exists( SH_SITE_FOLDER . __CLASS__ . '/needs_connection.php' ) ) {
            return true;
        }
        return false;
    }

    /**
     * Shows the user's profile
     */
    public function profile() {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        sh_cache::disable();

        if( !$this->isConnected() ) {
            $this->linker->path->redirect( __CLASS__, 'connect' );
        }
        if( isset( $_SESSION[ __CLASS__ ][ 'message' ] ) ) {
            $values[ 'message' ][ 'text' ] = true;
            unset( $_SESSION[ __CLASS__ ][ 'message' ] );
        }

        $this->linker->html->setTitle( $this->getI18n( 'myAccount' ) );
        $values[ 'user' ] = $_SESSION[ __CLASS__ ][ 'connected' ];
        $values[ 'links' ][ 'editProfile' ] = $this->translatePageToUri( '/editProfile/' );
        $values[ 'links' ][ 'editPassphrase' ] = $this->translatePageToUri( '/editPassphrase/' );
        $values[ 'links' ][ 'editPassword' ] = $this->translatePageToUri( '/editPassword/' );

        $classes = $this->get_shared_methods( 'accountTabs' );
        foreach( $classes as $class ) {
            $tabs = $this->linker->$class->user_getAccountTabs();
            if( is_array( $tabs ) ) {
                foreach( $tabs as $id => $tab ) {
                    if( !isset( $tab[ 'uid' ] ) ) {
                        $tab[ 'uid' ] = 'tab_' . substr( md5( microtime() ), 0, 8 );
                    }
                    $values[ 'tabs' ][ $class . '_' . $id ] = $tab;
                }
            }
        }

        $this->render( 'showProfile', $values );
    }

    public function editPassphrase_master() {
        $this->checkIntegrity();
        sh_cache::disable();
        $site = $this->getFromAnyServer( 'site' );
        $id = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'id' ), $site );
        $login = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'login' ), $site );
        $new = trim( $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'new' ), $site ) );
        if( strlen( $new > 150 ) ) {
            $new = str_replace( $new, 0, 147 ) . '...';
        }
        if( strlen( $this->clearData( $new ) ) < 6 ) {
            exit( self::WRONG_PASSPHRASE_FORMAT );
        }

        $id_verif = $user[ 'id' ];
        $this->db_execute(
            'changeVerification',
            array(
            'verification' => addslashes( $new ),
            'id' => $id
            )
        );
        exit( self::OK );
    }

    public function editPassphrase() {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        sh_cache::disable();
        if( !$this->isConnected() ) {
            $this->linker->path->redirect( __CLASS__, 'connect' );
        }
        if( $this->formSubmitted( 'editPassphrase' ) ) {
            if( strlen( $_POST[ 'new' ] ) > 5 ) {
                // We ask the master server to change it
                // Gets and prepares the data
                $id = $_SESSION[ __CLASS__ ][ 'connected' ][ 'id' ];
                $site = $this->linker->masterServer->getSiteCode();

                // Crypts it
                $id = $this->linker->masterServer->crypt( $id );
                $login = $this->linker->masterServer->crypt( $_SESSION[ __CLASS__ ][ 'connected' ][ 'login' ] );
                $new = $this->linker->masterServer->crypt( strip_tags( $_POST[ 'new' ] ) );

                // Sends it
                $uri = $this->shortClassName . '/editPassphrase_master.php';
                $connectionPage = $this->masterUrl . $uri;
                $requestId = $this->linker->postRequest->create( $connectionPage );
                $this->linker->postRequest->setData( $requestId, 'site', urlencode( $site ) );
                $this->linker->postRequest->setData( $requestId, 'id', urlencode( $id ) );
                $this->linker->postRequest->setData( $requestId, 'login', urlencode( $login ) );
                $this->linker->postRequest->setData( $requestId, 'new', urlencode( strip_tags( $new ) ) );
                $response = $this->linker->postRequest->send( $requestId );
                if( $response == self::OK ) {
                    $_SESSION[ __CLASS__ ][ 'message' ] = self::PASSPHRASE_CHANGED_SUCCESSFULLY;
                    $_SESSION[ __CLASS__ ][ 'connected' ][ 'verification' ] = nl2br( strip_tags( $_POST[ 'new' ] ) );
                    $this->linker->path->redirect( __CLASS__, 'profile' );
                    exit;
                } else {
                    $values[ 'error' ][ 'id' ] = $response;
                }
            } else {
                $values[ 'error' ][ 'id' ] = self::WRONG_PASSPHRASE_FORMAT;
            }
        }
        $this->linker->html->setTitle( $this->getI18n( 'verificationPhrase' ) );
        $values[ 'old' ][ 'passphrase' ] = preg_replace( '`<br(?: /)?>([\\n\\r])`', '$1',
                                                         $_SESSION[ __CLASS__ ][ 'connected' ][ 'verification' ] );
        $this->render( 'editPassphrase', $values );
    }

    public function editPassword_master() {
        sh_cache::disable();
        $this->checkIntegrity();
        sleep( 1 );
        $site = $this->getFromAnyServer( 'site' );
        $id = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'id' ), $site );
        $login = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'login' ), $site );
        $old = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'old' ), $site );
        $new = trim( $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'new' ), $site ) );
        $verif = trim( $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'verif' ), $site ) );
        if( $new != $verif ) {
            exit( self::WRONG_PASSWORD_COPY );
        }
        if( strlen( $this->clearData( $new ) ) < 6 ) {
            exit( self::WRONG_PASSWORD_FORMAT );
        }
        list($user) = $this->db_execute(
            'checkUser',
            array(
            'login' => $login,
            'password' => $this->preparePassword( $this->clearData( $old ) )
            )
        );

        $id_verif = $user[ 'id' ];
        if( $id == $id_verif ) {
            $this->db_execute(
                'changePassword',
                array(
                'newPassword' => $this->preparePassword( $this->clearData( $new ) ),
                'id' => $id
                )
            );
            exit( self::OK );
        } else {
            exit( self::WRONG_PASSWORD );
        }
    }

    public function editPassword() {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        sh_cache::disable();
        if( !$this->isConnected() ) {
            $this->linker->path->redirect( __CLASS__, 'connect' );
        }
        if( $this->formSubmitted( 'editPassword' ) ) {
            if( $_POST[ 'password' ][ 'new' ] == $_POST[ 'password' ][ 'verif' ] && strlen( trim( $_POST[ 'password' ][ 'new' ] ) ) > 5 ) {
                // We ask the master server to change it
                // Gets and prepares the data
                $id = $_SESSION[ __CLASS__ ][ 'connected' ][ 'id' ];
                $site = $this->linker->masterServer->getSiteCode();

                // Crypts it
                $id = $this->linker->masterServer->crypt( $id );
                $login = $this->linker->masterServer->crypt( $_SESSION[ __CLASS__ ][ 'connected' ][ 'login' ] );
                $old = $this->linker->masterServer->crypt( $_POST[ 'password' ][ 'old' ] );
                $new = $this->linker->masterServer->crypt( $_POST[ 'password' ][ 'new' ] );
                $verif = $this->linker->masterServer->crypt( $_POST[ 'password' ][ 'verif' ] );

                // Sends it
                $uri = $this->shortClassName . '/editPassword_master.php';
                $connectionPage = $this->masterUrl . $uri;
                $requestId = $this->linker->postRequest->create( $connectionPage );
                $this->linker->postRequest->setData( $requestId, 'id', urlencode( $id ) );
                $this->linker->postRequest->setData( $requestId, 'site', urlencode( $site ) );
                $this->linker->postRequest->setData( $requestId, 'login', urlencode( $login ) );
                $this->linker->postRequest->setData( $requestId, 'old', urlencode( $old ) );
                $this->linker->postRequest->setData( $requestId, 'new', urlencode( $new ) );
                $this->linker->postRequest->setData( $requestId, 'verif', urlencode( $verif ) );
                $response = $this->linker->postRequest->send( $requestId );
                if( $response == self::OK ) {
                    $_SESSION[ __CLASS__ ][ 'message' ] = self::PASSWORD_CHANGED_SUCCESSFULLY;
                    $this->linker->path->redirect( __CLASS__, 'profile' );
                    exit;
                } else {
                    $values[ 'error' ][ 'id' ] = $response;
                }
            } elseif( $_POST[ 'password' ][ 'new' ] != $_POST[ 'password' ][ 'verif' ] ) {
                $values[ 'error' ][ 'id' ] = self::WRONG_PASSWORD_COPY;
            } else {
                $values[ 'error' ][ 'id' ] = self::WRONG_PASSWORD_FORMAT;
            }
        }
        $this->linker->html->setTitle( $this->getI18n( 'passWord' ) );
        $this->render( 'editPassword', $values );
    }

    /**
     * Edits the user's profile
     */
    public function editProfile() {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        sh_cache::disable();

        if( !$this->isConnected() ) {
            $this->linker->path->redirect( __CLASS__, 'connect' );
        }
        if( $this->formSubmitted( 'editProfile' ) ) {
            // Gets and prepares the data
            $id = $_SESSION[ __CLASS__ ][ 'connected' ][ 'id' ];
            $login = $_SESSION[ __CLASS__ ][ 'connected' ][ 'login' ];
            $password = $_SESSION[ __CLASS__ ][ 'connected' ][ 'password' ];
            $site = $this->linker->masterServer->getSiteCode();

            // Crypts it
            $id = $this->linker->masterServer->crypt( $id );
            $login = $this->linker->masterServer->crypt( $login );
            $password = $this->linker->masterServer->crypt( $password );
            $name = $this->linker->masterServer->crypt( $_POST[ 'name' ] );
            $lastName = $this->linker->masterServer->crypt( $_POST[ 'lastName' ] );
            $mail = $this->linker->masterServer->crypt( $_POST[ 'mail' ] );
            $phone = $this->linker->masterServer->crypt( $_POST[ 'phone' ] );
            $address = $this->linker->masterServer->crypt( $_POST[ 'address' ] );
            $zip = $this->linker->masterServer->crypt( $_POST[ 'zip' ] );
            $city = $this->linker->masterServer->crypt( $_POST[ 'city' ] );

            // Sends it
            $uri = $this->shortClassName . '/editProfile_master.php';
            $connectionPage = $this->masterUrl . $uri;
            $requestId = $this->linker->postRequest->create( $connectionPage );
            $this->linker->postRequest->setData( $requestId, 'id', urlencode( $id ) );
            $this->linker->postRequest->setData( $requestId, 'login', urlencode( $login ) );
            $this->linker->postRequest->setData( $requestId, 'password', urlencode( $password ) );
            $this->linker->postRequest->setData( $requestId, 'site', urlencode( $site ) );
            $this->linker->postRequest->setData( $requestId, 'name', urlencode( $name ) );
            $this->linker->postRequest->setData( $requestId, 'lastName', urlencode( $lastName ) );
            $this->linker->postRequest->setData( $requestId, 'mail', urlencode( $mail ) );
            $this->linker->postRequest->setData( $requestId, 'phone', urlencode( $phone ) );
            $this->linker->postRequest->setData( $requestId, 'address', urlencode( $address ) );
            $this->linker->postRequest->setData( $requestId, 'zip', urlencode( $zip ) );
            $this->linker->postRequest->setData( $requestId, 'city', urlencode( $city ) );
            $response = $this->linker->postRequest->send( $requestId );
            if( false || $response == self::OK ) {
                // Done - We take the new datas
                $userData = $this->getOneUserData( $_SESSION[ __CLASS__ ][ 'connected' ][ 'id' ] );
                $_SESSION[ __CLASS__ ][ 'connected' ] = $userData;
                $_SESSION[ __CLASS__ ][ 'connected' ][ 'userId' ] = $_SESSION[ __CLASS__ ][ 'connected' ][ 'id' ];
                $this->linker->path->redirect(
                    __CLASS__, 'profile'
                );
            } else {
                // Error !!!
            }
        }
        $this->linker->html->setTitle( $this->getI18n( 'myAccount' ) );
        $values[ 'user' ] = $_SESSION[ __CLASS__ ][ 'connected' ];
        $this->render( 'editProfile', $values );
    }

    public function editProfile_master() {
        $this->checkIntegrity();
        $site = $this->getFromAnyServer( 'site' );
        $id = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'id' ), $site );
        $login = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'login' ), $site );
        $password = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'password' ), $site );
        $name = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'name' ), $site );
        $lastName = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'lastName' ), $site );
        $mail = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'mail' ), $site );
        $phone = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'phone' ), $site );
        $address = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'address' ), $site );
        $zip = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'zip' ), $site );
        $city = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'city' ), $site );
        list($user) = $this->db_execute(
            'checkUser', array(
            'login' => $login,
            'password' => $password
            )
        );
        $id_verif = $user[ 'id' ];
        if( $id == $id_verif ) {
            if( strtolower( $mail ) != $user[ 'mail' ] ) {
                // If the mail changed, we should send a message with a validation link.
            }
            $this->db_execute(
                'updateAccount',
                array(
                'name' => $name,
                'lastName' => $lastName,
                'mail' => $mail,
                'phone' => $phone,
                'address' => $address,
                'zip' => $zip,
                'city' => $city,
                'verification' => $verification,
                'login' => $login
                )
            );
            exit( self::OK );
        } else {
            exit( self::WRONG_DATA_TEXT );
        }
    }

    /**
     * Shows the form to enable and manage access restrictions to the site
     */
    public function manage() {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        $this->onlyAdmin( true );
        if( $this->formSubmitted( 'restrictionsEditor' ) ) {
            if( isset( $_POST[ 'needs_connection' ] ) ) {
                $this->set_needs_connection( true );
            } else {
                $this->set_needs_connection( false );
            }
            $mails = str_replace( array( ' ', ',', ';' ), "\n", $_POST[ 'allowedUsers' ] );
            $mailsList = explode( "\n", $mails );
            if( is_array( $mailsList ) ) {
                if( file_exists( SH_SITE_FOLDER . __CLASS__ . '/sentMails.php' ) ) {
                    include(SH_SITE_FOLDER . __CLASS__ . '/sentMails.php');
                }
                if( !is_array( $sentMails ) ) {
                    $sentMails = array( );
                }
                foreach( $mailsList as $mail ) {
                    $mail = trim( $mail );
                    $mailer = $this->linker->mailer->get();
                    if( $mailer->checkAddress( $mail ) ) {
                        $userId = $this->prot_getOneUserId( 'mail', $mail );
                        if( $userId == 0 ) {
                            if( !in_array( $mail, $sentMails ) ) {
                                $values[ 'websailors' ][ 'createAccountPage' ] =
                                    'http://www.websailors.fr/connection/create_account.php';
                                $values[ 'client' ][ 'site' ] = 'http://' . $this->linker->path->getDomain();
                                $values[ 'dest' ][ 'mail' ] = $mail;

                                $content = $this->render( 'mailModel', $values, false, false );




                                $mailObject = $mailer->em_create();
                                // Creating and sending the email itself
                                $address = $user[ 'mail' ];

                                $mails = explode( "\n", $this->getParam( 'command_mail' ) );
                                if( is_array( $mails ) ) {
                                    foreach( $mails as $oneMail ) {
                                        $mailer->em_addBCC( $mailObject, $oneMail );
                                    }
                                }

                                $mailer->em_addSubject(
                                    $mailObject,
                                    $this->getI18n( 'mail_authorization_title' ) . 'http://' . $this->linker->path->getDomain()
                                );
                                $mailer->em_addContent( $mailObject, $content );

                                if( !$mailer->em_send( $mailObject, array( array( $mail ) ) ) ) {
                                    // Error sending the email
                                    echo 'Erreur dans l\'envoi du mail de validation...';
                                }

                                $sentMails[ ] = $mail;
                            }
                            $inexistantButAllowedUsers[ ] = $mail;
                        } else {
                            if( !in_array( $mail, $sentMails ) ) {
                                $values[ 'websailors' ][ 'createAccountPage' ] =
                                    'http://www.websailors.fr/connection/create_account.php';
                                $values[ 'client' ][ 'site' ] = 'http://' . $this->linker->path->getDomain();
                                $values[ 'dest' ][ 'mail' ] = $mail;

                                $content = $this->render( 'mailAccountAuthorized', $values, false, false );

                                // Creating and sending the email itself
                                $mailObject = $mailer->em_create();
                                $address = $user[ 'mail' ];

                                $mails = explode( "\n", $this->getParam( 'command_mail' ) );
                                if( is_array( $mails ) ) {
                                    foreach( $mails as $oneMail ) {
                                        $mailer->em_addBCC( $mailObject, $oneMail );
                                    }
                                }

                                $mailer->em_addSubject(
                                    $mailObject,
                                    $this->getI18n( 'mail_authorization_title' ) . 'http://' . $this->linker->path->getDomain()
                                );
                                $mailer->em_addContent( $mailObject, $content );

                                if( !$mailer->em_send( $mailObject, array( array( $mail ) ) ) ) {
                                    // Error sending the email
                                    echo 'Erreur dans l\'envoi du mail de validation...';
                                }

                                $sentMails[ ] = $mail;
                            }
                            $allowedUser[ ] = $userId;
                        }
                    }
                }
                $this->helper->writeArrayInFile(
                    SH_SITE_FOLDER . __CLASS__ . '/allowed.php', 'allowedUsers', $allowedUser
                );
                $this->helper->writeArrayInFile(
                    SH_SITE_FOLDER . __CLASS__ . '/inexistantButAllowedUsers.php', 'inexistantButAllowedUsers',
                    $inexistantButAllowedUsers
                );
                $this->helper->writeArrayInFile(
                    SH_SITE_FOLDER . __CLASS__ . '/sentMails.php', 'sentMails', $sentMails
                );
            }
        }

        if( self::needs_connection( true ) ) {
            $values[ 'form' ][ 'needs_connection' ] = 'checked';
        }
        if( file_exists( SH_SITE_FOLDER . __CLASS__ . '/allowed.php' ) ) {
            include(SH_SITE_FOLDER . __CLASS__ . '/allowed.php');
            if( is_array( $allowedUsers ) ) {
                foreach( $allowedUsers as $allowedUser ) {
                    $userData = $this->getOneUserData( $allowedUser );
                    $values[ 'allowed' ][ 'mails' ] .= $separator . $userData[ 'mail' ];
                    $separator = "\n";
                }
            }
        }
        if( file_exists( SH_SITE_FOLDER . __CLASS__ . '/inexistantButAllowedUsers.php' ) ) {
            include(SH_SITE_FOLDER . __CLASS__ . '/inexistantButAllowedUsers.php');
            if( is_array( $inexistantButAllowedUsers ) ) {
                foreach( $inexistantButAllowedUsers as $inexistantButAllowedUser ) {
                    $values[ 'allowed' ][ 'mails' ] .= $separator . $inexistantButAllowedUser;
                    $separator = "\n";
                }
            }
        }
        $this->render( 'manage', $values );
    }

    /**
     * Sets the need connection state, adding or removing a file under<br />
     * SH_SITE_FOLDER/[class_name].<br />
     * This file name is needs_connection.php. If it exists, it means that the site
     * needs a connection.
     * @param boolean $state The state we want to set
     */
    public function set_needs_connection( $state ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( $state ) {
            $f = fopen( SH_SITE_FOLDER . __CLASS__ . '/needs_connection.php', 'w+' );
            fclose( $f );
        } else {
            if( file_exists( SH_SITE_FOLDER . __CLASS__ . '/needs_connection.php' ) ) {
                unlink( SH_SITE_FOLDER . __CLASS__ . '/needs_connection.php' );
            }
        }
        return true;
    }

    /**
     * Requests a user's id to the master website, using any field.<br />
     * Will call the url taken from the params file in master>getOneUserId.
     * @param string $field Mysql's field name in which we will look to find the value $value.
     * @param string $value Value that we are looking for in the database, in the field $field of the
     * table "users"
     * @return integer The id that have been found in the databse, or 0 if none were found.
     */
    protected function prot_getOneUserId( $field, $value ) {
        $connectionPage = $this->masterUrl . $this->getParam( 'master>getOneUserId' );
        $requestId = $this->linker->postRequest->create( $connectionPage );
        $this->linker->postRequest->setData( $requestId, 'field', $field );
        $this->linker->postRequest->setData( $requestId, 'value', $value );
        $id = $this->linker->postRequest->send( $requestId );
        return $id;
    }

    /**
     * Verifies that the site can be accessed by the current user.<br />
     * - If there is no need to be connected, it always returns true.<br />
     * - If there is a need to be connected, and the user is connected AND allowed
     * to access this site, it also returns true.<br />
     * - In any other cases, it returns false.
     */
    public function siteIsOpen() {
        if( !self::needs_connection() || $this->getConnection() === true ) {
            return true;
        }
        // Authorises the shared images
        if( substr( $this->linker->path->uri, 0, strlen( SH_SHAREDIMAGES_PATH ) ) == SH_SHAREDIMAGES_PATH ) {
            return true;
        }
        // Authorises the banner image
        if( $this->linker->path->uri == $this->getParam( 'banner_image' ) ) {
            return true;
        }
        // Authorises the favicon
        if( $this->linker->path->uri == $this->linker->favicon->getPath() ) {
            return true;
        }

        // Gets some variables
        $element = $this->linker->path->page[ 'element' ];
        $action = $this->linker->path->page[ 'action' ];
        if( is_array( $this->linker->$element->allowedActionsWhenNotOpen ) ) {
            if( in_array( $action, $this->linker->$element->allowedActionsWhenNotOpen ) ) {
                return true;
            }
        }

        $this->linker->cache->disable();
        if( $this->linker->path->page[ 'page' ] == $this->shortClassName . '/passwordForgotten/' ) {
            $values[ 'page' ][ 'connectionForm' ] = $this->passwordForgotten( false );
        } else {
            $values[ 'page' ][ 'connectionForm' ] = $this->connect( false );

            if( $this->getConnection() ) {
                $this->linker->path->refresh();
                return true;
            }
        }

        $values[ 'banner' ][ 'image' ] = $this->getParam( 'banner_image' );

        $values[ 'site' ][ 'name' ] = $this->linker->path->getDomain();

        echo $this->render( 'index', $values, false, false );
        exit;
    }

    /**
     * Prepares and returns the link to access connection page and disconnection page
     * @return array(string,string) Returns an array containing the state of the link
     * (connect or disconnect) as first argument, and the link as second.
     */
    public function getConnectionLink() {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( $this->isConnected() ) {
            return array( 'disconnect', $this->linker->path->getLink( 'user/disconnect/' ) );
        }
        return array( 'connect', $this->linker->path->getLink( 'user/connect/' ) );
    }

    /**
     * Prepares and returns the link to access connection page and disconnection page
     * @return array(string,string) Returns an array containing the state of the link
     * (connect or disconnect) as first argument, and the link as second.
     */
    public function getProfileLink() {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        return $this->linker->path->getLink( 'user/profile/' );
    }

    /**
     *
     * @return boolean The status of the connection.<br />
     * True if the user is connected.<br />
     * False if not.
     */
    protected function getConnection() {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( isset( $_SESSION[ __CLASS__ ][ 'connected' ][ 'userId' ] ) ) {
            $this->userId = $_SESSION[ __CLASS__ ][ 'connected' ][ 'userId' ];
            $this->connected = true;
            return true;
        }
        return false;
    }

    /**
     * Get all (or some of, depending on $fields) one user's params from the database, using the master site.<br />
     * The url that is called is set in the params under master>getUserData.<br />
     * Data is cached to avoid multiple identical queries. To remove cached datas, call this
     * method with $reInit = true.
     * @param integer $id Id of the user we want the params. May be null if $reInit = true.
     * @params array(str,str,...) $fields The fields names we want to get.
     * @param bool $reInit If true, nothing else than emptying the cache is done.
     * @return array()|bool(true) All the user's datas as $field=>$value entries in the array, or true
     * for $reInit = true.
     */
    public function getOneUserData( $id = null, $fields = null, $reInit = false ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        static $datas = array( );
        if( $reInit ) {
            $datas = array( );
            return true;
        }
        if( is_null( $fields ) ) {
            $datas_entry_name = 'default@' . $id;
        } else {
            $datas_entry_name = implode( '|', $fields ) . '@' . $id;
        }
        if( !isset( $datas[ $datas_entry_name ] ) ) {
            // Avoids making multiple calls to the master server for the same query
            $connectionPage = $this->masterUrl . $this->getParam( 'master>getUserData' );
            $requestId = $this->linker->postRequest->create( $connectionPage );
            $this->linker->postRequest->setData( $requestId, 'user', $id );
            $response = $this->linker->postRequest->send( $requestId );
            $entries = explode( "\n" . sh_masterServer::LINE_SEPARATOR . "\n", $response );
            foreach( $entries as $entry ) {
                list($fieldName, $fieldValue) = explode( "\n", $entry );
                if( $fieldName != '' && (is_null( $fields ) || in_array( $fieldName, $fields )) ) {
                    $ret[ $fieldName ] = $fieldValue;
                }
            }
            $datas[ $datas_entry_name ] = $ret;
        }
        return $datas[ $datas_entry_name ];
    }

    /**
     * Calls getOneUserData and returns its results with the user id stored in the session,
     * if the user is connected.<br />
     * If not, returns false.
     */
    public function getData() {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        if( $this->getConnection() ) {
            return $this->getOneUserData( $_SESSION[ __CLASS__ ][ 'connected' ][ 'userId' ] );
        }
        return false;
    }

    public function passwordForgotten_master() {
        $site = $this->getFromAnyServer( 'site' );
        $mail = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'mail' ), $site );
        $siteName = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'siteName' ), $site );

        $user = $this->db_execute(
            'getOneUserId', array(
            'field' => 'mail',
            'value' => $mail
            )
        );
        $id = $user[ 0 ][ 'id' ];
        $newPassword = substr( md5( __CLASS__ . microtime() ), 0, 8 );
        $dbPassword = $this->preparePassword( $newPassword );
        $this->db_execute( 'addTemporaryPassword',
                           array(
            'id' => $id,
            'temporaryPassword' => $dbPassword
        ) );

        $values[ 'password' ][ 'new' ] = $newPassword;
        $content = $this->render( 'mailTemporaryPassword', $values, false, false );

        $mailer = $this->linker->mailer->get();
        // Creating and sending the email itself
        $mailObject = $mailer->em_create();
        $address = $user[ 'mail' ];

        $mails = explode( "\n", $this->getParam( 'command_mail' ) );
        if( is_array( $mails ) ) {
            foreach( $mails as $oneMail ) {
                $mailer->em_addBCC( $mailObject, $oneMail );
            }
        }

        $mailer->em_addSubject(
            $mailObject, $this->getI18n( 'mail_temporaryPassword_title' ) . ' - ' . $siteName
        );
        $mailer->em_addContent( $mailObject, $content );

        if( !$mailer->em_send( $mailObject, array( array( $mail ) ) ) ) {
            // Error sending the email
            echo 'Erreur dans l\'envoi du mail...';
            return false;
        }
        echo 'OK';
        return true;
    }

    public function passwordForgotten( $sendToHtml = true ) {
        $this->debug( __FUNCTION__, 2, __LINE__ );
        $this->linker->cache->disable();
        //delays the script also to prevent brutforce attacks
        if( $this->formSubmitted( 'passwordForgotten' ) ) {

            $mailer = $this->linker->mailer->get();
            if( $mailer->checkAddress( $_POST[ 'mail' ] ) ) {
                // Gets and prepares the data
                $mail = trim( $_POST[ 'mail' ] );
                $site = $this->linker->masterServer->getSiteCode();

                // Crypts it
                $mail = $this->linker->masterServer->crypt( $mail );
                $siteName = $this->linker->masterServer->crypt( SH_SITENAME );

                // Sends it
                $connectionPage = $this->masterUrl . $this->getParam( 'master>passwordForgotten' );
                $requestId = $this->linker->postRequest->create( $connectionPage );
                $this->linker->postRequest->setData( $requestId, 'mail', urlencode( $mail ) );
                $this->linker->postRequest->setData( $requestId, 'site', urlencode( $site ) );
                $this->linker->postRequest->setData( $requestId, 'siteName', urlencode( $siteName ) );
                $response = $this->linker->postRequest->send( $requestId );
                if( $response == 'OK' ) {
                    return $this->render( 'passwordForgotten_response', $values, false, $sendToHtml );
                }
            }
            $values[ 'error' ][ 'message' ] = $this->getI18n( 'passwordForgotten_response_text_notfound' );
        }
        if( $sendToHtml ) {
            $this->render( 'passwordForgotten', $values );
            return false;
        }
        return $this->render( 'passwordForgotten', $values, false, false );
    }

    protected function get_last_connection( $user ) {
        $this->debug( __FUNCTION__ . '(' . $user . ')', 2, __LINE__ );
        $connectionPage = $this->masterUrl . $this->getParam( 'master>get_last_connection' );
        $this->debug( 'Connection page is ' . $connectionPage, 3, __LINE__ );
        $requestId = $this->linker->postRequest->create( $connectionPage );
        $this->linker->postRequest->setData( $requestId, 'user', $user );
        $response = $this->linker->postRequest->send( $requestId );
        $ret = $this->splitReturn( $response );
        return $ret;
    }

    public function master_get_last_connection() {
        $this->checkIntegrity();
        $user = $this->getFromAnyServer( 'user' );
        // It is a successfull connection
        list($ret) = $this->db_execute(
            'get_connection_successfull', array( 'user' => $user ), $qry
        );
        if( isset( $ret[ 'date' ] ) ) {
            echo 'site' . "\n" . $ret[ 'site' ] . "\n" . sh_masterServer::LINE_SEPARATOR . "\n";
            echo 'date' . "\n" . $ret[ 'date' ] . "\n";
        } else {
            echo 'No return ' . "\n" . 'qry : ' . str_replace( "\n", ' ', $qry ) . "\n";
        }
    }

    protected function splitReturn( $return ) {
        return $this->linker->masterServer->splitReturn( $return );
    }

    protected function get_connection_failures( $user ) {
        $this->debug( __FUNCTION__ . '(' . $user . ')', 2, __LINE__ );
        $connectionPage = $this->masterUrl . $this->getParam( 'master>get_connection_failures' );
        $requestId = $this->linker->postRequest->create( $connectionPage );
        $this->linker->postRequest->setData( $requestId, 'user', $user );
        $response = $this->linker->postRequest->send( $requestId );
        $ret = $this->splitReturn( $response );
        return $ret;
    }

    public function master_get_connection_failures() {
        $this->checkIntegrity();
        $user = $this->getFromAnyServer( 'user' );
        // It is a successfull connection
        $ret = $this->db_execute(
            'get_connection_failures', array( 'user' => $user ), $qry
        );
        $cpt = 0;
        if( is_array( $ret ) ) {
            list($number) = $this->db_execute(
                'get_connection_failures_number', array( 'user' => $user ), $qry
            );
            $number = $number[ 'count' ];
            foreach( $ret as $oneFailure ) {
                echo 'failure_' . $cpt . '_date' . "\n" . $oneFailure[ 'date' ] . "\n" . sh_masterServer::LINE_SEPARATOR . "\n";
                echo 'failure_' . $cpt . '_site' . "\n" . $oneFailure[ 'site' ] . "\n" . sh_masterServer::LINE_SEPARATOR . "\n";
                echo 'failure_' . $cpt . '_ip' . "\n" . $oneFailure[ 'ip' ] . "\n" . sh_masterServer::LINE_SEPARATOR . "\n";
                $cpt++;
            }
            echo 'number' . "\n" . $number;
        } else {
            echo 'number' . "\n" . '0';
        }
    }

    protected function set_connection_status( $site, $user, $status ) {
        $this->debug( __FUNCTION__ . '(' . $site . ', ' . $user . ', ' . $status . ')', 2, __LINE__ );
        $connectionPage = $this->masterUrl . $this->getParam( 'master>set_connection_status' );
        $requestId = $this->linker->postRequest->create( $connectionPage );
        $this->linker->postRequest->setData( $requestId, 'site', $site );
        $this->linker->postRequest->setData( $requestId, 'user', $user );
        $this->linker->postRequest->setData( $requestId, 'status', $status );
        $this->linker->postRequest->setData( $requestId, 'ip', $_SERVER[ 'REMOTE_ADDR' ] );
        $response = $this->linker->postRequest->send( $requestId );
        $entries = explode( "\n" . sh_masterServer::LINE_SEPARATOR . "\n", $response );
        foreach( $entries as $entry ) {
            list($fieldName, $fieldValue) = explode( "\n", $entry );
            if( $fieldName != '' ) {
                $ret[ $fieldName ] = $fieldValue;
            }
        }
        return $ret;
    }

    public function master_set_connection_status() {
        $this->checkIntegrity();
        $site = $this->getFromAnyServer( 'site' );
        $user = $this->getFromAnyServer( 'user' );
        $status = $this->getFromAnyServer( 'status' );
        $ip = $this->getFromAnyServer( 'ip' );
        if( $status ) {
            // It is a successfull connection
            list($ret) = $this->db_execute(
                'get_connection_successfull', array( 'user' => $user ), $qry
            );
            if( isset( $ret[ 'date' ] ) ) {
                // There is already an entry, so we modify it
                $this->db_execute(
                    'update_connection_successfull', array( 'site' => $site, 'user' => $user ), $qry
                );
                echo 'action' . "\n" . 'Success - Entry updated';
            } else {
                // It's the first connection, so we add an entry
                $this->db_execute(
                    'add_connection_successfull', array( 'site' => $site, 'user' => $user ), $qry
                );
                echo 'action' . "\n" . 'Success - Entry added';
            }
        } else {
            // It is a connection failure
            list($ret) = $this->db_execute(
                'add_connection_failure', array( 'site' => $site, 'user' => $user, 'ip' => $ip ), $qry
            );
            echo 'action' . "\n" . 'Failure - Entry added';
        }
    }

    protected function clear_connection_failures( $user ) {
        $this->debug( __FUNCTION__ . '(' . $site . ', ' . $user . ')', 2, __LINE__ );
        $connectionPage = $this->masterUrl . $this->getParam( 'master>clear_connection_failures' );
        $requestId = $this->linker->postRequest->create( $connectionPage );
        $this->linker->postRequest->setData( $requestId, 'user', $user );
        $response = $this->linker->postRequest->send( $requestId );
        return true;
    }

    public function master_clear_connection_failures() {
        $this->checkIntegrity();
        $user = $this->getFromAnyServer( 'user' );
        $this->db_execute(
            'clear_connections_failures', array( 'user' => $user )
        );
        echo 'OK';
    }

    public function connection_step1_master() {
        $this->checkIntegrity();
        sleep( 0.5 );
        $site = $this->getFromAnyServer( 'site' );
        $userName = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'user' ), $site );
        list($user) = $this->db_execute( 'getOneUserVerification', array( 'userName' => $userName ) );
        if( isset( $user[ 'verification' ] ) && $user[ 'active' ] == '1' ) {
            echo 'id' . "\n" . $user[ 'id' ] . "\n" . sh_masterServer::LINE_SEPARATOR . "\n";
            echo 'verification' . "\n" . $user[ 'verification' ];
            return true;
        }
        return false;
    }

    public function connection_step2_master() {
        $this->checkIntegrity();
        sleep( 0.5 );
        $site = $this->getFromAnyServer( 'site' );
        $userName = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'user' ), $site );
        $password = $this->preparePassword( $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'password' ),
                                                                                                           $site ) );
        $verifPhrase = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'verifPhrase' ), $site );
        list($user) = $this->db_execute(
            'connectOneUser',
            array(
            'userName' => $userName,
            'password' => $password,
            'verification' => $verifPhrase
            )
        );
        if( isset( $user[ 'id' ] ) ) {
            echo $user[ 'id' ];
            return true;
        }
        list($user) = $this->db_execute(
            'connectOneUserWithNewPassword',
            array(
            'userName' => $userName,
            'temporaryPassword' => $password,
            'verification' => $verifPhrase
            )
        );
        if( isset( $user[ 'id' ] ) ) {
            $this->db_execute( 'changePassword', array( 'id' => $user[ 'id' ], 'newPassword' => $password ) );
            echo $user[ 'id' ];
            return true;
        }
        return false;
    }

    public function getAllUsers_master() {
        // Only available oon master server
        $this->isMasterServer();
        $users = $this->db_execute( 'getAllUsers', array( ) );
        foreach( $users as $user ) {
            $ret[ ] = $user[ 'id' ];
        }
        return $ret;
    }

    /**
     * Creates and sets the connection cookie.
     * This cookie is not declared on the master server, are setting it or using it is only available
     * in this domain, and also because there is no private datas attached to it.
     * @param type $session_id
     * @return type 
     */
    protected function connection_create_cookie($user) {
        $cookie_id = md5( __FILE__ . rand( 0, 1000000 ) ) . md5( microtime() . rand( 0, 1000000 ) );
        $this->linker->cookie->set( 'shopsailors_auto_connection', $cookie_id, self::COOKIE_VALIDITY );
        $this->db_execute(
            'cookies_create',
            array( 'user' => $user, 'cookie' => $cookie_id, 'expire' => date('Y-m-d',time() + self::COOKIE_VALIDITY ))
        );
        return true;
    }

    protected function connection_delete_cookie() {
        $cookie_id = $this->linker->cookie->get('shopsailors_auto_connection','none');
        if($cookie_id != 'none'){
            $this->db_execute(
                'cookies_delete', array( 'user' => $this->getUserId(), 'cookie' => $cookie_id )
            );
            $this->linker->cookie->destroy( 'shopsailors_auto_connection' );
        }
    }

    protected function connection_delete_cookies_for_user() {
        $this->linker->cookie->destroy( 'shopsailors_auto_connection' );
        $this->db_execute(
            'cookies_deleteAllForUser', array( 'user' => $this->getUserId() )
        );
    }

    public function connection_step1( $userName ) {
        if( !isset( $_SESSION[ __CLASS__ ][ 'delay' ] ) ) {
            $_SESSION[ __CLASS__ ][ 'delay' ] = 0.5;
        }
        sleep( $_SESSION[ __CLASS__ ][ 'delay' ] );
        // Gets and prepares the data
        $userName = $this->clearData( $userName );
        $site = $this->linker->masterServer->getSiteCode();

        // Crypts it
        $userName = $this->linker->masterServer->crypt( $userName );

        // Sends it
        $uri = $this->shortClassName . '/connection_step1_master.php';
        $connectionPage = $this->masterUrl . $uri;
        $requestId = $this->linker->postRequest->create( $connectionPage );
        $this->linker->postRequest->setData( $requestId, 'user', urlencode( $userName ) );
        $this->linker->postRequest->setData( $requestId, 'site', urlencode( $site ) );
        $response = $this->linker->postRequest->send( $requestId );
        if( $rep !== false ) {
            $ret = $this->splitReturn( $response );
        }
        return $ret;
    }

    public function connection_step2( $password ) {
        if( !isset( $_SESSION[ __CLASS__ ][ 'delay' ] ) ) {
            $_SESSION[ __CLASS__ ][ 'delay' ] = 0.5;
        }
        sleep( $_SESSION[ __CLASS__ ][ 'delay' ] );
        $_SESSION[ __CLASS__ ][ 'delay' ] *= 2;
        // Gets and prepares the data
        $password = $this->clearData( $password );
        $verifPhrase = $_SESSION[ __CLASS__ ][ 'identification' ][ 'verifPhrase' ];
        $userName = $this->clearData( $_SESSION[ __CLASS__ ][ 'identification' ][ 'userName' ] );
        $site = $this->linker->masterServer->getSiteCode();

        // Crypts it
        $userName = $this->linker->masterServer->crypt( $userName, $site );
        $password = $this->linker->masterServer->crypt( $password, $site );
        $verifPhrase = $this->linker->masterServer->crypt( $verifPhrase, $site );

        // Sends it
        $uri = $this->shortClassName . '/connection_step2_master.php';
        $connectionPage = $this->masterUrl . $uri;
        $requestId = $this->linker->postRequest->create( $connectionPage );
        $this->linker->postRequest->setData( $requestId, 'user', urlencode( $userName ) );
        $this->linker->postRequest->setData( $requestId, 'password', urlencode( $password ) );
        $this->linker->postRequest->setData( $requestId, 'verifPhrase', urlencode( $verifPhrase ) );
        $this->linker->postRequest->setData( $requestId, 'site', urlencode( $site ) );
        $response = $this->linker->postRequest->send( $requestId );
        return $response;
    }

    protected function connect_single_step( $user, $password ) {
        sh_cache::disable();
        if( !isset( $_SESSION[ __CLASS__ ][ 'delay' ] ) ) {
            $_SESSION[ __CLASS__ ][ 'delay' ] = 0.5;
        }
        sleep( $_SESSION[ __CLASS__ ][ 'delay' ] );
        $_SESSION[ __CLASS__ ][ 'delay' ] *= 2;
        // Gets and prepares the data
        $userName = $this->clearData( $user );
        $password = $this->clearData( $password );
        $site = $this->linker->masterServer->getSiteCode();

        // Crypts it
        $userName = $this->linker->masterServer->crypt( $userName, $site );
        $password = $this->linker->masterServer->crypt( $password, $site );

        // Sends it
        $uri = $this->shortClassName . '/connection_single_step_master.php';
        $connectionPage = $this->masterUrl . $uri;
        $requestId = $this->linker->postRequest->create( $connectionPage );
        $this->linker->postRequest->setData( $requestId, 'user', urlencode( $userName ) );
        $this->linker->postRequest->setData( $requestId, 'password', urlencode( $password ) );
        $this->linker->postRequest->setData( $requestId, 'site', urlencode( $site ) );
        $response = $this->linker->postRequest->send( $requestId );
        return $response;
    }

    public function connection_single_step_master() {
        sh_cache::disable();
        $this->checkIntegrity();
        sleep( 0.5 );
        $site = $this->getFromAnyServer( 'site' );
        $userName = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'user' ), $site );
        $password = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'password' ), $site );
        $password = $this->preparePassword( $password );
        list($user) = $this->db_execute(
            'connectOneUser_single_step',
            array(
            'userName' => $userName,
            'password' => $password
            )
        );
        if( isset( $user[ 'id' ] ) ) {
            echo $user[ 'id' ];
            return true;
        }
        list($user) = $this->db_execute(
            'connectOneUserWithNewPassword',
            array(
            'userName' => $userName,
            'temporaryPassword' => $password
            )
        );
        if( isset( $user[ 'id' ] ) ) {
            $this->db_execute( 'changePassword', array( 'id' => $user[ 'id' ], 'newPassword' => $password ) );
            echo $user[ 'id' ];
            return true;
        }
        return false;
    }

    /**
     * This method creates a unic string that will allow a session to be opened using the same
     * id and password used here, on another website.
     * @param str $site the name of the site on which to connect
     * @param int $delay A delay (in seconds) during which the ticket may be used. Defaults to 1 day.
     * @param int $maxUseTimes The number of times the ticket may be used. Default to 1.<br />
     * Be carefull that it may be secureless to create tickets that may be used more than once.
     * @return str a string that should be passed
     */
    public function createConnectionTicket( $site, $delay = 86400, $maxUseTimes = 1 ) {
        $uri = $this->shortClassName . '/' . __FUNCTION__ . '_master.php';
        $connectionPage = $this->masterUrl . $uri;
        $requestId = $this->linker->postRequest->create( $connectionPage );
        $thisSite = $this->linker->masterServer->getSiteCode();
        $login = urlencode(
            $this->linker->masterServer->crypt( $_SESSION[ __CLASS__ ][ 'connected' ][ 'login' ] )
        );
        $password = urlencode(
            $this->linker->masterServer->crypt( $_SESSION[ __CLASS__ ][ 'connected' ][ 'password' ] )
        );
        $this->linker->postRequest->setData( $requestId, 'site', urlencode( $thisSite ) );
        $this->linker->postRequest->setData( $requestId, 'user', $login );
        $this->linker->postRequest->setData( $requestId, 'password', $password );
        $this->linker->postRequest->setData( $requestId, 'delay', $delay );
        $this->linker->postRequest->setData( $requestId, 'maxUseTimes', $maxUseTimes );
        $this->linker->postRequest->setData( $requestId, 'ticketToSite', urlencode( $site ) );
        $response = $this->linker->postRequest->send( $requestId );
        $ret = $this->splitReturn( $response );
        if( $ret[ 'response' ] == self::OK ) {
            return $ret[ 'ticket' ];
        }
        return false;
    }

    public function createConnectionTicket_master() {
        $this->checkIntegrity();
        $id = md5( __CLASS__ . $site . microtime() );
        $site = $this->getFromAnyServer( 'site' );
        $login = $this->getFromAnyServer( 'user' );
        $ticketToSite = $this->getFromAnyServer( 'ticketToSite' );
        $delay = $this->getFromAnyServer( 'delay' );
        $maxUseTimes = $this->getFromAnyServer( 'maxUseTimes' );
        $login = $this->linker->masterServer->uncrypt(
            $login, $site
        );
        $password = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'password' ), $site );

        list($user) = $this->db_execute(
            'checkUser', array(
            'login' => $login,
            'password' => $password
            )
        );
        if( isset( $user[ 'id' ] ) && $user[ 'id' ] > 1000 ) {
            $id = $user[ 'id' ];
            if( !is_dir( SH_SITE_FOLDER . __CLASS__ . '/tickets/' ) ) {
                mkdir( SH_SITE_FOLDER . __CLASS__ . '/tickets/' );
            }
            // The user is connected, so we create the ticket
            $ticketsFile = SH_SITE_FOLDER . __CLASS__ . '/tickets/' . $ticketToSite . '.params.php';
            $this->linker->params->addElement( $ticketsFile, true );
            $ticketExists = true;
            while( !is_null( $ticketExists ) ) {
                $ticketID = md5( microtime() );
                $ticketExists = $this->linker->params->get(
                    $ticketsFile, $ticketID, null
                );
            }
            $this->linker->params->set(
                $ticketsFile, $ticketID,
                array(
                'id' => $id,
                'creationDate' => date( 'U',
                                        mktime( date( 'H' ), date( 'i' ), date( 's' ), date( 'm' ), date( 'd' ),
                                                                                                          date( 'Y' ) ) ),
                'eraseDate' => date( 'U',
                                     mktime( date( 'H' ), date( 'i' ), date( 's' ) + $delay, date( 'm' ), date( 'd' ),
                                                                                                                date( 'Y' ) ) ),
                'maxUseTimes' => $maxUseTimes
                )
            );
            $this->linker->params->write(
                $ticketsFile
            );
            echo 'response' . "\n" . self::OK . "\n";
            echo sh_masterServer::LINE_SEPARATOR . "\n";
            echo 'ticket' . "\n" . $ticketID . "\n";
            echo sh_masterServer::LINE_SEPARATOR . "\n";
            echo 'ticketFile' . "\n" . $ticketsFile;
            return true;
        }
        echo 'user' . "\n" . $user[ 'id' ] . "\n";
        echo sh_masterServer::LINE_SEPARATOR . "\n";
        echo 'response' . "\n" . self::WRONG_DATA_TEXT . "\n";
        return false;
    }

    public function getUsedConnectionTicket() {
        if( isset( $_SESSION[ __CLASS__ ][ 'usedConnectionTicket' ] ) ) {
            return $_SESSION[ __CLASS__ ][ 'usedConnectionTicket' ];
        }
        return false;
    }

    public function useConnectionTicket() {
        sh_cache::disable();
        $ticket = $_GET[ 'ticket' ];
        $uri = $this->shortClassName . '/' . __FUNCTION__ . '_master.php';
        $connectionPage = $this->masterUrl . $uri;
        $requestId = $this->linker->postRequest->create( $connectionPage );
        $site = $this->linker->masterServer->getSiteCode();
        $siteName = $this->clearData( SH_SITENAME );
        $ticket = urlencode(
            $this->linker->masterServer->crypt( $ticket )
        );
        $this->linker->postRequest->setData( $requestId, 'site', urlencode( $site ) );
        $this->linker->postRequest->setData( $requestId, 'siteName', urlencode( $siteName ) );
        $this->linker->postRequest->setData( $requestId, 'ticket', $ticket );
        $response = $this->linker->postRequest->send( $requestId );
        $ret = $this->splitReturn( $response );
        if( $ret[ 'response' ] == self::OK ) {
            if( $this->connectUser( $ret[ 'id' ], false ) ) {
                $_SESSION[ __CLASS__ ][ 'usedConnectionTicket' ] = $_GET[ 'ticket' ];
                $link = $this->linker->path->getLink( 'index/show/' );

                $this->linker->path->redirect(
                    $link
                );
            }
            die( 'Error during auto connection...' );
        } else {
            echo 'Erreur dans la reponse<br />';
        }
        return false;
    }

    public function useConnectionTicket_master() {
        $this->checkIntegrity();
        sh_cache::disable();
        $id = md5( __CLASS__ . $site . microtime() );
        $site = $this->getFromAnyServer( 'site' );
        $siteName = $this->getFromAnyServer( 'siteName' );
        $ticketId = $this->getFromAnyServer( 'ticket' );
        $ticketId = $this->linker->masterServer->uncrypt(
            $ticketId, $site
        );
        // We look for the ticket
        $ticketsFile = SH_SITE_FOLDER . __CLASS__ . '/tickets/' . $siteName . '.params.php';
        $this->linker->params->addElement( $ticketsFile, true );
        $tickets = $this->linker->params->get( $ticketsFile, '', null );
        $ticket = $this->linker->params->get( $ticketsFile, $ticketId, null );
        if( is_null( $ticket ) ) {
            echo 'Response' . "\n" . self::WRONG_DATA_TEXT . "\n";
            echo sh_masterServer::LINE_SEPARATOR . "\n";
            echo 'text' . "\nELEMENT NOT FOUND!!\n";
            return false;
        }
        if( $ticket[ 'eraseDate' ] > date( 'U' ) && $ticket[ 'maxUseTimes' ] > 0 ) {
            echo 'response' . "\n" . self::OK . "\n";
            echo sh_masterServer::LINE_SEPARATOR . "\n";
            echo 'id' . "\n";
            echo $ticket[ 'id' ];
            $ticket[ 'maxUseTimes' ]--;
            if( $ticket[ 'maxUseTimes' ] == 0 ) {
                unlink( $ticketsFile );
            } else {
                $this->linker->params->set( $ticketsFile, $ticketId, $ticket );
                $this->linker->params->write( $ticketsFile );
            }
            return true;
        }
        echo 'response' . "\n" . self::DELAY_ELAPSED;
        return false;
    }

    public function afterAccountCreation_addLink( $link, $title = '', $image ='', $name = '' ) {
        if( empty( $name ) ) {
            $name = substr( md5( microtime() ), 0, 4 );
        }
        $_SESSION[ __CLASS__ ][ 'afterAccountCreation_links' ][ $name ] = array(
            'link' => $link,
            'title' => empty( $title ) ? $link : $title,
            'image' => $image
        );
    }

    protected function afterAccountCreation_getLinks( $erase = true ) {
        $ret = $_SESSION[ __CLASS__ ][ 'afterAccountCreation_links' ];
        if( $erase ) {
            unset( $_SESSION[ __CLASS__ ][ 'afterAccountCreation_links' ] );
        }
        return $ret;
    }

    public function afterAccountValidation_addLink( $link, $title = '', $image ='', $name = '' ) {
        if( empty( $name ) ) {
            $name = substr( md5( microtime() ), 0, 4 );
        }
        $_SESSION[ __CLASS__ ][ 'afterAccountValidation_links' ][ $name ] = array(
            'link' => $link,
            'title' => empty( $title ) ? $link : $title,
            'image' => $image
        );
    }

    protected function afterAccountValidation_getLinks( $erase = true ) {
        $ret = $_SESSION[ __CLASS__ ][ 'afterAccountValidation_links' ];
        if( $erase ) {
            unset( $_SESSION[ __CLASS__ ][ 'afterAccountValidation_links' ] );
        }
        return $ret;
    }

    public function single_step_connection() {
        $this->linker->cache->disable();
        // We check if this connection is allowed
        $classes = $this->get_shared_methods( 'single_step_connection' );
        $status = false;
        foreach( $classes as $class ) {
            if( $this->linker->$class->single_step_connection_allowed() ) {
                $status = true;
                break;
            }
        }
        if( !$status ) {
            $this->linker->path->error( 403 );
        }
        $user = $_GET[ 'user' ];
        $password = $_GET[ 'password' ];
        // @todo Uncrypt both datas
        $ret = $this->connect_single_step( $user, $password );
        if( $ret ) {
            $this->connectUser( $ret, false );
            echo 'OK';
        } else {
            echo 'WRONG_CREDENTIALS';
        }
    }

    /**
     * Creates the connection form, and manages with its submitted values.<br />
     * @param boolean $sendToHtml
     * If set to true (default behaviour), send the contents to the sh_html class.<br />
     * If set to false, returns the contents.
     */
    public function connect( $sendToHtml = true ) {
        $this->linker->cache->disable();
        if( isset( $_GET[ 'redirectionAfterConnection' ] ) ) {
            $_SESSION[ __CLASS__ ][ 'redirectionAfterConnection' ] = $_GET[ 'redirectionAfterConnection' ];
        }

        if( $this->isConnected() ) {
            $_SESSION[ __CLASS__ ][ 'delay' ] = 0.5;
            $userId = $_SESSION[ __CLASS__ ][ 'connected' ][ 'userId' ];
            if( $this->isMaster() ) {
                $values[ 'connected' ][ 'asMaster' ] = true;
            } else {
                if( !is_null( $this->linker->admin->getParam( 'master_by_mail', null ) ) ) {
                    // We should also check if the user is the master
                    $values[ 'connected' ][ 'asMaster' ] = $this->linker->admin->isNewMaster(
                        $userId, $_SESSION[ __CLASS__ ][ 'connected' ][ 'mail' ]
                    );
                } elseif( $this->isAdmin() ) {
                    $values[ 'connected' ][ 'asAdmin' ] = true;
                } else {
                    $values[ 'connected' ][ 'asUser' ] = true;
                }
            }

            $values[ 'user' ][ 'name' ] = $_SESSION[ __CLASS__ ][ 'connected' ][ 'name' ];
            $values[ 'user' ][ 'lastName' ] = $_SESSION[ __CLASS__ ][ 'connected' ][ 'lastName' ];
            // We get the last connection date
            $user = $_SESSION[ __CLASS__ ][ 'connected' ][ 'id' ];

            $failures = $this->get_connection_failures( $user );
            if( is_array( $failures ) ) {
                $count = $failures[ 'number' ];

                for( $a = 0; $a < $count; $a++ ) {
                    $dateAndTime = $this->linker->datePicker->dateAndTimeToLocal(
                        $failures[ 'failure_' . $a . '_date' ]
                    );
                    $values[ 'show' ][ 'failures' ] = true;
                    $values[ 'count' ][ 'failures' ] = $failures[ 'number' ];
                    $values[ 'failures' ][ ] = array(
                        'date' => $dateAndTime[ 'date' ],
                        'time' => $dateAndTime[ 'time' ],
                        'site' => $failures[ 'failure_' . $a . '_site' ],
                        'ip' => $failures[ 'failure_' . $a . '_ip' ]
                    );
                }
                if( $this->clear_connection_failures( $user ) ) {
                    // There was an error...
                }
            }
            $lastConnection = $this->get_last_connection( $user );
            if( is_array( $lastConnection ) ) {
                $dateAndTime = $this->linker->datePicker->dateAndTimeToLocal(
                    $lastConnection[ 'date' ]
                );
                $values[ 'user' ][ 'hasBeenConnected' ] = true;

                $values[ 'lastConnection' ] = array(
                    'date' => $dateAndTime[ 'date' ],
                    'time' => $dateAndTime[ 'time' ],
                    'site' => $lastConnection[ 'site' ]
                );
            }

            $this->set_connection_status( SH_SITENAME, $user, true );

            $redirection = $_SESSION[ __CLASS__ ][ 'redirectionAfterConnection' ];
            if( !empty( $redirection ) ) {
                unset( $_SESSION[ __CLASS__ ][ 'redirectionAfterConnection' ] );
                $this->linker->path->redirect( $this->linker->path->getLink( $redirection ) );
            }

            return $this->render( 'connected', $values, false, $sendToHtml );
        }
        $result = $this->formSubmitted( 'user_connection_step1', true );
        if( $result ) {
            if( $result === true ) {
                $datas = $this->connection_step1( $_POST[ 'userName' ] );

                $userId = $datas[ 'id' ];
                $verification = $datas[ 'verification' ];
                if( $userId ) {
                    $_SESSION[ __CLASS__ ][ 'identification' ][ 'verifPhrase' ] = $verification;
                    $_SESSION[ __CLASS__ ][ 'identification' ][ 'userName' ] = $_POST[ 'userName' ];
                    $values[ 'verif' ][ 'phrase' ] = $verification;
                    $values[ 'user' ][ 'name' ] = $_POST[ 'userName' ];
                    $values[ 'link' ][ 'passPhrase_link' ] = $this->translatePageToUri(
                        $this->shortClassName . '/passwordForgotten/'
                    );
                    return $this->render( 'connection_step2', $values, false, $sendToHtml );
                }
                $values[ 'error' ][ 'message' ] = $this->getI18n( 'loginNotFound' );
            } elseif( $result == sh_captcha::CAPTCHA_ERROR ) {
                $values[ 'old' ][ 'userName' ] = $_POST[ 'userName' ];
                $values[ 'captcha' ][ 'error' ] = 'true';
            }
        } else {
            $values[ 'captcha' ][ 'error' ] = '';
        }
        $result = $this->formSubmitted( 'user_connection_step2' );
        if( $result ) {
            $userId = $this->connection_step2( $_POST[ 'password' ] );

            if( !empty( $userId ) ) {
                $values[ 'error' ][ 'message' ] = $this->connectUser( $userId );
            } else {
                $datas = $this->connection_step1( $_SESSION[ __CLASS__ ][ 'identification' ][ 'userName' ] );

                $this->set_connection_status( SH_SITENAME, $datas[ 'id' ], false );
                $values[ 'error' ][ 'message' ] = $this->getI18n( 'WRONG_DATA' );
            }
        }
        $masterUrl = $this->getMasterUrl( false );
        $values[ 'createAccount' ][ 'link' ] = $this->linker->path->getLink( 'user/createAccount/' );
        return $this->render( 'connection_step1', $values, false, $sendToHtml );
    }

    public function connectUser( $userId, $refresh = true ) {
        $_SESSION[ __CLASS__ ][ 'delay' ] = 0.5;
        $isAdmin = $this->linker->admin->isAdmin( $userId, false );
        $isMaster = $this->linker->admin->isMaster( $userId );
        // We don't check if the site is restricted if the user is an admin
        // or a master
        $userData = $this->getOneUserData( $userId );
        if( !$this->needs_connection() || $isAdmin || $isMaster ) {
            $connected = true;
        } else {
            if( file_exists( SH_SITE_FOLDER . __CLASS__ . '/allowed.php' ) ) {
                include(SH_SITE_FOLDER . __CLASS__ . '/allowed.php');
                if( is_array( $allowedUsers ) ) {
                    if( in_array( $userData[ 'id' ], $allowedUsers ) ) {
                        $connected = true;
                    }
                }
            }
            if( file_exists( SH_SITE_FOLDER . __CLASS__ . '/inexistantButAllowedUsers.php' ) ) {
                include(SH_SITE_FOLDER . __CLASS__ . '/inexistantButAllowedUsers.php');
                if( is_array( $inexistantButAllowedUsers ) ) {
                    if( in_array( $userData[ 'mail' ], $inexistantButAllowedUsers ) ) {
                        $connected = true;
                    }
                }
            }
        }

        if( $connected === true ) {
            // Connection was successfull
            if( isset( $_POST[ 'cookie' ] ) ) {
                $this->connection_create_cookie($userId);
            }
            $this->connected = true;
            $_SESSION[ __CLASS__ ][ 'connected' ] = $userData;
            $_SESSION[ __CLASS__ ][ 'connected' ][ 'completeName' ] = $userData[ 'lastName' ] . ' ' . $userData[ 'name' ];
            $_SESSION[ __CLASS__ ][ 'connected' ][ 'userId' ] = $userId;
            if( $isMaster ) {
                $this->linker->admin->connect( sh_admin::CONNECT_AS_MASTER );
            } elseif( $isAdmin ) {
                $this->linker->admin->connect( sh_admin::CONNECT_AS_ADMIN );
            } else {
                $this->linker->admin->connect( sh_admin::CONNECT_AS_USER );
            }
            if( $refresh ) {
                $this->linker->path->refresh();
            }
            return true;
        } else {
            return $this->getI18n(
                    self::SHOPSAILORS_USERNAME_NOT_ALLOWED_TEXT
            );
        }
    }

    public function get( $what, $booleanError = false ) {
        if( !isset( $_SESSION[ __CLASS__ ][ 'connected' ] ) ) {
            if( $booleanError ) {
                return false;
            }
            return self::NOT_CONNECTED;
        }
        if( isset( $_SESSION[ __CLASS__ ][ 'connected' ][ $what ] ) ) {
            return $_SESSION[ __CLASS__ ][ 'connected' ][ $what ];
        }
        if( $booleanError ) {
            return false;
        }
        return self::USER_DATA_NOT_FOUND;
    }

    /**
     * Disconnects the user, removing its connection data from the session, and
     * redirects to the index page.
     */
    public function disconnect() {
        $this->linker->cache->disable();
        $this->connection_delete_cookie();
        $this->linker->admin->disconnect();
        $this->connected = false;
        unset( $_SESSION[ __CLASS__ ][ 'connected' ] );
        session_destroy();
        header( 'location: /' );
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        $methods = array(
            'manage',
            'disconnect',
            'connect',
            'getOneUserId',
            'getUserData',
            'tryToConnect',
            'createAccount',
            'confirmAccountCreation',
            'master_confirmAccountCreation',
            'createWebsite',
            'passwordForgotten_master',
            'passwordForgotten',
            'master_get_last_connection',
            'master_set_connection_status',
            'master_get_connection_failures',
            'master_clear_connection_failures',
            'connection_step1',
            'connection_step2',
            'connection_step1_master',
            'connection_step2_master',
            'profile',
            'editProfile',
            'editProfile_master',
            'editPassword',
            'editPassword_master',
            'editPassphrase',
            'editPassphrase_master',
            'createConnectionTicket_master',
            'useConnectionTicket',
            'useConnectionTicket_master',
            'master_createAccount',
            'master_verifyUsernameAvailability',
            'single_step_connection',
            'connection_single_step_master'
        );
        list($class, $method, $id) = explode( '/', $page );
        if( in_array( $method, $methods ) ) {
            return '/' . $this->shortClassName . '/' . $method . '.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( preg_match( '`/' . $this->shortClassName . '/([^/]+)\.php`', $uri, $matches ) ) {
            $methods = array(
                'manage',
                'disconnect',
                'connect',
                'getOneUserId',
                'getUserData',
                'tryToConnect',
                'createAccount',
                'confirmAccountCreation',
                'master_confirmAccountCreation',
                'createWebsite',
                'passwordForgotten_master',
                'passwordForgotten',
                'master_get_last_connection',
                'master_set_connection_status',
                'master_get_connection_failures',
                'master_clear_connection_failures',
                'connection_step1',
                'connection_step2',
                'connection_step1_master',
                'connection_step2_master',
                'profile',
                'editProfile',
                'editProfile_master',
                'editPassword',
                'editPassword_master',
                'editPassphrase',
                'editPassphrase_master',
                'createConnectionTicket_master',
                'useConnectionTicket',
                'useConnectionTicket_master',
                'master_createAccount',
                'master_verifyUsernameAvailability',
                'single_step_connection',
                'connection_single_step_master'
            );
            if( in_array( $matches[ 1 ], $methods ) ) {
                return $this->shortClassName . '/' . $matches[ 1 ] . '/';
            }
        }
        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////
    //                        USED ON MASTER SITE ONLY                            //
    ////////////////////////////////////////////////////////////////////////////////
    /**
     * USED ON MASTER SITE ONLY<br />
     * This method is mainly called by customers' websites to get some users ids.<br />
     * it gets the data from the database, and returns it as a string.
     * @param string|null $field
     * The field name to search in the database.<br />
     * If null (default behaviour), gets it from $_POST.
     * @param string|null $value
     * The field value to search in the database.<br />
     * If null (default behaviour), gets it from $_POST.
     * @return string
     * The id found as a string, or "0" if one was found.
     */
    public function getOneUserId( $field = null, $value = null ) {
        $this->isMasterServer();
        if( $field == null || $value == null ) {
            $echo = true;
            $field = stripslashes( $_POST[ 'field' ] );
            $value = stripslashes( $_POST[ 'value' ] );
        }
        list($element) = $this->db_execute(
            'getOneUserId', array(
            'field' => $field,
            'value' => $value
            ), $qry
        );
        if( $element[ 'id' ] > 0 ) {
            if( $echo ) {
                echo $element[ 'id' ];
            }
            return $element[ 'id' ];
        } else {
            if( $echo ) {
                echo '0';
            }
            return false;
        }
        return true;
    }

    /**
     * Gets a user's datas.
     * @return string
     * The return is a text in which:<br />
     * - all %3 lines are field names.<br />
     * - all %3 + 1 lines are their values.<br />
     * - all %3 + 2 lines are just separator (sh_masterServer::LINE_SEPARATOR).
     */
    public function getUserData( $id = null ) {
        $this->isMasterServer();
        if( $id == null ) {
            $id = $this->getFromAnyServer( 'user' );
            $echo = true;
        }
        list($user) = $this->db_execute( 'getUserData', array( 'id' => $id ), $qry );

        if( !$echo ) {
            return $user;
        }
        if( is_array( $user ) ) {
            foreach( $user as $name => $value ) {
                echo $name . "\n";
                echo $value . "\n";
                echo sh_masterServer::LINE_SEPARATOR . "\n";
            }
            return true;
        }
        return false;
    }

    /**
     * Checks if that function is called by the master website.<br />
     * May send an error page if not, depending on $raiseErrorIfNotMaster
     * @param boolean $raiseErrorIfNotMaster
     * In case that this function is called from any other server than the master:<br />
     * - Raises a 404 error if set to true (default behaviour)<br />
     * - Returns false if set to false
     */
    public function isMasterServer( $raiseErrorIfNotMaster = true ) {
        if( SH_MASTERSERVER ) {
            return true;
        }
        if( $raiseErrorIfNotMaster ) {
            $this->linker->path->error( 404 );
        }
        return false;
    }

    /**
     * Checks whether the calling website may call this master server or not.
     * @return bool Returns true if ok, or sends an error message to the outpu, and exits if not.
     */
    protected function checkIntegrity() {
        $site = str_replace( '/', '', SH_SITE );
        if( $this->isMasterServer( false ) ) {
            if( in_array( $_SERVER[ 'REMOTE_ADDR' ], $this->getParam( 'master>allowedSites' ) ) ) {
                return true;
            }
            echo self::SITE_NOT_ALLOWED_TEXT;
        } else {
            echo self::ERROR_USING_FORM_TEXT;
        }
        exit;
    }

    /**
     * public function tryToConnect
     *
     */
    public function tryToConnect() {
        $this->linker->cache->disable();
        $this->checkIntegrity();
        // We get the data
        $cryptedUserName = $this->getFromAnyServer( 'user' );
        $cryptedPassword = $this->getFromAnyServer( 'password' );
        $site = $this->getFromAnyServer( 'site' );
        // We uncrypt it
        $userName = $this->linker->masterServer->uncrypt( $cryptedUserName, $site );
        $password = $this->preparePassword( $this->linker->masterServer->uncrypt( $cryptedPassword, $site ) );
        // We verify if the account exists
        list($user) = $this->db_execute(
            'verify', array(
            'name' => $userName,
            'password' => $password
            )
        );
        if( isset( $user[ 'id' ] ) ) {
            if( $user[ 'active' ] == 1 ) {
                echo $user[ 'id' ];
            } else {
                echo self::ACCOUNT_NOT_ACTIVATED_TEXT;
            }
        } else {
            // We verify if the account exists
            list($user) = $this->db_execute(
                'verifyByTemporaryPassword',
                array(
                'name' => $userName,
                'temporaryPassword' => $password
                ), $qry
            );
            if( isset( $user[ 'id' ] ) ) {
                if( $user[ 'active' ] == 1 ) {
                    $this->db_execute( 'changePassword',
                                       array(
                        'newPassword' => $password,
                        'id' => $user[ 'id' ]
                    ) );
                    echo $user[ 'id' ];
                } else {
                    echo self::ACCOUNT_NOT_ACTIVATED_TEXT;
                }
            } else {
                echo self::WRONG_DATA_TEXT;
            }
        }
        return true;
    }

    public function master_verifyUsernameAvailability() {
        $this->checkIntegrity();

        $site = $this->getFromAnyServer( 'site' );
        $login = $this->getFromAnyServer( 'login' );
        $login = $this->linker->masterServer->uncrypt( $login, $site );
        // We check if it is not already used
        list($rep) = $this->db_execute(
            'getOneUserId', array(
            'field' => 'login',
            'value' => $login
            )
        );
        if( !empty( $rep[ 'id' ] ) ) {
            echo self::ERROR_LOGIN_ALREADY_IN_USE;
            return true;
        }
        // it's ok for the username
        // We check for the email
        $mail = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'mail' ), $site );
        list($rep) = $this->db_execute(
            'getOneUserId', array(
            'field' => 'mail',
            'value' => $mail
            )
        );
        if( !empty( $rep[ 'id' ] ) ) {
            echo self::ERROR_MAIL_ALREADY_IN_USE;
            return true;
        }

        // It's OK
        echo self::OK;
        return true;
    }

    public function master_createAccount() {
        $this->checkIntegrity();

        $site = $this->getFromAnyServer( 'site' );
        $name = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'name' ), $site );
        $lastName = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'lastName' ), $site );
        $phone = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'phone' ), $site );
        $mail = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'mail' ), $site );
        $login = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'login' ), $site );
        $password = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'password' ), $site );
        $address = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'address' ), $site );
        $zip = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'zip' ), $site );
        $city = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'city' ), $site );
        $verification = $this->linker->masterServer->uncrypt( $this->getFromAnyServer( 'verification' ), $site );

        //We set the auto-increment to the first value of today
        $this->db_execute(
            'createAccount_setIncrement', array( 'increment' => date( 'ymd' ) . '00001' )
        );
        // Creates the user in the database
        $this->db_execute(
            'createAccount',
            array(
            'name' => $name,
            'lastName' => $lastName,
            'phone' => $phone,
            'mail' => $mail,
            'login' => $login,
            'password' => $this->preparePassword( $password ),
            'address' => $address,
            'zip' => $zip,
            'city' => $city,
            'verification' => $verification
            ), $qry
        );
        echo $this->db_insertId();
        return true;
    }

    /**
     * public function createAccount
     *
     */
    public function createAccount() {
        $this->linker->cache->disable();

        // Checks if the form has been submitted
        $formSubmitted = $this->formSubmitted( 'createAccountForm' );
        if( $formSubmitted === true ) {
            $error = false;

            // Name
            $name = trim( stripslashes( $_POST[ 'name' ] ) );
            if( strlen( $name ) < 2 ) {
                $error = true;
                $values[ 'name' ][ 'error' ] = 'error';
                $name = '';
                $this->linker->html->addMessage( $this->getI18n( 'createAccount_error_name' ) );
            }

            // Last name
            $lastName = trim( stripslashes( $_POST[ 'lastName' ] ) );
            if( strlen( $lastName ) < 2 ) {
                $error = true;
                $values[ 'lastName' ][ 'error' ] = 'error';
                $lastName = '';
                $this->linker->html->addMessage( $this->getI18n( 'createAccount_error_lastname' ) );
            }

            // Phone
            $phone = trim( stripslashes( $_POST[ 'phone' ] ) );
            $phone = str_replace(
                array( '-', '_', ' ', '.', "'", '"' ), '', $phone
            );
            if( preg_match( '`([^0-9+]+)`', $phone ) ) {
                $error = true;
                $values[ 'phone' ][ 'error' ] = 'error';
                $phone = '';
                $this->linker->html->addMessage( $this->getI18n( 'createAccount_error_phone' ) );
            }

            // Email
            $mail = trim( stripslashes( $_POST[ 'mail' ] ) );
            $mailer = $this->linker->mailer->get();
            $mailError = false;
            if( !$mailer->checkAddress( $mail ) ) {
                $error = true;
                $values[ 'mail' ][ 'error' ] = 'error';
                $mail = '';
                $mailError = true;
                $this->linker->html->addMessage( $this->getI18n( 'createAccount_error_mail' ) );
            }

            // Address
            $address = trim( stripslashes( $_POST[ 'address' ] ) );
            $zip = trim( stripslashes( $_POST[ 'zip' ] ) );
            $city = trim( stripslashes( $_POST[ 'city' ] ) );

            // Login
            $login = trim( stripslashes( $_POST[ 'login' ] ) );
            $login = $this->clearData( $login );
            if( strlen( $login ) < 5 ) {
                $error = true;
                $values[ 'login' ][ 'error' ] = 'error';
                $login = '';
                $this->linker->html->addMessage( $this->getI18n( 'createAccount_error_login_too_short' ) );
            } else {
                // Gets and prepares the data
                $site = $this->linker->masterServer->getSiteCode();
                // Crypts the datas
                $crypted_login = $this->linker->masterServer->crypt( $this->clearData( $login ) );
                $crypted_mail = $this->linker->masterServer->crypt( $mail );

                // Sends it
                $uri = $this->shortClassName . '/master_verifyUsernameAvailability.php';
                $connectionPage = $this->masterUrl . $uri;
                $requestId = $this->linker->postRequest->create( $connectionPage );
                $this->linker->postRequest->setData( $requestId, 'site', urlencode( $site ) );
                $this->linker->postRequest->setData( $requestId, 'login', urlencode( $crypted_login ) );
                $this->linker->postRequest->setData( $requestId, 'mail', urlencode( $crypted_mail ) );
                $response = $this->linker->postRequest->send( $requestId );
                if( $response == self::ERROR_LOGIN_ALREADY_IN_USE ) {
                    $error = true;
                    $values[ 'login' ][ 'error' ] = 'error';
                    $login = '';
                    $values[ 'message' ][ 'error' ] .= $this->getI18n( 'login_already_used' );
                    $this->linker->html->addMessage( $this->getI18n( 'login_already_used' ) );
                } elseif( !$mailError && ($response == self::ERROR_MAIL_ALREADY_IN_USE) ) {
                    $error = true;
                    $values[ 'mail' ][ 'error' ] = 'error';
                    $mail = '';
                    $values[ 'message' ][ 'error' ] .= $this->getI18n( 'mail_already_used' );
                    $this->linker->html->addMessage( $this->getI18n( 'login_already_used' ) );
                }
            }
            // Password
            $password = trim( stripslashes( $_POST[ 'password' ] ) );
            $passwordConfirm = trim( stripslashes( $_POST[ 'passwordConfirm' ] ) );
            if( strlen( $password ) < 5 || $password != $passwordConfirm ) {
                $error = true;
                $values[ 'password' ][ 'error' ] = 'error';
                $password = '';
                $this->linker->html->addMessage( $this->getI18n( 'createAccount_error_password_too_short' ) );
            }

            $verification = trim( $_POST[ 'verification' ] );
            if( empty( $verification ) ) {
                $error = true;
                $values[ 'verification' ][ 'error' ] = 'error';
                $verification = '';
                $this->linker->html->addMessage( $this->getI18n( 'createAccount_error_passwords_different' ) );
            }

            if( !$this->linker->captcha->verify( 'createAccountForm' ) ) {
                $error = true;
                $values[ 'captcha' ][ 'error' ] = 'error';
                $this->linker->html->addMessage( $this->getI18n( 'createAccount_error_captcha' ) );
            }

            // Checks if errors have occured
            if( !$error ) {
                // Gets and prepares the data
                $site = $this->linker->masterServer->getSiteCode();

                // Crypts it
                $crypted_login = $this->linker->masterServer->crypt( $this->clearData( $login ) );
                $crypted_mail = $this->linker->masterServer->crypt( $mail );
                $crypted_name = $this->linker->masterServer->crypt( $name );
                $crypted_lastName = $this->linker->masterServer->crypt( $lastName );
                $crypted_phone = $this->linker->masterServer->crypt( $phone );
                $crypted_password = $this->linker->masterServer->crypt( $password );
                $crypted_address = $this->linker->masterServer->crypt( $address );
                $crypted_zip = $this->linker->masterServer->crypt( $zip );
                $crypted_city = $this->linker->masterServer->crypt( $city );
                $crypted_verification = $this->linker->masterServer->crypt( $verification );

                // Saving the new user on the master server
                $uri = $this->shortClassName . '/master_createAccount.php';
                $connectionPage = $this->masterUrl . $uri;
                $requestId = $this->linker->postRequest->create( $connectionPage );
                $this->linker->postRequest->setData( $requestId, 'site', urlencode( $site ) );
                $this->linker->postRequest->setData( $requestId, 'name', urlencode( $crypted_name ) );
                $this->linker->postRequest->setData( $requestId, 'lastName', urlencode( $crypted_lastName ) );
                $this->linker->postRequest->setData( $requestId, 'phone', urlencode( $crypted_phone ) );
                $this->linker->postRequest->setData( $requestId, 'mail', urlencode( $crypted_mail ) );
                $this->linker->postRequest->setData( $requestId, 'login', urlencode( $crypted_login ) );
                $this->linker->postRequest->setData( $requestId, 'password', urlencode( $crypted_password ) );
                $this->linker->postRequest->setData( $requestId, 'address', urlencode( $crypted_address ) );
                $this->linker->postRequest->setData( $requestId, 'zip', urlencode( $crypted_zip ) );
                $this->linker->postRequest->setData( $requestId, 'city', urlencode( $crypted_city ) );
                $this->linker->postRequest->setData( $requestId, 'verification', urlencode( $crypted_verification ) );
                $userId = $this->linker->postRequest->send( $requestId );

                // Prepares the confirmation on the server by adding the necessary file
                $link = $this->linker->path->getLink( 'user/confirmAccountCreation/' );
                $key = MD5( __CLASS__ . microtime() );
                $link .= '?key=' . $key;
                $this->helper->writeInFile( SH_SITE_FOLDER . __CLASS__ . '/' . $key . '.php', $mail );

                // Renders the content of the mail

                $values[ 'confirmation' ][ 'link' ] = $this->linker->path->getBaseUri() . $link;
                $values[ 'user' ][ 'mail' ] = $mail;
                $values[ 'user' ][ 'login' ] = $login;
                $content = $this->render( 'mail_newAccount', $values, false, false );

                // Preparation of the confirmation mail
                $mailer = $this->linker->mailer->get();


                $mailObject = $mailer->em_create();
                // Creating and sending the email itself
                $address = $user[ 'mail' ];

                $mails = explode( "\n", $this->getParam( 'command_mail' ) );
                if( is_array( $mails ) ) {
                    foreach( $mails as $oneMail ) {
                        $mailer->em_addBCC( $mailObject, $oneMail );
                    }
                }

                $mailer->em_addSubject(
                    $mailObject, $this->getI18n( 'mail_confirmation_title' )
                );
                $mailer->em_addContent( $mailObject, $content );

                if( $mailer->em_send( $mailObject, array( array( $mail ) ) ) ) {
                    $values[ 'messages' ] = $this->afterAccountValidation_getLinks();
                    $this->render( 'confirmationSent', $values );
                    return true;
                }
                // The mail was not sent (why???) so we send an error message
                $values[ 'message' ][ 'error' ] .= $this->getI18n( 'error_sending_mail' );
            }
        } elseif( $formSubmitted == sh_captcha::CAPTCHA_ERROR ) {
            echo 'ERREUR DANS LA CAPTCHA!!!';
        }

        if( empty( $mail ) && isset( $_GET[ 'mail' ] ) ) {
            $mail = $_GET[ 'mail' ];
        }

        // Prepares the old entries to pre-fill the form
        $values[ 'old' ] = array(
            'name' => $name,
            'lastName' => $lastName,
            'phone' => $phone,
            'mail' => $mail,
            'login' => $login,
            'password' => $password,
            'address' => $address,
            'verification' => $verification
        );
        // Renders the form
        $this->render( 'createAccount', $values );
        return true;
    }

    public function master_confirmAccountCreation() {
        $this->linker->cache->disable();
        $this->isMasterServer();

        $site = $this->getFromAnyServer( 'site' );
        $mail = $this->getFromAnyServer( 'mail' );
        $mail = $this->linker->masterServer->uncrypt( $mail, $site );

        $this->db_execute( 'activateAccount', array( 'mail' => $mail ), $qry );

        $id = $this->getOneUserId( 'mail', $mail );
        // It's OK
        echo $id;
        return true;
    }

    /**
     * public function confirmAccountCreation
     *
     */
    public function confirmAccountCreation() {
        $this->linker->cache->disable();
        $key = stripslashes( $_GET[ 'key' ] );
        if( file_exists( SH_SITE_FOLDER . __CLASS__ . '/' . $key . '.php' ) ) {
            $mail = file_get_contents( SH_SITE_FOLDER . __CLASS__ . '/' . $key . '.php' );

            // Gets and prepares the data
            $site = $this->linker->masterServer->getSiteCode();
            // Crypts the datas
            $crypted_mail = $this->linker->masterServer->crypt( $mail );

            // Sends it
            $uri = $this->shortClassName . '/master_confirmAccountCreation.php';
            $connectionPage = $this->masterUrl . $uri;
            $requestId = $this->linker->postRequest->create( $connectionPage );
            $this->linker->postRequest->setData( $requestId, 'site', urlencode( $site ) );
            $this->linker->postRequest->setData( $requestId, 'mail', urlencode( $crypted_mail ) );
            $id = $this->linker->postRequest->send( $requestId );

            if( $id ) {
                // We should connect the user
                $this->connectUser( $id, false );
                $values[ 'user' ] = $this->getOneUserData( $id );
                $values[ 'messages' ] = $this->afterAccountCreation_getLinks();
                $this->linker->events->onUserAccountCreation( $values[ 'user' ] );
                $this->render( 'accountCreationConfirmed', $values );
                unlink( SH_SITE_FOLDER . __CLASS__ . '/' . $key . '.php' );
                $_SESSION[ __CLASS__ ][ 'accountJustCreated' ] = true;
                return true;
            }
        }
        $this->render( 'accountCreationAborted', $values );
        return true;
    }

    /**
     * Creates a new Website
     */
    public function createWebsite() {
        $this->isMasterServer();
        if( $this->formSubmitted( 'createWebsite' ) ) {

            if( isset( $_POST[ 'login' ] ) ) {
                $login = trim( stripslashes( $_POST[ 'login' ] ) );
                if( !preg_match( '`^[a-zA-Z0-9-_]{5,}$`', $login ) ) {
                    echo 'L\'identifiant n\'est pas bon!!!<br />';
                }
            }
            //domain
            //siteName
            echo 'The form was submitted<br />';
        }
        $this->render( 'createWebsite', array( ) );
    }

    ////////////////////////////////////////////////////////////////////////////////
    //                        USED ON BOTH                                        //
    ////////////////////////////////////////////////////////////////////////////////

    /**
     * protected function preparePassword
     *
     */
    protected function preparePassword( $original ) {
        $siteCode = $this->linker->masterServer->getSiteCode();
        return md5( $siteCode . strtolower( $original ) );
    }

    /**
     * protected function getFromAnyServer
     *
     */
    protected function getFromAnyServer( $argName ) {
        return urldecode( stripslashes( $_POST[ $argName ] ) );
    }

    /**
     * protected function clearData
     *
     */
    protected function clearData( $dirty ) {
        $dirty = stripslashes( $dirty );
        $remove = array( 'à', 'á', 'â', 'ã', 'ä', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ' );
        $replace = array( 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y' );
        $dirty = str_replace( $remove, $replace, $dirty );
        $dirty = preg_replace(
            '`([^a-zA-Z0-9_]+)`', '_', $dirty
        );
        return $dirty;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}
