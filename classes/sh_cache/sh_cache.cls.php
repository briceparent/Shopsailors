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
 * Class creating and sending the cache of entire html pages
 */
class sh_cache extends sh_core {

    const CLASS_VERSION = '1.1.11.11.07';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected static $enabled = true;
    protected static $isCss = false;

    const LANGS_CURRENT = 'current';
    const LANGS_ALL = '*';

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->db->updateQueries( __CLASS__ );
            if( version_compare( $installedVersion, '1.1.11.09.29', '<' ) ) {
                $this->db_execute( 'create_table_cache_parts', array( ) );
                $this->db_execute( 'modify_table_cache_parts_add_unique', array( ) );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        if( !isset( $_SESSION[ __CLASS__ ][ 'cache' ] ) ) {
            if( strpos( strtolower( $_SERVER[ 'HTTP_USER_AGENT' ] ), 'shopsailors_no_cache' ) ) {
                $_SESSION[ __CLASS__ ][ 'cache' ] = false;
            } else {
                $_SESSION[ __CLASS__ ][ 'cache' ] = true;
            }
        }
        if( isset( $_GET[ 'shopsailors_no_cache' ] ) ) {
            $_SESSION[ __CLASS__ ][ 'cache' ] = !($_GET[ 'shopsailors_no_cache' ] == true);
        }
    }

    public function part_cache( $class, $part, $content, $lang=self::LANGS_CURRENT ) {
        if( empty( $class ) || empty( $part ) || empty( $lang ) ) {
            return false;
        }
        $this->part_remove( $class, $part, array( $lang ) );
        if( strlen( $content ) > 65000 ) {
            // we should split the entry
            $splits = str_split( $content, 65000 );
            foreach( $splits as $number => $split ) {
                $this->part_cache( $class, $part . '_split_' . $number, $split, $lang );
            }
        } else {
            if( $this->linker->method_exists( $class, 'cache_prepareForCaching' ) ) {
                $content = $this->linker->$class->cache_prepareForCaching( $content );
            }
            $this->db_execute(
                'part_create',
                array(
                'class' => $class,
                'part' => addslashes( $part ),
                'content' => addslashes( $content ),
                'lang' => $lang
                )
            );
        }
        return true;
    }

    public function part_get( $class, $part, $lang=self::LANGS_CURRENT ) {
        if( empty( $class ) || empty( $part ) || empty( $lang ) ) {
            return false;
        }
        list($result) = $this->db_execute(
            'part_get',
            array(
            'class' => $class,
            'part' => addslashes( $part ),
            'lang' => $lang
            )
        );
        if( isset( $result[ 'content' ] ) ) {
            $content = stripslashes( $result[ 'content' ] );
            if( $this->linker->method_exists( $class, 'cache_prepareForUsing' ) ) {
                $content = $this->linker->$class->cache_prepareForUsing( $content );
            }
            return $content;
        } else {
            // It could be a splitted string
            $splits = $this->db_execute(
                'part_get_split',
                array(
                'class' => $class,
                'part' => addslashes( $part ),
                'lang' => $lang
                ), $qry
            );
            $result = '';
            foreach( $splits as $split ) {
                $result .= stripslashes( $split[ 'content' ] );
            }
            if( $this->linker->method_exists( $class, 'cache_prepareForUsing' ) ) {
                $result = $this->linker->$class->cache_prepareForUsing( $result );
            }
            return $result;
        }
        return false;
    }

    public function part_remove( $class, $part, $langs = self::LANGS_ALL ) {
        if( empty( $class ) || empty( $part ) || empty( $langs ) ) {
            return false;
        }

        if( $part == '*' ) {
            if( $langs == self::LANGS_ALL ) {
                $query = 'all_parts_delete_all_langs';
                $langs = array( );
            } else {
                $query = 'all_parts_delete';
            }
        } else {
            if( $langs == self::LANGS_ALL ) {
                $query = 'parts_delete_all_langs';
                $langs = array( );
            } else {
                $query = 'parts_delete';
            }
        }
        list($result) = $this->db_execute(
            $query,
            array(
            'class' => $class,
            'part' => addslashes( $part ),
            'langs' => '"' . implode( '","', $langs ) . '"'
            )
        );

        if( isset( $result[ 'content' ] ) ) {
            return $result[ 'content' ];
        }
        return false;
    }

    /**
     * public static function disable
     */
    public static function disable() {
        self::$enabled = false;
    }

    public static function removeCacheFile( $path = '' ) {
        if( $path == '' ) {
            $path = self::getCacheName();
        }
        if( file_exists( $path ) ) {
            unlink( $path );
        }
    }

