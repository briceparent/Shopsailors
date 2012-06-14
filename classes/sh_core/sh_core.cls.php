<?php

/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version 1.09.153.1
 * @package Shopsailors Core Classes
 */
if( !defined( 'SH_MARKER' ) )
    header( 'location: directCallForbidden.php' );

/**
 * @abstract Class to be extended by most of the classes in the Shopsailors' engine.
 *
 */
abstract class sh_core {

    const CLASS_VERSION = '1.1';
    protected static $usesRightsManagement = false;

    /**
     * This var is an array listing the methods names for which access should be granted.<br />
     * Only usefull if $usesRightsManagement is set to true.
     * @var array(str,str,...)
     */
    protected $rights_methods = array( );

    /**
     * This array lists the different methods that share a common id (for the methods that
     * should be callWithId).<br />
     * They should be written in the order we want them to appear (like access to the page before edition).<br />
     * It helps the sh_rights class to order them by contents, and not only by action.<br />
     * Ex : <br />
     * // The class myClass has 2 types of contents : Texts, and categories of texts. Both of them may be shown
     * separately
     * $rights_methods = array(<br />
     * &nbsp;&nbsp;array('showText','editText','deleteText','getAsPDF'),<br />
     * &nbsp;&nbsp;array('showCategory','editCategory')<br />
     * );<br /><br />
     * Only usefull if $usesRightsManagement is set to true.
     * @var array(array(str,str,...),...)
     */
    protected $rights_shared_ids = array( );

    /**
     * Lists the classes that should be buit before this one.
     * Only usefull for sh_* classes.
     * @var array
     */
    public $shopsailors_dependencies = array( );

    /**
     * The linker object, which gives access to all other classes.
     * @var sh_linker
     */
    protected $linker = null;

    /**
     * The helper object, which gives access to some common methods.
     * @var sh_helper
     */
    protected $helper = null;

    /**
     * This class' debugger.
     * @var sh_debugger
     */
    protected $debugger = null;
    protected static $form_verifier = null;
    protected static $needs_params = true;
    protected static $needs_db = true;
    protected static $needs_form_verifier = true;
    protected $isCustomClass = false;
    protected $classFolder = '';
    private $paramsFile = '';
    private $paramsBase = '';
    private $i18nClassName = '';
    static $instances = array( );

    /**
     * An array containing all this class' methods that render content directly, without the use
     * of the sh_html class.<br />
     * It is of the form :<br />
     * array('sh_class_1' => true, 'sh_class_2' => true)<br />
     * There is no need to mark the ones that are false, (like ''sh_class_3'=>false), as it is
     * the default behaviour.
     * @var array(str=>bool,str=>bool,...)
     */
    protected $minimal = array( );

    /**
     * The class name, without the sh_ or cm_ prefix.
     * @var str
     */
    protected $shortClassName = '';

    /**
     * The class name with the sh_ or cm_ prefix.
     * @var str
     */
    protected $className = '';

    /**
     * An array containing the constants that may be used by the render using constant:[constantName].<br />
     * It is of the form :<br />
     * array('constant_1' => 'Value 1', 'constant_2' => 'Value 2')
     * @var array(str=>str,str=>str,...)
     */
    public $renderingConstants = array( );

    /**
     * Contains the methods names than may be call directly form urls like the following :<br />
     * /[short_class_name]/[method_name].php<br />
     * For the ones of the type /[short_class_name]/[method_name]/[id]-[description].php, see
     * $callWithId.
     * @var array(str,str,...)
     */
    public $callWithoutId = array( );

    /**
     * Contains the methods names than may be call directly form urls like the following :<br />
     * /[short_class_name]/[method_name]/[id]-[description].php<br />
     * For the ones of the type /[short_class_name]/[method_name].php, see $callWithoutId.
     * @var array(str,str,...)
     */
    public $callWithId = array( );

    /**
     * Constructor
     * @param str $class The name of the class
     */
    private final function __construct( $class ) {
        $this->linker = sh_linker::getInstance();
        self::$instances[ $class ] = $this;
        $className = $this->__tostring();
        $this->className = $className;
        if( substr( $className, 0, strlen( SH_PREFIX ) ) != SH_PREFIX ) {
            $this->isCustomClass = true;
            $this->shortClassName = str_replace( SH_CUSTOM_PREFIX, '', $className );
        } else {
            $this->shortClassName = str_replace( SH_PREFIX, '', $className );
        }

        if( $this->isCustomClass ) {
            $this->classFolder = SH_CUSTOM_CLASS_FOLDER . $this->__tostring() . '/';
        } else {
            $this->classFolder = SH_CLASS_FOLDER . $this->__tostring() . '/';
        }

        if( isset( $_SESSION[ 'core' ][ $this->className ][ 'version' ] ) ) {
            // We check if an update has been made
            if( $_SESSION[ 'core' ][ $this->className ][ 'version' ] != $this::CLASS_VERSION ) {
                $needUpdate = true;
            }
        }
        if( $this->shortClassName != 'helper' ) {
            $this->helper = $this->linker->helper;
        }

        $this->linker->$className = $this;
        if( $className::$needs_params ) {
            $this->paramsFile = $className;
            $this->i18nClassName = $className;
            $this->linker->params->addElement( $this->paramsFile );
        }
        if( $className::$needs_db ) {
            $this->linker->db->addElement( $className );
            if( $needUpdate ) {
                $this->linker->db->updateQueries( $this->className );
            }
        }
        if( is_null( self::$form_verifier ) && $class::$needs_form_verifier ) {
            self::$form_verifier = new sh_form_verifier();
        }
        $this->debugger = new sh_debugger;
        $_SESSION[ 'core' ][ $this->className ][ 'version' ] = $this::CLASS_VERSION;
    }

