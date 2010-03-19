<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

/**
 * Class that is used to create the html page, and include the various elements, like
 * css or javascript files.
 * It is used only on pages that are not "minimal".
 * @package Shopsailors Core Classes
 */
class sh_html extends sh_core{
        protected $scripts = array();
        protected $textScript = '';
        protected $css = array();
        protected $textCSS = '';

        protected $title = '';
        protected $bodyData = array('style'=>'','class'=>'','id'=>'','js'=>'');
        protected $bodyContent = '';
        protected $afterBody = '';
        protected $otherContent = array();
        protected $basePath = '';
        protected $caching = false;
        protected $modules = array();
        protected $template = '';
        protected $variation = '';
        protected $templateName = '';
        protected $browserCacheEnabled = true;
        protected $siteName = '';
        protected $showingTitle = true;
        public static $willRender = false;
        public static $scriptsSent = false;

        const DEFAULTIMAGE = 'defaultImage/global_image.png';
        const DEFAULTLOGO = 'logoImage/global_logo.png';
        
        const SCRIPT_FIRST = 0;
        const SCRIPT_LAST = 1;

        public function construct(){

            $site = $this->linker->site;
            $this->templateName = $site->templateName;
            $this->template = $site->templateFolder;
            $this->variation = $site->variation;
            $this->title = $site->defaultTitle;
            $this->siteName = $site->siteName;

            $this->menusNumber = $this->linker->template->menusNumber;

            /*$this->addScript(SH_INCLUDE_FOLDER.'scriptaculous/lib/prototype.js');
            $this->addScript(SH_INCLUDE_FOLDER.'scriptaculous/src/scriptaculous.js');*/

            $this->headLine = $site->defaultHeadLine;
            $this->global_image = SH_IMAGES_FOLDER.self::DEFAULTIMAGE;
            $this->global_image_text = SH_IMAGES_PATH.self::DEFAULTIMAGE;
        }

           // HEAD PART
    /**
     * public function browserCache
     *
     */
        public function browserCache($status){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->browserCacheEnabled = $status;
        }

    /**
     * public function setHeadLine
     *
     */
        public function setHeadLine($meta){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->headLine = $meta;
        }

    /**
     * public function getHeadLine
     *
     */
        public function getHeadLine(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);

            $rep = $this->headLine;

