<?php
/**
 * @author Brice PARENT for Shopsailors
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
    public $uri;
    public $pathinfo;
    public $protocol;
    public $url;
    public $parsed_url;
    protected $domain;
    protected $baseUri;
    protected $thisPage;
    public $page;
    protected $langFile = false;

    /*
     * Prepares all the variables due to the url
     */
    public function construct(){
        if(!isset($_SESSION)){
            session_start();
        }

        // Sets the main variables
        $this->domain = $_SERVER['SERVER_NAME'];
        $this->pathinfo = pathInfo($_SERVER['REQUEST_URI']);
        if($_SERVER['https']){
            $this->protocol='https';
        }else{
            $this->protocol='http';
        }
        $request = $_SERVER['REQUEST_URI'];
        $uriParts = explode('?',$request);
        $this->uri = $uriParts[0];

        $this->baseUri = $this->protocol.'://'.$this->domain;
        $this->url = $this->baseUri.$request;

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
        }elseif(isset($_GET['path_type']) && $_GET['path_type'] == 'image'){
            // We are asking for an image
            $this->page = array(
                'element'=>'images',
                'action'=>'get'
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
/*echo $this->uri.' -> '.$data.'<br />';
exit;/**/

            if(!($data)){
                $this->error(404);
            }

            $this->thisPage = $data;
            $parts = explode('/',$data);

            list($category) = $this->db_execute('getCategoryByLink',array('link'=>$data));
            list($categoryName) = $this->db_execute('getCategoryInformations',array('category'=>$category['category']));
            //$categoryName = $this->db_unicReturn($query);
            // Set the $page var with every usefull things
            $this->page = array(
                'element'=>$parts[0],
                'action'=>$parts[1],
                'id'=>$parts[2],
                'page'=>$data,
                'category'=>$category['category'],
                'categoryName'=>$categoryName['title'],
                'categoryLink'=>$categoryName['link']
            );

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

    public function getHistory($num){
        return $_SESSION['history'][$num];
    }

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
            $templateFolder.'images/variations/'.$variation.'/',
            $templateFolder.'images/',
            SH_TEMPLATE_FOLDER.'$1/images/',
            SH_IMAGES_FOLDER,
            SH_SHAREDIMAGES_FOLDER,
            SH_TEMP_FOLDER,
            SH_BUILDER_FOLDER.$buttonType.'/variations/'.$variation.'/'
        );
        $ret = preg_replace($replace,$with,$askedFolder);

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
            SH_TEMPLATE_BUILDER.'[TYPE]/variations/'.$variation
        );
        $with = array(
            '/images/template/variation/',
            '/images/template/',
            '/images/templates/',
            '/images/site/',
            '/images/shared/',
            '/images/temp/',
            '/images/generated/'
        );
        $ret = str_replace($replace,$with,$askedFolder);
        
        return str_replace('//','/',$ret);
    }

    public function refresh(){
        $this->redirect();
    }

    /**
     * Redirects to a given page, or refreshes if none is given. This function tries
     * redirecting using headers if the headers haven't been sent, and using
     * javascript if they have.
     * @param str $url The url we want to access, or "refresh" to refresh the active
     * page. <br />
     * Defaults to "refresh".
     * @param str $method If $method is set, this function will act differently:
     * The first param is the class, the second the method, and eventually the third
     * the id of the page we want to access.
     * @param int $id See $method.
     */
    public function redirect($url = 'refresh', $method = null, $id = null){
        if(!is_null($method)){
            // In that case, we were given a class name, method name and eventually an id
            // instead of a pre-built url
            $page = $this->linker->$url->getClassName(true).'/'.$method.'/'.$id;
            $url = $this->getLink($page);
        }
        if($url == 'refresh'){
            $url = $this->url;
        }
        if (!headers_sent()){
            header('Location: '.$url);
            exit;
        }else{
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$url.'";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
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
        //array_shift($_SESSION['history']);
        if (!headers_sent()){
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
        return MD5(__CLASS__.$server.$page.$getArgs);
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
        if(substr($uri,0,1) == '/'){
            $formattedUri = substr($uri,1);
        }else{
            $formattedUri = $uri;
        }
        $class = $this->linker->helper->getRealClassName(
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
            $classes = scandir(SH_CLASS_SHARED_FOLDER.$this->className);
            foreach($classes as $oneClass){
                $class = $this->linker->helper->getRealClassName(
                    substr($oneClass,0,-4)
                );
                if($class !== false){
                    $page = $this->linker->$class->translateUriToPage($uri);
                    if($page !== false){
                        return $page;
                    }
                }
            }
        }

        if(preg_match('`/?(google[a-f0-9]{16}\.html)`',$uri,$matches)){
            $googleForWebmasters = sh_site::GOOGLE_FOR_WEBMASTERS;
            $_SESSION[$googleForWebmasters] = $matches[1];
            return 'site/'.$googleForWebmasters.'/';
        }

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
     * public function getUri
     *
     */
    public function getUri($page,$desc=''){
        return $this->getLink($page,$desc);
    }

    public function cleanUri($uri){
        return preg_replace(
            array(
                '` +`',
                '`[éèê]`',
                '`[àâ]`',
                '`û`',
                '`î`',
                '`[^a-zA-Z0-9._()?&/-]`',
                '`\?`'
            ),
            array(
                '_',
                'e',
                'a',
                'u',
                'i',
                '_',
                ''
            ),
            $uri
        );
    }

    public function getLink($page,$desc=''){
        // Trying with the in-class uri translation
        $class = array_shift(explode('/',$page));
        if($this->linker->helper->getRealClassName($class)){
            $uri = $this->cleanUri(
                $this->linker->$class->translatePageToUri($page)
            );
            if($uri !== false){
                return $uri;
            }
        }else{
            $classes = scandir(SH_CLASS_SHARED_FOLDER.$this->className);
            foreach($classes as $oneClass){
                $class = $this->linker->helper->getRealClassName(substr($oneClass,0,-4));
                if($class !== false){
                    $uri = $this->cleanUri(
                        $this->linker->$class->translatePageToUri($page)
                    );
                    if($uri !== false){
                        return $uri;
                    }
                }
            }
        }
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
                            $desc = '-'.urlencode(str_replace(' ','_',$rep2[$field]));
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
