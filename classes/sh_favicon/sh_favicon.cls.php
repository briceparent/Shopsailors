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
 * Class that creates and serves favicon images
 */
class sh_favicon extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $minimal = array( 'get' => true, 'changer' => true );
    const LANG_DIR = 'SH_LANG/';
    protected $completePath = '';

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            if( !is_dir( SH_SITE_FOLDER . __CLASS__ ) ) {
                mkdir( SH_SITE_FOLDER . __CLASS__ );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        $favicon = SH_SITE_FOLDER . __CLASS__ . '/favicon.ico';
        if( !file_exists( $favicon ) ) {
            $favicon = SH_SHAREDIMAGES_FOLDER . 'icons/favicon.ico';
        }
        $this->completePath = $favicon;
    }

    public function setPath( $faviconPath ) {
        $this->completePath = $faviconPath;
    }

    public function getCompletePath() {
        return $this->completePath;
    }

    public function getPath() {
        return '/favicon.ico';
    }

    public function get() {
        $favicon = $this->getCompletePath();

        // It does so, so we send it with the apropriate header
        $contentType = mime_content_type( $favicon );
        $contentType = 'image/x-icon';
        header( 'Content-type: ' . $contentType );
        readfile( $favicon );
        return true;
    }

    public function getChanger() {
        $values[ 'links' ][ 'faviconChanger' ] = $this->translatePageToUri(
            $this->shortClassName . '/changer/'
        );
        $changer = $this->render( 'openChanger', $values, false, false );
        return $changer;
    }

    public function changer() {
        if( $this->formSubmitted( 'faviconChanger' ) ) {
            if( $_FILES[ 'favicon' ][ 'type' ] == 'image/vnd.microsoft.icon' ) {
                move_uploaded_file(
                    $_FILES[ 'favicon' ][ 'tmp_name' ], SH_SITE_FOLDER . __CLASS__ . '/favicon.ico'
                );
                $values[ 'response' ][ 'done' ] = true;
            } else {
                $values[ 'response' ][ 'wrongFileType' ] = true;
            }
        }

        if( file_exists( SH_SITE_FOLDER . __CLASS__ . '/favicon.ico' ) ) {
            $favicon = SH_SITE_PATH . __CLASS__ . '/favicon.ico';
        } else {
            $favicon = SH_SHAREDIMAGES_PATH . 'icons/favicon.ico';
        }
        $values[ 'favicon' ][ 'actual' ] = $favicon;
        echo $this->render( 'changer', $values, false, false );
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        if( $page == $this->shortClassName . '/changer/' ) {
            $uri = '/' . $this->shortClassName . '/changer.php';
            return $uri;
        }

        return parent::translatePageToUri( $page );
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( $uri == '/' . $this->shortClassName . '/changer.php' ) {
            $page = $this->shortClassName . '/changer/';
            return $page;
        }

        return parent::translateUriToPage( $uri );
    }

    public function create( $srcImage ) {
        include(dirname( __FILE__ ) . '/phpThumb_1.7.9/phpthumb.ico.php');
        include(dirname( __FILE__ ) . '/phpThumb_1.7.9/phpthumb.functions.php');
        $phpthumb_ico = new phpthumb_ico();
        $ext = strtolower( array_pop( explode( '.', $srcImage ) ) );
        if( $ext == 'png' ) {
            $gdImage = imagecreatefrompng( $srcImage );
        } elseif( $ext == 'gif' ) {
            $gdImage = imagecreatefromgif( $srcImage );
        } elseif( $ext == 'jpg' || $ext == 'jpeg' ) {
            $gdImage = imagecreatefromjpeg( $srcImage );
        }

        $gdImages = array( $gdImage );
        $ret = GD2ICOstring( $gdImages );
        var_dump( $ret );
    }

    public function __tostring() {
        return get_class();
    }

}