            return $rep;
        }

    /**
     * public function setMetaDescription
     *
     */
        public function setMetaDescription($meta){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->metaDescription = $meta;
        }

    /**
     * public function getMetaDescription
     *
     */
        public function getMetaDescription(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            if($this->metaDescription != ''){
                return $this->metaDescription;
            }else{
                return $this->linker->site->metaDescription;
            }
        }

        public function setBase($newBase){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->base = $newBase;
        }

        public function showTitle($status = true){
            $this->showingTitle = $status;
        }

        public function setTitle($title,$after=''){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->title = $title.$after;
            //$this->linker->breadcrumbs->title = $title;
        }

        public function addScript($script,$position = self::SCRIPT_LAST){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            if(!in_array($script,$this->scripts)){
                $this->debug('We add the "'.$script.'" script', 2, __LINE__);
                if($position == self::SCRIPT_LAST){
                    $this->scripts[] = $script;
                }else{
                    array_unshift($this->scripts,$script);
                }
                return true;
            }
            $this->debug('We didn\'t add the "'.$script.'" script, because it was already loaded', 2, __LINE__);
            return true;
        }

        public function render_javascript($params,$contents){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            if(isset($params['when']) && $params['when']== 'onEnd'){
                $this->endingScripts .= $contents."\n";
                return true;
            }
            $this->addTextScript($contents);
            return true;
        }

        public function render_endJavascript($params,$contents){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->endingScripts .= $contents."\n";
            return true;
        }

        public function addTextScript($text){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->textScript.=$text."\n";
        }

        public function addTextCSS($text){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->textCSS .= $text."\n";
        }

        public function render_css($params,$contents){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->addTextCSS($contents);
            return true;
        }

        public function addCSS(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $css = func_get_args();
            for ($a = 0; $a < count($css); $a = $a + 2){
                if($css[$a] == ''){
                    $css[$a + 1] = $css[$a];
                }
                if(!in_array($css[$a], $this->css)){
                    if(strpos($css[$a],'/') === false){
                        if(file_exists($this->template.'css/'.$css[$a])){
                            $this->css[] = '/CSS/'.$this->variation.'/'.$css[$a];
                        }
                    }else{
                        $this->css[] = $css[$a];
                    }
                }
            }
        }

        public function getOneCSS($name){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            return $this->css[$name];
        }

        public function getBase(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            if($this->base==''){
                $base = $this->linker->path->protocol.'://'.$this->linker->path->getDomain().'/';
            }else{
                $base = $this->basePath;
            }
            return '<base href="'.$base.'" />';
        }

        public function getScripts($outCall = true){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            if(!$outCall){
                return '';
            }
            foreach($this->scripts as $script){
                $this->debug('Creating the <script tag for "'.$script.'"', 3, __LINE__);
                $ret .= '<script type="text/javascript" src="'.$script.'"></script>';
            }
            if($this->textScript != ''){
                $this->debug('Adding the text scripts', 3, __LINE__);
                $ret2 .= $this->textScript;
            }

            if($ret2!=''){
                $ret .= '<script type="text/javascript">';
                $ret.=$ret2;
                $ret .= '</script>';
            }
            return $ret;
        }
        public function getCSS(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            foreach($this->css as $css){
                $ret .= '<link rel="stylesheet" media="screen" type="text/css" href="'.$css.'" ></link>';
            }
            if(trim($this->textCSS) != ''){
                $ret .= '<style type="text/css">'.$this->textCSS.'</style>';
            }
            return $ret;
        }

        public function getTitle($head = false){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            if($this->title == sh_params::VALUE_NOT_SET){
                $rep = $this->linker->site->defaultTitle;
            }else{
                $rep = $this->title;
            }
            if($head){
                if($this->showingTitle){
                    return $rep;
                }else{
                    return '';
                }
            }
            return '<title>'.$this->siteName.' - '.$rep.'</title>';
        }

        public function getHead(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->replaceElements();
            $ret = '<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />';
            $ret .= $this->getTitle();
            $ret .= $this->getBase();
            if(!$this->browserCacheEnabled){
                $ret .= '<meta http-equiv="pragma" content="no-cache" />';
            }
            $ret .= '<meta name="description" content="'.$this->getMetaDescription().'" />';
            $ret .= '<link rel="shortcut icon" href="'.$this->linker->favicon->getPath().'"/>';
            $ret .= $this->getCSS();
            self::$scriptsSent = true;
            $ret .= $this->getScripts();
            return $ret;
        }

        protected function replaceElements(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            foreach($this->css as $key=>$css){
                $this->css[$key] = $this->fromRoot($this->replaceTemplateDir($css));
            }
            foreach($this->scripts as $key=>$script){
                $this->scripts[$key] = $this->fromRoot($this->replaceTemplateDir($script));
            }
        }

            // BODY PART
    /**
     * public function setGeneralImage
     *
     */
        public function setGeneralImage($image,$text = ''){
             $this->global_image = $image;
             $this->global_image_text = $text;
        }

        public function getBodyContent(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            return $this->bodyContent.'<br />';
        }

        public function getBodyData(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            if(strlen($this->bodyData['style'])>0){
                $ret=' style="'.$this->bodyData['style'].'"';
            }
            if(strlen($this->bodyData['class'])>0){
                $ret.=' class="'.$this->bodyData['class'].'"';
            }
            if(strlen($this->bodyData['id'])>0){
                $ret.=' id="'.$this->bodyData['id'].'"';
            }
            if(strlen($this->bodyData['onload'])>0){
                $ret.=' onload="'.$this->bodyData['onload'].'"';
            }
            if(strlen($this->bodyData['js'])>0){
                $ret.=' '.$this->bodyData['js'];
            }
            $ret = str_replace(array(';;','  '),array(';',' '),$ret);
            return $ret;
        }

        public function addToBody($type,$content){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            switch(strtolower($type)){
                case  'style':
                $this->bodyData['style'] .= $bodySeparators['style'].$content;
                $bodySeparators['style'] = ';';
                break;
                case 'class':
                $this->bodyData['class'] .= ' '.$content;
                break;
                case 'id':
                $this->bodyData['id'] .= ' '.$content;
                break;
                case 'onload':
                $this->bodyData['onload'] .= $bodySeparators['onLoad'].$content;
                $bodySeparators['onLoad'] = ';';
                break;
                default:
                $this->bodyData['js'].=' '.$type.'="'.$content.'"';
                break;
            }
        }

        public function addAfterBody($content){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->afterBody .= $content;
        }

        protected function getAfterBody(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            return $this->afterBody;
        }

            // TEMPLATES & VARIATIONS
    /**
     * public function getVariation
     *
     */
        public function getVariation(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            return $this->variation;
        }

    /**
     * public function getTemplateName
     *
     */
        public function getTemplateName(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            return $this->templateName;
        }

    /**
     * public function getTemplatePath
     *
     */
        public function getTemplatePath($variation = false){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            if(!$variation){
                return $this->template;
            }
            return $this->template.$this->variation.'/';
        }

     /**
     * public function replaceTemplateDir
     */
        public function replaceTemplateDir($dir){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $dir = str_replace('#TEMPLATE_DIR#',$this->template,$dir);
            return $dir;
        }

            // OTHER

    /**
     * public function onGeneralLogoChange
     *
     */
    public function onGeneralLogoChange($event, $folder){
        $files = scandir($folder);
        foreach($files as $file){
            if(substr($file,0,1) != '.' && $file != basename(self::DEFAULTLOGO)){
                $image = $file;
            }
        }
        if($image != ''){
            @unlink(SH_IMAGES_FOLDER.self::DEFAULTLOGO);
            rename($folder.'/'.$image,SH_IMAGES_FOLDER.self::DEFAULTLOGO);
        }
        return true;
    }

    /**
     * public function onGeneralImageChange
     *
     */
    public function onGeneralImageChange($event, $folder){
        $files = scandir($folder);
        foreach($files as $file){
            if(substr($file,0,1) != '.' && $file != basename(self::DEFAULTIMAGE)){
                $image = $file;
            }
        }
        if($image != ''){
            @unlink(SH_IMAGES_FOLDER.self::DEFAULTIMAGE);
            rename($folder.'/'.$image,SH_IMAGES_FOLDER.self::DEFAULTIMAGE);
        }
        return true;
    }

     /**
     * public function addModule
     */
        public function addModule($position,$title,$content){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $cpt = count($this->modules[$position]);
            $this -> modules[$position][$cpt]['moduleTitle'] = $title;
            $this -> modules[$position][$cpt]['moduleContent'] = $content;
        }

     /**
     * public function getModules
     */
        public function getModules($position,$model){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            if(is_array($this->modules[$position])){
                $modules['MODULES'] = $this->modules[$position];
                return $this->renderer->render($model,'',$modules);
            }
            return '';
        }

        public function createPopupLink($link,$content,$width = 250,$height = 200){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $link = $this->linker->path->getLink($link);
            if(substr($link,0,4)!='http' && !($link)){
                return $content;
            }
            $temp='<span class="falseLink" onclick="window.open(\''.$link.'\',\'Websailors\', config = \'height='.$height.', width='.$width.', toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no\')">';
            $temp.=$content.'</span>';
            return $temp;
        }

        public function createLink($link,$content,$opt=array()){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            if(substr($link,0,4) != 'http' && strtolower(substr($link,0,7)) != 'mailto:'){
                $link = $this->linker->path->getLink($link);
                if(!$link){
                    return $content;
                }
            }
            $temp='<a href="'.$link.'"';
            foreach ($opt as $optName=>$optValue){
                if($optame=="target"){
                    $temp.=' onclick="window.open(this.href,\''.$optValue.'\');return false;"';
                }else{
                    $temp.=' '.$optName.'="'.$optValue.'"';
                }
            }
            $temp.='>'.$content.'</a>';
            return $temp;
        }

        public function insert($content){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->bodyContent .= $content;
            if($this->caching){
                echo $content;
            }
        }

        public function cache($status=false){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            $this->caching=$status;
        }


        public function addOtherContent($content,$type=null){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            if(!isset($type)){
                $this->otherContent[]=$content;
            }else{
                $this->otherContent[$type]=$content;
            }
        }

        public function getOtherContent($type='all'){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            if($type=='all'){
                return implode("\n",$this->otherContent);
            }else{
                return $this->otherContent[$type];
            }
        }

        public function render(){
            $this->debug('function : '.__FUNCTION__, 2, __LINE__);
            self::$willRender = false;
            $this->linker->javascript->get(sh_javascript::SCRIPTACULOUS);
            $this->addCSS('main.css','MAIN','/templates/global/global.css','GLOBAL');

            $loadClasses = $this->linker->template->get('loadClasses',array());
            if(is_array($loadClasses)){
                foreach($loadClasses as $loadClass){
                    $this->linker->$loadClass;
                }
            }

            // Prepares the data for the output
            $this->bodyContent = $this->fromRoot(
                str_replace(
                    '#TEMPLATE_DIR#',
                    $this->template,
                    $this->bodyContent
                )
            );

            // Sets the variables to fill the output
            $data['body']['adminpanel'] = $this->linker->admin->get();

            $data['body']['searchEngine'] = $this->linker->searcher->get();

            $data['body']['headline'] = $this->getHeadLine();
            $data['language']['selector'] = $this->linker->i18n->getLanguageSelector();

            for($menuNumber = 1;$menuNumber <= $this->menusNumber; $menuNumber++){
                $data['body']['menu_'.$menuNumber] = $this->linker->menu->get($menuNumber);
            }
            $data['body']['title'] = $this->getTitle(true);
            $data['body']['content'] = $this->getBodyContent();
            $data['body']['global_image'] = str_replace(SH_ROOT_FOLDER,SH_ROOT_PATH,$this->global_image);
            $data['body']['global_image_text'] = $this->global_image_text;
            $data['body']['logo'] = $this->linker->site->logo;

            $data['body']['copyrights'] = $this->linker->legacy->getLegacyLine();

            $data['diaporamas']['display'] = $this->linker->site->diaporamaDisplay;
            $data['body']['otherContents'] = $this->getOtherContent();

            $data['body']['analytics'] = $this->linker->googleServices->getAnalytics(true);
            $data['body']['analytics'] .=
            '<script type="text/javascript">'.$this->endingScripts.'</script>';

            list($action,$link) = $this->linker->user->getConnectionLink();

            $data[$action]['link'] = $link;

            // These vars should be built just before rendering,
            // to be sure that they contain everything that is built
            $data['body']['data'] = $this->getBodyData();
            $data['body']['beginning'] = $this->getAfterBody();
            $data['head']['content'] = $this->getHead();

            // Renders the html document
            $pageContent = parent::render($this->template.'/template.rf.xml',$data,false,false);

            $cpt = 1;
            $pageContent = str_replace('<html ','<html xmlns="http://www.w3.org/1999/xhtml" ',$pageContent,$cpt);

            // xmlDeclaration
            $xmlTag = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
            // DocType
            $docType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";

            sh_cache::startCache();
            $pageContent = $xmlTag.$docType.$pageContent;
            echo $pageContent;
            echo sh_cache::stopCache();
        }

        public function __tostring(){
            return get_class();
        }
    }
