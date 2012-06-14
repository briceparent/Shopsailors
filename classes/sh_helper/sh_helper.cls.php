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
 * Class that serves all other classes
 */
class sh_helper extends sh_core {

    // 1.1 is the shopsailors version, the rest is the date
    const CLASS_VERSION = '1.1.12.03.12';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    public $events = null;
    protected $needsHelper = false;
    protected $baseConstructed = false;

    const FOLDER_CONTENTS_FILES = 1;                //Every files but the hidden ones
    const FOLDER_CONTENTS_FOLDERS = 2;              //Every folders but the hidden ones
    const FOLDER_CONTENTS_HIDDENFILES = 4;         //Every hidden files
    const FOLDER_CONTENTS_HIDDENFOLDERS = 8;        //Every hidden folders

    const FOLDER_CONTENTS_ALL = 3;                  //Every files and folders, but the hidden ones
    const FOLDER_CONTENTS_FILESWITHHIDDEN = 5;      //Every files
    const FOLDER_CONTENTS_FOLDERSWITHHIDDEN = 10;   //Every folder
    const FOLDER_CONTENTS_ALLWITHHIDDEN = 15;       //Every files and folder

    const FOLDER_CONTENTS_RETURNFILENAME = 1;       //Only returns the files names (like my_file.php)
    const FOLDER_CONTENTS_RETURNFILEPATH = 2;       //Returns the files pathes (like /var/www/my_file.php)

    const TRACE_AS_HTML = 'html';
    const TRACE_AS_ARRAY = 'array';
    const TRACE_AS_TEXT = 'text';

    protected $isAdminPage_value = false;

    /**
     * Constructor
     */
    public function construct() {
        mb_internal_encoding( "UTF-8" );
        mb_http_input( "UTF-8" );
        mb_http_output( "UTF-8" );

        if( !is_array( $_SESSION[ __CLASS__ ][ 'classes_versions' ] ) ) {
            $_SESSION[ __CLASS__ ][ 'classes_versions' ] = array( );
        }

        $installedVersion = $this->getClassInstalledVersion( __CLASS__ );
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->db->updateQueries( __CLASS__ );
            if( version_compare( $installed_version, '1.1.11.03.28', '<' ) ) {
                // We have to create the tables, because the insertion of the version is done at 
                // the same time, so if there isn't a version, it is because the table doesn't exist.
                $this->db_execute( 'classes_installed_version_add_table', array( ) );
                $this->db_execute( 'shared_methods_add_table', array( ) );
            }
            if( version_compare( $installed_version, '1.1.12.03.12', '<' ) ) {
                $this->db_execute( 'create_table_datas_256', array( ) );
            }

            $this->setClassInstalledVersion( __CLASS__, self::CLASS_VERSION );
        }