    /**
     * Gets the class' folder (like /[path_to_shopsailors]/classes/[classname]/)
     * @return str The class folder
     */
    public function getClassFolder() {
        return $this->classFolder;
    }

    /**
     * This method allows the class to cache a part of content, especially if this part takes time to be 
     * generated.<br />
     * The part is defined by a name ($part) and a lang ($lang).<br /><br />
     * The cached part may be retrieved using cachedPart_get() and removed using cachedPart_remove().
     * @param str $content The content to cache
     * @param str $part The name of the part, like "menu_1_page_content_8" (the menu would be stored as it appears on content/show/8).<br />
     * Defaults to "CURRENT_PAGE", which is replaced by the page name (like content/show/8).
     * @param str $lang The lang used to generate this content.<br />
     * Defaults to sh_cache::LANGS_CURRENT, which will be replaced by the lang the user has selected.<br />
     * If the part is the same for every language, be sure to set the $lang to the same value, whatever the selected
     * language is (it will save caching time and space in the db).
     * @return bool Returns the return of cache->part_cache() with these arguments.
     */
    protected function cachedPart_cache( $content, $part='CURRENT_PAGE', $lang = sh_cache::LANGS_CURRENT ) {
        if( $part == 'CURRENT_PAGE' || !is_string( $part ) ) {
            $part = $this->linker->path->getPage();
        }
        if( $lang == sh_cache::LANGS_CURRENT || !is_string( $lang ) ) {
            $lang = $this->linker->i18n->getLang();
        }

        return $this->linker->cache->part_cache( $this->className, $part, $content, $lang );
    }

    /**
     * This method allows the class to get a part of content that has been cached through cachedPart_cache().<br />
     * The part is defined by a name ($part) and a lang ($lang).<br /><br />
     * The cached part may be removed using cachedPart_remove().
     * @param str $part The name of the part, like "menu_1_page_content_8" (the menu would be stored as it appears on content/show/8).<br />
     * Defaults to "CURRENT_PAGE", which is replaced by the page name (like content/show/8).
     * @param str $lang The lang in which we want this content.<br />
     * Defaults to sh_cache::LANGS_CURRENT, which will be replaced by the lang the user has selected.<br />
     * If the part is the same for every language, be sure to set the $lang to the same value, whatever the selected
     * language is (it will save caching time and space in the db).
     * @return bool|str Returns the return of cache->part_get() with these arguments.<br />
     * <i>As of sh_cache in its 1.1.11.08.30 version :<br />
     * If the content doesn't exist in the cache db, it will return false, in order to let the script create
     * the content and generate the cache for next time.<br />
     * If the content is found, it will be returned as a string.
     * </i>
     */
    protected function cachedPart_get( $part = 'CURRENT_PAGE', $lang = sh_cache::LANGS_CURRENT ) {
        if( $part == 'CURRENT_PAGE' || !is_string( $part ) ) {
            $part = $this->linker->path->getPage();
        }
        if( $lang == sh_cache::LANGS_CURRENT || !is_string( $lang ) ) {
            $lang = $this->linker->i18n->getLang();
        }

        return $this->linker->cache->part_get( $this->className, $part, $lang );
    }

    /**
     * This method allows the class to remove a part of content that has been cached through cachedPart_cache().<br />
     * The part is defined by a name ($part) and a (set of) lang ($lang).<br /><br />
     * The cached part may be got using cachedPart_get().
     * @param str $part The name of the part, like "menu_1_page_content_8" (the menu would be stored as it appears on content/show/8),
     * or as a Mysql REGEXP. Notice that a "^" is prepended to the string, so the first characters must match 
     * (so you may use "menu_1_" for everything starting with that string, or "menu_[0-9]+_page_content_.+" for eavery contents<br />
     * Defaults to "CURRENT_PAGE", which is replaced by the page name (like content/show/8).<br /><br />
     * @param str|array $lang The lang in which we want this content.<br />
     * Defaults to sh_cache::LANGS_ALL, which will remove every contents, whatever the language is.
     * @return bool Returns the return of cache->cachedPart_remove() with these arguments.<br />
     * <i>As of sh_cache in its 1.1.11.08.30 version :<br />
     * <b>true</b> for success<br />
     * <b>false</b> for failure</b>
     * </i>
     */
    protected function cachedPart_remove( $part = 'CURRENT_PAGE', $lang=sh_cache::LANGS_ALL ) {
        if( $part == 'CURRENT_PAGE' || !is_string( $part ) ) {
            $part = $this->linker->path->getPage();
        }
        if( $lang == sh_cache::LANGS_CURRENT ) {
            $langs = array( $this->linker->i18n->getLang() );
        } elseif( is_string( $lang ) && $lang != sh_cache::LANGS_ALL ) {
            $langs = array( $lang );
        } else {
            $langs = $lang;
        }

        return $this->linker->cache->part_remove( $this->className, $part, $langs );
    }

    /**
     * Returns an array containing the classes names that share some functionalities with this class.
     * @param str $type <b>optional</b>, defaults to an empty string. Used if this class shares more than one
     * functionality with the other ones.
     * @return array An array containing the classes that use the functionality $type.<br />
     * It is of the form : <br />
     * array(<br />
     * &#160;&#160;&#160;&#160;0 => array(<br />
     * &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;'long' => 'sh_content',<br />
     * &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;'short' => 'content'<br />
     * &#160;&#160;&#160;&#160;),<br />
     * &#160;&#160;&#160;&#160;1 => array(<br />
     * &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;'long' => 'sh_shop',<br />
     * &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;'short' => 'shop'<br />
     * &#160;&#160;&#160;&#160;)<br />
     * )
     * @uses sh_helper::getClassesSharedMethods
     */
    public function get_shared_methods( $type = '' ) {
        $class = $this->className;
        $ret = $this->helper->getClassesSharedMethods( $class, $type );
        return $ret;
    }