    /**
     * protected static function staticGetPath
     *
     */
    protected static function staticGetPath() {
        return SH_CACHE_FOLDER;
    }

    /**
     * This function should only be called from this file and from
     * sh_core
     */
    public static function removeCache() {
        $path = self::staticGetPath();
        $linker = sh_linker::getInstance();
        $linker->helper->deleteDir( $path );
        return true;
    }

    /**
     * protected static function staticRecursiveRm
     *
     */
    protected static function staticRecursiveRm( $element ) {
        if( is_dir( $element ) ) {
            $files = scandir( $element );
            foreach( $files as $file ) {
                if( $file != '.' && $file != '..' ) {
                    if( is_dir( $file ) ) {
                        self::staticRecursiveRm( $element . '/' . $file );
                    } else {
                        unlink( $element . '/' . $file );
                    }
                }
            }
            rmdir( $element );
        } elseif( file_exists( $element ) ) {
            unlink( $element );
        }
    }

    public static function getCachedFile() {
        if( $_SESSION[ __CLASS__ ][ 'cache' ] ) {
            if( sh_session::staticIsAdmin() ) {
                return false;
            }
            $cacheName = self::getCacheName();
            if( !isset( $_POST[ 'verif' ] ) && file_exists( $cacheName ) ) {
                return self::replaceBrowser( file_get_contents( $cacheName ) );
            }
        }
        return false;
    }

    public static function content_is_css() {
        self::$isCss = true;
    }

    protected static function replaceBrowser( $content ) {
        self::prepareBrowser();
        $classes = $_SESSION[ __CLASS__ ][ 'userAgent' ][ 'browser' ] . '  ' . $_SESSION[ __CLASS__ ][ 'userAgent' ][ 'browserAndVersion' ];
        $content = str_replace( 'shopsailors_navigator', $classes, $content );
        return $content;
    }

    protected static function prepareBrowser() {
        if( !isset( $_SESSION[ __CLASS__ ][ 'userAgent' ] ) ) {
            include(dirname( __FILE__ ) . '/browser.php');
            $browser = new Browser();
            $browserName = str_replace( ' ', '_', $browser->getBrowser() );
            $browserVersion = str_replace( '.', '_', $browser->getVersion() );
            preg_match( '`([0-9]+(_[0-9]+)?).*`', $browserVersion, $matches );
            $version = $matches[ 1 ];
            $_SESSION[ __CLASS__ ][ 'userAgent' ][ 'browser' ] = $browserName;
            $_SESSION[ __CLASS__ ][ 'userAgent' ][ 'browserAndVersion' ] = $browserName . '_' . $version;
        }
    }

    public static function saveCache( $content ) {
        if( $_SESSION[ __CLASS__ ][ 'cache' ] ) {
            if( self::$enabled ) {
                self::prepareBrowser();
                $cacheFile = self::getCacheName();
                $src[ 'cache' ] = '<!-- ALL THIS PAGE WAS TAKEN FROM CACHE - (the cache was built on ' . date( 'Y/m/d' ) . ') -->';
                $content = str_replace( '</body', $src[ 'cache' ] . '</body', $content );
                $cacheName = fopen( $cacheFile, 'w+' );
                $rep = fwrite( $cacheName, $content );
                fclose( $cacheName );
            }
        }
        return self::replaceBrowser( $content );
    }

    public static function startCache() {
        ob_start();
    }

    public static function stopCache() {
        $buffer = ob_get_contents();
        ob_end_clean();
        if( self::$enabled ) {
            if( !self::$isCss ) {
                $src[ 'cache' ] = '<!-- ALL THIS PAGE WAS TAKEN FROM CACHE - (the cache was built on ' . date( 'Y/m/d' ) . ') -->';
                $writtenBuffer = str_replace( '</body', $src[ 'cache' ] . '</body', $buffer );
            } else {
                $writtenBuffer = $buffer . "\n" . '/* ALL THIS PAGE WAS TAKEN FROM CACHE - (the cache was built on ' . date( 'Y/m/d' ) . ') */';
            }
            $cacheFile = self::getCacheName();
            $cacheName = fopen( $cacheFile, 'w+' );
            $rep = fwrite( $cacheName, $writtenBuffer );
            fclose( $cacheName );
        }
        return $buffer;
    }

    public static function getCacheName() {
        $path = self::staticGetPath();
        if( !is_dir( $path ) ) {
            mkdir( $path, 0777, true );
        }
        $file = $path . sh_i18n::getLang() . sh_path::staticGetUnicId() . '.php';

        return $file;
    }

    public function __tostring() {
        return get_class();
    }

}