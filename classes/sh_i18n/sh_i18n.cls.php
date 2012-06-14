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
 * Class that manages the translations and internationalisation.
 */
class sh_i18n extends sh_core {

    const CLASS_VERSION = '1.1.11.09.28';

    public $shopsailors_dependencies = array(
        'sh_linker', 'sh_params', 'sh_db'
    );
    protected $minimal = array( 'getSelector' => true );
    protected $translations = array( );
    protected $lang = '';
    protected $forcedValues = '';

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if( $installedVersion != self::CLASS_VERSION ) {
            $this->linker->db->updateQueries( __CLASS__ );

            if( version_compare( $installed_version, '1.1.11.03.28', '<' ) ) {
                $this->db_execute( 'create_table', array( ) );
                $this->helper->addClassesSharedMethods( 'sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__ );
                $this->linker->renderer->add_render_tag( 'render_i18nInput', __CLASS__, 'render_i18nInput' );
                $this->linker->renderer->add_render_tag( 'render_i18nTextarea', __CLASS__, 'render_i18nTextarea' );
                $this->linker->renderer->add_render_tag( 'render_i18nWEditor', __CLASS__, 'render_i18nWEditor' );
            }
            $this->setClassInstalledVersion( self::CLASS_VERSION );
        }

        $this->defaultLang = 'fr_FR';
        $this->setLang();
        $lang = $this->getLang();
        $this->renderer_addConstants(
            array(
            'lang' => $lang,
            'xmlLang' => array_shift( explode( '_', $lang ) )
            ), false
        );

        $this->forcedValues = $this->linker->template->get( __CLASS__, array( ) );
    }

    public function master_getMenuContent() {
        $masterMenu[ 'Section Master' ][ ] = array(
            'link' => 'i18n/checkUnstranslated/', 'text' => 'Traductions', 'icon' => 'picto_tool.png'
        );
        return $masterMenu;
    }

    public function admin_getMenuContent() {
        return array( );
    }

    /**
     * Creates a php file that could be include()ed to restore the contents
     * @param str $class The class name of the elements to export
     * @param str file The php file in which to put the contents
     */
    public function export( $class, $file ) {
        if( !is_dir( dirname( $file ) ) ) {
            $this->helper->createDir( dirname( $file ) );
        }
        $list = $this->db_execute( 'export', array( 'class' => $class ), $qry );
        $f = fopen( $file, 'w+' );
        fwrite( $f, '<?php' . "\n// i18n entries in database for class $class\n" );
        foreach( $list as $element ) {
            fwrite( $f,
                    '$this->setI18n(' . $element[ 'id' ] . ',\'' . addslashes( $element[ 'text' ] ) . '\',\'' . $element[ 'lang' ] . '\');' . "\n" );
        }
        fclose( $f );
    }

    /**
     * Sets the contents for a class
     * @param str $class The class name of the elements to export
     * @param str $file The file in which to red the entries to add
     * @param bool $emptyBeforeAdding If <b>true</b> (default behaviour), all the entries for the
     * class $class will be removed from the i18n database table before the new one are added.<br />
     * If <b>false</b>, the new entries will be added. If some entries are already there, their
     * values will be replaced.
     * @return bool Returns <b>true</b> for success, <b>false</b> for failure (the file was not found).
     */
    public function import( $class, $file, $emptyBeforeAdding = true ) {
        if( !file_exists( $file ) ) {
            return false;
        }

        include($file);
        $list = $this->db_execute( 'export', array( 'class' => $class ), $qry );
        $f = fopen( $file, 'w+' );
        fwrite( $f, '<?php' . "\n// i18n entries in database for class $class\n" );
        foreach( $list as $element ) {
            fwrite( $f,
                    '$this->db_set("' . $class . '",' . $element[ 'id' ] . ',\'' . addslashes( $element[ 'text' ] ) . '\',\'' . $element[ 'lang' ] . '\');' . "\n" );
        }
        fclose( $f );
    }