    public function getClassInstalledVersion( $class = null ) {
        if( is_null( $class ) ) {
            $class = $this->className;
        }
        return $this->helper->getClassInstalledVersion( $class );
    }

    public function setClassInstalledVersion( $version ) {
        return $this->helper->setClassInstalledVersion( $this->className, $version );
    }

    /**
     * Saves the datas from the RENDER_CREDENTIALS form.
     * @param int $newId If <b>null</b> (default behaviour), will use the id that were given to the
     * RENDER_CREDENTIALS tag.<br />
     * Else, the id will be used to create save the page, but only if the id that was given to
     * RENDER_CREDENTIALS was 0. With anything else, the id that was passed will be kept.<br />
     * This is usefull to save the credentials for a newly generated page, which should have $id=0.
     */
    public function saveCredentials( $newId = null ) {
        $this->linker->rights->render_credentials_save( $newId );
    }

    /**
     * This method tells if the class uses the sh_rights class to manage the users rights.
     * @return bool Returns <b>true</b> if it does, <b>false</b> if not.
     */
    public static function isUsingRightsManagement() {
        return static::$usesRightsManagement;
    }

    /**
     * This methods checks if the user is allowed to access a page from this class, and if not,
     * may redirect to a 403 error page.
     * @param str $method The method to check.
     * @param int|str $id The <b>id</b> of the page, for the pages that are called with one, or an empty string
     * for those which are called without.<br />
     * Defaults to an empty string.
     * @param int $right The right to test. Should be either <b>sh_rights::RIGHT_READ</b> or <b>sh_rights::RIGHT_WRITE</b>.<br />
     * Defaults to sh_rights::RIGHT_READ.
     * @param bool $redirectTo403 <b>True</b> (default behaviour) to redirect to a 403 error page if the rights are not enough.<br />
     * <b>false</b> to return a boolean.
     * @return bool If the user is allowed to access the page, returns <b>true</b>, else, depending on $redirectTo403,
     * the function may return <b>false</b> or redirect immediately to the 403 error page.
     */
    protected function rights_check( $method, $id = '', $user = null, $right = sh_rights::RIGHT_READ,
                                     $redirectTo403 = true ) {
        $page = $this->className . '/' . $method . '/' . $id;
        $rights = $this->linker->rights->getUserRights( $user, $page );
        if( $rights & $right ) {
            return true;
        }

        return false;
    }

    protected function rights_setForUser( $method, $id, $rights, $user=null ) {
        if( is_null( $id ) ) {
            $id = '';
        }
        $page = $this->className . '/' . $method . '/' . $id;
        return $this->linker->rights->setUserRights( $page, $rights, $user );
    }

    protected function rights_setForGroup( $method, $id, $rights, $groups=null ) {
        if( is_null( $id ) ) {
            $id = '';
        }
        $page = $this->className . '/' . $method . '/' . $id;
        if( empty( $groups ) ) {
            // We get all the groups the user belongs to.
            $groups = $this->linker->rights->getUserGroups();
        }
        if( !is_array( $groups ) ) {
            $groups = array( $groups );
        }
        $ret = true;
        foreach( $groups as $group ) {
            $ret = $this->linker->rights->setGroupRights( $page, $rights, $group ) && $ret;
        }
        return $ret;
    }

    /**
     * Allows the class to prepare everything on the installation of a master server.
     * Be carefull that a master server will have both shopsailors_installMasterServer
     * and shopsailors_install be called for every class, in this order.
     */
    public function shopsailors_installMasterServer() {
        
    }

    /**
     * Allows a class to install itself and all the files it needs, in every folder it needs to.
     * (like images for example)
     */
    public function shopsailors_install() {
        
    }

    /**
     * Allows a class to create all the files it needs for a new site, in the site's folder
     * (like params files, images, folders, etc).
     * @param str $siteName The name of the site, which will be the folder name in [ROOT]/sites/ .
     */
    public function shopsailors_addSite( $siteName ) {
        
    }

    /**
     * Allows a class to do what it needs to backup everything for a site, in a way that it may be
     * restored using shopsailors_restoreSite().
     * @param str $siteName The name of the site, which will be the folder name in [ROOT]/sites/ .
     * @param str $pathToBeZipped The path in which to put the contents (like database dumps, config files,
     * etc) to store them. <br /><br />
     * <b>Caution</b>That folder will be zipped, so no symlinks should be in there, and the user's
     * rights may be changed.
     */
    public function shopsailors_backupSite( $siteName, $pathToBeZipped ) {
        
    }

    /**
     * Allows a class to do what it needs to restore everything for a site, from a folder that was
     * filled using shopsailors_restoreSite().
     * @param str $siteName The name of the site, which will be the folder name in [ROOT]/sites/ .
     * @param str $unzippedPath The path in which are the contents (like database dumps, config files,
     * etc) to be restored them.
     */
    public function shopsailors_restoreSite( $siteName, $unzippedPath ) {
        
    }

    /**
     * Adds an entry in the searcher's database, in order to allow the contents to
     * be found.
     * @uses sh_searcher::addEntry()
     * @param int $id The id of the element to return in search results
     * @param str $level_1 The contents of the most important words or sentences
     * (like h1, title, etc). The number of entries found is multiplied by 10
     * to create the results order.
     * @param str $level_2 Some contents that are not as important as the
     * previous one, but that are more important than the bigger part of the page
     * (like h2, h3).The number of entries found is multiplied by 3 to create
     * the results order.
     * @param str $level_3 The contents itself. The number of entries found is
     * just added (multiplied by 1).
     * @return The return of sh_searcher::addEntry
     */
    protected function search_addEntry( $method, $id, $level_1='', $level_2='', $level_3='' ) {
        return $this->linker->searcher->addEntry(
                $this->shortClassName, $method, $id, $level_1, $level_2, $level_3
        );
    }

