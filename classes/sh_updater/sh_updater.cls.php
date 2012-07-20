<?php

/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2012
 * @license http://www.cecill.info
 * @version See version in self::CLASS_VERSION.
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) ) {
    header( 'location: directCallForbidden.php' );
}

/**
 * This class only launches every other classes in order to have them updated.
 * To automatically update (or install) a class, just change this class' version number
 */
class sh_updater extends sh_core {

    const CLASS_VERSION = '1.1.12.05.29.4';

    protected $minimal = array(
        'ajax_echo_ok' => true
    );
    public $callWithoutId = array(
        'ajax_echo_ok'
    );
    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db', 'sh_i18n', 'sh_renderer'
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();

        if( $installedVersion != self::CLASS_VERSION ) {

            $this->helper->addClassesSharedMethods( 'sh_masterServer', '', __CLASS__ );
            // We construct all classes, in case any of them still needs to be updated
            $order = array(
                'db', 'path', 'renderer', 'wEditor', 'captcha', 'flash', 'video', 'sound', 'html', 'i18n', 'form_elements',
                'javascript', 'browser', 'facebook', 'helpToolTips'
            );
            foreach( $order as $one ) {
                $this->linker->$one;
            }

            $classes = scandir( SH_CLASS_FOLDER );
            $forLater = array( );
            foreach( $classes as $class ) {
                if( substr( $class, 0, 1 ) != '.' ) {
                    if( file_exists( SH_CLASS_FOLDER . $class . '/' . $class . '.cls.php' ) ) {
                        $may_be_updated = !file_exists( SH_CLASS_FOLDER . $class . '/doesnt_extend_core.php' );
                        $may_be_updated = $may_be_updated && !file_exists( SH_CLASS_FOLDER . $class . '/is_abstract.php' );
                    if( $may_be_updated ) {
                            if( substr( $class, 0, strlen( SH_PREFIX ) ) == SH_PREFIX ) {
                            $this->linker->$class;
                            } elseif( substr( $class, 0, strlen( SH_PREFIX ) ) == SH_PREFIX ) {
                                $forLater[] = $class;
                            }
                        }
                    }
                }
            }
            foreach( $forLater as $class ) {
                $this->linker->$class;
            }

            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
        $this->linker->masterServer;
    }

    public function masterServer_getMethods() {
        return array( 'ajax_echo_ok' );
    }

    /**
     * This page is to be called using ajax when we want the installation to be udated.<br />
     * It only echoes "OK" when done (before that, during its construction, it should have 
     * updated every other classes).
     */
    public function ajax_echo_ok() {
        echo 'OK';
        exit;
    }

    public function getPageName() {
        
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}