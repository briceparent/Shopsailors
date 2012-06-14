<?php

/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * Class that integrates some Google features into the Shopsailors engine.
 */
class sh_googleServices extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->helper->addClassesSharedMethods( 'sh_path', '', $this->className );
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }

    /**
     * public function get
     *
     */
    public function get() {
        return $this->getParams( 'code', false );
    }

    /**
     * public function getAnalytics
     *
     */
    public function getAnalytics( $force = false ) {
        $this->debug( 'function : ' . __FUNCTION__, 2, __LINE__ );
        if( $force || (!$this->isAdmin() && !$this->isMaster()) ) {
            $domain = $this->linker->path->getDomain();
            list($firstPart) = explode( '.', $domain );
            if( $force || !defined( SH_GLOBAL_DEBUG ) ) {
                return $this->getParam( 'analytics>code', '' );
                ;
            }
        }
    }

    public function setAnalytics( $analytics ) {
        $this->onlyAdmin();
        $this->setParam( 'analytics>code', stripslashes( $analytics ) );
        $this->writeParams();
        return true;
    }

    public function getGoogleForWebmasters() {
        return $this->getParam( 'googleForWebmasters>link', '' );
    }

    public function setGoogleForWebmasters( $link ) {
        $link = str_replace( '/', '', $link );
        if( substr( strtolower( $link ), 0, 6 ) == 'google' ) {
            $this->setParam( 'googleForWebmasters>link', stripslashes( $link ) );
            $this->writeParams();
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
        //There is no reason for this class to build links
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( $uri != '/' && $uri == '/' . $this->getParam( 'googleForWebmasters>link', '' ) ) {
            // In this case, we don\'t have to do more than to send a 200 header,
            // so we send the required text, and quit
            echo 'google-site-verification: ' . $this->getParam( 'googleForWebmasters>link', '' );
            exit( 1 );
        }
        return false;
    }

    public function __tostring() {
        return get_class();
    }

}