    /**
     * Removes an entry from the searcher's database.
     * @uses sh_searcher::removeEntry()
     * @param int $id The id of the entry we want to remove
     * @return The return of sh_searcher::removeEntry
     */
    protected function search_removeEntry( $method, $id = 0, $language = sh_searcher::ALL_LANGUAGES ) {
        return $this->linker->searcher->removeEntry(
                $this->shortClassName, $method, $id, $language
        );
    }

    /**
     * This function only gives the class name.<br />
     * It is usefull to know which kind of class is sent when calling
     * $this->linker->[some_short_class_name]->getClassName().<br />
     * This way, it will permit to extend and replace original classes by others.<br />
     * After php 5.3, will be even more usefull because will help the calling of
     * class constants this way:<br />
     * $this->linker->[some_short_class_name]->getClassName()::[SOME_CONSTANT_NAME] without having
     * to know the real name of a class.
     * @param bool $short If false, will return the real class name, if true, will return
     * the short one (without the prefix).
     * @return str The class name, like sh_html.
     */
    public function getClassName( $short = false ) {
        if( !$short ) {
            return $this->className;
        } else {
            return $this->shortClassName;
        }
    }

    /**
     * public function construct
     *
     */
    public function construct() {
        return true;
        // Replaces the __construct() method for the extending classes.
    }

    /**
     * Creates or gets a name for a page.<br />
     * This name can be used to describe it, like a kind of title.<br />
     * To build good names, the classes that create main contents should
     * extend this function, or put a reverse in the "uri" table of the database
     * @param string $action
     * Action of the page (second part of the page name, like show in shop/show/17)
     * @param integer $id
     * optional - defaults to null<br />
     * Id of the page (third part of the page name, like 17 in shop/show/17)
     * @return string New name of the page
     */
    public function getPageName( $action, $id = null ) {
        $name = $this->getI18n( 'action_' . $action );
        if( !is_null( $id ) ) {
            $page = str_replace(
                    array( SH_PREFIX, SH_CUSTOM_PREFIX ), array( '', '' ), $this->__tostring()
                ) . '/' . $action . '/' . $id;
            $link = $this->linker->path->getLink( $page );
            $name = str_replace(
                array( '{id}', '{link}' ), array( $id, $link ), $name
            );
        }
        if( $name != '' ) {
            return $name;
        }
        return $this->__toString() . '->' . $action . '->' . $id;
    }

    /**
     * Gets the "singles" folder from the actual class
     * @param boolean $fromRoot optional, defaults to false<br />
     * True for a full disk path (like SH_[anything]_FOLDER)<br />
     * False for a "from root" path (like SH_[anything]_PATH)
     * @return string The path to the singles/ folder
     */
    protected function getSinglePath( $fromRoot = false ) {
        if( $fromRoot ) {
            return $this->classFolder . 'singles/';
        }
        return '/' . $this->className . '/singles/';
    }

    /**
     * Method that returns a class
     * @param str $calledClass The name of the class we want
     * @return object The class we asked for
     * @static
     */
    public static final function getInstance( $calledClass ) {
        if( isset( self::$instances[ $calledClass ] ) ) {
            return self::$instances[ $calledClass ];
        }
        $class = new $calledClass( $calledClass );
        return $class;
    }

    /**
     * Defines if the function that was called is set to minimal.<br/>
     * (Minimal functions won't load a big part of Websailors Core, used for example
     * on function that are called by xmlhttprequest).
     * @param string $function optional, defaults to ""<br />
     * The name of the function that was called.
     * @return boolean True if minimal<br/>False if not
     */
    public function isMinimal( $function = "" ) {
        if( $this->minimal === true ) {
            return true;
        }
        if( isset( $this->minimal[ $function ] ) && $this->minimal[ $function ] === true ) {
            return true;
        }
        return false;
    }

    /**
     * Helps subclasses to share param classes with their master class.
     * They wil store their params in the [class_name] key
     * @param str $class Master class name
     */
    protected function shareI18nFile( $class ) {
        if( class_exists( $class ) ) {
            $this->i18nClassName = $class;
        }
    }

    /**
     * Removes a value from the i18n class.<br />
     * Calls remove() on the i18n class.
     * @param string $varName Name of the variable we want to remove
     * @param string $lang optional, defaults to null<br />
     * Language we want to write the text in.
     * @return string The text in the local language
     * @see sh_i18n::remove()
     */
    protected function removeI18n( $varName, $lang = null ) {
        return $this->linker->i18n->remove( $this->i18nClassName, $varName, $lang );
    }

    /**
     * Gets a value from the i18n class.<br />
     * Calls get() on the i18n class.
     * @param string|integer $varName
     * If string, the name of the text we will search in the i18n files<br />
     * If integer, the id of the text we will search in the database
     * @return string The text in the local language
     * @see sh_i18n::get()
     */
    protected function getI18n( $varName, $lang = null ) {
        return $this->linker->i18n->get( $this->i18nClassName, $varName, $lang );
    }

    /**
     * Sets a value within the i18n class.<br />
     * Calls set() on it.
     * @param string $varName Name of the variable we want to set
     * @param string $value Text of the variable named $varName
     * @param string $lang optional, defaults to null<br />
     * Language we want to write the text in.
     * @return boolean Returns the return of the sh_i18n::set() function
     */
    protected function setI18n( $varName, $value, $lang = NULL ) {
        return $this->linker->i18n->set( $this->i18nClassName, $varName, $value, $lang );
    }