        return true;
    }

    /**
     * Allows to get the trace without the need of the xdebug extension. The trace may be returned in several formats.
     * @param str $format Any of the sh_helper::TRACE_* constants.<br />
     * <b>self::TRACE_AS_HTML</b> (default) will return an html table<br />
     * <b>self::TRACE_AS_ARRAY</b> will return a php array<br />
     * <b>self::TRACE_AS_TEXT</b> will return a log-ready string<br />
     * @return string The generated trace.
     */
    public function get_trace( $format = self::TRACE_AS_HTML ) {
        $trace = debug_backtrace( false );
        if( $format == self::TRACE_AS_HTML ) {
            $trace_from = $trace[ 0 ];
            unset( $trace[ 0 ] );
            $ret = '<table class="shopsailors_trace">';
            $ret .= '<tr><th>Position</th><th>Method</th></tr>';
            foreach( $trace as $one ) {
                $ret .= '<tr>';
                $ret .= '<td>/' . str_replace( SH_ROOT_FOLDER, '', $one[ 'file' ] ) . ':' . $one[ 'line' ] . '</td>';
                $ret .= '<td>' . $one[ 'class' ] . $one[ 'type' ] . $one[ 'function' ] . '(';
                $ret .= count( $one[ 'args' ] ) . ' args)</td>';
                $ret .= '</tr>';
            }

            $ret .= '<tr><td colspan="2" class="shopsailors_trace_caller">Trace called in /' . str_replace( SH_ROOT_FOLDER,
                                                                                                            '',
                                                                                                            $trace_from[ 'file' ] ) . ':' . $trace_from[ 'line' ] . '</td></tr>';
            $ret .= '</table>';
        } elseif( $format == self::TRACE_AS_ARRAY ) {
            $ret = $trace;
        } else {
            $ret = 'Trace called from ' . str_replace( SH_ROOT_FOLDER, '', $trace[ 0 ][ 'file' ] ) . ':' . $trace[ 0 ][ 'line' ] . "\n";
            unset( $trace[ 0 ] );
            foreach( $trace as $one ) {
                $fileString = str_replace( SH_ROOT_FOLDER, '', $one[ 'file' ] ) . ':' . $one[ 'line' ];
                $ret .= $fileString;
                $ret .= str_repeat( ' ', 60 - strlen( $fileString ) );
                $ret .= $one[ 'class' ] . $one[ 'type' ] . $one[ 'function' ] . '(' . count( $one[ 'args' ] ) . ' args)' . "\n";
            }
        }
        return $ret;
    }

    public function isAdminPage( $value = 'get' ) {
        if( strtolower( $value ) === 'get' ) {
            return $this->isAdminPage_value;
        }
        $this->isAdminPage_value = ( bool ) $value;
        return true;
    }

    /**
     * This method encodes the special chars
     * @param str $source The text to encode
     * @param bool $addslashes Do we have to add slashes?
     * @return str The encoded string
     * @deprecated
     */
    public function encodeSpecialChars( $source, $addslashes = false ) {
        /* $table = array(
          '&#96;'  => chr(96), '&#126;' => chr(126),'&#160;' => chr(160),'&#162;' => chr(162),'&#163;' => chr(163),
          '&#164;' => chr(164),'&#165;' => chr(165),'&#166;' => chr(166),'&#167;' => chr(167),'&#168;' => chr(168),
          '&#169;' => chr(169),'&#170;' => chr(170),'&#171;' => chr(171),'&#172;' => chr(172),'&#173;' => chr(173),
          '&#174;' => chr(174),'&#175;' => chr(175),'&#176;' => chr(176),'&#177;' => chr(177),'&#178;' => chr(178),
          '&#179;' => chr(179),'&#180;' => chr(180),'&#181;' => chr(181),'&#182;' => chr(182),'&#183;' => chr(183),
          '&#184;' => chr(184),'&#185;' => chr(185),'&#186;' => chr(186),'&#187;' => chr(187),'&#188;' => chr(188),
          '&#189;' => chr(189),'&#190;' => chr(190),'&#191;' => chr(191),'&#192;' => chr(192),'&#193;' => chr(193),
          '&#194;' => chr(194),'&#195;' => chr(195),'&#196;' => chr(196),'&#197;' => chr(197),'&#198;' => chr(198),
          '&#199;' => chr(199),'&#200;' => chr(200),'&#201;' => chr(201),'&#202;' => chr(202),'&#203;' => chr(203),
          '&#204;' => chr(204),'&#205;' => chr(205),'&#206;' => chr(206),'&#207;' => chr(207),'&#208;' => chr(208),
          '&#209;' => chr(209),'&#210;' => chr(210),'&#211;' => chr(211),'&#212;' => chr(212),'&#213;' => chr(213),
          '&#214;' => chr(214),'&#215;' => chr(215),'&#216;' => chr(216),'&#217;' => chr(217),'&#218;' => chr(218),
          '&#219;' => chr(219),'&#220;' => chr(220),'&#221;' => chr(221),'&#222;' => chr(222),'&#223;' => chr(223),
          '&#224;' => chr(224),'&#225;' => chr(225),'&#226;' => chr(226),'&#227;' => chr(227),'&#228;' => chr(228),
          '&#229;' => chr(229),'&#230;' => chr(230),'&#231;' => chr(231),'&#232;' => chr(232),'&#233;' => chr(233),
          '&#234;' => chr(234),'&#235;' => chr(235),'&#236;' => chr(236),'&#237;' => chr(237),'&#238;' => chr(238),
          '&#239;' => chr(239),'&#240;' => chr(240),'&#241;' => chr(241),'&#242;' => chr(242),'&#243;' => chr(243),
          '&#244;' => chr(244),'&#245;' => chr(245),'&#246;' => chr(246),'&#247;' => chr(247),'&#248;' => chr(248),
          '&#249;' => chr(249),'&#250;' => chr(250),'&#251;' => chr(251),'&#252;' => chr(252),'&#253;' => chr(253),
          '&#254;' => chr(254),'&#255;' => chr(255),'&#338;' => chr(338),'&#339;' => chr(339),'&#8492;'=>'™' ,
          '\'' => '‘','\''=>'’','"'=>'“','"'=>'”','"'=>'„','...'=>'…','-'=>'–','-'=>'—'
          );
          $source = str_replace(array_values($table),array_keys($table),$source); */
        if( $addslashes ) {
            return addslashes( $source );
        }
        return $source;
    }

    /**
     * This method replaces the special chars with non spécial chars (ie: é→e)
     * @param str $source The text to work on
     * @param bool $replaceDoubleQuotes Do we also have to remove the double quotes by spaces?
     * @return str The string without the special chars.
     */
    public function replaceSpecialChars( $source, $replaceDoubleQuotes = true ) {
        $table = array(
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a',
            'Þ' => 'B', 'þ' => 'b',
            'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c', 'Ç' => 'C', 'ç' => 'c',
            'Đ' => 'Dj', 'đ' => 'dj',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'è' => 'e', 'é' => 'e', 'ê' => 'e',
            'ë' => 'e',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'ì' => 'i', 'í' => 'i', 'î' => 'i',
            'ï' => 'i',
            'Ñ' => 'N', 'ñ' => 'n',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
            'ð' => 'o', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'Ŕ' => 'R', 'ŕ' => 'r',
            'Š' => 'S', 'š' => 's', 'ß' => 'Ss',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'ù' => 'u', 'ú' => 'u', 'û' => 'u',
            'Ý' => 'Y', 'ý' => 'y', 'ý' => 'y', 'ÿ' => 'y',
            'Ž' => 'Z', 'ž' => 'z',
            '\'' => ' '
        );

        if( $replaceDoubleQuotes ) {
            $table[ '"' ] = ' ';
        }

        return trim( strtr( $source, $table ) );
    }

    public function storeData( $class, $data_id, $data ) {
        $this->db_execute( 'store_256', array( 'class' => $class, 'data_id' => addslashes( $data_id ), 'data' => addslashes( $data ) ) );
    }

    public function getData( $class, $data_id, $default = '' ) {
        list($data) = $this->db_execute( 'get_256', array( 'class' => $class, 'data_id' => addslashes( $data_id ) ) );
        if( !empty( $data ) ) {
            return stripslashes( $data[ 'data' ] );
        }
        return $default;
    }

    /**
     * Returns an array containing the classes names that share some functionalities with this class.
     * @param str $class The name of the class which uses other the classes.
     * @param str $type <b>optional</b>, defaults to an empty string. Used if this class shares more than one
     * functionality with the other ones.
     * @return array An array containing the classes that use the functionality $type.<br />
     * It is of the form : <br />
     * array(<br />
     * &#160;&#160;&#160;&#160;0 => 'sh_content',<br />
     * &#160;&#160;&#160;&#160;0 => 'sh_shop'<br />
     * )
     */
    public function getClassesSharedMethods( $class, $type = '' ) {
        $ret = array( );
        $classes = $this->db_execute( 'shared_methods_get', array( 'loader_class' => $class, 'type' => $type ) );
        if( is_array( $classes ) ) {
            foreach( $classes as $class ) {
                $ret[ ] = $class[ 'loaded_class' ];
            }
        }
        return $ret;
    }

    /**
     * This method declares shared method to a class.
     * @param str $loaderClass The name of the class that will use the method.
     * @param str $type The type of the method (a class may use more than one shared method). May be empty.
     * @param str $loadedClass The class that should be called by $loaderClass when needed. Most of
     * the time, it is used with __CLASS__.
     * @return bool Return false if any error occured, anything else for ok.
     */
    public function addClassesSharedMethods( $loaderClass, $type, $loadedClass ) {
        return $this->db_execute(
                'shared_methods_add',
                array( 'loader_class' => $loaderClass, 'type' => $type, 'loaded_class' => $loadedClass )
        );
    }

    /**
     * Removes a shared method for a class.
     * @param str $loaderClass The name of the class that used to use the method.
     * @param str $type The type of the method (a class may use more than one shared method). May be empty.
     * @param str $loadedClass The class that should not be called anymore by $loaderClass when needed. Most of
     * the time, it is used with __CLASS__.
     * @return bool Return false if any error occured, anything else for ok.
     */
    public function removeClassesSharedMethods( $loaderClass, $type, $loadedClass ) {
        $ret = $this->db_execute(
            'shared_methods_delete_one',
            array( 'loader_class' => $loaderClass, 'type' => $type, 'loaded_class' => $loadedClass )
        );
        return $ret;
    }

    /**
     * Gets the version of the class $class that is in use now.
     * @param str $class The class name
     * @return str The number of the version 
     */
    public function getClassInstalledVersion( $class ) {
        if( in_array( $class, $_SESSION[ __CLASS__ ][ 'classes_versions' ] ) ) {
            return $_SESSION[ __CLASS__ ][ 'classes_versions' ][ $class ];
        }
        list($rep) = $this->db_execute( 'installed_version_get', array( 'class' => $class ) );
        $_SESSION[ __CLASS__ ][ 'classes_versions' ][ $class ] = $rep[ 'version' ];
        if( empty( $rep[ 'version' ] ) ) {
            return 0;
        }
        return $rep[ 'version' ];
    }

    /**
     * This method updates the version number of the class $class to $version.
     * @param str $class The class name
     * @param str $version The version number.
     * @return bool Return false if any error occured, anything else for ok. 
     */
    public function setClassInstalledVersion( $class, $version ) {
        $installedVersion = $this->getClassInstalledVersion( $class );
        if( empty( $installedVersion ) ) {
            // We should first add the entry
            $this->db_execute( 'installed_version_add', array( 'class' => $class ) );
        }
        // We update the version
        return $this->db_execute( 'installed_version_update', array( 'class' => $class, 'version' => $version ) );
    }

    /**
     * Lists all the pages that are present in the sitemap file, eventually
     * with a marker on a special page, and excepting the pages that are given
     * using special regular expression
     * @param str $startSelection The page to put the marker in.<br />
     * This marker adds a "checked" value for the state key of the page, and
     * sets "unfolder" to true for the category
     * @param array $exceptions An array of pages. The pages may use * to create
     * patterns.<br />
     * Ex : <br/>
     * array(
     *  'contact/show/', //(the contact page)
     *  'content/showShortList/*', //(every short list)
     *  'shop/* /*', //(without the space, for every page created by sh_shop)
     *  '* /specialMethod/*', //(without the space, any page created by a method
     * named specialMethod)
     * );
     * @return array The pages.<br />
     * Ex : <br />
     * $ret[0][name] => General<br />
     * $ret[0][description] => Général<br />
     * $ret[0][unfolded] => 1<br />
     * $ret[0][elements][0][name] => contact/show/<br />
     * $ret[0][elements][0][value] => Page de contact<br />
     * $ret[0][elements][0][address] => http://dev.websailors.fr/contact/show.php<br />
     * $ret[0][elements][0][state] => checked<br />
     * $ret[0][elements][1][name] => index/show/<br />
     * $ret[0][elements][1][value] => Page d'accueil<br />
     * $ret[0][elements][1][address] => http://dev.websailors.fr/index.php<br />
     * $ret[0][elements][1][state] => <br />
     * $ret[1][name] => content<br />
     * $ret[1][description] => Pages de contenus et listes<br />
     * $ret[1][elements][0][name] => content/show/10<br />
     * $ret[1][elements][0][value] => Article "/content/show/10-Bienvenue...php"<br />
     * $ret[1][elements][0][address] => http://dev.websailors.fr/content/show/10-Bienvenue...php<br />
     * $ret[1][elements][0][state] =><br />
     */
    public function listLinks( $startSelection = '', $exceptions = array( ) ) {
        $addresses = $this->linker->sitemap->getSitemapPagesList();
        if( !empty( $exceptions ) ) {
            $reg = preg_replace(
                array(
                '`(\*\/)`',
                '`(\*)`',
                '`(\/)`'
                ), array(
                '[^/]+/',
                '.*',
                '\/'
                ), implode( '$)|(^', $exceptions )
            );
            $reg = '`(^' . $reg . '$)`';
            $thereAreExceptions = true;
        }
        if( is_array( $addresses[ 'PAGES' ] ) ) {
            foreach( $addresses[ 'PAGES' ] as $page => $address ) {
                if( !$thereAreExceptions || !preg_match( $reg, $page ) ) {
                    $state = '';
                    list($class, $action, $id) = explode( '/', $page );
                    $checked = '';
                    if( $startSelection == $page ) {
                        $state = 'checked';
                    }
                    $value = $this->linker->$class->getPageName( $action, $id );
                    $elements[ $class ][ $action . $id ] = array(
                        'name' => $page,
                        'value' => $value,
                        'address' => $address[ 'address' ],
                        'state' => $state,
                        'uid' => md5( microtime() )
                    );
                }
            }
        }
        $datas = array( );
        $classId = 1;
        if( is_array( $elements ) ) {
            foreach( $elements as $class => $parts ) {
                if( count( $parts ) == 1 ) {
                    $class = 'general';
                    $generalClass = true;
                    $oldClassId = $classId;
                    $classId = 0;
                    $className = $this->getI18n( 'singleEntry_title' );
                } else {
                    $className = $this->linker->i18n->get( $class, 'className' );
                }
                if( $className == '' ) {
                    $className = $class;
                }
                $datas[ $classId ][ 'name' ] = $class;
                $datas[ $classId ][ 'description' ] = $className;
                $partsId = 0;
                if( is_array( $parts ) ) {
                    ksort( $parts );
                    $datas[ $classId ][ 'display' ] = 'none';
                    foreach( $parts as $part ) {
                        if( $part[ 'state' ] == 'checked' ) {
                            $datas[ $classId ][ 'unfolded' ] = true;
                        }
                        $datas[ $classId ][ 'elements' ][ ] = $part;
                        $partsId++;
                    }
                    if( $datas[ $classId ][ 'unfolded' ] == true ) {
                        $datas[ $classId ][ 'display' ] = '';
                    }
                }
                if( $generalClass ) {
                    $classId = $oldClassId;
                    $generalClass = false;
                } else {
                    $classId++;
                }
            }
        }
        return $datas;
    }

    /**
     * Creates a symlink with a relative path (ready to be compressed).
     * @param str $element The absolute path to the file or folder to create the link to.
     * @param str $link The absolute path to the symlink.
     * @return bool returns the return of the symlink() function.
     */
    function create_symLink( $element, $link ) {
        if( substr( $element, -1 ) == '/' ) {
            $element = substr( $element, 0, -1 );
        }
        if( substr( $link, -1 ) == '/' ) {
            $link = substr( $link, 0, -1 );
        }
        $element = str_replace( '//', '/', $element );
        $partselement = explode( '/', $element );
        $elementBaseName = array_pop( $partselement );
        $partsLink = explode( '/', $link );
        $linkBaseName = array_pop( $partsLink );
        $stillRunning = true;
        $relativePath = '';
        for( $a = 1; $a < max( count( $partselement ), count( $partsLink ) ); $a++ ) {
            if( !empty( $partselement[ $a ] ) && !empty( $partsLink[ $a ] ) ) {
                if( $partselement[ $a ] != $partsLink[ $a ] ) {
                    $stillRunning = false;
                    $relativePath = '../' . $relativePath . '/' . $partselement[ $a ] . '/';
                }
            } elseif( !empty( $partselement[ $a ] ) ) {
                if( empty( $relativePath ) ) {
                    $relativePath = '.';
                }
                $relativePath = $relativePath . '/' . $partselement[ $a ] . '/';
            } else {
                $relativePath = '../' . $relativePath . '/';
            }
        }
        $relativePath = str_replace( '//', '/', $relativePath );
        if( empty( $relativePath ) ) {
            $relativePath = './';
        }
        if( !is_dir( dirname( $link ) ) ) {
            $this->createDir( dirname( $link ) );
        }
        return symlink( $relativePath . $elementBaseName, $link );
    }

    /**
     * Lists all the files and/or folders within a folder
     * @param str $folder The folder to look in.
     * @param int $what A constant defining whether we want to list the files (hidden or not) and the folders (hidden or not).
     * See this class' constants for more details.
     * @return array Returns an array listing the elements.
     */
    public function getFolderContents( $folder, $what = self::FOLDER_CONTENTS_ALL,
                                       $return = self::FOLDER_CONTENTS_RETURNFILENAME ) {
        $ret = array( );
        if( is_dir( SH_CLASS_SHARED_FOLDER . $folder ) ) {
            $folder = SH_CLASS_SHARED_FOLDER . $folder;
        }
        if( is_dir( $folder ) ) {
            $folder = realpath( $folder );
            $elements = scandir( $folder );
            foreach( $elements as $element ) {
                $removed = false;
                if( $element != '.' && $element != '..' ) {
                    $hidden = substr( $element, 0, 1 ) == '.';
                    if( is_dir( $folder . '/' . $element ) ) {
                        if( $hidden && !($what & self::FOLDER_CONTENTS_HIDDENFOLDERS) ) {
                            // We should not show the hidden folders
                            $removed = true;
                        } elseif( !$hidden && !($what & self::FOLDER_CONTENTS_FOLDERS) ) {
                            // We should not show the folders that are not hidden
                            $removed = true;
                        }
                    } else {
                        if( $hidden && !($what & self::FOLDER_CONTENTS_HIDDENFILES) ) {
                            // We should not show the files folders
                            $removed = true;
                        } elseif( !$hidden && !($what & self::FOLDER_CONTENTS_FILES) ) {
                            // We should not show the files that are not hidden
                            $removed = true;
                        }
                    }
                } else {
                    $removed = true;
                }
                if( !$removed ) {
                    if( $return == self::FOLDER_CONTENTS_RETURNFILENAME ) {
                        $ret[ ] = $element;
                    } else {
                        $ret[ ] = $folder . '/' . $element;
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * This method tells if the user is an admin or not.
     * @param bool $alsoVerifyIfMaster If set to <b>true</b> (default), will also return true if the 
     * user is not an admin, but a master.
     * @return bool True for yes, false for no.
     */
    public function isAdmin( $alsoVerifyIfMaster = true ) {
        return parent::isAdmin( $alsoVerifyIfMaster );
    }

    /**
     * Removes the shopsailors prefixes from the class $class name
     * @param str $class The class we want the short name from
     * @return str The short class name
     */
    public function getShortClassName( $class ) {
        return preg_replace( '`^(' . SH_PREFIX . '|' . SH_CUSTOM_PREFIX . ')?(.+)`', '$2', $class );
    }

    /**
     * This methods tells what the class name is for the class $class.
     * @param str $class May be a short class name, or already a full class name.
     * @return str|bool The full class name, or false if $class can't be parsed to create a full class name.
     */
    public function getRealClassName( $class ) {
        if( class_exists( SH_PREFIX . $class ) ) {
            return SH_PREFIX . $class;
        } elseif( class_exists( $class ) ) {
            return $class;
        } elseif( class_exists( SH_CUSTOM_PREFIX . $class ) ) {
            return SH_CUSTOM_PREFIX . $class;
        }
        return false;
    }

    /**
     * Writes some text into a file.<br />
     * If the file already exists, it is replaced, unless $appends = true.
     * @param string $file contains the path and name of the file we want to create
     * @param string $content contains the text that we want to write into the file
     * @param int $append
     * If 0 (default), writes into a new file (deletes the old one if it already exists).<br />
     * If 1 (or true), appends the text at the end of the document if it already exists, or creates it.<br />
     * If 2, adds the text at the beginning of the file if it exists, or creates it.
     * @return string This function resturns the return of fwrite
     */
    public static function writeInFile( $file, $content, $append = 0 ) {
        $folder = dirname( $file );
        if( !is_dir( $folder ) ) {
            mkdir( $folder, 0770, true );
        }
        if( $append == 1 && file_exists( $file ) ) {
            $content = file_get_contents( $file ) . $content;
        } elseif( $append == 2 && file_exists( $file ) ) {
            $content .= file_get_contents( $file );
        }
        if( file_exists( $file ) ) {
            unlink( $file );
        }
        $f = fopen( $file, 'w+' );
        //$ret = fwrite($f,"\xEF\xBB\xBF".$content);
        $ret = fwrite( $f, $content );
        fclose( $f );
        return $ret;
    }

    /**
     * Writes the contents of an array into a file so that it can be include()ed.<br />
     * If the file already exists, it is replaced.<br />
     * The file is generated using the function var_export.
     * Can only be called by this class and its dependencies
     * @param string $file contains the path and name of the file we want to create
     * @param string $name contains the name of the variable that will be generated into the file
     * @param array $array is the contents of the file
     * @return string This function resturns the return of writeInFile
     */
    public static function writeArrayInFile( $file, $name, $array, $insertLicense = true ) {
        $content = '<?php' . "\n";
        // inserts the copyrights from the file license_header.php, which is in
        // the same folder that this file.
        if( $insertLicense ) {
            $content .= file_get_contents( dirname( __FILE__ ) . '/license_header.php' );
        }

        $content .= file_get_contents( dirname( __FILE__ ) . '/generated_with_shopsailors.php' );

        $content .= 'if(!defined(\'SH_MARKER\')){' . "\n";
        $content .= '    header(\'location: directCallForbidden.php\');' . "\n";
        $content .= '}' . "\n\n";
        $content .= '$' . $name . ' = ';
        $content .= var_export( $array, true ) . ';';
        return self::writeInFile( $file, $content );
    }

    /**
     * This method creates the directory $path. If needed, it creates the parent directories before.
     * @param str $path The directory to create.
     * @return bool Return true for success, false for failure (most of the time, php is not 
     * can't access the folder for writting
     */
    function createDir( $path ) {
        if( is_dir( $path ) ) {
            return true;
        }
        return mkdir( $path, 0777, true );
    }

    /**
     * Deletes a directory, and all it's contents (recursively)
     * @param string $path The directory's path
     * @return boolean The status of the operation
     */
    function deleteDir( $path ) {
        $rep = true;
        if( is_dir( $path ) ) {
            $dir = scandir( $path );
            foreach( $dir as $file ) {
                if( $file != "." && $file != ".." ) {
                    $fullpath = $path . '/' . $file;
                    if( is_dir( $fullpath ) ) {
                        $rep = $rep && $this->deleteDir( $fullpath );
                    } else {
                        $rep = $rep && unlink( $fullpath );
                    }
                }
            }
            $rep = $rep && rmdir( $path );
        } else {
            $rep = true;
        }
        return $rep;
    }

    /**
     * Deletes all the contents of a directory, but the hidden files
     * @param string $path The directory's path
     * @return boolean The status of the operation
     */
    function emptyDir( $path ) {
        $rep = true;
        if( is_dir( $path ) ) {
            $dir = scandir( $path );
            foreach( $dir as $file ) {
                if( substr( $file, 0, 1 ) != '.' ) {
                    $fullpath = $path . '/' . $file;
                    if( is_dir( $fullpath ) ) {
                        $rep = $rep && $this->deleteDir( $fullpath );
                    } else {
                        $rep = $rep && unlink( $fullpath );
                    }
                }
            }
        } else {
            $rep = true;
        }
        return $rep;
    }

    /**
     * Moves all the contents of a directory into another one
     * @param string $from The directory's path from which to copy
     * @param string $to The directory's path to copy to
     * @return boolean The status of the operation
     */
    function moveDirContent( $from, $to, $alsoHidden = false ) {
        $rep = true;
        if( is_dir( $from ) ) {
            $dir = scandir( $from );
            foreach( $dir as $file ) {
                if( $file != '.' && $file != '..' ) {
                    if( $alsoHidden || substr( $file, 0, 1 ) != '.' ) {
                        $fromFullpath = $from . '/' . $file;
                        $toFullpath = $to . '/' . $file;
                        $rep = $rep && rename( $fromFullpath, $toFullpath );
                    }
                }
            }
        } else {
            $rep = true;
        }
        return $rep;
    }

    /**
     * Simply changes a boolean "true" to "checked" or a "false" to an empty string
     * @param bool $condition The condition to evaluate (in fact, when here, it has already
     * been evaluated...)
     * @return str "checked" if $condition is true, "" if $condition is false
     */
    public function addChecked( $condition ) {
        if( $condition == 1 ) {
            return 'checked';
        }
        return '';
    }

    /**
     * Method that does the same as array_merge_recursive, excepted that if
     * an integer key already exists in $original, it is replaced.
     * @param array $original The array of which we want to add some values
     * @param array $added An array containing the values we want to add to $original
     * @return array The elements that are present in $original, in which we have added
     * those of $added. The keys that were existing in both are now those of $added.
     */
    public function array_merge_recursive_replace( $original, $added ) {
        $ret = $original;

        if( !is_array( $original ) && !is_array( $added ) ) {
            return false;
        }

        if( !is_array( $original ) ) {
            return $added;
        }
        if( !is_array( $added ) ) {
            return $original;
        }

        foreach( $added as $key => $value ) {
            if( is_array( $value ) && is_array( $original[ $key ] ) ) {
                // Both of them being array, we have do recurse
                $thisRet = $this->array_merge_recursive_replace( $value, $original[ $key ] );
                $ret[ $key ] = $thisRet;
            } elseif( is_array( $value ) || $value != $original[ $key ] ) {
                // Adding the array/value
                $ret[ $key ] = $value;
            }
        }
        return $ret;
    }

    /**
     * This method makes as if the array_diff_assoc_recursive was a pre built method.
     * It works as the array_diff_assoc, but does it recursively.
     * @param array $original The array of which we want to remove some values
     * @param array $compared An array containing the values we want to remove from $original
     * @return array The elements that are present in $original, but missing in $compared
     */
    public function array_diff_assoc_recursive( $original, $compared ) {
        $ret = array( );

        if( !is_array( $original ) ) {
            // We can only make test on array
            return false;
        }
        if( !is_array( $compared ) ) {
            // We return the original, bacause nothing is the same...
            return $original;
        }
        foreach( $original as $key => $value ) {
            if( is_array( $value ) && is_array( $compared[ $key ] ) ) {
                // Both of them being array, we have do recurse
                $thisRet = $this->array_diff_assoc_recursive( $value, $compared[ $key ] );
                if( $thisRet !== false ) {
                    // Adding the return of the recursion
                    $ret[ $key ] = $thisRet;
                }
            } elseif( is_array( $value ) || $value != $compared[ $key ] ) {
                // Adding the array/value
                $ret[ $key ] = $value;
            }
        }
        if( $ret == array( ) ) {
            return false;
        }
        return $ret;
    }

    public function __tostring() {
        return __CLASS__;
    }

}

class sh_price {

    protected $taxDetails = array( );
    protected $price = array( );
    protected $taxIncluded = false;
    protected $taxRate = null;
    protected $untaxedPrice = 0;
    protected $taxedPrice = 0;
    protected $taxAmount = 0;
    protected $givenPrice = 0;

    const TAX_MODE_INCLUDED = true;
    const TAX_MODE_EXCLUDE = false;

    /**
     * An array containing all the datas
     */
    const ALL = 'all';
    /**
     * The price with no tax
     */
    const PRICE_UNTAXED = 'untaxed';
    /**
     * The price taxes includes
     */
    const PRICE_TAXED = 'taxed';
    /**
     * The amount of taxes in the price
     */
    const TAX_AMOUNT = 'tax';
    /**
     * The tax rate, from 0 to 1
     */
    const TAX_RATE = 'rate';
    /**
     * true if the taxes are includes, false if not
     */
    const TAX_TYPE = 'type';
    /**
     * The id of the error
     */
    const ERROR = 'error';

    protected $ready = false;
    protected $error = false;
    const ERROR_NONE = false;
    /**
     * This error occurs when the tax rate is not between 0 and 1.
     */
    const ERROR_TAX_NOT_IN_RANGE = 1;
    /**
     * This error occurs if the price is set as untaxed and the tax rate is 100%, because the untaxed can only be 0€
     */
    const ERROR_CANT_BE_BASED_ON_UNTAXED_WHEN_ONLY_TAXES = 2;
    /**
     * This error occurs when we try to calculate something when the calculation can't be done (like missing the taxRate)
     */
    const ERROR_NOT_READY = 3;
    const ERROR_DIVISION_BY_ZERO = 4;
    /**
     * The tax rate can't be calculated (maybe because tax included price equals to zero)<br />
     * The process continues without modifying the tax rate.
     */
    const ERROR_CANT_CALCULATE_TAX_RATE = 5;

    /**
     * Sets the basic datas for a price
     * @param bool $taxeIncluded True if the price $price includes taxes, false if not.
     * @param float $taxRate The tax rate (as a float number, 0.2 for 20% taxes).
     * @param float $price The price, with or without tax, depending on $taxeIncluded.
     */
    public function __construct( $taxeIncluded, $taxRate, $price ) {
        if( $taxRate < 0 || $taxRate > 1 ) {
            $this->error = self::ERROR_TAX_NOT_IN_RANGE;
            return false;
        }
        $this->setTaxIncluded( $taxeIncluded );
        $this->setTaxRate( $taxRate );
        $this->setPrice( $price );

        $this->taxDetails[ $taxRate ] = $this->taxedPrice - $this->untaxedPrice;
    }

    public function get( $what = self::ALL ) {
        if( $this->ready ) {
            if( $what == self::ALL ) {
                return $this->price;
            }
            return $this->price[ $what ];
        }
        $this->error = self::ERROR_NOT_READY;
        return false;
    }

    public function isReady() {
        $this->ready = !is_null( $this->taxRate );
        return $this->ready;
    }

    protected function calculate() {
        if( !$this->isReady() ) {
            $this->error = self::ERROR_NOT_READY;
            return false;
        }
        if( $this->error ) {
            return $this->error;
        }
        if( $this->taxIncluded == self::TAX_MODE_INCLUDED ) {
            $this->untaxedPrice = $this->taxedPrice * (1 - $this->taxRate);
        } elseif( $this->taxRate < 1 ) {
            $this->taxedPrice = $this->untaxedPrice / (1 - $this->taxRate);
        } else {
            $this->error = self::ERROR_CANT_BE_BASED_ON_UNTAXED_WHEN_ONLY_TAXES;
            $this->ready = false;
            return false;
        }
        $this->taxAmount = $this->taxedPrice - $this->untaxedPrice;
        $this->preparePrice();
        return $this->price;
    }

    protected function preparePrice() {
        $this->price = array(
            self::PRICE_UNTAXED => $this->untaxedPrice,
            self::PRICE_TAXED => $this->taxedPrice,
            self::TAX_AMOUNT => ($this->taxedPrice - $this->untaxedPrice),
            self::TAX_RATE => $this->taxRate,
            self::TAX_TYPE => $this->taxIncluded,
            self::ERROR => $this->error
        );
    }

    protected function setPrice( $price ) {
        if( $this->taxIncluded ) {
            $this->taxedPrice = $price;
        } else {
            $this->untaxedPrice = $price;
        }
        return $this->calculate();
    }

    protected function setTaxRate( $taxRate ) {
        $this->taxRate = $taxRate;
        if( $this->taxExcluded && $this->taxRate == 1 ) {
            $this->error = self::ERROR_CANT_BE_BASED_ON_UNTAXED_WHEN_ONLY_TAXES;
            return false;
        }
        return $this->calculate();
    }

    public function setAmounts( $untaxedPrice, $taxedPrice ) {
        $this->untaxedPrice = $untaxedPrice;
        $this->taxedPrice = $taxedPrice;
        if( $taxedPrice == 0 ) {
            $this->error = self::ERROR_CANT_CALCULATE_TAX_RATE;
        } else {
            $this->taxRate = 1 - ($untaxedPrice / $taxedPrice);
        }
        return $this->calculate();
    }

    protected function setTaxIncluded( $status ) {
        $this->taxIncluded = $status;
        if( $this->taxExcluded && $this->taxRate == 1 ) {
            $this->error = self::ERROR_CANT_BE_BASED_ON_UNTAXED_WHEN_ONLY_TAXES;
            return false;
        }
        if( $this->ready ) {
            return $this->calculate();
        }
        return true;
    }

    public function reverse() {
        $this->untaxedPrice = - $this->untaxedPrice;
        $this->taxedPrice = - $this->taxedPrice;
        $this->calculate();
        return $this;
    }

    public function add( sh_price $price ) {
        $newUntaxedPrice = $this->untaxedPrice + $price->get( self::PRICE_UNTAXED );
        $newTaxedPrice = $this->taxedPrice + $price->get( self::PRICE_TAXED );
        $this->setAmounts( $newUntaxedPrice, $newTaxedPrice );

        if( !isset( $this->taxDetails[ $price->get( self::TAX_RATE ) ] ) ) {
            $this->taxDetails[ $price->get( self::TAX_RATE ) ] = 0;
        }
        $this->taxDetails[ $price->get( self::TAX_RATE ) ] += $price->get( self::PRICE_TAXED ) - $price->get( self::PRICE_UNTAXED );
        return $this;
    }

    public function remove( sh_price $price, $allowNegativeReturns = true ) {
        $tmpPrice = clone $price;
        $tmpPrice->reverse();
        $ret = $this->add( $tmpPrice );
        unset( $tmpPrice );
        return $ret;
    }

    public function multiply( $factor ) {
        $newUntaxedPrice = $this->untaxedPrice * $factor;
        $newTaxedPrice = $this->taxedPrice * $factor;
        $this->setAmounts( $newUntaxedPrice, $newTaxedPrice );

        // We should mulpiply by $factor every tax entry
        foreach( $this->taxDetails as $taxRate => $taxAmount ) {
            $this->taxDetails[ $taxRate ] *= $taxAmount;
        }
        return $this;
    }

    public function divide( $number ) {
        if( $number == 0 ) {
            $this->error = self::ERROR_DIVISION_BY_ZERO;
            return false;
        }
        $factor = 1 / $number;
        return $this->multiply( $factor );
    }

}

/*
  class sh_price{
  protected $total = null;

  protected $error = 0;
  const ERROR_NONE = 0;

  public function __construct(sh_price_element $price = null){
  if(!is_null($price)){
  $this->total = $price;
  }
  }

  public function get($what = sh_price_element::ALL){
  if(is_null($this->total)){
  echo 'The object hasn\'t been filled yet...';
  return false;
  }
  return $this->total->get($what);
  }

  public function add(sh_price_element $price){
  if(is_null($total)){
  $this->total = $price;
  return $this->total;
  }
  $newUntaxedPrice = $this->total->get(price::PRICE_UNTAXED) + $price->get(price::PRICE_UNTAXED);
  $newTaxedPrice = $this->total->get(price::PRICE_TAXED) + $price->get(price::PRICE_TAXED);
  $this->total->setAmounts($newUntaxedPrice, $newTaxedPrice);
  return $this->total;
  }

  public function remove(sh_price_element $price,$allowNegativeReturns = true){
  $price->reverse();
  if(is_null($total)){
  $this->total = $price;
  return $this->total;
  }
  return $this->add($price);
  }

  public function reverse(sh_price_element $price){
  return $price->reverse();
  }

  public function multiply( $factor){
  $newUntaxedPrice = $this->total->get(price::PRICE_UNTAXED) * $factor;
  $newTaxedPrice = $this->total->get(price::PRICE_TAXED) * $factor;
  $this->total->setAmounts($newUntaxedPrice, $newTaxedPrice);
  return $this->total;
  }

  public function divide( $number){
  if($number == 0){
  $this->error = self::ERROR_DIVISION_BY_ZERO;
  return false;
  }
  $factor = 1 / $factor;
  return $this->multiply($factor);
  }
  } */