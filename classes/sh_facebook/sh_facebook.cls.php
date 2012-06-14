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
class sh_facebook extends sh_core {

    const CLASS_VERSION = '1.1.11.10.12';

    protected static $usesRightsManagement = false;
    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db', 'sh_renderer', 'sh_site', 'sh_template', 'sh_variation',
        'sh_admin', 'sh_browser', 'sh_css', 'sh_html', 'sh_events', 'sh_cron', 'sh_helper'
    );
    public $callWithoutId = array(
        'manage'
    );
    public $callWithId = array( );
    protected $likeButtonEnabled = false;

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->params->updateParams( __CLASS__ );

            // The class datas are not in the same version as this file, or don't exist (installation)
            if( version_compare( $installedVersion, '1.1.11.05.16', '<' ) ) {
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
                $this->linker->renderer->add_render_tag( 'render_facebook_likebutton', __CLASS__, 'render_likebutton' );
                $this->linker->renderer->add_render_tag( 'render_facebook_likebox', __CLASS__, 'render_likeBox' );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        $this->likeButtonEnabled = $this->getParam( 'likeButton>activated', false );
    }

    public function master_getMenuContent() {
        return array( );
    }

    public function admin_getMenuContent() {
        $adminMenu[ 'Contenu' ][ ] = array(
            'link' => 'facebook/manage/', 'text' => 'Paramètres Facebook', 'icon' => 'picto_facebook.png'
        );

        return $adminMenu;
    }

    public function manage() {
        $this->onlyAdmin( true );
        $this->linker->html->setTitle( $this->getI18n( 'action_manage' ) );
        if( $this->formSubmitted( 'facebook' ) ) {
            $likeButtonParams = array(
                'modules' => $_POST[ 'likeButtons' ]
            );
            $this->setParam( 'likeButton', $likeButtonParams );
            $this->setParam( 'facebook_id', $_POST[ 'facebook_id' ] );
            $this->setParam( 'siteName', $_POST[ 'siteName' ] );
            $this->writeParams();
        }
        $values[ 'facebook' ][ 'siteName' ] = $this->getParam( 'siteName', $this->linker->path->getBaseUri() );
        $values[ 'facebook' ][ 'id' ] = $this->getParam( 'facebook_id', '' );
        $likeButtonParams = $this->getParam( 'likeButton', array( ) );

        $classes = $this->get_shared_methods();
        foreach( $classes as $class ) {
            $modules = $this->linker->$class->facebook_getModules();
            foreach( $modules as $name => $description ) {
                $state = '';
                if( isset( $likeButtonParams[ 'modules' ][ $class ][ $name ] ) ) {
                    $state = 'checked';
                }
                $values[ 'modules' ][ ] = array(
                    'class' => $class,
                    'name' => $name,
                    'description' => $description,
                    'state' => $state
                );
            }
        }

        $this->render( 'manage', $values );
    }

    public function render_likeButton( $attributes ) {
        // We first check if the page may show like buttons
        if( empty( $attributes[ 'class' ] ) || empty( $attributes[ 'element' ] ) ) {
            return false;
        }
        if( !$this->getParam( 'likeButton>modules>' . $attributes[ 'class' ] . '>' . $attributes[ 'element' ], false ) ) {
            return false;
        }

        if( isset( $attributes[ 'title' ] ) ) {
            $values[ 'likeButton' ][ 'title' ] = $attributes[ 'title' ];
            $this->linker->html->addMetaProperty( 'og:title', $values[ 'likeButton' ][ 'title' ] );
        } else {
            return '';
        }

        if( isset( $attributes[ 'image' ] ) ) {
            $values[ 'likeButton' ][ 'image' ] = $this->linker->path->getBaseUri() . $attributes[ 'image' ] . '.resized.50.50.png';
            $this->linker->html->addMetaProperty( 'og:image', $values[ 'likeButton' ][ 'image' ] );
        } else {
            return '';
        }

        if( isset( $attributes[ 'type' ] ) ) {
            $values[ 'likeButton' ][ 'type' ] = $attributes[ 'type' ];
        } else {
            $values[ 'likeButton' ][ 'type' ] = 'article';
        }
        $this->linker->html->addMetaProperty( 'og:type', $values[ 'likeButton' ][ 'type' ] );

        if( isset( $attributes[ 'url' ] ) ) {
            $values[ 'likeButton' ][ 'url' ] = urlencode( $attributes[ 'url' ] );
        } else {
            $values[ 'likeButton' ][ 'url' ] = urlencode( $this->linker->path->url );
        }
        $this->linker->html->addMetaProperty( 'og:url', $values[ 'likeButton' ][ 'url' ] );

        if( isset( $attributes[ 'site_name' ] ) ) {
            $values[ 'likeButton' ][ 'site_name' ] = $attributes[ 'site_name' ];
        } else {
            $values[ 'likeButton' ][ 'site_name' ] = $this->getParam( 'siteName', $this->linker->path->getBaseUri() );
        }
        $this->linker->html->addMetaProperty( 'og:site_name', $values[ 'likeButton' ][ 'site_name' ] );

        $this->linker->html->addMetaProperty( 'fb:admins', $this->getParam( 'facebook_id' ) );

        if( isset( $attributes[ 'layout' ] ) && in_array( $attributes[ 'layout' ], array( 'button_count', 'box_count' ) ) ) {
            $values[ 'likeButton' ][ 'layout' ] = $attributes[ 'layout' ];
        } else {
            $values[ 'likeButton' ][ 'layout' ] = 'standard';
        }

        if( $attributes[ 'show_faces' ] == 'show_faces' ) {
            $values[ 'likeButton' ][ 'show_faces' ] = 'true';
        } else {
            $values[ 'likeButton' ][ 'show_faces' ] = 'false';
        }

        if( isset( $attributes[ 'width' ] ) ) {
            $values[ 'likeButton' ][ 'width' ] = $attributes[ 'width' ];
        } else {
            $values[ 'likeButton' ][ 'width' ] = 450;
        }

        if( isset( $attributes[ 'height' ] ) ) {
            $values[ 'likeButton' ][ 'height' ] = $attributes[ 'height' ];
        } else {
            $values[ 'likeButton' ][ 'height' ] = 35;
        }

        if( $attributes[ 'action' ] == 'recommend' ) {
            $values[ 'likeButton' ][ 'action' ] = 'recommend';
        } else {
            $values[ 'likeButton' ][ 'action' ] = 'like';
        }

        if( isset( $attributes[ 'font' ] ) && in_array( $attributes[ 'action' ],
                                                        array( 'arial', 'lucida grande', 'segoe ui', 'tahoma', 'trebuchet ms', 'verdana' ) ) ) {
            $values[ 'likeButton' ][ 'action' ] = $attributes[ 'font' ];
        } else {
            $values[ 'likeButton' ][ 'action' ] = 'arial';
        }

        if( $attributes[ 'action' ] == 'colorscheme' ) {
            $values[ 'likeButton' ][ 'colorscheme' ] = 'dark';
        } else {
            $values[ 'likeButton' ][ 'colorscheme' ] = 'light';
        }


        $ret = $this->render( 'likeButton', $values, false, false );
        return $ret;
    }

    public function render_likeBox( $attributes ) {
        $values[ 'likebox' ][ 'facebook_id' ] = $this->getParam( 'facebook_id', null );

        if( !is_null( $values[ 'likebox' ][ 'facebook_id' ] ) ) {
            if( isset( $attributes[ 'width' ] ) ) {
                $values[ 'likebox' ][ 'width' ] = $attributes[ 'width' ];
            } else {
                $values[ 'likebox' ][ 'width' ] = 475;
            }

            if( isset( $attributes[ 'height' ] ) ) {
                $values[ 'likebox' ][ 'height' ] = $attributes[ 'height' ];
            } else {
                $values[ 'likebox' ][ 'height' ] = 280;
            }

            if( isset( $attributes[ 'connections' ] ) ) {
                $values[ 'likebox' ][ 'connections' ] = $attributes[ 'connections' ];
            } else {
                $values[ 'likebox' ][ 'connections' ] = 16;
            }

            if( isset( $attributes[ 'stream' ] ) ) {
                $values[ 'likebox' ][ 'stream' ] = $attributes[ 'stream' ];
            } else {
                $values[ 'likebox' ][ 'stream' ] = 'false';
            }

            if( isset( $attributes[ 'header' ] ) ) {
                $values[ 'likebox' ][ 'header' ] = $attributes[ 'header' ];
            } else {
                $values[ 'likebox' ][ 'header' ] = 'false';
            }

            if( isset( $attributes[ 'scrolling' ] ) ) {
                $values[ 'likebox' ][ 'scrolling' ] = $attributes[ 'scrolling' ];
            } else {
                $values[ 'likebox' ][ 'scrolling' ] = 'no';
            }

            if( isset( $attributes[ 'frameborder' ] ) ) {
                $values[ 'likebox' ][ 'frameborder' ] = $attributes[ 'frameborder' ];
            } else {
                $values[ 'likebox' ][ 'frameborder' ] = '0';
            }

            if( isset( $attributes[ 'allowTransparency' ] ) ) {
                $values[ 'likebox' ][ 'allowTransparency' ] = $attributes[ 'allowTransparency' ];
            } else {
                $values[ 'likebox' ][ 'allowTransparency' ] = 'true';
            }


            return $this->render( 'likeBox', $values, false, false );
        }
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
        return $this->__toString() . '->' . $action . '->' . $id;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}