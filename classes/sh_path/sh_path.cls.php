<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

/**
 * Class that manages the pathes (on the system, and urls).
 */
class sh_path extends sh_core{
    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );
    /**
     * The uri without the get string
     * @var str
     */
    public $uri;
    /**
     * The return of pathinfo() on the active uri
     * @var array
     */
    public $pathinfo;
    /**
     * http or https
     * @var str
     */
    public $protocol;
    /**
     * The complete url (uri + query string)
     * @var str
     */
    public $url;
    
    /**
     * The return of parse_url()
     * @var <type> 
     */
    public $parsed_url;
    /**
     * The domain name
     * @var str
     */
    protected $domain;
    /**
     * Protocol + domain
     * @var str
     */
    protected $baseUri;
    /**
     * The complete
     * @var str
     */
    protected $thisPage;
    /**
     * The page we are showing (class + method [+ id])
     * @var array()
     * <br />The array is of the form array(<br />
     * &#160;&#160;'element' => (str) $element, <br />
     * &#160;&#160;'action' => (str) $action, <br />
     * &#160;&#160;'id' => (int) $id<br />
     * )
     */
    public $page;
    
    protected $langFile = false;

    /*
     * Prepares all the variables due to the url
     */
    public function construct(){
        if(!isset($_SESSION)){
            session_start();
        }
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }

        // Sets the main variables
        $this->domain = $_SERVER['SERVER_NAME'];
        $this->pathinfo = pathInfo($_SERVER['REQUEST_URI']);
        if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on'){
            $this->protocol='https';
        }else{
            $this->protocol='http';
        }
        $request = $_SERVER['REQUEST_URI'];
        $uriParts = explode('?',$request);
        $this->uri = $uriParts[0];

        $this->baseUri = $this->protocol.'://'.$this->domain;
        $this->url = $this->baseUri.$request;
        $this->thisLink = $request;

        $this->parsed_url = parse_url($this->url);
        parse_str(
            $this->parsed_url['query'],
            $this->parsed_url['parsed_query']
        );

        // If we're not waiting for an html file :
        if(isset($_GET['path_type']) && $_GET['path_type'] == 'favicon'){
            // We are asking for the favicon image.
            $this->page = array(
                'element'=>'favicon',
                'action'=>'get'
            );
        }elseif(isset($_GET['path_type']) && $_GET['path_type'] == 'css'){
            // We are asking for a CSS file.
            $this->page = array(
                'element'=>'css',
                'action'=>'get'
            );
            sh_cache::content_is_css();;
        }elseif(isset($_GET['path_type']) && $_GET['path_type'] == 'image'){
            // We are asking for an image
            if(isset($_GET['width']) && isset($_GET['height'])){
                $_GET['file'] .= '.resized.'.$_GET['width'].'.'.$_GET['height'].'.png';
            }elseif(isset($_GET['width'])){
                $_GET['file'] .= '.resizedX.'.$_GET['width'].'.png';
            }elseif($_GET['file'] != 'createPreview' && isset($_GET['height'])){
                //echo '<div><span class="bold">$_GET : </span>'.nl2br( htmlentities( print_r( $_GET, true ) ) ).'</div>';exit;
                $_GET['file'] .= '.resizedY.'.$_GET['height'].'.png';
            }
            $this->page = array(
                'element'=>'images',
                'action'=>'get'
            );
        }elseif(isset($_GET['path_type']) && $_GET['path_type'] == 'menuImage'){
            // We are asking for an image
            $this->page = array(
                'element'=>'images',
                'action'=>'get_menuImage'
            );
        }elseif(isset($_GET['path_type']) && $_GET['path_type'] == 'browser'){
            // We are asking for a browser
            $this->page = array(
                'element'=>'browser',
                'action'=>'show'
            );
        }elseif(isset($_GET['path_type']) && $_GET['path_type'] == 'mp3'){
            // We are loading a sound
            sh_cache::disable();
            if(file_exists(SH_SITE_FOLDER.'sh_mp3/'.$_GET['folder'].$_GET['file'])){
                header('Content-type: audio/mpeg');
                readfile(SH_SITE_FOLDER.'sh_mp3/'.$_GET['file']);
                exit;
            }
            $this->error(404);
        }else{
            // Updates the session's history variable, if needed
            if(!isset($_SESSION['history'])){
                $_SESSION['history'] = array();
            }
            if($_SESSION['history'][0] != urldecode($request)){
                array_unshift($_SESSION['history'],urldecode($request));
            }
            if(count($_SESSION['history']) > 10){
                array_pop($_SESSION['history']);
            }

            // If we don't find the url in the db, we send a 404 error
            $data = $this->getPage($this->uri);
            if(!($data)){
                $this->error(404);
            }

            $this->thisPage = $data;
            $parts = explode('/',$data);

            // Set the $page var with every usefull things
            $this->page = array(
                'element'=>$parts[0],
                'action'=>$parts[1],
                'id'=>$parts[2],
                'page'=>$data
            );
            $this->linker->html->addToBody('class', 'pages_'.$this->page['element']);
            $this->linker->html->addToBody('class', 'pages_'.$this->page['element'].'_'.$this->page['action']);
            $this->linker->html->addToBody('class', 'pages_'.$this->page['element'].'_'.$this->page['action'].'_'.$this->page['id']);
            
        }
        if(SH_MASTERSERVER && !SH_MASTERISUSER){
            // We check if the page that is called may be called on a master server
            if(!$this->linker->masterServer->isPathAllowed($this->linker->cleanObjectName($parts[0]),$parts[1])){
                header('HTTP/1.1 403 Forbidden');
                echo 'ERROR : 403';
                exit;
            }
        }
    }
    
    /**
     * 
     */
    public function getBaseUri(){
        return $this->baseUri;
    }

    /**
     * public function getDomain
     */
    public function getDomain(){
        return $this->domain;
    }

    /**
     * Returns the $num's last page that was called previously (only the 10 last are stored)
     * @param int $num
     * @return str
     */
    public function getHistory($num){
        return $_SESSION['history'][$num];
    }

    /**
     * Removes $numberToRemove entries from the history, and returns them
     * @param int $numberToRemove The number of entries to remove
     * @param bool $fromBeginning If <b>true</b> (default behaviour), will remove the entries from the beginning.
     * If <b>false</b>, will remove them from the end.
     * @return array The entries that were removed
     */
    public function removeFromHistory($numberToRemove,$fromBeginning = true){
        $rep = array();
        if($fromBeginning){
            for($a = 0;$a<$numberToRemove;$a++){
                $rep[] = array_shift($_SESSION['history']);
            }
        }else{
            for($a = 0;$a<$numberToRemove;$a++){
                $rep[] = array_pop($_SESSION['history']);
            }
        }
        return $rep;
    }

    /**
     * public function changeToRealFolder
     *
     */
    public function changeToRealFolder($askedFolder,$file = '',$buttonType = ''){
        $templateFolder = $this->linker->site->templateFolder;
        $variation = $this->linker->site->variation;
        $saturation = $this->linker->site->saturation;
        if(substr($askedFolder,0,strlen(SH_SHAREDIMAGES_PATH)) == SH_SHAREDIMAGES_PATH){
            // We allow the templates to replace common icons with custom ones
            //which may be found in /templates/[template]/images/[path_to_the_image_from_sharedimagefolder]
            $rewritedImage = str_replace(
                SH_SHAREDIMAGES_PATH,
                $templateFolder.'images/',
                $askedFolder
            );
            if(file_exists($rewritedImage.$file)){
                return $rewritedImage;
            }
        }
        // We replace the path using some regexp
        $replace = array(
            '`/images/template/variation/`',
            '`/images/template/`',
            '`/images/templates/((sh_|cm_)[0-9]+-.+)/`',
            '`/images/site`',
            '`/images/shared`',
            '`/images/temp`',
            '`/images/builder/`'
        );
        $with = array(
            $templateFolder.'images/variations/'.$variation.'_'.$saturation.'/',
            $templateFolder.'images/',
            SH_TEMPLATE_FOLDER.'$1/images/',
            SH_IMAGES_FOLDER,
            SH_SHAREDIMAGES_FOLDER,
            SH_TEMP_FOLDER,
            SH_BUILDER_FOLDER.$buttonType.'/variations/'.$variation.'/'
        );
        $ret = preg_replace($replace,$with,$askedFolder);

        if(substr($ret,0,strlen(SH_ROOT_FOLDER)) != SH_ROOT_FOLDER){
            // We should try to replace the root path by the root folder
            $ret = preg_replace('`^('.SH_ROOT_PATH.')(.*)$`', SH_ROOT_FOLDER.'$2', $ret);
        }

        // We clean the folder name (remove double slashes)
        return str_replace('//','/',$ret);
    }

    /**
     * public function changeToShortFolder
     *
     */
    public function changeToShortFolder($askedFolder){
        $templateFolder = $this->linker->site->templateFolder;
        $variation = $this->linker->site->variation;
        $askedFolder = str_replace('//','/',$askedFolder);
        
        $replace = array(
            $templateFolder.'images/variations/'.$variation.'/',
            $templateFolder.'images/',
            SH_TEMPLATE_FOLDER.'[TYPE]/images/',
            SH_IMAGES_FOLDER,
            SH_SHAREDIMAGES_FOLDER,
            SH_TEMP_FOLDER,
            SH_TEMPLATE_BUILDER.'[TYPE]/variations/'.$variation,
            SH_ROOT_FOLDER
        );
        $with = array(
            '/images/template/variation/',
            '/images/template/',
            '/images/templates/',
            '/images/site/',
            '/images/shared/',
            '/images/temp/',
            '/images/generated/',
            SH_ROOT_PATH
        );
        $ret = str_replace($replace,$with,$askedFolder);
        
        return str_replace('//','/',$ret);
    }

    /**
     * Refreshes the page. Synonym of $linker->path->redirect();
     */
    public function refresh(){
        $this->redirect();
    }

    /**
     * Redirects to a given page, or refreshes if none is given. This function tries
     * redirecting using headers if the headers haven't been sent, and using
     * javascript if they have.
     * @param str $urlOrClass The url we want to access, the string "refresh" to refresh the active
     * page, or the class name if $method is set. <br />
     * Defaults to "refresh".
     * @param str $method If $method is set, this function will act differently:
     * The first param is the class, the second the method, and eventually the third
     * the id of the page we want to access.
     * @param int $id See $method.
     */
    public function redirect($urlOrClass = 'refresh', $method = null, $id = null){
        if(!is_null($method)){
            // In that case, we were given a class name, method name and eventually an id
            // instead of a pre-built url
            if(!empty($urlOrClass)){
                $class = $urlOrClass;
            }else{
                $class = $this->linker->$url->getClassName(true);
            }
            $page = $class.'/'.$method.'/'.$id;
            $urlOrClass = $this->getLink($page);
        }
        if($urlOrClass == 'refresh'){
            $urlOrClass = $this->url;
        }
        if (!headers_sent()){
            header('Location: '.$urlOrClass);
            exit;
        }else{
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$urlOrClass.'";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="0;url='.$urlOrClass.'" />';
            echo '</noscript>';
            exit;
        }
    }

    /**
     * Redirects to the error page given as parametter
     * @param int $type Index of the error (eg: 404 for Not Found).
     */
    public function error($type){
        $this->linker->error->prepare();
        // We first try to lauch the event, if it is a 403 or a 404 error
        if($type == 403){
            $this->linker->events->onError403();
        }elseif($type == 404){
            $this->linker->events->onError404();
        }
        if(!headers_sent()){
            header('location: '. $this->getLink('error/show/'.$type), true, $type);
        }
        $this->redirect($this->getLink('error/show/'.$type));
        exit;
    }

    /**
     * public static function staticGetShortName
     */
    public static function staticGetUnicId(){
        $server = $_SERVER['SERVER_NAME'];
        $page = $_SERVER['REQUEST_URI'];
        
        $mobile = '';
        if($_SESSION['SH_SESSION_ISMOBILE']){
            $mobile = '_mobile';
        }

        $get = $_GET;
        if(isset($get['submitImage'])){
            unset($get[$get['submitImage'].'_x']);
            unset($get[$get['submitImage'].'_y']);
            unset($get['submitImage']);
        }
        $getArgs = print_r($get,true);

        if(preg_match('`(.*/[0-9]+)-[^/]*?\.php`', $page,$match)){
            $page = $match[1].'.php';
        }
        $_SESSION['debug'] = "MD5(__CLASS__.$server.$page.$getArgs).$mobile";
        return MD5(__CLASS__.$server.$page.$getArgs).$mobile;
    }

    /**
     * Gets a page formatted as [class]/[function]/[id of the page] using its uri,
     * or the actual page.
     * @param string $uri The uri we want to translate as path, or empty string for actual page.
     * @return string The page formatted
     */
    public function getPage($uri = ''){
        if($uri == ''){
            return $this->thisPage;
        }
        // Removing the first slash
        if(substr($uri,0,1) == '/'){
            $formattedUri = substr($uri,1);
        }else{
            $formattedUri = $uri;
        }

        if(preg_match('`/([^/]+)/([^/]+)(/([0-9]+)(-[^/]+)?)?\.php`',$uri,$matches)){
            list(,$class,$method,,$id) = $matches;
            if($this->linker->method_exists($class,$method)){
                if(in_array($method,$this->linker->$class->callWithoutId)){
                    return $class.'/'.$method.'/';
                 }elseif(in_array($method,$this->linker->$class->callWithId)){
                    return $class.'/'.$method.'/'.$id;
                }
            }
        }
        
        $class = $this->helper->getRealClassName(
            trim(
                array_shift(
                    explode('/',$formattedUri)
                )
            )
        );
        if($class !== false){
            $page = $this->linker->$class->translateUriToPage($uri);
            if($page !== false){
                return $page;
            }
        }else{
            $classes = $this->get_shared_methods();
            foreach($classes as $class){
                $page = $this->linker->$class->translateUriToPage($uri);
                if($page !== false){
                    return $page;
                }
            }
        }

        if(preg_match('`/?(google[a-f0-9]{16}\.html)`',$uri,$matches)){
            $googleForWebmasters = sh_site::GOOGLE_FOR_WEBMASTERS;
            $_SESSION[$googleForWebmasters] = $matches[1];
            return 'site/'.$googleForWebmasters.'/';
        }
        if(preg_match('`/?sitemap\.xml`',$uri,$matches)){
            return 'sitemap/show/';
        }
        return false;

        list($rep) = $this->db_execute('getPageByUri',array('uri'=>$uri));
        if(!isset($rep['page']) && preg_match('`(.*/)([0-9]+)(-[^/]*)?\.php`', $uri, $results)){
            list($rep) = $this->db_execute('getPageByUri',array('uri'=>$results[1].'[ID]'),$qry);
            if($rep['page'] != ''){
                $rep['page'].= $results[2];
            }
        }

        return $rep['page'];
    }

    /**
     * Synonym to getLink();
     * @see sh_path::getLink
     */
    public function getUri($page = '',$desc=''){
        return $this->getLink($page,$desc);
    }

    public function cleanUri($uri){
        $clean = preg_replace(
            array(
                '` +`',
                '`[àâ]`',
                '`[éèê]`',
                '`îï`',
                '`ûüù`',
                '`[^a-zA-Z0-9._()?&/-]`',
                '`\?`',
                '`%2F`i'
            ),
            array(
                '_',
                'a',
                'e',
                'i',
                'u',
                '_',
                '',
                '_'
            ),
            $uri
        );
        return $clean;
    }

    /**
     * Translates a page (class + method [+ id]) to a uri
     * @param str $page The page to translate (like "content/show/1").
     * @param str $desc Optionnal - A string containing an additional description that will be
     * urlencoded and added at the end of the url.
     * @return str The link
     */
    public function getLink($page='',$desc=''){
        $this->debug(__FUNCTION__.'('.$page.',"'.$desc.'")',3,__LINE__);
        if($page == ''){
            return $this->thisLink;
        }

        list($class,$method,$id) = explode('/',$page);
        $shortClassName = $this->helper->getShortClassName($class);
        if($this->linker->method_exists($class,$method)){
            if(in_array($method,$this->linker->$class->callWithoutId)){
                return '/'.$shortClassName.'/'.$method.'.php';
             }elseif(in_array($method,$this->linker->$class->callWithId)){
                $name = $this->linker->$class->getPageName($method, $id, true);
                if(!empty($name)){
                    $name = '-'.urlencode(str_replace('/','_',$name));
                }
                return '/'.$shortClassName.'/'.$method.'/'.$id.$name.'.php';
            }
        }
        
        // Trying with the in-class uri translation
        if($this->helper->getRealClassName($class)){
            $uri = $this->cleanUri(
                $this->linker->$class->translatePageToUri($page)
            );
            if($uri !== false){
                return $uri;
            }
        }else{
            $classes = $this->get_shared_methods();
            foreach($classes as $class){
                $uri = $this->cleanUri(
                    $this->linker->$class->translatePageToUri($page)
                );
                if($uri !== false){
                    return $uri;
                }
            }
        }
        return false;
        // Using the database uri translation
        if($desc){
            $desc = '-'.urlencode(str_replace(' ','_',$desc));
        }
        list($rep) = $this->db_execute('getUriByPage',array('page'=>$page));
        if(!isset($rep['uri']) && preg_match('`^([^/]+/[^/]+/)([0-9]+)$`', $page, $results)){
            list($rep) = $this->db_execute('getUriByPage',array('page'=>$results[1]));
            if($rep['uri'] != ''){
                $rep['uri'] = str_replace('[ID]',$results[2],$rep['uri']);
                if(strlen($rep['reverse'])>1){
                    if(substr($rep['reverse'],0,strlen('db:')) == 'db:'){
                        list($table,$field,$id) = explode('|',substr($rep['reverse'],strlen('db:')));
                        list($rep2) = $this->db_execute('getUriByReverse',array(
                            'field' => $field,
                            'table' => $table,
                            'id' => $id,
                            'value'=>$results[2]));
                        if($rep2[$field] != ''){
                            $desc = '-'.urlencode(str_replace('/','_',$rep2[$field]));
                        }
                    }elseif(substr($rep['reverse'],0,strlen('fct:')) == 'fct:'){
                        list($class,$method) = explode('|',substr($rep['reverse'],strlen('fct:')));
                        $desc = '-'.urlencode($this->linker->$class->$method($results[2]));
                    }
                }
                $rep['uri'] .= $desc . '.php';
            }else{
                $rep = false;
            }
        }
        return $rep['uri'];
    }

    public function __tostring(){
        return get_class();
    }
}
