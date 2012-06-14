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
 * This class manages the cookies.<br />
 * To update a cookie's value, we just have to re-set it.<br />
 * Also, until now, there is no check whether cookies are enabled on the user's computer or not!
 */
class sh_cookie extends sh_core {

    const CLASS_VERSION = '1.1.12.02.01';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );

    const VALIDITY = 7776000; // 90 days
    const PATH = '/';

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }
    }

    /**
     * Creates the cookie $name, containing $content. <br />
     * <b>Caution</b> : As this method uses the setcookie() function, not output should have been sent before.
     * @param str $name The name of the cookie, which will be called using $class->get($name).<br />
     * May be a string representation of an array, like var1[var2].
     * @param str $content The value of the cookie (the return of $class->get($name)).
     * @param int $validity The number of seconds in the future when the cookie should expire.
     * @return bool <b>true</b> for success, false for failure (most of the time, when the $name is empty
     * or when something has already been output).
     */
    public function set( $name, $content, $validity = self::VALIDITY ) {
        if( empty( $name ) ) {
            return false;
        }
        return setcookie( $name, $content, time() + $validity, self::PATH );
    }

    /**
     * Gets the value of the cookie $name, or $default if not set.
     * @param str $name The name of the cookie, or the string representation of the array (like var1[var2]).
     * @param str $default The text to return if the cookie is not set
     * @return str The value of the sookie $name, or $default if none.
     */
    public function get( $name, $default = '' ) {
        if( isset( $_COOKIE[ $name ] ) ) {
            return $_COOKIE[ $name ];
        }
        $cleaned = str_replace( ']', '', $name );
        $parts = explode( '[', $cleaned );
        if( count( $cleaned ) > 1 ) {
            // This is an array
            $scope = $_COOKIE;
            foreach( $parts as $part ) {
                if( isset( $scope[ $part ] ) ) {
                    $scope = $scope[ $part ];
                } else {
                    return $default;
                }
            }
            return $scope;
        }
        return $default;
    }

    /**
     * Destroys all the cookies (caution, it might destroy the session itself - not tested)
     * @return true for success, false for _any_ failure (if only 1 cookie could not be destroyed, 
     * this will return false anyway).
     */
    function destroyAll() {
        $ret = true;
        foreach( $_COOKIE as $name => $val ) {
            $ret = $this->destroy( $name ) && $ret;
        }
        return $ret;
    }

    /**
     * Destroys the cookie $name.
     * @param str $name The name of the cookie or the string representation of the array (like var1[var2]).
     * @return bool true for success, false for failure
     */
    function destroy( $name ) {
        $_COOKIE[ $name ] = null;
        $expire = time() - 60 * 60 * 24; // one day ago, so the cookie shoud expire
        return setcookie( $name, "", $expire, self::PATH );
    }

    public function __tostring() {
        return get_class();
    }

}