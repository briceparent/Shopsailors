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
 * Class that creates and manages image captchas, using The Kankrelune's captchas generator.
 */
class sh_captcha extends sh_core {

    const CLASS_VERSION = '1.1.12.11.26';

    protected $minimal = array( 'change' => true );
    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db', 'sh_i18n', 'sh_renderer'
    );

    const CAPTCHA_ERROR = 'Captcha_error';

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            if( version_compare( $installedVersion, '1.1.11.03.29', '<=' ) ) {
                $this->linker->renderer->add_render_tag( 'render_captcha', __CLASS__, 'render_captcha' );
            }
            if( version_compare( $installedVersion, '1.1.12.11.26', '<=' ) ) {
                symlink('./captcha.php', dirname(__FILE__).'/singles/captcha.gif');
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }

    /**
     * public function change
     *
     */
    public function change() {
        $form = $_POST[ 'form' ];
        echo $this->getParam( 'imagePath' ) . $form . '&id=' . MD5( microtime() );
    }

    public function render_captcha( $attributes = array( ) ) {
        if( isset( $attributes[ 'what' ] ) ) {
            $what = $attributes[ 'what' ];
        } else {
            return false;
        }
        if( isset( $attributes[ 'tabindex' ] ) ) {
            $tabindex = $attributes[ 'tabindex' ];
        } else {
            $tabindex = null;
        }
        $error = trim( $attributes[ 'error' ] ) != '';

        return $this->create( $what, $error, $tabindex );
    }

    /**
     * public function create
     */
    public function create( $form, $error = false, $tabindex = null ) {
        $this->linker->html->addScript( '/' . __CLASS__ . '/singles/captcha.js' );

        $uid = str_replace( ' ', '_', microtime() );
        $captcha[ 'captcha' ][ 'image' ] = $this->getParam( 'imagePath' ) . $form . '&#38;id=' . $uid;
        $captcha[ 'captcha' ][ 'name' ] = 'captcha_' . $form;
        $captcha[ 'captcha' ][ 'form' ] = $form;
        $captcha[ 'captcha' ][ 'tabindex' ] = $tabindex;
        $captcha[ 'captcha' ][ 'change' ] = $this->linker->path->getUri( 'captcha/change/' );
        if( $error ) {
            $captcha[ 'captcha' ][ 'error' ] = 'input_error';
        }

        $captcha[ 'i18n' ] = __CLASS__;

        $ret = $this->render( 'captcha', $captcha, false, false );
        return $ret;
    }

    /**
     * public function verify
     */
    public function verify( $form ) {
        if( isset( $_POST[ 'captcha' ] ) ) {
            $entered = $_POST[ 'captcha' ];
        } else {
            $entered = $_GET[ 'captcha' ];
        }
        $realValue = $_SESSION[ __CLASS__ ][ $form ][ 'captcha' ];
        if( trim( $realValue ) == '' || strtoupper( $entered ) != strtoupper( $realValue ) ) {
            return false;
        }
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        list($class, $method, $id) = explode( '/', $page );
        if( $method == 'change' ) {
            $uri = '/' . $this->shortClassName . '/' . $this->getI18n( 'change_uri' ) . '.php';
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
        if( preg_match( '`/' . $this->shortClassName . '/' . $this->getI18n( 'change_uri' ) . '\.php`', $uri ) ) {
            $page = $this->shortClassName . '/change/';
            return $page;
        }

        return parent::translateUriToPage( $uri );
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}