    /**
     * Executes a query using the sh_db class.
     * @param str $queryName The name of the query that is stored in the class'
     * queries.params.php file.
     * @param array $replacements An array containing as keys the names of the
     * variables (that are in the query), and as values the text to put instead.
     * @param str|bool <b>false</b> for no debug, or a string that will contain
     * the query.
     * @return mixed Returns the result of the query.
     */
    protected function db_execute( $queryName, $replacements = array( ), &$debug = false ) {
        if( !isset( $this->db_element_added ) || !$this->db_element_added ) {
            $this->linker->db->addElement( $this->className );
            $this->db_element_added = true;
        }
        return $this->linker->db->execute( $this->className, $queryName, $replacements, $debug );
    }

    protected function db_getRowCount() {
        if( !isset( $this->db_element_added ) || !$this->db_element_added ) {
            return false;
        }
        return $this->linker->db->getRowCount( $this->className );
    }

    protected function db_getFoundRows() {
        if( !isset( $this->db_element_added ) || !$this->db_element_added ) {
            return false;
        }
        return $this->linker->db->getFoundRows( $this->className );
    }

    protected function db_lastError() {
        return $this->linker->db->getLastError( $this->className );
    }

    /**
     * Returns the last inserted id for this class. Remember that the query has to be
     * of the type "insert" for this to work.
     * @return id The id of the last insert element
     */
    protected function db_insertId() {
        $ret = $this->linker->db->insert_id( $this->className );
        return $ret;
    }

    /**
     * Helps subclasses to share param classes with their master class.
     * They wil store their params in the [class_name] key
     * @param str $class Master class name
     */
    protected function shareParamsFile( $class, $base = '' ) {
        if( class_exists( $class ) ) {
            $this->sharedParamsFile = true;
            $this->paramsFile = $class;
            if( empty( $base ) ) {
                $base = $this->className;
            }
            $this->paramsBase .= $base;
        }
    }

    /**
     * Removes a parametter from the class' param file(s), using the sh_params class.
     * @param str $paramName The name of the param to read. To go deeper in the array,
     * use &gt; as separator.
     * @return mixed Returns the content of the param, or the default value.
     */
    protected function removeParam( $paramName = '' ) {
        if( $paramName != '' && $this->paramsBase != '' ) {
            $paramName = $this->paramsBase . '>' . $paramName;
        } elseif( $this->paramsBase != '' ) {
            $paramName = $this->paramsBase;
        }
        return $this->linker->params->remove( $this->paramsFile, $paramName );
    }

    /**
     * Alias to removeParam
     */
    protected function removeParams( $paramName = '' ) {
        if( $paramName != '' && $this->paramsBase != '' ) {
            $paramName = $this->paramsBase . '>' . $paramName;
        } elseif( $this->paramsBase != '' ) {
            $paramName = $this->paramsBase;
        }
        return $this->linker->params->remove( $this->paramsFile, $paramName );
    }

    /**
     * Gets a parametter into the class' param file(s), using the sh_params class.
     * @param str $paramName The name of the param to read. To go deeper in the array,
     * use &gt; as separator.
     * @param str $defaultValue The value to return if none is set. By default, will
     * return sh_params::VALUE_NOT_SET
     * @return mixed Returns the content of the param, or the default value.
     */
    protected function getParam( $paramName = '', $defaultValue = sh_params::VALUE_NOT_SET ) {
        if( $paramName != '' && $this->paramsBase != '' ) {
            $paramName = $this->paramsBase . '>' . $paramName;
        } elseif( $this->paramsBase != '' ) {
            $paramName = $this->paramsBase;
        }
        return $this->linker->params->get( $this->paramsFile, $paramName, $defaultValue );
    }

    /**
     * Alias to getParam
     */
    protected function getParams( $paramName = '', $defaultValue = null ) {
        return $this->getParam( $paramName, $defaultValue );
    }

    /**
     * Sets a parametter into the user's class param file(s), using the sh_params class.<br />
     * Will only do it in memory until a call to writeParams is done.
     * @param str $paramName The name of the param to set. To go deeper in the array,
     * use &gt; as separator.
     * @param str $paramValue The value of the param. If none is set, will set it to
     * an empty string.
     * @return mixed Returns result of the sh_params::set.
     */
    protected function setParam( $paramName, $paramValue = '' ) {
        if( $paramName != '' && $this->paramsBase != '' ) {
            $paramName = $this->paramsBase . '>' . $paramName;
        } elseif( $this->paramsBase != '' ) {
            $paramName = $this->paramsBase;
        }
        return $this->linker->params->set( $this->paramsFile, $paramName, $paramValue );
    }

    /**
     * Alias to setParam
     */
    protected function setParams( $paramName, $paramValue = '' ) {
        return $this->setParam( $paramName, $paramValue );
    }

    /**
     * Writes the params that were modified using setParam or setParams into the file.
     * @return mixed Returns result of the sh_params::write.
     */
    protected function writeParams() {
        return $this->linker->params->write( $this->paramsFile );
    }

    /**
     * Alias to writeParams
     */
    protected function writeParam() {
        return $this->writeParams();
    }

    /**
     * Returns the number of params read for the query $paramName.
     * @param str $paramName The name of the param to count the entries from.<br />
     * Defaults to an empty string.
     * @return int The number of items found with the query $paramName.
     */
    protected function countParams( $paramName = '' ) {
        return $this->linker->params->count( $this->paramsFile, $paramName );
    }

