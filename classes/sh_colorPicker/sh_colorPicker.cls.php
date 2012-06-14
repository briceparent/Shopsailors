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
 * Class that builds colorpickers, using the refresh_web's javascript colorPicker.
 */
class sh_colorPicker extends sh_core {

    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    const DEFAULTCOLOR = '6699CC';

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->renderer->add_render_tag( 'render_colorPicker', __CLASS__, 'render_colorPicker' );
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }

    public function render_colorPicker( $attributes = array( ) ) {
        if( isset( $attributes[ 'name' ] ) ) {
            $name = $attributes[ 'name' ];
        } else {
            return false;
        }
        if( isset( $attributes[ 'id' ] ) ) {
            $id = $attributes[ 'id' ];
        } else {
            $id = null;
        }
        if( isset( $attributes[ 'value' ] ) ) {
            $value = $attributes[ 'value' ];
        } else {
            $value = self::DEFAULTCOLOR;
        }
        if( isset( $attributes[ 'onchange' ] ) ) {
            $onchange = $attributes[ 'onchange' ];
        } else {
            $onchange = '';
        }
        return $this->getOne( $name, $value, $id, $onchange );
    }

    /**
     * public function getOne
     *
     */
    public function getOne( $name, $value = '336699', $id = null, $onchange = '' ) {
        if( $value == '' || $value == 'default' ) {
            $text = 'default';
            $value = 'transparent';
        } else {
            $value = str_replace( '#', '', $value );
            $text = $value;
        }
        if( is_null( $id ) ) {
            $id = md5( 'colorpicker' . microtime() );
        }
        $values[ 'colorPicker' ] = array(
            'id' => $id,
            'name' => $name,
            'text' => $text,
            'default' => $value,
            'onchangeEvent' => $onchange
        );
        $ret = $this->render( 'colorPicker', $values, false, false );
        return $ret;
    }

    public function __tostring() {
        return get_class();
    }

}

