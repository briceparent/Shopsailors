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

/*
 * Calls events on every object in $this->helper->objects
 * if they exist
 */

class sh_events extends sh_core {

    const CLASS_VERSION = '1.1.11.03.28';

    protected static $needs_db = false;
    protected static $needs_form_verifier = false;
    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $events = array( );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }

    /**
     * This method calls the $event event method on every class that need it (should be declared using
     * linker->addClassesSharedMethod('sh_events','[event name]','[class that requires a call]').
     * @var string $event The name of the event. Should always start with the string "on".
     * @var string $args The args to give to the called method.
     * @return bool True if every called class return true, false if any of them return false, and null , or if no class 
     * has to answer to this event.
     */
    public function __call( $event, $args ) {
        $this->debug( 'Event ' . $event . ' is fired.', 3, __LINE__, true );
        if( substr( $event, 0, 2 ) == 'on' ) {
            $ret = true;
            $classes = $this->get_shared_methods( $event );
            foreach( $classes as $class ) {
                $ret = $this->linker->$class->$event( $args ) && $ret;
            }
            return $ret;
        }
        return null;
    }

    public function __tostring() {
        return get_class();
    }

}