    /**
     * Adds a page to the sitemap class (in order to include it in sitemap.xml)
     * @param string $page optional, defaults to ""<br />
     * The page to add
     * @param string $priority optional<br />(from 0 to 1)<br />
     * See Google help on sitemaps
     * @param string $frequency optional<br /> (from 0 to 1)<br />
     * See Google help on sitemaps
     * @param string $date optional<br />
     * Date of the last change
     * @return mixed Returns the return of the sh_sitempa::addToSitemap() method
     */
    protected function addToSitemap( $page = '', $priority = '', $frequency = '', $date = '' ) {
        if( $page == '' ) {
            $page = $this->linker->path->getPage();
        }
        $sitemap = $this->linker->sitemap;
        if( $date != '' && preg_match( '`([0-9]{4}-[0-9]{2}-[0-9]{2}).+`', $date, $match ) ) {
            $date = $match[ 1 ];
        } elseif( $date != '' && preg_match( '`([0-9]{2})-([0-9]{2})-([0-9]{4}).+`', $date, $match ) ) {
            $date = $match[ 3 ] . '-' . $match[ 2 ] . '-' . $match[ 1 ];
        } else {
            $date = date( 'Y-m-d' );
        }
        $frequencies = $sitemap->getFrequencies();
        $defaultFrequency = $sitemap->getDefaultFrequency();
        if( !in_array( $frequency, $frequencies ) ) {
            if( in_array( $this->getParam( 'sitemap>frequency' ), $frequencies ) ) {
                $frequency = $this->getParam( 'sitemap>frequency' );
            } else {
                $frequency = $defaultFrequency;
            }
        }
        $defaultPriority = $sitemap->getDefaultPriority();
        if( $priority < 0.1 || $priority > 1 ) {
            if( $this->getParam( 'sitemap>priority' ) >= 0.1 && $this->getParam( 'sitemap>priority' ) <= 1 ) {
                $priority = $this->getParam( 'sitemap>priority' );
            } else {
                $priority = $defaultPriority;
            }
        }
        if( $this->linker->menu->changeSitemapPriority( $page ) > $priority ) {
            $priority = $this->linker->menu->changeSitemapPriority( $page );
        }
        if( $this->linker->index->changeSitemapPriority( $page ) > $priority ) {
            $priority = $this->linker->index->changeSitemapPriority( $page );
        }

        return $sitemap->addToSitemap( $page, $priority, $date, $frequency );
    }

    /**
     * Removes a page from the sitemap (using sh_sitemap::removeFromSitemap())
     * @param string $page The page to remove, or "" for the actual page
     * @return mixed Returns the same that sh_sitemap::removeFromSitemap()
     */
    protected function removeFromSitemap( $page = '' ) {
        $sitemap = $this->linker->sitemap;
        if( $page == '' ) {
            $page = $this->linker->path->getPage();
        }
        return $sitemap->removeFromSitemap( $page );
    }

    /**
     * Creates an uri-like path (without the domain) to the given folder.
     * @param str $path The folder name to modify.
     * @return str The uri-like path.
     */
    protected function fromRoot( $path ) {
        return str_replace( SH_ROOT_FOLDER, SH_ROOT_PATH, $path );
    }

    /**
     * Orders a rendering process on a .rf.xml file
     * @param string $file The .rf.xml file to render
     * @param array $values Defaults to an empty array.<br />
     * The values that can be used for replacements (See sh_renderer)
     * @param boolean|integer $debug Defaults to false.<br />
     * Asks to enable the debugger, or not (see $this->debugging()).
     * @param boolean $sendToHTML
     * If true (default), sends the rendered element to sh_html::insert(), and returns it.<br />
     * if false, returns the rendered element.
     * @return string The rendered element
     */
    protected function render( $file, $values = array( ), $debug = false, $sendToHTML = true ) {
        $renderer = $this->linker->renderer;
        $ret = '';
        if( !isset( $values[ 'i18n' ] ) ) {
            $values[ 'i18n' ] = $this->__tostring();
        }
        if( !is_array( $values[ 'constants' ] ) ) {
            $values[ 'constants' ] = $this->renderingConstants;
        } else {
            $values[ 'constants' ] = array_merge( $this->renderingConstants, $values[ 'constants' ] );
        }

        // We check if there is a specific renderFile in the template
        if( isset( $renderer->renderFiles[ $values[ 'i18n' ] . '_' . $file ] ) ) {
            $ret = $renderer->render(
                $this->linker->site->templateFolder . 'renderFiles/' . $renderer->renderFiles[ $values[ 'i18n' ] . '_' . $file ],
                $values, $debug
            );
            if( $sendToHTML ) {
                return $this->linker->html->insert( $ret );
            } else {
                $ret = $renderer->toHtml( $ret );
            }
        }

        if( empty( $ret ) ) {
            if( substr( $file, -7 ) != '.rf.xml' && substr( $file, -4 ) != '.css' ) {
                $filePath = 'renderFiles/' . $file . '.rf.xml';
            } else {
                // We set to a file name that should never be there...
                $filePath = '!^!';
            }

            if( file_exists( $this->classFolder . $filePath ) ) {
                $ret = $renderer->render(
                    $this->classFolder . $filePath, $values, $debug
                );
            } elseif( file_exists( $this->classFolder . $file ) ) {
                $ret = $renderer->render(
                    $this->classFolder . $file, $values, $debug
                );
            } else {
                $ret = $renderer->render( $file, $values, $debug );
            }
            if( $sendToHTML ) {
                return $this->linker->html->insert( $ret );
            } else {
                $ret = $renderer->toHtml( $ret );
            }
        }
        return $ret;
    }

    /**
     * Adds constants that may also be used in the render files using
     * constants>[name_of_the_constant]
     * @param array $constants An array containing the names of the constants
     * as keys and the values as values
     * @param bool $localConstant <b>true (default)</b> for constants that won't
     * be declared the same way as global constants (using define()), but that
     * may be used in render files<br />
     * <b>false</b> for constants that are also declared using define().
     * @return bool Always returns true
     */
    protected function renderer_addConstants( $constants = array( ), $localConstant = true ) {
        if( $localConstant ) {
            $this->renderingConstants = array_merge( $this->renderingConstants, $constants );
        } else {
            foreach( $constants as $name => $value ) {
                if( !defined( strtoupper( $name ) ) ) {
                    define( strtoupper( $name ), $value );
                }
            }
        }
        return true;
    }

