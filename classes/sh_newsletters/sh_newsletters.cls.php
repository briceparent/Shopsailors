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
 * Class that manages the newsletters
 */
class sh_newsletters extends sh_core {

    const CLASS_VERSION = '1.1.12.03.05';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    public $minimal = array( 'sendTest' => true, 'cron_send' => true );
    static $todaysFolder = '';
    protected $constants = array(
        'list_title' => 1,
        'list_intro' => 2,
        'subscription_title' => 3,
        'subscription_intro' => 4
    );

    public function construct() {
        define( 'NEWSLETTERS_PARAMSFOLDER', SH_SITE_FOLDER . __CLASS__ . '/' );
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            if( version_compare( $installedVersion, '1.1.12.03.05' ) < 0 ) {
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
                $this->helper->addClassesSharedMethods( 'sh_sitemap', '', __CLASS__ );
                if( !is_dir( SH_IMAGES_FOLDER . 'newsletters/' ) ) {
                    $this->linker->browser->createFolder(
                        SH_IMAGES_FOLDER . 'newsletters/', sh_browser::ALL
                    );
                    $this->linker->browser->addDimension(
                        SH_IMAGES_FOLDER . 'newsletters/', 500, 500
                    );
                    $this->linker->browser->setNoMargins(
                        SH_IMAGES_FOLDER . 'newsletters/'
                    );
                    mkdir( NEWSLETTERS_PARAMSFOLDER );
                }
            }
            if( version_compare( $installedVersion, '1.1.12.08.21', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_site', 'sharedSettings', __CLASS__ );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        self::$todaysFolder = date( 'Y/m/d' );

        $this->renderer_addConstants(
            $this->constants, true
        );
    }

    public function getSharedSettings() {
        $values[ 'settings' ][ 'active' ] = $this->getParam( 'module_active', false ) ? 'checked' : '';
        $return = array(
            'title' => $this->getI18n( 'title' ),
            'form' => $this->render( 'sharedSettingsForm', $values, false, false )
        );
        return $return;
    }

    public function setSharedSetting() {
        $this->setParam( 'module_active', isset( $_POST[ 'newsletters' ][ 'active' ] ) );
        $this->writeParams();
        $this->news_is_active = $this->getParam( 'module_active', true );
    }

    public function master_getMenuContent() {
        return array( );
    }

    public function admin_getMenuContent() {
        $adminMenu[ 'Newsletters' ][ ] = array(
            'link' => 'newsletters/manage/',
            'text' => 'Gérer la newsletter',
            'icon' => 'picto_tool.png'
        );
        $newsletterClass = sh_linker::getInstance()->newsletters;
        if( $newsletterClass->isActivated() ) {
            $adminMenu[ 'Newsletters' ][ ] = array(
                'link' => 'newsletters/createNewsletter/0',
                'text' => 'Créer une newsletter',
                'icon' => 'picto_add.png'
            );
            $adminMenu[ 'Newsletters' ][ ] = array(
                'link' => 'newsletters/showInvisible/',
                'text' => 'Liste des newsletters',
                'icon' => 'picto_list.png'
            );
            $adminMenu[ 'Newsletters' ][ ] = array(
                'link' => 'newsletters/manageLists/0',
                'text' => 'Gérer les listes de diffusion',
                'icon' => 'picto_list.png'
            );
        }
        return $adminMenu;
    }

//          GENERAL PART            \\
    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew() {
        if( !$this->isActivated() ) {
            return true;
        }
        $this->addToSitemap(
            $this->shortClassName . '/showList/', 0.5, sh_sitemap::FREQUENCY_WEEKLY
        );
        $this->addToSitemap(
            $this->shortClassName . '/subscribe/', 0.6, sh_sitemap::FREQUENCY_MONTHLY
        );

        $newsletters = NEWSLETTERS_PARAMSFOLDER . 'list.params.php';
        $this->linker->params->addElement( $newsletters, true );
        $list = $this->linker->params->get(
            $newsletters, '', array( )
        );

        if( is_array( $list ) ) {
            foreach( $list as $id => $newsletter ) {
                if( $id != 'count' ) {
                    if( !$newsletter[ 'sent' ] || $newsletter[ 'hidden' ] === true ) {
                        continue;
                    }
                    $this->addToSitemap(
                        $this->shortClassName . '/show/' . $id, 0.4, sh_sitemap::FREQUENCY_MONTHLY
                    );
                }
            }
        }
        return true;
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
    public function getPageName( $action, $id = null ) {
        $name = $this->getI18n( 'action_' . $action );

        $newsletters = NEWSLETTERS_PARAMSFOLDER . 'list.params.php';
        $this->linker->params->addElement( $newsletters, true );

        if( $action == 'show' ) {
            $title = $this->linker->params->get( $newsletters, $id . '>title' );
            $name = str_replace(
                array( '{id}', '{link}', '{title}' ), array( $id, $link, $title ), $name
            );
        }
        if( $action == 'shortList' ) {
            list($title) = $this->db_execute( 'getTitleWithInactive', array( 'id' => $id ) );
            $title = $this->getParam( 'list>' . $id . '>name' );
            $title = $this->getI18n( $title );
            $name = str_replace(
                array( '{id}', '{link}', '{title}' ), array( $id, $link, $title ), $name
            );
        }
        if( !is_null( $id ) ) {
            $page = str_replace(
                    array( SH_PREFIX, SH_CUSTOM_PREFIX ), array( '', '' ), $this->__tostring()
                ) . '/' . $action . '/' . $id;
            $link = $this->linker->path->getLink( $page );
        }
        if( $name != '' ) {
            return $name;
        }
        return $this->shortClassName . '->' . $action . '(' . $id . ')';
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        list(, $method, $id) = explode( '/', $page );
        $unchanged = array(
            'manage', 'sendTest', 'showPage',
            'isThereANewsletterWaiting', 'edit_newslettersList', 'subscribe',
            'confirmSubscription', 'unsubscribe', 'showInvisible', 'showList'
        );
        if( in_array( $method, $unchanged ) ) {
            return '/' . $this->shortClassName . '/' . $method . '.php';
        }
        $withId = array(
            'createNewsletter', 'manageLists', 'removeList',
            'createNewsletter', 'delete', 'show'
        );
        if( in_array( $method, $withId ) ) {
            return '/' . $this->shortClassName . '/' . $method . '/' . $id . '.php';
        }
        $withName = array(
            'show'
        );
        if( in_array( $method, $withName ) ) {
            $name = trim( $this->getNewsletterTitle( $id ) );
            if( !empty( $name ) ) {
                $name = urlencode( '-' . $name );
            }
            return '/' . $this->shortClassName . '/' . $method . '/' . $id . $name . '.php';
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
            $unchanged = array(
                'manage', 'sendTest', 'showPage',
                'isThereANewsletterWaiting', 'edit_newslettersList', 'subscribe',
                'confirmSubscription', 'unsubscribe', 'showInvisible', 'showList'
            );
            if( in_array( $matches[ 1 ], $unchanged ) ) {
                return $this->shortClassName . '/' . $matches[ 1 ] . '/';
            }
            $withId = array(
                'createNewsletter', 'manageLists', 'removeList',
                'createNewsletter', 'delete', 'show'
            );
            if( in_array( $matches[ 1 ], $withId ) ) {
                return $this->shortClassName . '/' . $matches[ 1 ] . '/' . $matches[ 3 ];
            }
        }
        return false;
    }

    public function manage() {
        $this->onlyAdmin( true );
        $this->linker->html->setTitle( $this->getI18n( 'manageTitle' ) );
        if( $this->formSubmitted( 'manage_newsletters' ) ) {
            $this->setParam( 'activated', isset( $_POST[ 'activated' ] ) );
            $this->setParam( 'mailSender', $_POST[ 'mailSender' ] );

            $this->setI18n( $this->constants[ 'subscription_title' ], $_POST[ 'subscription_title' ] );
            $this->setI18n( $this->constants[ 'subscription_intro' ], $_POST[ 'subscription_intro' ] );
            $this->setParam( 'subscription_showTitle', isset( $_POST[ 'subscription_showTitle' ] ) );

            $this->setI18n( $this->constants[ 'list_title' ], $_POST[ 'list_title' ] );
            $this->setI18n( $this->constants[ 'list_intro' ], $_POST[ 'list_intro' ] );
            $this->setParam( 'list_showTitle', isset( $_POST[ 'list_showTitle' ] ) );
            $this->writeParams();
        }
        if( $this->getParam( 'activated', true ) ) {
            $values[ 'content' ][ 'activated' ] = 'checked';
        }
        if( $this->getParam( 'subscription_showTitle', true ) ) {
            $values[ 'content' ][ 'subscription_showTitle' ] = 'checked';
        }
        if( $this->getParam( 'list_showTitle', true ) ) {
            $values[ 'content' ][ 'list_showTitle' ] = 'checked';
        }
        $values[ 'mailSender' ][ 'none' ] = 'checked';
        $this->render( 'manage', $values );
    }

//          MAILING LISTS PART          \\

    public function removeList() {
        $this->onlyAdmin( true );
        $id = ( int ) $this->linker->path->page[ 'id' ];
        $mailer = $this->linker->mailer->get( true );
        if( $this->formSubmitted( 'delete_newslettersList' ) ) {
            $mailer->ml_delete( $id );
            $this->linker->path->redirect(
                __CLASS__, 'manageLists', 0
            );
        }
        $values[ 'list' ][ 'name' ] = $mailer->ml_getName( $id );
        $this->render( 'removeList', $values );
    }

    protected function getMailingListParamsFile( $id ) {
        return 'mailing_list_' . $id;
    }

    /**
     * Shows the admin page that allows to manage the mailing lists
     */
    public function manageLists() {
        $this->onlyAdmin( true );
        $id = ( int ) $this->linker->path->page[ 'id' ];
        $mailer = $this->linker->mailer->get( true );

        if( $this->formSubmitted( 'edit_diffusionList' ) ) {

            if( $id == 0 ) {
                // We create the mailing list
                $id = $mailer->ml_create(
                    stripslashes( $_POST[ 'name' ] ), stripslashes( $_POST[ 'description' ] )
                );
                if( $id === false || $id == sh_mailsenders::ERROR_RETURN ) {
                    $values[ 'error' ][ 'text' ] = $mailer->getErrorMessage();
                    $values[ 'list' ][ 'name' ] = stripslashes( $_POST[ 'name' ] );
                    $values[ 'list' ][ 'description' ] = stripslashes( $_POST[ 'description' ] );
                }

                $mailer->ml_addAddress(
                    $id, 'newsletters_autoSubscribedUser@websailors.fr', 'Auto Subscribed User'
                );
            } else {
                // We modify the mailing list
                $mailer->ml_edit(
                    $id, stripslashes( $_POST[ 'name' ] ), stripslashes( $_POST[ 'description' ] )
                );
            }
            if( false ) {
                $this->linker->path->redirect(
                    __CLASS__, 'showList'
                );
            }
        }

        $values[ 'new' ][ 'link' ] = $this->translatePageToUri( '/' . __FUNCTION__ . '/0' );

        // Creating the form
        $values[ 'diffusionList' ] = $mailer->ml_getAll();
        foreach( $values[ 'diffusionList' ] as $oneId => &$oneList ) {
            if( $id == $oneId ) {
                $values[ 'diffusionList' ][ $oneId ][ 'dontShow' ] = true;
            }
            $oneList[ 'edit' ] = $this->translatePageToUri( '/' . __FUNCTION__ . '/' . $oneId );
            $oneList[ 'remove' ] = $this->translatePageToUri( '/removeList/' . $oneId );
        }

        if( $id > 0 && isset( $values[ 'diffusionList' ][ $id ] ) ) {
            // We are editing an existing list
            $values[ 'list' ] = $values[ 'diffusionList' ][ $id ];
        } else {
            // We are creating a new list
            $values[ 'list' ][ 'new' ] = true;
            $id = 0;
        }
        $this->render( 'manageLists', $values );
    }

    /**
     * Lets the users subscribe to the mailing lists
     */
    public function subscribe() {
        sh_cache::disable();

        if( $result = $this->formSubmitted( 'subscribeNewsForm', true ) ) {
            $submitted = true;
            $mailAddress = $_POST[ 'mail' ];
            $mailer = $this->linker->mailer->get( false );
            $mailOk = $mailer->checkAddress( $mailAddress );
            if( $result === true ) {
                if( $mailOk && is_array( $_POST[ 'newsletters' ] ) ) {
                    $values[ 'subscription' ][ 'done' ] = true;
                    $verifyer = md5( __CLASS__ . microtime() );

                    foreach( array_keys( $_POST[ 'newsletters' ] ) as $newsletter ) {
                        $paramsFile = SH_SITEPARAMS_FOLDER . __CLASS__ . '_sub_' . $newsletter;
                        $this->linker->params->addElement( $paramsFile, true );
                        $needConfirmation = $this->linker->params->get(
                            $paramsFile, 'needConfirmation', array( )
                        );
                        $needConfirmation[ $mailAddress ] = array(
                            'verif' => $verifyer,
                            'date' => date(
                                'U', mktime( 0, 0, 0, date( "m" ), date( "d" ) + 15, date( "Y" ) )
                            )
                        );
                        $this->linker->params->set( $paramsFile, 'needConfirmation', $needConfirmation );
                        $this->linker->params->write( $paramsFile );
                    }
                    // We send a mail
                    $site = $this->linker->path->getBaseUri();
                    $link .= $site . $this->translatePageToUri( '/confirmSubscription/' );
                    $link .= '?mail=' . $mailAddress . '!AMP!verif=' . $verifyer;
                    $values[ 'confirmation' ][ 'link' ] = $link;
                    $values[ 'confirmation' ][ 'site' ] = $site;
                    $content = $this->render( 'subscription_mail', $values, false, false );

                    $mail = $mailer->em_create();
                    $mailer->em_from(
                        $mail, 'nss@websailors.com', 'Newsletter Subscription Service'
                    );
                    $mailer->em_addSubject(
                        $mail, $this->getI18n( 'confirmSubscriptionMail_title' )
                    );
                    $content = str_replace( '!AMP!', '&', $content );
                    $mailer->em_addContent( $mail, $content );
                    $rep = $mailer->em_send( $mail, array( array( $mailAddress ) ) );
                    if( !$rep ) {
                        echo 'Erreur dans l\'envoi du mail de confirmation...';
                    }
                }
            } elseif( $result == sh_captcha::CAPTCHA_ERROR ) {
                $values[ 'captcha' ][ 'error' ] = 'true';
            }
        }
        $this->linker->html->setTitle(
            $this->getI18n( $this->constants[ 'subscription_title' ] )
        );
        $intro = $this->getI18n(
            $this->constants[ 'subscription_intro' ]
        );
        if( trim( $intro ) != '' ) {
            $values[ 'content' ][ 'intro' ] = $intro;
        }
        if( $submitted == true ) {
            if( !is_array( $_POST[ 'newsletters' ] ) ) {
                $values[ 'error' ][ 'nothingSelected' ] = true;
                $values[ 'error' ][ 'oneAtLeast' ] = true;
            } else {
                $selectedNewsletters = array_keys( $_POST[ 'newsletters' ] );
            }
            if( !$mailOk ) {
                $values[ 'error' ][ 'mail' ] = true;
                $values[ 'error' ][ 'oneAtLeast' ] = true;
            } else {
                $values[ 'mail' ][ 'value' ] = $_POST[ 'mail' ];
            }
        }

        $mailer = $this->linker->mailer->get( true );
        $values[ 'newsletters' ] = $mailer->ml_getAll();
        foreach( $values[ 'newsletters' ] as &$newsletter ) {
            $newsletter[ 'description' ] = '<div>' . nl2br( $newsletter[ 'description' ] ) . '</div>';
        }
        if( count( $values[ 'newsletters' ] ) > 1 ) {
            if( is_array( $values[ 'newsletters' ] ) && is_array( $selectedNewsletters ) ) {
                foreach( $values[ 'newsletters' ] as &$newsletter ) {
                    if( in_array( $newsletter[ 'id' ], $selectedNewsletters ) ) {
                        $newsletter[ 'state' ] = 'checked';
                    }
                }
            }
            $values[ 'nl_list' ][ 'moreThanOne' ] = true;
        }

        $this->render( 'subscribe', $values );
    }

    public function unsubscribe() {
        sh_cache::disable();
        $this->linker->html->setTitle(
            $this->getI18n( 'unsubscription_title' )
        );
        $mail = $_GET[ 'mail' ];
        $values = array( );
        if( trim( $mail ) != '' ) {
            $values[ 'mail' ][ 'value' ] = $mail;
            $mailer = $this->linker->mailer->get( true );
            if( is_array( $_GET[ 'mailingLists' ] ) ) {
                // The mailingLists are chosen
                foreach( array_keys( $_GET[ 'mailingLists' ] ) as $mailingList ) {
                    $mailer->ml_removeAddress( $mailingList, $mail );
                }
                $this->render( 'unsubscription_successfull', $values );
                return true;
            }

            // The user has to select the mailing lists he wants to unsubscribe from
            $values[ 'mailingLists' ] = $mailer->ml_getOneMailMailingLists( $mail );

            if( !is_array( $values[ 'mailingLists' ] ) ) {
                $values[ 'error' ][ 'noSubscription' ] = true;
                $values[ 'error' ][ 'oneAtLeast' ] = true;
            } else {
                $this->render( 'unsubscribe_selectML', $values );
                return true;
            }
        }
        $this->render( 'unsubscribe', $values );
    }

    public function confirmSubscription() {
        sh_cache::disable();
        $mail = $_GET[ 'mail' ];
        $verif = $_GET[ 'verif' ];

        $mailer = $this->linker->mailer->get( true );
        $list = $mailer->ml_getAll();
        if( is_array( $list ) ) {
            foreach( $list as $id => $newsletter ) {
                $paramsFile = SH_SITEPARAMS_FOLDER . __CLASS__ . '_sub_' . $id;
                $this->linker->params->addElement( $paramsFile, true );
                $preliminaryList = $this->linker->params->get(
                    $paramsFile, 'needConfirmation', array( )
                );

                if( isset( $preliminaryList[ $mail ] ) ) {
                    $date = $preliminaryList[ $mail ][ 'date' ];
                    if( $date > date( 'U' ) ) {
                        if( $verif == $preliminaryList[ $mail ][ 'verif' ] ) {
                            $this->linker->params->set(
                                $paramsFile, 'needConfirmation>' . $mail . '>verif', 'DONE'
                            );
                            $this->linker->params->write( $paramsFile );

                            $mailer = $this->linker->mailer->get( true );
                            $mailer->ml_addAddress( $newsletter[ 'id' ], $mail );

                            $values[ 'response' ][ 'ok' ] = true;
                            $values[ 'response' ][ 'validated' ] = true;
                        } elseif( 'DONE' == $preliminaryList[ $mail ][ 'verif' ] ) {
                            $values[ 'response' ][ 'ok' ] = true;
                            $values[ 'response' ][ 'alreadyValidated' ] = true;
                        }
                    } else {
                        $values[ 'response' ][ 'dateOver' ] = true;
                    }
                }
            }
        }
        $values[ 'links' ][ 'subscribe' ] = $this->translatePageToUri(
            '/subscribe/'
        );

        $values[ 'site' ][ 'base' ] = $this->linker->path->getBaseUri();
        $this->render( 'subscription_confirmation', $values );
        return true;
    }

//          NEWSLETTERS PART            \\
    public function getNewsletterTitle( $id ) {
        $newsletters = NEWSLETTERS_PARAMSFOLDER . 'list.params.php';
        $this->linker->params->addElement( $newsletters );
        return $this->linker->params->get(
                $newsletters, $id . '>title'
        );
    }

    /**
     * Shows a newsletter identified by its id
     * @return bool <b>true</b> for OK, <b>false</b> for not found, or not
     * authorized
     */
    public function show() {
        $id = ( int ) $this->linker->path->page[ 'id' ];

        $mailer = $this->linker->mailer->get( true );
        $hasBeenSent = $mailer->nl_hasBeenSent( $id );

        if(
            $hasBeenSent == sh_mailsenders::ERROR_NL_DOESNOTEXIST
            || ($hasBeenSent == false && !$this->isAdmin())
        ) {
            $this->linker->path->error( 404 );
            return false;
        }

        // Adds an entry in the command panel
        $this->linker->admin->insert(
            '<a href="' . $this->translatePageToUri(
                '/createNewsletter/' . $id
            ) . '">Modifier cette newsletter</a>', 'Newsletter', 'bank1/picto_modify.png'
        );

        $this->linker->html->setTitle(
            $mailer->nl_getTitle( $id )
        );
        $values[ 'newsletter' ][ 'content' ] = $mailer->nl_getContent( $id );
        $values[ 'link' ][ 'news_backToList' ] = $this->translatePageToUri( '/showList/' );
        $this->render( 'newsletter', $values );
        return true;
    }

    public function getSubmenus( $method, $id ) {
        $allResults = array( );
        if( $method == 'showList' ) {
            $newsletters = NEWSLETTERS_PARAMSFOLDER . 'list.params.php';
            $this->linker->params->addElement( $newsletters );
            $lists = $this->linker->params->get( $newsletters, '', array( ) );
            foreach( $lists as $id => $newsletter ) {
                if( $newsletter[ 'sent' ] && !$newsletter[ 'hidden' ] ) {
                    $allResults[ $newsletter[ 'date' ] . '-' . $id ] = array(
                        'title' => $newsletter[ 'title' ],
                        'link' => $this->translatePageToUri( '/show/' . $id )
                    );
                }
            }
            if( is_array( $allResults ) ) {
                krsort( $allResults );
                if( count( $allResults > 5 ) ) {
                    list($ret) = array_chunk( $allResults, 5, true );
                }
                $ret[ ] = array(
                    'title' => $this->getI18n( 'action_subscribe' ),
                    'link' => $this->translatePageToUri( '/subscribe/' )
                );
            }
        }
        return $ret;
    }

    public function showList() {
        $newsletters = NEWSLETTERS_PARAMSFOLDER . 'list.params.php';
        $this->linker->params->addElement( $newsletters );
        $lists = $this->linker->params->get( $newsletters, '', array( ) );
        foreach( $lists as $id => $newsletter ) {
            if( $newsletter[ 'sent' ] && !$newsletter[ 'hidden' ] ) {
                list($year, $month, $day) = explode( '-', $newsletter[ 'date' ] );
                $values[ 'monthes' ][ $year . $month ][ 'year' ] = $year;
                $values[ 'monthes' ][ $year . $month ][ 'month' ] = $month;
                $values[ 'monthes' ][ $year . $month ][ 'name' ] = $this->getI18n(
                    'monthAndYear'
                );
                $values[ 'monthes' ][ $year . $month ][ 'newsletters' ][ $day . $id ] = array(
                    'id' => $id,
                    'title' => $newsletter[ 'title' ],
                    'date' => $this->linker->datePicker->dateToLocal(
                        $newsletter[ 'date' ]
                    ),
                    'link' => $this->translatePageToUri( '/show/' . $id )
                );
            }
        }
        if( is_array( $values[ 'monthes' ] ) ) {
            krsort( $values[ 'monthes' ] );
            foreach( $values[ 'monthes' ] as &$month ) {
                krsort( $month[ 'newsletters' ] );
            }
        } else {
            $values[ 'newsletters' ][ 'noneSent' ] = true;
        }

        $values[ 'subscribe' ][ 'link' ] = $this->translatePageToUri( '/subscribe/' );
        $values[ 'unsubscribe' ][ 'link' ] = $this->translatePageToUri( '/unsubscribe/' );

        if( $this->getParam( 'showIntro', true ) ) {
            $values[ 'intro' ][ 'show' ] = true;
            $values[ 'intro' ][ 'content' ] = $this->getI18n(
                $this->constants[ 'list_intro' ]
            );
        }
        $title = $this->getI18n(
            $this->constants[ 'list_title' ]
        );
        $this->linker->html->setTitle( $title );

        $this->render( 'showList', $values );
    }

    public function showInvisible() {
        $this->onlyAdmin( true );

        $mailer = $this->linker->mailer->get( true );

        $sent = $mailer->nl_getAll( sh_mailsenders::NL_SENT );
        foreach( $sent as $id => $newsletter ) {
            $values[ 'newsletters_sent' ][ $newsletter[ 'date' ] . '-' . $id ] = array(
                'id' => $id,
                'date' => $this->linker->datePicker->dateToLocal( $newsletter[ 'date' ] ),
                'title' => $newsletter[ 'title' ],
                'showLink' => $this->translatePageToUri( '/show/' . $id ),
                'editLink' => $this->translatePageToUri( '/createNewsletter/' . $id ),
                'deleteLink' => $this->translatePageToUri( '/delete/' . $id ),
            );
            $values[ 'newsletters' ][ 'sent' ] = true;
        }

        $planned = $mailer->nl_getAll( sh_mailsenders::NL_PLANNED );
        foreach( $planned as $id => $newsletter ) {
            $values[ 'newsletters_planned' ][ $newsletter[ 'date' ] . '-' . $id ] = array(
                'id' => $id,
                'date' => $this->linker->datePicker->dateToLocal( $newsletter[ 'date' ] ),
                'title' => $newsletter[ 'title' ],
                'showLink' => $this->translatePageToUri( '/show/' . $id ),
                'editLink' => $this->translatePageToUri( '/createNewsletter/' . $id ),
                'deleteLink' => $this->translatePageToUri( '/delete/' . $id ),
            );
            $values[ 'newsletters' ][ 'planned' ] = true;
        }

        $notPlanned = $mailer->nl_getAll( sh_mailsenders::NL_NOTPLANNED );
        foreach( $notPlanned as $id => $newsletter ) {
            $values[ 'newsletters_notPlanned' ][ $newsletter[ 'date' ] . '-' . $id ] = array(
                'id' => $id,
                'title' => $newsletter[ 'title' ],
                'showLink' => $this->translatePageToUri( '/show/' . $id ),
                'editLink' => $this->translatePageToUri( '/createNewsletter/' . $id ),
                'deleteLink' => $this->translatePageToUri( '/delete/' . $id ),
            );
            $values[ 'newsletters' ][ 'notPlanned' ] = true;
        }

        $this->render( 'showInvisible', $values );
    }

    public function showListForSending() {
        $this->onlyAdmin();
        $id = ( int ) $this->linker->path->page[ 'id' ];
        $newsletters = $this->db_execute( 'newsletters_get_all' );
        if( !is_array( $newsletters ) ) {
            $values[ 'newsletters' ][ 'none' ] = true;
        } else {
            $values[ 'newsletters' ] = $newsletters;
        }

        $this->render( __FUNCTION__, $values );
    }

    public function edit_newslettersList() {
        $this->onlyAdmin( true );

        $newsletters = NEWSLETTERS_PARAMSFOLDER . 'list.params.php';

        $this->linker->params->addElement( $newsletters, true );
        // We get the next newsletter id
        $list = $this->linker->params->get(
            $newsletters, '', array( )
        );

        if( is_array( $list ) ) {
            $cpt = 0;
            foreach( $list as $id => $newsletter ) {
                if( $id != 'count' ) {
                    $element = $newsletter[ 'date' ] . '-' . $cpt;
                    if( $newsletter[ 'sent' ] ) {
                        $category = 'sentNewsletters';
                    } else {
                        $category = 'newsletters';
                    }
                    $values[ $category ][ $element ][ 'title' ] = $newsletter[ 'title' ];
                    $values[ $category ][ $element ][ 'date' ] = $this->linker->datePicker->dateToLocal(
                        $newsletter[ 'date' ]
                    );
                    $values[ $category ][ $element ][ 'link' ] = $this->translatePageToUri(
                        '/show/' . $id
                    );
                    $values[ $category ][ $element ][ 'editLink' ] = $this->translatePageToUri(
                        '/createNewsletter/' . $id
                    );
                    $values[ $category ][ $element ][ 'deleteLink' ] = $this->translatePageToUri(
                        '/delete/' . $id
                    );
                    $cpt++;
                }
            }
            if( is_array( $values[ 'newsletters' ] ) ) {
                ksort( $values[ 'newsletters' ] );
            }
            if( is_array( $values[ 'sentNewsletters' ] ) ) {
                ksort( $values[ 'sentNewsletters' ] );
            }
        } else {
            $values[ 'newsletters' ][ 'none' ] = true;
        }

        $this->render( 'edit_newslettersList', $values );
    }

    /**
     * Gets the parametters from the active newsletters, and the total count
     * (active + inactive)
     * @param bool|int $totalCount An argument to be passed by reference to get
     * the total number of lists
     * @return array The lists
     */
    protected function getNewslettersList( $totalCount = false ) {
        $lists = $this->getParam( 'list', array( ) );
        $totalCount = count( $lists );
        foreach( $lists as $key => $list ) {
            if( isset( $list[ 'removed' ] ) ) {
                unset( $lists[ $key ] );
            }
        }
        return $lists;
    }

    public function isActivated() {
        return $this->getParam( 'activated', true );
    }

    public function delete() {
        $this->onlyAdmin( true );
        $id = ( int ) $this->linker->path->page[ 'id' ];
        $mailer = $this->linker->mailer->get( true );
        $values[ 'links' ][ 'showInvisible' ] = $this->translatePageToUri( '/showInvisible/' );
        if( $this->formSubmitted( 'confirmNLDeletion' ) ) {
            if( isset( $_POST[ 'cancel' ] ) ) {
                $this->linker->path->redirect( $values[ 'links' ][ 'showInvisible' ] );
            }
            $rep = $mailer->nl_delete( $id );
            $this->render( 'newsletter_deleted', $values );
            return true;
        }
        if( !$mailer->nl_exists( $id ) ) {
            $this->render( 'newsletter_delete_inexistant', $values );
            return true;
        }
        $this->render( 'newsletter_delete', $values );
        return true;
    }

    public function createNewsletter() {
        $this->onlyAdmin( true );
        $this->linker->html->setTitle( $this->getI18n( 'createTitle' ) );
        $templatePath = $this->linker->html->getTemplatePath();
        $id = ( int ) $this->linker->path->page[ 'id' ];
        // STEP 2
        if( $this->formSubmitted( 'create_newsletter' ) ) {
            // Creation of the newsletter itself
            $classesContents = '';
            if( is_array( $_POST[ 'classes' ] ) ) {
                foreach( $_POST[ 'classes' ] as $class => $value ) {
                    $classesContents .= $this->linker->$class->createNewsletter( $id );
                }
            }
            $title = stripslashes( $_POST[ 'title' ] );
            $date = stripslashes( $_POST[ 'date' ] );
            $dateI18ned = stripslashes( $_POST[ 'date_i18ned' ] );
            $values[ 'content' ][ 'date' ] = $dateI18ned;
            $values[ 'content' ][ 'title' ] = $title;
            $values[ 'content' ][ 'plugins' ] = $classesContents;
            $model = $_POST[ 'model' ];
            $templateName = $this->linker->site->templateName;
            $variation = $this->linker->site->variation;
            $variationReplacements = array_change_key_case(
                $this->linker->variation->get( sh_variation::ALL_VALUES )
            );
            $values[ 'variation' ] = $variationReplacements;
            $content = $this->render(
                $templatePath . '/newsletter/' . $model . '.rf.xml', $values, false, false
            );

            $content = preg_replace(
                array(
                '`/images/template/variation/`',
                '`/images/template/`',
                ),
                array(
                '/templates/' . $templateName . '/images/variations/' . $variation . '/',
                '/templates/' . $templateName . '/images/',
                ), $content
            );

            // Creation of the form
            $values[ 'newsletter' ][ 'title' ] = $title;
            $values[ 'newsletter' ][ 'content' ] = $content;
            $values[ 'newsletter' ][ 'date' ] = $date;

            if( is_array( $_POST[ 'newsletters' ] ) ) {
                $mailer = $this->linker->mailer->get( true );
                $newsletters = $mailer->ml_getAll();
                foreach( $_POST[ 'newsletters' ] as $id => $newsletter ) {
                    $name = $newsletters[ $id ][ 'name' ];
                    $values[ 'newsletters' ][ ] = array(
                        'id' => $id,
                        'name' => $name
                    );
                }
            }

            $values[ 'tester' ][ 'mail' ] = $_SESSION[ 'sh_user' ][ 'connected' ][ 'mail' ];
            return $this->render( 'edit_newsletter', $values );
        } elseif( $this->formSubmitted( 'edit_newsletter' ) ) {// Saving
            return $this->saveNewsletter();
        } elseif( $id > 0 ) {
            $mailer = $this->linker->mailer->get( true );
            $date = $mailer->nl_getPlannedDate( $id );
            if( $date == false ) {
                $date = '';
            } else {
                $values[ 'newsletter' ][ 'sendIt' ] = 'checked';
            }

            if( $mailer->nl_hasBeenSent( $id ) ) {
                $values[ 'newsletter' ][ 'sent' ] = true;
            }

            $values[ 'newsletter' ][ 'date' ] = $date;

            $sendingTo = $mailer->nl_getMailingLists( $id );
            $newslettersList = $mailer->ml_getAll();

            foreach( $sendingTo as $nlId ) {
                $name = $newslettersList[ $nlId ][ 'name' ];
                $values[ 'newsletters' ][ ] = array(
                    'id' => $nlId,
                    'name' => $name
                );
            }

            $values[ 'newsletter' ][ 'content' ] = utf8_encode( $mailer->nl_getContent( $id, false ) );
            $values[ 'newsletter' ][ 'title' ] = $mailer->nl_getTitle( $id );
            return $this->render( 'edit_newsletter', $values );
        }

        // Step 1
        $mailer = $this->linker->mailer->get( true );

        $addToForm = '';
        $classses = $this->get_shared_methods( 'newsletter_parts_proposals' );
        foreach( $classses as $class ) {
            $addToForm .= $this->linker->$file->newsletter_parts_proposals( $id );
            $values[ 'classes' ][ ][ 'name' ] = $class;
        }


        $values[ 'addToForm' ][ 'content' ] = $addToForm;

        $models = scandir( $templatePath . '/newsletter/' );
        foreach( $models as $model ) {
            if( substr( $model, -7 ) == '.rf.xml' ) {
                $values[ 'models' ][ ][ 'name' ] = substr( $model, 0, -7 );
            }
        }

        $values[ 'newsletters' ] = $mailer->ml_getAll();

        $values[ 'newsletter' ][ 'content' ] = '';
        $values[ 'newsletter' ][ 'date' ] = '';

        $this->render( 'create_newsletter', $values );
    }

    public function saveNewsletter() {
        $id = ( int ) $this->linker->path->page[ 'id' ];
        $mailer = $this->linker->mailer->get( true );
        if( $id == 0 ) {
            $id = $mailer->nl_create();
        }
        // We check if the newsletter has already been sent
        $hasBeenSent = $mailer->nl_hasBeenSent( $id );

        $mailer->nl_addTitle( $id, stripslashes( $_POST[ 'title' ] ) );
        $mailer->nl_addContent( $id, stripslashes( stripslashes( $_POST[ 'content' ] ) ) );

        if( $hasBeenSent !== true ) {
            // We remove all the mailing lists associated to this newsletter
            $mailer->nl_removeMailingList( $id );
            // And add the selected ones
            if( is_array( $_POST[ 'newsletters' ] ) ) {
                foreach( array_keys( $_POST[ 'newsletters' ] ) as $mailingList ) {
                    $mailer->nl_addMailingList( $id, $mailingList );
                }
            }

            if( isset( $_POST[ 'sendIt' ] ) ) {
                $mailer->nl_sendPlanned( $id, $_POST[ 'date' ] );
                $values[ 'newsletter' ][ 'toBeSent' ] = true;
                $values[ 'newsletter' ][ 'sendDate' ] = $this->linker->datePicker->dateToLocal(
                    $_POST[ 'date' ]
                );
            } else {
                $mailer->nl_sendPlanned( $id, false );
                $values[ 'newsletter' ][ 'noSendingDate' ] = true;
            }
        } else {
            $values[ 'newsletter' ][ 'alreadySent' ] = true;
        }

        // We set the parametters for this newsletter
        $values[ 'title' ] = stripslashes( $_POST[ 'title' ] );
        $this->render( 'newsletter_saved', $values );
        return true;
    }

    public function isThereANewsletterWaiting() {
        if( is_dir( NEWSLETTERS_PARAMSFOLDER . self::$todaysFolder ) ) {
            
        }
        return false;
    }

    public function showPage() {
        $page = str_replace(
            array( '!QM!', '!AMP!', '!EQ!' ), array( '?', '&', '=' ), $_GET[ 'page' ]
        );
        $this->linker->path->redirect( $page );
        return true;
    }

    public function sendTest() {
        $this->onlyAdmin( true );
        $content = stripslashes( $_POST[ 'content' ] );
        $domain = $this->linker->path->getBaseUri();
        $page = $this->translatePageToUri( '/showPage/' );
        $linkRoot = $domain . $page . '?nl=test&page=';
        //Removing the ? and & in links
        preg_match_all( '`(<a [^>]*href=")([^>]+)("[^>]*>)`', $content, $matches, PREG_SET_ORDER );
        foreach( $matches as $match ) {
            if( strtolower( substr( $match[ 2 ], 0, 7 ) ) != 'mailto:' ) {
                $oldLink = str_replace(
                    array( '?', '&amp;', '=' ), array( '!QM!', '!AMP!', '!EQ!' ), $match[ 2 ]
                );
                $content = str_replace(
                    $match[ 0 ], $match[ 1 ] . $linkRoot . $oldLink . $match[ 3 ], $content
                );
            }
        }
        
        $content = $this->getI18n( 'test_intro' ) . $content . $this->getI18n( 'test_outro' );
        $content = $this->linker->mailer->cleanContent( $content );

        $mailer = $this->linker->mailer->get( false );

        $mail = $mailer->em_create();
        $mailer->em_from(
            $mail, 'nts@websailors.com', 'Newsletter Testing Service'
        );
        $mailer->em_addSubject(
            $mail, $this->getI18n( 'test_subjectBeginning' ) . stripslashes( $_POST[ 'title' ] )
        );
        $content = str_replace( '!AMP!', '&', $content );
        $mailer->em_addContent( $mail, $content, sh_mailsenders::EM_CONTENTTYPE_HTML, false );
        $address = $mailer->em_addAddress( $mail, $_POST[ 'mail' ] );
        if( $address !== true || !$mailer->em_send( $mail ) ) {
            echo $mailer->getErrorMessage();
        } else {
            echo $this->getI18n( 'test_sentSuccessfully' );
        }
        // We mask the message 5 seconds later
        echo '<script type="text/javascript">
window.setTimeout(function(){$("test_mail_response").innerHTML="";}, 10000);
</script>';

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
