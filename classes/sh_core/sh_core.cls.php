<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version 1.09.153.1
 * @package Shopsailors Core Classes
 */

if(!defined('SH_MARKER'))
	header('location: directCallForbidden.php');

/**
 * @abstract Class to be extended by most of all classes in the Shopsailors' engine.
 *
 */
abstract class sh_core{
    static $instances = array();
    protected $links = null;
    protected $debugger = null;
    protected static $form_verifier = null;
    protected $minimal = array();
    protected $shortClassName = '';
    protected $className = '';
    public $renderingConstants = array();

    private $paramsFile = '';
    private $paramsBase = '';

    private $i18nClassName = '';

    /**
     * Constructor
     * @param str $class The name of the class
     */
    private final function __construct($class){/*
        $this->links = sh_links::getInstance();
        $className = $this->__tostring();
        $this->links->$className = $this;*/
        $this->links = sh_links::getInstance();
        self::$instances[$class] = $this;
        $className = $this->__tostring();
        $this->className = $className;
        $this->shortClassName = str_replace(SH_PREFIX,'',$className);
        $this->shortClassName = str_replace(SH_CUSTOM_PREFIX,'',$this->shortClassName);
        $this->links->$className = $this;
        if($className != 'sh_params'){
            $this->paramsFile = $className;
            $this->i18nClassName = $className;
            $this->links->params->addElement($this->paramsFile);
            if($className != 'sh_db'){
                $this->links->db->addElement($className);
            }
        }
        $this->debugger = new sh_debugger;
        if(is_null(self::$form_verifier)){
            self::$form_verifier = new sh_form_verifier();
        }
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
    protected function search_addEntry($method,$id,$level_1,$level_2,$level_3){
        return $this->links->searcher->addEntry(
            $this->shortClassName,
            $method,
            $id,
            $level_1,
            $level_2,
            $level_3
        );
    }

    /**
     * Removes an entry from the searcher's database.
     * @uses sh_searcher::removeEntry()
     * @param int $id The id of the entry we want to remove
     * @return The return of sh_searcher::removeEntry
     */
    protected function search_removeEntry($method,$id,$language = sh_searcher::ALL_LANGUAGES){
        return $this->links->searcher->removeEntry(
            $this->shortClassName,
            $method,
            $id,
            $language
        );
    }

    /**
     * Returns an array containing the classes that are present in this class'
     * shared folder
     * @param str $folder A subfolder, or nothing
     * @return array The classes
     */
    public function getClassesFromSharedFolder($folder = ''){
        $className = $this->className.'/';
        if($folder != '' && substr($folder,-1) != '/'){
            $folder .= '/';
        }
        if(is_dir(SH_CLASS_SHARED_FOLDER.$className.$folder)){
            $classes = scandir(SH_CLASS_SHARED_FOLDER.$className.$folder);
            foreach($classes as $class){
                if(substr($class,0,1) != '.'){
                    $shortClassName = preg_replace(
                        '`^('.SH_PREFIX.'|'.SH_CUSTOM_PREFIX.')?(.+)(\.php)$`',
                        '$2',
                        $class
                    );
                    $longClassName = preg_replace(
                        '`^(.+)(\.php)$`', '$1', $class
                    );
                    $classesList[] = array(
                        'long'=>$longClassName,
                        'short'=>$shortClassName,
                        'file'=>SH_CLASS_SHARED_FOLDER.$className.$folder.$class
                    );
                }
            }
            return $classesList;
        }
        return array();
    }

    /**
     * This function only gives the class name.<br />
     * It is usefull to know which kind of class is sent when calling
     * $this->links->[some_short_class_name]->getClassName().<br />
     * This way, it will permit to extend and replace original classes by others.<br />
     * After php 5.3, will be even more usefull because will help the calling of
     * class constants this way:<br />
     * $this->links->[some_short_class_name]->getClassName()::[SOME_CONSTANT_NAME] without having
     * to know the real name of a class.
     * @param bool $short If false, will return the real class name, if true, will return
     * the short one (without the prefix).
     * @return str The class name, like sh_html.
     */
    public function getClassName($short = false){
        if(!$short){
            return $this->className;
        }else{
            return $this->shortClassName;
        }
    }

    /**
     * public function construct
     *
     */
    public function construct(){
        return true;
        // Replaces the __construct() method for the extending classes.
        // Adding the return to this (to show if construction raised an error)
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
    public function getPageName($action, $id = null){
        $name = $this->getI18n('action_'.$action);
        if(!is_null($id)){
            $page = str_replace(
                    array(SH_PREFIX,SH_CUSTOM_PREFIX),
                    array('',''),
                    $this->__tostring()
                ).'/'.$action.'/'.$id;
            $link = $this->links->path->getLink($page);
            $name = str_replace(
                array('{id}','{link}'),
                array($id,$link),
                $name
            );
        }
        if($name != ''){
            return $name;
        }
        return $this->__toString().'->'.$action.'->'.$id;
    }

    /**
     * Gets the "singles" folder from the actual class
     * @param boolean $fromRoot optional, defaults to false<br />
     * True for a full disk path (like SH_[anything]_FOLDER)<br />
     * False for a "from root" path (like SH_[anything]_PATH)
     * @return string The path to the singles/ folder
     */
    protected function getSinglePath($fromRoot = false){
        if($fromRoot){
            return SH_CLASS_FOLDER.$this->className.'/singles/';
        }
        return '/'.$this->className.'/singles/';
    }

    /**
     * Method that returns a class
     * @param str $calledClass The name of the class we want
     * @return object The class we asked for
     * @static
     */
    public static final function getInstance($calledClass){
        if(isset(self::$instances[$calledClass])){
            return self::$instances[$calledClass];
        }
        $class = new $calledClass($calledClass);
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
    public function isMinimal($function = ""){
        if($this->minimal === true){
            return true;
        }
        if(isset($this->minimal[$function]) && $this->minimal[$function] === true){
            return true;
        }
        return false;
    }



    /**
     * Helps subclasses to share param classes with their master class.
     * They wil store their params in the [class_name] key
     * @param str $class Master class name
     */
    protected function shareI18nFile($class){
        if(class_exists($class)){
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
    protected function removeI18n($varName, $lang = null){
        return $this->links->i18n->remove($this->i18nClassName,$varName,$lang);
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
    protected function getI18n($varName,$lang = null){
        return $this->links->i18n->get($this->i18nClassName,$varName,$lang);
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
    protected function setI18n($varName,$value,$lang = NULL){
        return $this->links->i18n->set($this->i18nClassName,$varName, $value, $lang);
    }

    /**
     * Executes a query using the sh_db class.
     * @param str $queryName The name of the query that is stored in the class'
     * queries.params.php file.
     * @param array $replacements An array containing as keys the names of the
     * variables (that are in the query), and as values the text to put instead.
     * @param int|bool $debug The debug level. More informations in the sh_db documentation.
     * @return mixed Returns the result of the query.
     */
    protected function db_execute($queryName,$replacements = array(),&$debug = false){
        if(!$this->db_element_added){
            $this->links->db->addElement($this->className);
            $this->db_element_added = true;
        }
        return $this->links->db->execute($this->className,$queryName,$replacements,$debug);
    }

    /**
     * Returns the last inserted id for this class. Remember that the query has to be
     * of the type "insert" for this to work.
     * @return id The id of the last insert element
     */
    protected function db_insertId(){
        $ret = $this->links->db->insert_id($this->className);
        return $ret;
    }

    /**
     * Helps subclasses to share param classes with their master class.
     * They wil store their params in the [class_name] key
     * @param str $class Master class name
     */
    protected function shareParamsFile($class){
        if(class_exists($class)){
            $this->sharedParamsFile = true;
            $this->paramsFile = $class;
            $this->paramsBase .= $this->className;
        }
    }

    /**
     * Removes a parametter from the class' param file(s), using the sh_params class.
     * @param str $paramName The name of the param to read. To go deeper in the array,
     * use &gt; as separator.
     * @return mixed Returns the content of the param, or the default value.
     */
    protected function removeParam($paramName = ''){
        if($paramName != '' && $this->paramsBase != ''){
            $paramName = $this->paramsBase.'>'.$paramName;
        }elseif($this->paramsBase != ''){
            $paramName = $this->paramsBase;
        }
        return $this->links->params->remove($this->paramsFile,$paramName);
    }

    /**
     * Alias to removeParam
     */
    protected function removeParams($paramName = ''){
        if($paramName != '' && $this->paramsBase != ''){
            $paramName = $this->paramsBase.'>'.$paramName;
        }elseif($this->paramsBase != ''){
            $paramName = $this->paramsBase;
        }
        return $this->links->params->remove($this->paramsFile,$paramName);
    }

    /**
     * Gets a parametter into the class' param file(s), using the sh_params class.
     * @param str $paramName The name of the param to read. To go deeper in the array,
     * use &gt; as separator.
     * @param str $defaultValue The value to return if none is set. By default, will
     * return sh_params::VALUE_NOT_SET
     * @return mixed Returns the content of the param, or the default value.
     */
    protected function getParam($paramName = '',$defaultValue = sh_params::VALUE_NOT_SET){
        if($paramName != '' && $this->paramsBase != ''){
            $paramName = $this->paramsBase.'>'.$paramName;
        }elseif($this->paramsBase != ''){
            $paramName = $this->paramsBase;
        }
        return $this->links->params->get($this->paramsFile,$paramName,$defaultValue);
    }

    /**
     * Alias to getParam
     */
    protected function getParams($paramName = '',$defaultValue = null){
        return $this->getParam($paramName,$defaultValue);
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
    protected function setParam($paramName,$paramValue = ''){
        if($paramName != '' && $this->paramsBase != ''){
            $paramName = $this->paramsBase.'>'.$paramName;
        }elseif($this->paramsBase != ''){
            $paramName = $this->paramsBase;
        }
        return $this->links->params->set($this->paramsFile,$paramName,$paramValue);
    }

    /**
     * Alias to setParams
     */
    protected function setParams($paramName,$paramValue = ''){
        return $this->setParam($paramName,$paramValue);
    }

    /**
     * Writes the params that were modified using setParam or setParams into the file.
     * @return mixed Returns result of the sh_params::write.
     */
    protected function writeParams(){
        return $this->links->params->write($this->paramsFile);
    }

    /**
     * Alias to writeParam
     */
    protected function writeParam(){
        return $this->writeParams();
    }

    protected function countParams($paramName = ''){
        return $this->links->params->count($this->paramsFile,$paramName);
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
    protected function addToSitemap($page = '', $priority = '', $frequency = '', $date = ''){
        if($page == ''){
            $page = $this->links->path->getPage();
        }
        $sitemap = $this->links->sitemap;
        if($date != '' && preg_match('`([0-9]{4}-[0-9]{2}-[0-9]{2}).+`',$date,$match)){
            $date = $match[1];
        }elseif($date != '' && preg_match('`([0-9]{2})-([0-9]{2})-([0-9]{4}).+`',$date,$match)){
            $date = $match[3].'-'.$match[2].'-'.$match[1];
        }else{
            $date = date('Y-m-d');
        }
        $frequencies = $sitemap->getFrequencies();
        $defaultFrequency = $sitemap->getDefaultFrequency();
        if(!in_array($frequency, $frequencies)){
            if(in_array($this->getParam('sitemap>frequency'), $frequencies)){
                $frequency = $this->getParam('sitemap>frequency');
            }else{
                $frequency = $defaultFrequency;
            }
        }
        $defaultPriority = $sitemap->getDefaultPriority();
        if($priority < 0.1 || $priority > 1){
            if($this->getParam('sitemap>priority') >= 0.1 && $this->getParam('sitemap>priority') <= 1){
                $priority = $this->getParam('sitemap>priority');
            }else{
                $priority = $defaultPriority;
            }
        }
        if($this->links->menu->changeSitemapPriority($page) > $priority){
            $priority = $this->links->menu->changeSitemapPriority($page);
        }
        if($this->links->index->changeSitemapPriority($page) > $priority){
            $priority = $this->links->index->changeSitemapPriority($page);
        }

        return $sitemap->addToSitemap($page, $priority, $date, $frequency);
    }

    /**
     * Removes a page from the sitemap (using sh_sitemap::removeFromSitemap())
     * @param string $page The page to remove, or "" for the actual page
     * @return mixed Returns the same that sh_sitemap::removeFromSitemap()
     */
    protected function removeFromSitemap($page = ''){
        $sitemap = $this->links->sitemap;
        if($page == ''){
            $page = $this->links->path->getPage();
        }
        return $sitemap->removeFromSitemap($page);
    }

    /**
     * Creates an uri-like path (without the domain) to the given folder.
     * @param str $path The folder name to modify.
     * @return str The uri-like path.
     */
    protected function fromRoot($path){
        return str_replace(SH_ROOT_FOLDER,SH_ROOT_PATH,$path);
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
    protected function render($file,$values = array(),$debug = false,$sendToHTML = true){
        if(!isset($values['i18n'])){
            $values['i18n'] = $this->__tostring();
        }
        if(!is_array($values['constants'])){
            $values['constants'] = $this->renderingConstants;
        }else{
            $values['constants'] = array_merge($this->renderingConstants,$values['constants']);
        }
/*
        // TEST
        if(file_exists(SH_CLASS_FOLDER.$this->__tostring().'/renderFiles/'.$file.'.rf.xml')){
            $content = file_get_contents(SH_CLASS_FOLDER.$this->__tostring().'/renderFiles/'.$file.'.rf.xml');
        }elseif(file_exists(SH_CLASS_FOLDER.$this->__tostring().'/'.$file)){
            $content = file_get_contents(
                SH_CLASS_FOLDER.$this->__tostring().'/'.$file
            );
        }else{
            $content = $file;
        }
        $ret = $this->links->renderer->render($content,$values,$debug);
        if($sendToHTML === true){
            return $this->links->html->insert($ret);
        }
        return $ret;
        // END OF TEST
*/
        if(substr($file,-7) != '.rf.xml' && substr($file,-4) != '.css'){
            $filePath = 'renderFiles/'.$file.'.rf.xml';
        }else{
            // We set to a file name that should never be there...
            $filePath='!^!';
        }

        if(file_exists(SH_CLASS_FOLDER.$this->__tostring().'/'.$filePath)){
            $ret = $this->links->renderer->render(
                SH_CLASS_FOLDER.$this->__tostring().'/'.$filePath,
                $values,
                $debug
            );
        }elseif(file_exists(SH_CLASS_FOLDER.$this->__tostring().'/'.$file)){
            $ret = $this->links->renderer->render(
                SH_CLASS_FOLDER.$this->__tostring().'/'.$file,
                $values,
                $debug
            );
        }else{
            $ret = $this->links->renderer->render($file,$values,$debug);
        }
        if($sendToHTML === true){
            return $this->links->html->insert($ret);
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
    protected function renderer_addConstants($constants = array(),$localConstant = true){
        if($localConstant){
            $this->renderingConstants = array_merge($this->renderingConstants,$constants);
        }else{
            foreach($constants as $name=>$value){
                if(!defined(strtoupper($name))){
                    define(strtoupper($name),$value);
                }
            }
        }
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);
        if(in_array($method,$this->minimal)){
            if($id != ''){
                $ending = '/'.$id.'-minimal.php';
            }else{
                $ending = '.php';
            }
            $uri = '/'.$this->shortClassName.'/'.$method.$ending;
            return $uri;
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if(preg_match('`/'.$this->shortClassName.'/([^/]+)(/([0-9]+)-[^/]+)?\.php`',$uri,$matches)){
            if(in_array($matches[1],$this->minimal)){
                $page = $this->shortClassName.'/'.$matches[1].'/'.$matches[3];
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
                    $status = sh_debugger::STATUS,
                    $inFile = sh_debugger::INFILE
                ){
        return $this->debugger->debugging($status,$inFile);
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
                    $text,
                    $level = sh_debugger::LEVEL,
                    $line = sh_debugger::LINE,
                    $showClassName = sh_debugger::SHOWCLASS
                ){
        return $this->debugger->debug($text,$level,$line,$showClassName);
    }

    /**
     * Verifies if a form has been submitted when accessing to this page.
     * @param str $formId The form id that was written in the id parametter of the
     * RENDER_FORM tag.
     * @param bool $captcha True if a captcha has to be verified, False if not.
     * Default to sh_form_verifier::VERIFY_CAPTCHA .
     * @param bool $erase True if the form could be called more than once,
     * False, if we want the form to be inactive after the first test. <br />
     * Defaults to sh_form_verifier::ERASE .<br />
     * @example If the form is a shop command form, turning this to <b>false</b> will
     * make successive refreshing of the page do nothing more than what had been done
     * by the first one. <br />
     * Turning this to <b>true</b> will make successive calls of the page
     * (refresh or clicks on previous and next page, for example) submit the
     * command everytime.
     * @return bool True if the given form was submitted, false if not.
     */
    protected function formSubmitted(
                    $formId,
                    $captcha = sh_form_verifier::VERIFY_CAPTCHA,
                    $erase = sh_form_verifier::ERASE
                ){
        return self::$form_verifier->submitted($formId,$captcha,$erase);
    }

    /**
     * Verifies that the user is at least an admin (which means that he is an admin or a master).
     * @return bool
     * true if ok<br />
     * Redirects with a 403 error if not
     */
    protected function onlyAdmin(){
        if(!$this->isAdmin()){
            $this->links->path->error('403');
        }
        return true;
    }

    /**
     * Verifies that the user is at least an admin (which means that he is an admin or a master).
     * @return bool
     * true if ok<br />
     * Redirects with a 403 error if not
     */
    protected function isAdmin(){
        $userId = $this->links->user->userId;
        if(!$userId){
            return false;
        }
        return $this->links->admin->isAdmin($userId);
    }

    /**
     * Verifies that the user is at least an admin (which means that he is an admin or a master).
     * @return bool
     * true if ok<br />
     * Redirects with a 403 error if not
     */
    protected function onlyMaster(){
        if(!$this->isMaster()){
            $this->links->path->error('403');
        }
        return true;
    }

    /**
     * Verifies that the user is at least an admin (which means that he is an admin or a master).
     * @return bool
     * true if ok<br />
     * Redirects with a 403 error if not
     */
    protected function isMaster(){
        $userId = $this->links->user->userId;
        if(!$userId){
            return false;
        }
        return $this->links->admin->isMaster($userId);
    }

    public function __tostring(){
        return get_class();
    }
}

class sh_debugger{
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
    public function __construct(){
        $this->links = sh_links::getInstance();
        //$this->file = date('Ymd-His').md5(sh_links::getInstance()->path->url);
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
    public function debugging($status = self::STATUS,$inFile=self::INFILE){
        //$inFile = $this->file;
        if(is_null($status)){
            return $this->debugEnabled;
        }
        if($inFile !== false){
            $this->debugInFile = true;
            $this->debugFile = $inFile;

            $class = $this->__tostring();
            $f = fopen($this->debugFile,'w+');
            fwrite($f,'Debug for class '.$class."\n");
            fwrite($f,'Started on '.date('Y-m-d H:i:s')."\n\n");
            fclose($f);
        }

        if($status === false || $status < $this->debugEnabled){
            if($status === false){
                $toldStatus = 'false';
            }
            $this->debug('Changing debug level to '.$status.$toldStatus, 1);
            $alreadyTold = true;
        }
        $previousStatus = $this->debugEnabled;
        $this->debugEnabled = $status;
        if(!$alreadyTold){
            if($status === false){
                $toldStatus = 'false';
            }
            $this->debug('Changing debug level to '.$status.$toldStatus, 1);
        }
        return $previousStatus;
    }

    public function forcedDebug($text,$level = self::LEVEL,$line = self::LINE,$showClassName = true){
        $status = $this->debugging();
        $this->debugging($level);
        debug($text,$level,$line,$showClassName);
        $this->debugging($status);
    }

    /**
     * Adds text to the debug, sending it to the html or writting it to a file, depending on
     * self::debugging()
     * @param string $text The text to add to the debug
     * @param integer $level (from 0 to 3)<br />The level of the debug (see self::debugging()).
     * @param integer $line The line number where this function was called
     * @return boolean The status of this operation
     */
    public function debug($text,$level = self::LEVEL,$line = self::LINE,$showClassName = true){
        if($this->debugEnabled === false || $level > $this->debugEnabled){
            return true;
        }
        if($level == 0){
            $color = 'red';
        }elseif($level == 1){
            $color = 'orange';
        }elseif($level == 2){
            $color = 'green';
        }else{
            $color = 'blue';
        }
        if($this->debugInFile == false){
            echo '<div level="'.$level.'" class="debugging" style="background-color:black;color:'.$color.';">';
            if($showClassName){
                $class = $this->__tostring();
                echo $class;
                if($line != '0'){
                    echo '::'.$line;
                }
                echo ' : ';
            }
            echo htmlentities($text).'</div>'."\n";
        }else{
            $f = fopen($this->debugFile,'a+');
            fwrite($f,'line '.$line.': '.$text."\n");
            fclose($f);
        }
        return true;
    }

    public function  __toString() {
        return __CLASS__;
    }

}