    /**
     * @deprecated
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri( $page ) {
        list($class, $method, $id) = explode( '/', $page );
        if( in_array( $method, $this->minimal ) ) {
            if( $id != '' ) {
                $ending = '/' . $id . '-minimal.php';
            } else {
                $ending = '.php';
            }
            $uri = '/' . $this->shortClassName . '/' . $method . $ending;
            return $uri;
        }
        return false;
    }

    /**
     * @deprecated
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage( $uri ) {
        if( preg_match( '`/' . $this->shortClassName . '/([^/]+)(/([0-9]+)-[^/]+)?\.php`', $uri, $matches ) ) {
            if( in_array( $matches[ 1 ], $this->minimal ) ) {
                $page = $this->shortClassName . '/' . $matches[ 1 ] . '/' . $matches[ 3 ];
                return $page;
            }
        }
        return false;
    }

    /**
     * Sets the debugging status
     * @param bool|int $status <b>null or Empty (default)</b> returns the actual
     * status<br />
     * <b>false</b> for "no debug"<br />
     * <b>0->3</b> for a debug level
     * @param bool|str $inFile <b>false</b> for "on screen"<br />
     * <b>[file name]</b> for a the file to debug to
     * @return bool The return of sh_debugger::debugging (which may be the
     * actual status if $status is null)
     */
    protected function debugging(
    $status = sh_debugger::STATUS, $inFile = sh_debugger::INFILE
    ) {
        return $this->debugger->debugging( $status, $inFile, $this->className );
    }

    protected function xdebug( $text = '' ) {
        return $this->debugger->xdebug( $text );
    }

    /**
     * Sends some text to the debugger.
     * @param str $text The text to write in the debug
     * @param int $level The debug level (from 0 to 3, 0 being the most important).
     * Defaults to sh_debugger::LEVEL .
     * @param int $line The line number that should be written at the beginning of
     * the lines in the debug.
     * Defaults to sh_debugger::LINE .
     * @param bool $showClassName
     * True to show the class name before the line number.
     * False not to show it.
     * Defaults to sh_debugger::SHOWCLASS
     * @return bool
     * True if the text was added to the debug,
     * False if not.
     */
    protected function debug(
    $text, $level = sh_debugger::LEVEL, $line = sh_debugger::LINE, $showClassName = sh_debugger::SHOWCLASS
    ) {
        return $this->debugger->debug( $text, $level, $line, $showClassName, $this->className );
    }

    /**
     * Verifies if a form has been submitted when accessing to this page.
     * @param str $formId The form id that was written in the id parametter of the
     * RENDER_FORM tag.
     * @param bool $captcha See the sh_form_verifier's submitted() method<br />
     * Default to sh_form_verifier::VERIFY_CAPTCHA .
     * @param bool $erase  See the sh_form_verifier's submitted() method <br />
     * Defaults to sh_form_verifier::ERASE .<br />
     * @example If the form is a shop command form, turning this to <b>false</b> will
     * make successive refreshing of the page do nothing more than what had been done
     * by the first one. <br />
     * Turning this to <b>true</b> will make successive calls of the page
     * (refresh or clicks on previous and next page, for example) submit the
     * command everytime.
     * @return bool True if the given form was submitted, false if not.
     * @see sh_form_verifier::submitted()
     */
    protected function formSubmitted( $formId, $captcha = sh_form_verifier::DONT_VERIFY_CAPTCHA,
                                      $erase = sh_form_verifier::ERASE ) {
        return self::$form_verifier->submitted( $formId, $captcha, $erase );
    }

    /**
     * Verifies that the user is at least an admin (which means that he is an admin or a master).
     * @return bool
     * true if ok<br />
     * Redirects with a 403 error if not
     */
    protected function onlyAdmin( $thisIsAnAdminPage = false ) {
        if( $thisIsAnAdminPage ) {
            $this->helper->isAdminPage( true );
        }
        if( !$this->isAdmin() ) {
            $this->linker->path->error( '403' );
        }
        return true;
    }

    /**
     * Verifies that the user is connected.
     * @return bool
     * true if ok
     */
    protected function isConnected() {
        return $this->linker->user->isConnected();
    }

    /**
     * Verifies that the user is at least an admin (which means that he is an admin or a master).
     * @param bool $alsoVerifyIfMaster <b>true</b> (default behaviour) if we want to know if 
     * the user is an admin OR a master, <b>false</b> if we only want to know if the user is 
     * an admin.
     * @return bool
     * true if ok
     */
    protected function isAdmin( $alsoVerifyIfMaster = true ) {
        $userId = $this->linker->user->userId;
        if( !$userId ) {
            return false;
        }
        return $this->linker->admin->isAdmin( $userId, $alsoVerifyIfMaster );
    }

    /**
     * Verifies that the user is at least an admin (which means that he is an admin or a master).
     * @return bool
     * true if ok<br />
     * Redirects with a 403 error if not
     */
    protected function onlyMaster() {
        if( !$this->isMaster() ) {
            $this->linker->path->error( '403' );
        }
        return true;
    }

    /**
     * Verifies that the user is at least an admin (which means that he is an admin or a master).
     * @return bool
     * true if ok<br />
     * Redirects with a 403 error if not
     */
    protected function isMaster() {
        $userId = $this->linker->user->userId;
        if( !$userId ) {
            return false;
        }
        return $this->linker->admin->isMaster( $userId );
    }

    public function __tostring() {
        return get_class();
    }

}

class sh_debugger {

    protected $debugEnabled = false;

    /**
     * The staus of debugging (null|false|0-3)
     * @access public
     */
    const STATUS = null;
    /**
     * The file to debug to, or false for on-screen-debugging
     * @access public
     */
    const INFILE = false;