    public function isEmpty( $array = array( ) ) {
        if( is_array( $array ) ) {
            $atLeastOne = false;
            foreach( $array as $lang => $value ) {
                $atLeastOne = $atLeastOne || (trim( $value ) != '');
                if( $atLeastOne ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * This method shows all the i18n entries from the classes folders that exist in at least
     * on language, but not in everyone.
     */
    public function checkUnstranslated() {
        $this->onlyMaster();
        $classes = scandir( SH_CLASS_FOLDER );
        sort( $classes );
        foreach( $classes as $class ) {
            $languages = array( );
            if( substr( $class, 0, 3 ) == SH_PREFIX ) {
                if( is_dir( SH_CLASS_FOLDER . $class . '/i18n' ) ) {
                    $i18nDir = SH_CLASS_FOLDER . $class . '/i18n/';
                    $langs = scandir( $i18nDir );
                    sort( $langs );
                    foreach( $langs as $lang ) {
                        if( is_dir( $i18nDir . $lang ) && substr( $lang, 0, 1 ) != '.' ) {
                            $languages[ ] = $lang;
                            $files = scandir( $i18nDir . $lang );
                            sort( $files );
                            foreach( $files as $file ) {
                                if( substr( $file, -4 ) == '.php' ) {
                                    include($i18nDir . $lang . '/' . $file);
                                    ksort( $i18n );
                                    foreach( $i18n as $key => $value ) {
                                        $entries[ $class ][ $file ][ $key ][ $lang ] = true;
                                        $entriesValues[ $class . $file . $key ] = $value;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if( is_array( $entries[ $class ] ) ) {
                foreach( $entries[ $class ] as $fileName => $file ) {
                    $echoHr = false;
                    $toTheEnd = '';
                    foreach( $file as $keyName => $key ) {
                        foreach( $languages as $language ) {
                            if( !$key[ $language ] ) {
                                echo '<b>' . $class . '</b> -> <b>' . $fileName . '</b> -> <b>' . $keyName . '</b> is not set in <b>' . $language . '</b><br />';
                                $toTheEnd .= '\'' . $keyName . '\' => \'' . htmlspecialchars( $entriesValues[ $class . $fileName . $keyName ] ) . '\',' . "\n";
                                $echoHr = true;
                            }
                        }
                    }
                    if( $echoHr ) {
                        echo '<textarea style="width:800px;height:200px;">' . $toTheEnd . '</textarea>';
                        echo '<hr />';
                    }
                }
            }
        }
    }

    /**
     * public function getLanguageSelector
     *
     */
    public function getLanguageSelector( $forMobile = false ) {
        $site = $this->linker->site;
        $langs = $site->langs;
        $lang = $this->getLang();
        if( is_array( $langs ) ) {
            foreach( $langs as $oneLang ) {
                if( $lang != $oneLang ) {
                    // We update the args list to create the url
                    $this->linker->path->parsed_url[ 'parsed_query' ][ 'lang' ] = $oneLang;
                    $args = '';
                    $separator = '';
                    foreach( $this->linker->path->parsed_url[ 'parsed_query' ] as $argName => $argValue ) {
                        $args .= $separator . $argName . '=' . $argValue;
                        $separator = '&';
                    }
                    $destPage = $this->linker->path->uri . '?' . $args;
                    $values[ 'language' ][ ] = array(
                        'image' => '/images/shared/flags/' . $oneLang . '.png',
                        'desc' => $this->getParam( 'show_in_' . $oneLang, 'View in ' . $oneLang ),
                        'shortDesc' => $this->get( __CLASS__, 'lang_name', $oneLang ),
                        'link' => $destPage
                    );
                }
            }
            $this->renderer = $this->linker->renderer;
            if( !$forMobile ) {
                return $this->render( 'select_language', $values, false, false );
            } else {
                return $this->render( 'mobile_select_language', $values, false, false );
            }
        }
        return false;
    }

    /**
     * public function getSelectorValues
     *
     */
    public function getSelectorValues( $id ) {
        if( isset( $_SESSION[ __CLASS__ . 'changer' ][ $id ] ) ) {
            return $_SESSION[ __CLASS__ . 'changer' ][ $id ][ 'selected' ];
        }
        return array( );
    }

    /**
     * public function getSelector
     *
     */
    public function getSelector() {
        $this->linker->cache->disable();
        $this->renderer = $this->linker->renderer;
        $id = $_GET[ 'id' ];

        if( $_SESSION[ __CLASS__ . 'changer' ][ $id ][ 'onEnabledOnly' ] ) {
            $site = $this->linker->site;
            $langs = $site->langs;
        } else {
            $availableLanguages = scandir( SH_SHAREDIMAGES_FOLDER . 'flags' );
            $languages = '';
            foreach( $availableLanguages as $oneImage ) {
                list($lang, $ext) = explode( '.', $oneImage );
                if( substr( $lang, -(strlen( '_small' )) ) != '_small' ) {
                    if( substr( $oneImage, 0, 1 ) != '.' && strtolower( $ext ) == 'png' ) {
                        $langs[ ] = $lang;
                    }
                }
            }
        }

        $flagsRoot = SH_SHAREDIMAGES_PATH . 'flags/';

        if( $this->formSubmitted( 'i18n_changer' ) ) {
            $_SESSION[ __CLASS__ . 'changer' ][ $id ][ 'selected' ] = array( );

            foreach( $_POST as $postElement ) {
                if( in_array( $postElement, $langs ) ) {
                    $html .= '<img src="' . $flagsRoot . $postElement . '.png" alt="' . $postElement . '" title="' . $postElement . '"/>';
                    $_SESSION[ __CLASS__ . 'changer' ][ $id ][ 'selected' ][ ] = $postElement;
                }
            }
            $values[ 'response' ][ 'container' ] = $id;
            $values[ 'response' ][ 'content' ] = $html;
            echo $this->render( 'send_values', $values, false, false );
            return true;
        }

        $values[ 'params' ][ 'type' ] = $_SESSION[ __CLASS__ . 'changer' ][ $id ][ 'type' ];
        $values[ 'params' ][ $_SESSION[ __CLASS__ . 'changer' ][ $id ][ 'type' ] ] = true;


        foreach( $langs as $oneLang ) {
            if( in_array( $oneLang, $_SESSION[ __CLASS__ . 'changer' ][ $id ][ 'selected' ] ) ) {
                $state = 'checked';
            } else {
                $state = '';
            }
            if( $values[ 'params' ][ 'type' ] == 'checkbox' ) {
                $inputName = $oneLang;
            } else {
                $inputName = $id . '_value';
                $values[ 'selector' ][ 'inputName' ] = $inputName;
            }
            $values[ 'langs' ][ ] = array(
                'flag' => $flagsRoot . $oneLang . '.png',
                'language' => $oneLang,
                'languageName' => $this->get( __CLASS__, 'lang_' . $oneLang ),
                'state' => $state,
                'inputName' => $inputName
            );
        }

        $values[ 'selector' ][ 'id' ] = $id;
        $values[ 'flags' ][ 'root' ] = $flagsRoot;

        echo $this->render( 'get_selector', $values, false, false );
        return true;
    }

    /**
     * public function createSelector
     *
     */
    public function createDoubleSelector( $id, $enabledLangs, $defaultLang ) {
        $this->renderer = $this->linker->renderer;
        $availableLanguages = scandir( SH_SHAREDIMAGES_FOLDER . 'flags' );
        $languages = '';
        $avLangs = array( );
        foreach( $availableLanguages as $oneImage ) {
            list($lang, $ext) = explode( '.', $oneImage );
            if( substr( $lang, -(strlen( '_small' )) ) != '_small' ) {
                if( substr( $oneImage, 0, 1 ) != '.' && strtolower( $ext ) == 'png' ) {
                    if( in_array( $lang, $enabledLangs ) ) {
                        $state = 'checked';
                        $display = 'auto';
                    } else {
                        $state = '';
                        $display = 'none';
                    }
                    $defaultState = '';
                    if( $lang == $defaultLang ) {
                        $defaultState = 'checked';
                    }
                    $flag = SH_SHAREDIMAGES_PATH . 'flags/' . $lang . '.png';
                    $values[ 'langs' ][ ] = array(
                        'lang' => $lang,
                        'state' => $state,
                        'flag' => $flag,
                        'display' => $display,
                        'languageName' => $this->get( __CLASS__, 'lang_' . $lang ),
                        'defaultState' => $defaultState
                    );
                }
            }
        }
        $values[ 'selector' ][ 'id' ] = $id;
        return $this->render( 'selector', $values, false, false );
    }

    /**
     * public function createSelector
     *
     */
    public function createSelector( $id, $default, $type = 'checkbox', $onEnabledOnly = true ) {
        $this->renderer = $this->linker->renderer;

        $_SESSION[ __CLASS__ . 'changer' ][ $id ][ 'type' ] = $type;
        $_SESSION[ __CLASS__ . 'changer' ][ $id ][ 'onEnabledOnly' ] = $onEnabledOnly;
        $_SESSION[ __CLASS__ . 'changer' ][ $id ][ 'selected' ] = array( );
        if( is_array( $default ) ) {
            foreach( $default as $lang ) {
                $flag = SH_SHAREDIMAGES_PATH . 'flags/' . $lang . '.png';
                $values[ 'langs' ][ ] = array(
                    'flag' => $flag,
                    'language' => $lang,
                    'languageName' => $this->get( __CLASS__, 'lang_' . $lang )
                );
                $_SESSION[ __CLASS__ . 'changer' ][ $id ][ 'selected' ][ ] = $lang;
            }
        }

        $values[ 'action' ][ 'container' ] = $id;
        $values[ 'action' ][ 'langsChanger' ] = 'changeI18nValues(\'' . $id . '\');';

        $this->linker->html->addScript( $this->getSinglePath() . 'changeContent.js' );
        return $this->render( 'viewer', $values, false, false );
    }

    /**
     *
     * @param str $class The name of the class which owns the entry.
     * @param int $id The number of the entry in the class $class.
     * @param str $lang The language of the value (defaults to NULL, which will be replaced by the active
     * language).
     * @param bool|str $qry Present for debug purpose only. If not <b>false</b> (default behavious) and passed
     * by reference, will contain the query that leads to the return.
     * @return str The value that was read into the database.
     */
    public function db_get( $class, $id, $lang = null, $qry = false ) {
        if( is_null( $lang ) ) {
            $lang = $this->getLang();
        }
        if( $lang != $this->linker->site->lang ) {
            $tryThisNext = $this->linker->site->lang;
        }
        if( $lang == '*' ) {
            // We return an array like 'en_EN'=>'Example','fr_FR'=>'Exemple'
            $texts = $this->db_execute( 'get_star', array( 'class' => $class, 'id' => $id ), $qry );
            foreach( $texts as $text ) {
                $ret[ $text[ 'lang' ] ] = $text[ 'text' ];
            }
            return $ret;
        }
        list($text) = $this->db_execute( 'get', array( 'class' => $class, 'id' => $id, 'lang' => $lang ), $qry );

        if( trim( $text[ 'text' ] ) == '' ) {
            if( $tryThisNext ) {
                list($text) = $this->db_execute( 'get', array( 'class' => $class, 'id' => $id, 'lang' => $tryThisNext ),
                                                 $qry );
            }
        }
        return stripslashes( $text[ 'text' ] );
    }

    /**
     * Sets the i18n value $value for the class $class, at the id $id, in the lang $lang.
     * @param str $class The name of the class which owns the entry.
     * @param int $id The number of the entry in the class $class.<br />
     * If <b>0</b>, we add a new entry.
     * @param str $value The value.
     * @param str $lang The language of the value (defaults to NULL, which will be replaced by the active
     * language).
     * @param bool $force If <b>false</b> (default behaviour), if $value is empty, the value will not be added,
     * but the previous value (if any) will be removed (used for secondary languages).<br />
     * If <b>true</b>, the value is added, even if it is an empty string (used for main language).
     * @return int Returns the id of the added entry.
     */
    public function db_set( $class, $id, $value, $lang = NULL, $force = false, $encodeSpecialChars = true ) {
        $args = func_get_args();
        if( $id == 0 ) {
            list($rep) = $this->db_execute( 'getMax', array( 'class' => $class ), $qry );
            $id = $rep[ 'id' ] + 1;
        }
        if( is_null( $lang ) ) {
            $lang = $this->getLang();
        }
        $this->db_execute( 'remove', array( 'class' => $class, 'id' => $id, 'lang' => $lang ), $qry );
        if( !empty( $value ) || $force ) {
            if( $encodeSpecialChars ) {
                $value = $this->helper->encodeSpecialChars( $value, false );
            }
            $this->db_execute( 'set', array( 'class' => $class, 'id' => $id, 'value' => addslashes( $value ), 'lang' => $lang ),
                                                                                                    $qry );
        }
        return $id;
    }

    /**
     * Gets the value of the i18n entry for the $varName entry of the class $className, in the language $lang.
     * @param string $className Name of the class that calls this function
     * @param string $varName Name of the text we want to get in the actual language
     * @return string The text translated
     */
    public function get( $className, $varName, $lang = null ) {
        if( is_null( $lang ) ) {
            $lang = $this->getLang();
        }
        if( is_numeric( $varName ) ) {
            return $this->db_get( $className, $varName, $lang );
        }
        $varName = strtolower( $varName );
        if( $className == '' ) {
            $this->debug( 'The class name was not filled, we can\'t read the values in that case', 0, __LINE__ );
            $className = __CLASS__;
        } elseif( !is_dir( $className ) ) {
            $className = $this->linker->cleanObjectName( $className );
        }
        if( isset( $this->forcedValues[ $className ][ $varName . '|' . $lang ] ) ) {
            return $this->forcedValues[ $className ][ $varName . '|' . $lang ];
        }

        $this->debug( 'We get the value for "' . $varName . '" in the class "' . $className . '"', 2, __LINE__ );
        if( !is_array( $this->translations[ $className ][ $lang ] ) ) {
            $this->readLangFile( $className, $lang );
        }
        if( isset( $this->translations[ $className ][ $lang ][ $varName ] ) ) {
            $this->debug( 'This value is "' . $this->translations[ $className ][ $lang ][ $varName ] . '"', 2, __LINE__ );
            return $this->translations[ $className ][ $lang ][ $varName ];
        }
        if( $className != __CLASS__ ) {
            $ret = $this->get( __CLASS__, $varName, $lang );
            if( $ret !== false ) {
                return $ret;
            }
        }
        $classPath = $this->linker->$className->getClassFolder();
        if( file_exists( $classPath . 'i18n/default.php' ) ) {
            include( $classPath . 'i18n/default.php');
            if( $defaultLang != $lang ) {
                return $this->get( $className, $varName, $defaultLang );
            }
        }

        return false;
    }

    /**
     *
     * @param str $class The class that owns the entry.
     * @param str $id The id of the entry.
     * @param str|array $value If <b>string</b>, the value to store.<br />
     * If <b>array</b>, in keys, the languages, and in values, the values to store.
     * @param str $lang The language of the entry, or <b>"all"</b> (default behaviour) for every language.
     * @param bool $force If <b>false</b> (default behaviour), if $value is empty, the value will not be added,
     * but the previous value (if any) will be removed (used for secondary languages).<br />
     * If <b>true</b>, the value is added, even if it is an empty string (used for main language).
     * @return int Returns <b>id</b> for success, <b>false</b> for failure.
     */
    public function set( $class, $id, $value, $lang = null, $force = false, $encodeSpecialChars = true ) {
        if( is_null( $lang ) || empty( $lang ) ) {
            $lang = $this->getLang();
        }
        if( is_numeric( $id ) ) {
            // We set the value in the database
            if( is_array( $value ) ) {
                $old = $this->get( $class, $id, $this->linker->site->lang );
                foreach( $value as $entryName => $entryValue ) {
                    $forceIt = $force;
                    if( $entryName == $this->linker->site->lang ) {
                        // If it is the default language, we have to write a
                        // value, even if it is an empty string
                        $forceIt = true;
                    }
                    if( $entryValue == $value[ $this->linker->site->lang ] || $entryValue == $old ) {
                        if( $entryName != $this->linker->site->lang ) {
                            $entryValue = '';
                        }
                    }

                    $ret = $this->db_set( $class, $id, $entryValue, $entryName, $forceIt, $encodeSpecialChars );
                    if( $id == 0 ) {
                        $id = $ret;
                    }
                }
                return $ret;
            } else {
                return $this->db_set( $class, $id, $value, $lang, $force, $encodeSpecialChars );
            }
        }
        // This method cannot write into the i18n files, but only into the i18n table of the database
        return false;
    }

    /**
     * Removes an entry from the i18n database.
     * @param str $class The class that owns the entry.
     * @param str $id The id of the entry.
     * @param str $lang The language of the entry, or <b>"all"</b> (default behaviour) for every language.
     * @return bool Return <b>true</b> for success, <b>false</b> for failure.
     */
    public function remove( $class, $id, $lang = 'all' ) {
        if( is_numeric( $id ) ) {
            if( is_null( $lang ) || $lang == 'all' ) {
                $this->db_execute( 'removeAll', array( 'id' => $id, 'class' => $class ), $qry );
                return true;
            }
            $this->db_execute( 'remove', array( 'id' => $id, 'class' => $class, 'lang' => $lang ) );
            return true;
        }
        // This method cannot write into the i18n files, but only into the i18n table of the database
        return false;
    }

    /**
     * Sets the active language.
     * @return bool Always returns true.
     */
    public function setLang() {
        $site = $this->linker->site;
        $langs = $site->langs;

        if( isset( $_GET[ 'lang' ] ) ) {
            $lang = $_GET[ 'lang' ];
        } elseif( !empty( $_SESSION[ __CLASS__ ][ 'lang' ] ) ) {
            $lang = $_SESSION[ __CLASS__ ][ 'lang' ];
        } else {
            $lang = $site->lang;
        }
        if( !is_array( $langs ) || !in_array( $lang, $langs ) ) {
            $lang = $site->lang;
        }

        $_SESSION[ __CLASS__ ][ 'lang' ] = $lang;
        return true;
    }

    /**
     * protected function readLangFile
     *
     */
    protected function readLangFile( $class, $lang = null ) {
        $this->debug( 'We look for the translation files for the class "' . $class . '"', 2, __LINE__ );
        $classPath = $this->linker->$class->getClassFolder();
        if( is_null( $lang ) ) {
            $lang = $this->getLang();
            if( !is_dir( $classPath ) && !is_dir( $class ) ) {
                $this->debug( 'The folder that should contain the lang files for class "' . $class . '" was not found',
                              0, __LINE__ );
                return false;
            }
        }
        if( is_dir( $class ) ) {
            $folder = $class . '/' . $lang;
        } else {
            $folder = $classPath . '/i18n/' . $lang;
        }
        $this->debug( 'Looking for "' . $folder . '" ', 1, __LINE__ );
        if( !is_dir( $folder ) ) {
            $this->debug( 'The "' . $class . '" class has no translation files for ' . $lang, 1, __LINE__ );
            return false;
        }
        $scan = scandir( $folder );
        if( !is_array( $scan ) ) {
            $this->debug( 'The folder "' . $folder . '/' . $element . '" does not exist', 1, __LINE__ );
            return false;
        }
        $temp = array( );
        foreach( $scan as $element ) {
            if( substr( $element, 0, 1 ) != '.' && file_exists( $folder . '/' . $element ) ) {
                include($folder . '/' . $element);
                $this->debug( 'We add the file "' . $folder . '/' . $element . '"', 2, __LINE__ );
                if( is_array( $i18n ) ) {
                    $temp = array_merge( $temp, $i18n );
                }
            }
        }
        $entries = array_change_key_case( $temp );

        $this->translations[ $class ][ $lang ] = $entries;
        return true;
    }

    public function render_i18nInput( $attributes = array( ) ) {
        if( !isset( $attributes[ 'class' ] ) ) {
            return false;
        }
        if( !isset( $attributes[ 'i18n' ] ) || !isset( $attributes[ 'name' ] ) ) {
            return false;
        }
        $this->linker->html->addScript( $this->getSinglePath() . 'editorLangChooser.js' );
        $class = $attributes[ 'class' ];
        $i18n = $attributes[ 'i18n' ];
        $name = $attributes[ 'name' ];

        if( isset( $attributes[ 'width' ] ) ) {
            $width = 'width:' . $attributes[ 'width' ] . ';';
        }

        $id = 'i18n_' . substr( md5( microtime() ), 0, 8 );

        $langs = $this->linker->site->langs;

        if( is_array( $langs ) && count( $langs ) > 0 ) {
            foreach( $langs as $oneLang ) {
                // We get the value, replacing " with its equivalent char code
                $value = str_replace( '"', '&#34;', $this->db_get( $class, $i18n, $oneLang, &$qry ) );
                $thisArgs = ' name="' . $name . '[' . $oneLang . ']" value="' . $value . '"';
                $thisArgs .= ' style="background:#ffffff url(/images/shared/flags/' . $oneLang . '_small.png) no-repeat top left;padding-left:20px;' . $width . '"';
                $thisArgs .= ' class="' . $id . ' form_i18n_input"';
                $values[ 'langs' ][ ] = array(
                    'name' => $oneLang,
                    'id' => $id . '_' . $oneLang,
                    'args' => $thisArgs . ' id="' . $id . '_' . $oneLang . '"'
                );
            }
        }
        $values[ 'lang' ][ 'firstDisplayed' ] = $id . '_fr_FR';
        $values[ 'lang' ][ 'id' ] = $id;

        $ret = $this->render( 'input', $values, false, false );
        return $ret;
    }

    public function render_i18nTextarea( $attributes = array( ) ) {
        if( !isset( $attributes[ 'class' ] ) || !isset( $attributes[ 'class' ] ) ) {
            return false;
        }
        if( !isset( $attributes[ 'i18n' ] ) || !isset( $attributes[ 'name' ] ) ) {
            return false;
        }
        if( isset( $attributes[ 'type' ] ) && !empty( $attributes[ 'type' ] ) ) {
            $type = ' type="' . $attributes[ 'type' ] . '"';
        }
        $this->linker->html->addScript( $this->getSinglePath() . 'editorLangChooser.js' );
        $classes = explode( ' ', $attributes[ 'class' ] );
        $class = array_shift( $classes );
        $classes = ' ' . implode( $classes );
        $id = 'i18n_' . substr( md5( microtime() ), 0, 8 );
        $i18n = $attributes[ 'i18n' ];
        $name = $attributes[ 'name' ];

        $langs = $this->linker->site->langs;
        if( is_array( $langs ) && count( $langs ) > 0 ) {
            foreach( $langs as $oneLang ) {
                $value = $this->db_get( $class, $i18n, $oneLang );
                $thisArgs = $type . ' name="' . $name . '[' . $oneLang . ']"';
                $thisArgs .= ' style="background:#ffffff url(/images/shared/flags/' . $oneLang . '_small.png) no-repeat top left;padding-left:20px;"';
                $thisArgs .= ' class="' . $id . ' form_i18n_textarea' . $classes . '" id="' . $id . '_' . $oneLang . '"';
                $values[ 'langs' ][ ] = array(
                    'name' => $oneLang,
                    'id' => $id . '_' . $oneLang,
                    'args' => $thisArgs,
                    'value' => $value
                );
            }
        }
        $values[ 'lang' ][ 'firstDisplayed' ] = $id . '_fr_FR';
        $values[ 'lang' ][ 'id' ] = $id;

        $ret = $this->render( 'textarea', $values, false, false );
        return $ret;
    }

    public function render_i18nWEditor( $attributes = array( ) ) {
        if( !isset( $attributes[ 'i18nClass' ] ) ) {
            echo 'Need an i18nClass argument!<br />';
            return false;
        }
        if( !isset( $attributes[ 'i18n' ] ) || !isset( $attributes[ 'name' ] ) ) {
            echo 'Need an i18n or a name argument!<br />';
            return false;
        }
        if( !isset( $attributes[ 'type' ] ) ) {
            $type = sh_wEditor::DEFAULT_TYPE;
        } else {
            $type = $attributes[ 'type' ];
        }
        $this->linker->html->addScript( $this->getSinglePath() . 'editorLangChooser.js' );
        $class = $attributes[ 'class' ];
        $i18nClass = $attributes[ 'i18nClass' ];
        $i18n = $attributes[ 'i18n' ];
        $baseName = $attributes[ 'name' ];

        $id = 'i18n_' . substr( md5( microtime() ), 0, 8 );

        $langs = $this->linker->site->langs;

        if( is_array( $langs ) && count( $langs ) > 0 ) {
            foreach( $langs as $oneLang ) {
                $value = $this->db_get( $i18nClass, $i18n, $oneLang );
                $values[ 'langs' ][ ] = array(
                    'class' => $class,
                    'i18nClass' => $i18nClass,
                    'content' => $value,
                    'name' => $baseName . '[' . $oneLang . ']',
                    'langName' => $oneLang,
                    'id' => $id . '_' . $oneLang
                );
            }
        }
        $values[ 'lang' ][ 'firstDisplayed' ] = $id . '_fr_FR';
        $values[ 'lang' ][ 'id' ] = $id;
        $values[ 'lang' ][ 'type' ] = $type;
        $ret = $this->render( 'wEditor', $values, false, false );
        return $ret;
    }

    /**
     * protected function lowerizeKeys
     *
     */
    protected function lowerizeKeys( $array ) {
        
    }

    /**
     * public function getLang
     *
     */
    public function getLang() {
        return $_SESSION[ __CLASS__ ][ 'lang' ];
    }

    /**
     * public function getDefaultLang
     *
     */
    public function getDefaultLang() {
        return $this->linker->site->lang;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        list($class, $method, $id) = explode( '/', $page );
        if( $method == 'checkUnstranslated' ) {
            return '/' . $this->shortClassName . '/checkUnstranslated.php';
        }
        if( $method == 'getSelector' ) {
            return '/' . $this->shortClassName . '/getSelector.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( $uri == '/' . $this->shortClassName . '/checkUnstranslated.php' ) {
            return $this->shortClassName . '/checkUnstranslated/';
        }
        if( $uri == '/' . $this->shortClassName . '/getSelector.php' ) {
            return $this->shortClassName . '/getSelector/';
        }
        return false;
    }

    public function __tostring() {
        return get_class();
    }

}
