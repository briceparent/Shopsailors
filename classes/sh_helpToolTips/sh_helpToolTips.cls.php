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
 * Class that builds help bubbles (tool tips), and their contents and js.
 */
class sh_helpToolTips extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->linker->renderer->add_render_tag( 'render_help', __CLASS__, 'render_help' );
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        $this->addJavascript();
    }

    public function getJavascript() {
        $singlePath = $this->getSinglePath() . 'wz_tooltip/';
        return '<script type="text/javascript" src="' . $singlePath . 'wz_tooltip.js"/>' .
            '<script type="text/javascript" src="' . $singlePath . 'tip_balloon.js"/>';
    }

    public function addJavascript() {
        $singlePath = $this->getSinglePath() . 'wz_tooltip/';
        $this->linker->html->addAfterBody(
            '<script type="text/javascript" src="' . $singlePath . 'wz_tooltip.js"/>'
        );
        $this->linker->html->addAfterBody(
            '<script type="text/javascript" src="' . $singlePath . 'tip_balloon.js"/>'
        );
    }

    public function render_help( $attributes = array( ), $contents = '' ) {
        if( isset( $attributes[ 'what' ] ) ) {
            $values[ 'help' ][ 'what' ] = $attributes[ 'what' ];
        } else {
            if( empty( $contents ) ) {
                return false;
            }
            $values[ 'help' ][ 'what' ] = $contents;
        }

        $values[ 'help' ][ 'id' ] = substr( MD5( __CLASS__ . microtime() ), 0, 12 );


        if( sh_html::$willRender ) {
            return $this->render( 'help', $values, false, false );
        }
        return $this->render( 'help_minimal', $values, false, false );
    }

    public function __tostring() {
        return get_class();
    }

}