    /**
     * The debugging level (from 0 to 3)
     * @access public
     */
    const LEVEL = 0;
    /**
     * The default line number
     * @access public
     */
    const LINE = 0;
    /**
     * A boolean telling if we have to show the class name
     * @access public
     */
    const SHOWCLASS = true;

    /**
     * public function __construct
     *
     */
    public function __construct() {
        $this->linker = sh_linker::getInstance();
        //$this->file = date('Ymd-His').md5(sh_linker::getInstance()->path->url);
    }

    /**
     * Reads or sets the debugging level.
     * @param boolean|integer $status optional, defaults to null<br />
     * If null, we only read the actual debugging level<br />
     * If false, disables the debugging process.<br />
     * Else (0, 1, 2, or 3), sets the debugging level to that number.<br />
     * 0 is showing only errors.<br />
     * 1 is showing errors, and bigger titles.<br />
     * 2 is showing the processes step by step (each functions that are called, and for which a debug was created).
     * 3 is detailling all debug messages.
     * @param boolean|string $inFile
     * If false, debugs sending the data into the html
     * Else, debugs into the file given in this parametter
     * @return boolean|string Returns the actual status.
     */
    public function debugging( $status = self::STATUS, $inFile=self::INFILE, $class = '' ) {
        //$inFile = $this->file;
        if( is_null( $status ) ) {
            return $this->debugEnabled;
        }
        if( $inFile !== false ) {
            $this->debugInFile = true;
            $this->debugFile = $inFile;
            if( empty( $class ) ) {
                $class = $this->__tostring();
            }
            $f = fopen( $this->debugFile, 'w+' );
            fwrite( $f, 'Debug for class ' . $class . "\n" );
            fwrite( $f, 'Started on ' . date( 'Y-m-d H:i:s' ) . "\n\n" );
            fclose( $f );
        }

        if( !empty( $class ) ) {
            $forClass = ' for class ' . $class;
        }
        if( $status === false || $status < $this->debugEnabled ) {
            if( $status === false ) {
                $toldStatus = 'false';
            }
            $this->debug( 'Changing debug level to ' . $status . $toldStatus . $forClass, 1 );
            $alreadyTold = true;
        }
        $previousStatus = $this->debugEnabled;
        $this->debugEnabled = $status;
        if( !$alreadyTold ) {
            if( $status === false ) {
                $toldStatus = 'false';
            }
            $this->debug( 'Changing debug level to ' . $status . $toldStatus . $forClass, 1 );
        }
        return $previousStatus;
    }

    public function forcedDebug( $text, $level = self::LEVEL, $line = self::LINE, $showClassName = true, $class = '' ) {
        $status = $this->debugging();
        if( $class == '' ) {
            $class = __CLASS__;
        }
        $this->debugging( $level );
        debug( $text, $level, $line, $showClassName );
        $this->debugging( $status );
    }

    /**
     * Adds text to the debug, sending it to the html or writting it to a file, depending on
     * self::debugging()
     * @param string $text The text to add to the debug
     * @param integer $level (from 0 to 3)<br />The level of the debug (see self::debugging()).
     * @param integer $line The line number where this function was called
     * @return boolean The status of this operation
     */
    public function debug( $text, $level = self::LEVEL, $line = self::LINE, $showClassName = true, $class = '' ) {
        if( $this->debugEnabled === false || $level > $this->debugEnabled ) {
            return true;
        }
        if( $level == 0 ) {
            $color = 'red';
        } elseif( $level == 1 ) {
            $color = 'orange';
        } elseif( $level == 2 ) {
            $color = 'green';
        } else {
            $color = 'blue';
        }
        if( $this->debugInFile == false ) {
            echo '<div level="' . $level . '" class="debugging" style="background-color:black;color:' . $color . ';">';
            if( $showClassName ) {
                if( $class == '' ) {
                    $class = __CLASS__;
                }
                echo $class;
                if( $line != '0' ) {
                    echo '::' . $line;
                }
                echo ' : ';
            }
            if( is_array( $text ) ) {
                echo 'Array<br />';
                echo '--------------<br />';
                foreach( $text as $key => $line ) {
                    if( is_array( $line ) ) {
                        echo 'Debugging only works for strings and 1 level deep array<br />';
                        break;
                    }
                    echo $key . '->' . $line . '<br />';
                }
                echo '--------------</div>';
            } else {
                echo htmlspecialchars( $text ) . '</div>' . "\n";
            }
        } else {
            $f = fopen( $this->debugFile, 'a+' );
            fwrite( $f, 'line ' . $line . ': ' . $text . "\n" );
            fclose( $f );
        }
        return true;
    }

    public function xdebug( $text = '' ) {
        if( $this->debugInFile == false ) {
            echo '<div class="debugging" style="background-color:black;color:blue;">';
            if( !empty( $text ) ) {
                $text = ' for ' . $text;
            }
            echo '-------------- Starting of trace' . $text . ' --------------<br />';
            $dump = xdebug_get_function_stack();
            array_shift( $dump );
            array_pop( $dump );
            array_pop( $dump );
            foreach( $dump as $line ) {
                echo '<div>';
                echo '<span style="color:green;">' . $line[ 'class' ] . '::' . $line[ 'function' ] . '()</span> - ';
                echo '<span style="color:blue;">' . $line[ 'file' ] . ' : ' . $line[ 'line' ] . '</span>';
                echo '</div>';
            }
            echo '-------------- Ending of trace' . $text . ' --------------</div>';
        } else {
            $f = fopen( $this->debugFile, 'a+' );
            fwrite( $f, 'line ' . $line . ': ' . $text . "\n" );
            fclose( $f );
        }
        return true;
    }

    public function __toString() {
        return __CLASS__;
    }

}
