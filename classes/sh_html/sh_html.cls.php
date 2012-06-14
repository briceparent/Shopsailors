<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 */
if(!defined('SH_MARKER')) {
    header('location: directCallForbidden.php');
}

/**
 * Class that is used to create the html page, and include the various elements, like
 * css or javascript files.
 * It is used only on pages that are not "minimal".
 * @package Shopsailors Core Classes
 */
class sh_html extends sh_core {
    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );
    protected $scripts = array();
    protected $textScript = '';
    protected $css = array();
    protected $textCSS = '';

    protected $title = '';
    protected $metaProperties = array();
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

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->linker->renderer->add_render_tag('render_css',__CLASS__,'render_css');
            $this->linker->renderer->add_render_tag('render_js',__CLASS__,'render_javascript');
            $this->linker->renderer->add_render_tag('render_endjs',__CLASS__,'render_endJavascript');
            $this->linker->renderer->add_render_tag('render_table',__CLASS__,'render_table');
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }

        $site = $this->linker->site;
        $this->templateName = $site->templateName;
        $this->template = $site->templateFolder;
        $this->variation = $site->variation;
        $this->title = $site->defaultTitle;
        $this->siteName = $site->siteName;

        $this->menusNumber = $this->linker->template->menusNumber;

        $this->headLine = $site->defaultHeadLine;
        $this->global_image = SH_IMAGES_FOLDER.self::DEFAULTIMAGE;
        $this->global_image_text = SH_IMAGES_PATH.self::DEFAULTIMAGE;
    }

    // HEAD PART
    /**
     * public function browserCache
     *
     */
    public function browserCache($status) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->browserCacheEnabled = $status;
    }

    /**
     * public function setHeadLine
     *
     */
    public function setHeadLine($meta) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->headLine = $meta;
    }

    /**
     * public function getHeadLine
     *
     */
    public function getHeadLine() {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);

        $rep = $this->headLine;

        return $rep;
    }

    /**
     *
     */
    public function addMetaProperty($propertyName,$propertyValue) {
        $propertyName = trim($propertyName);
        if(!empty($propertyName)){
            $this->metaProperties[str_replace('"','\"',$propertyName)] = str_replace('"','\"',$propertyValue);
        }
    }

    protected function getMetaProperties(){
        $ret = '';
        foreach($this->metaProperties as $propertyName=>$propertyValue){
            $ret .= '<meta property="'.$propertyName.'" content="'.$propertyValue.'"/>';
        }
        return $ret;
    }

    /**
     * public function setMetaDescription
     *
     */
    public function setMetaDescription($meta) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->metaDescription = $this->helper->replaceSpecialChars(strip_tags($meta));
    }

    /**
     * public function getMetaDescription
     *
     */
    public function getMetaDescription() {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if($this->metaDescription != '') {
            return $this->metaDescription;
        }else {
            return $this->linker->site->metaDescription;
        }
    }

    public function setBase($newBase) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->base = $newBase;
    }

    public function showTitle($status = true) {
        $this->showingTitle = $status;
    }

    public function setTitle($title,$after='') {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->title = $title.$after;
        //$this->linker->breadcrumbs->title = $title;
    }

    public function addScript($script,$position = self::SCRIPT_LAST) {
        $this->debug('function : '.__FUNCTION__.'("'.$script.'")', 2, __LINE__);
        if(!in_array($script,$this->scripts)) {
            $this->debug('We add the "'.$script.'" script', 2, __LINE__);
            if($position == self::SCRIPT_LAST) {
                $this->scripts[] = $script;
            }else {
                array_unshift($this->scripts,$script);
            }
            return true;
        }
        $this->debug('We didn\'t add the "'.$script.'" script, because it was already loaded', 2, __LINE__);
        return true;
    }

    public function render_javascript($params,$contents) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if(isset($params['when']) && $params['when']== 'onEnd') {
            $this->endingScripts .= $contents."\n";
            return true;
        }
        $this->addTextScript($contents);
        return true;
    }

    public function render_endJavascript($params,$contents) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->endingScripts .= $contents."\n";
        return true;
    }

    public function addTextScript($text) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->textScript.=$text."\n";
    }

    public function addTextCSS($text) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->textCSS .= $text."\n";
    }

    public function render_css($params,$contents) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->addTextCSS($contents);
        return true;
    }

    public function addCSS() {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $css = func_get_args();
        for ($a = 0; $a < count($css); $a = $a + 2) {
            if($css[$a] == '') {
                $css[$a + 1] = $css[$a];
            }
            if(!in_array($css[$a], $this->css)) {
                if(strpos($css[$a],'/') === false) {
                    if(file_exists($this->template.'css/'.$css[$a])) {
                        $this->css[] = '/CSS/'.$this->variation.'/'.$css[$a];
                    }
                }else {
                    $this->css[] = $css[$a];
                }
            }
        }
    }

    public function getOneCSS($name) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        return $this->css[$name];
    }

    public function getBase() {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if($this->base=='') {
            $base = $this->linker->path->protocol.'://'.$this->linker->path->getDomain().'/';
        }else {
            $base = $this->basePath;
        }
        return '<base href="'.$base.'" />';
    }

    public function getScripts($outCall = true) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if(!$outCall) {
            return '';
        }
        foreach($this->scripts as $script) {
            $this->debug('Creating the <script tag for "'.$script.'"', 3, __LINE__);
            $ret .= '<script type="text/javascript" src="'.$script.'"></script>';
        }
        if($this->textScript != '') {
            $this->debug('Adding the text scripts', 3, __LINE__);
            $ret2 .= $this->textScript;
        }

        if($ret2!='') {
            $ret .= '<script type="text/javascript">';
            $ret.=$ret2;
            $ret .= '</script>';
        }
        return $ret;
    }
    public function getCSS() {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);

        $suffix = $this->linker->site->images_suffix;

        foreach($this->css as $css) {
            if(strpos('?',$css) > 0) {
                $add = '&'.$suffix;
            }else {
                $add = '?'.$suffix;
            }
            $ret .= '<link rel="stylesheet" media="screen" type="text/css" href="'.$css.$add.'" ></link>';
        }
        if(trim($this->textCSS) != '') {
            $ret .= '<style type="text/css">'.$this->textCSS.'</style>';
        }
        return $ret;
    }

    public function getTitle($head = false) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if($this->title == sh_params::VALUE_NOT_SET) {
            $rep = $this->linker->site->defaultTitle;
        }else {
            $rep = $this->title;
        }
        if($head) {
            if($this->showingTitle) {
                return $rep;
            }
        }
        if(empty($this->metaTitle) || empty($rep)) {
            return '<title>'.$this->siteName.' - '.$this->metaTitle.$rep.'</title>';
        }
        return '<title>'.$this->siteName.' - '.$rep.' - '.$this->metaTitle.'</title>';
    }

    public function setMetaTitle($meta) {
        if(empty($this->metaTitle) || empty($meta)) {
            $this->metaTitle .= strip_tags($meta);
        }else {
            $this->metaTitle .= ' - '.strip_tags($meta);
        }
    }

    public function getHead($mobile = false) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->replaceElements();
        $ret = '<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />';
        $ret .= $this->getTitle();
        $ret .= $this->getBase();
        if(!$this->browserCacheEnabled) {
            $ret .= '<meta http-equiv="pragma" content="no-cache" />';
        }
        $ret .= '<meta name="description" content="'.$this->getMetaDescription().'" />';
        $ret .= '<link rel="shortcut icon" href="'.$this->linker->favicon->getPath().'"/>';
        $ret .= $this->getMetaProperties();
        if(!$mobile) {
            $ret .= $this->getCSS();
            self::$scriptsSent = true;
            $ret .= $this->getScripts();
        }
        return $ret;
    }

    protected function replaceElements() {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        foreach($this->css as $key=>$css) {
            $this->css[$key] = $this->fromRoot($this->replaceTemplateDir($css));
        }
        foreach($this->scripts as $key=>$script) {
            $this->scripts[$key] = $this->fromRoot($this->replaceTemplateDir($script));
        }
    }

    // BODY PART
    /**
     * public function setGeneralImage
     *
     */
    public function setGeneralImage($image,$text = '') {
        $this->global_image = $image;
        $this->global_image_text = $text;
    }

    public function getBodyContent() {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        return '<div class="sh_bodyContent">'.$this->bodyContent.'</div>';
    }

    public function getBodyData() {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if(strlen($this->bodyData['style'])>0) {
            $ret=' style="'.$this->bodyData['style'].'"';
        }
        if(strlen($this->bodyData['class'])>0) {
            $ret.=' class="'.$this->bodyData['class'].'"';
        }
        if(strlen($this->bodyData['id'])>0) {
            $ret.=' id="'.$this->bodyData['id'].'"';
        }
        if(strlen($this->bodyData['onload'])>0) {
            $ret.=' onload="'.$this->bodyData['onload'].'"';
        }
        if(strlen($this->bodyData['js'])>0) {
            $ret.=' '.$this->bodyData['js'];
        }
        $ret = str_replace(array(';;','  '),array(';',' '),$ret);
        return $ret;
    }

    public function addToBody($type,$content) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        switch(strtolower($type)) {
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

    /**
     * This method allows a class to set special vars for the rendering of the template.
     * @param str $name The name of the variable (called by html:$name) if $contents is a string, or a part of
     * the var class (called by html_$name:[another_var]) if $contents is an array( which in this case is associative)
     * @param str|array $contents
     */
    public function addSpecialContents($name,$contents) {
        if(is_array($contents)) {
            $this->specialContents['html_'.$name] = $contents;
        }else {
            $this->specialContents['html'][$name] = $contents;
        }
    }

    public function addAfterBody($content) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->afterBody .= $content;
    }

    protected function getAfterBody() {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        return $this->afterBody;
    }

    // TEMPLATES & VARIATIONS
    /**
     * public function getVariation
     *
     */
    public function getVariation() {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        return $this->variation;
    }

    /**
     * public function getTemplateName
     *
     */
    public function getTemplateName() {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        return $this->templateName;
    }

    /**
     * public function getTemplatePath
     *
     */
    public function getTemplatePath($variation = false) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if(!$variation) {
            return $this->template;
        }
        return $this->template.$this->variation.'/';
    }

    /**
     * public function replaceTemplateDir
     */
    public function replaceTemplateDir($dir) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $dir = str_replace('#TEMPLATE_DIR#',$this->template,$dir);
        return $dir;
    }

    // OTHER

    /**
     * public function onGeneralLogoChange
     *
     */
    public function onGeneralLogoChange($event, $folder) {
        $files = scandir($folder);
        foreach($files as $file) {
            if(substr($file,0,1) != '.' && $file != basename(self::DEFAULTLOGO)) {
                $image = $file;
            }
        }
        if($image != '') {
            @unlink(SH_IMAGES_FOLDER.self::DEFAULTLOGO);
            rename($folder.'/'.$image,SH_IMAGES_FOLDER.self::DEFAULTLOGO);
        }
        return true;
    }

    /**
     * public function onGeneralImageChange
     *
     */
    public function onGeneralImageChange($event, $folder) {
        $files = scandir($folder);
        foreach($files as $file) {
            if(substr($file,0,1) != '.' && $file != basename(self::DEFAULTIMAGE)) {
                $image = $file;
            }
        }
        if($image != '') {
            @unlink(SH_IMAGES_FOLDER.self::DEFAULTIMAGE);
            rename($folder.'/'.$image,SH_IMAGES_FOLDER.self::DEFAULTIMAGE);
        }
        return true;
    }

    /**
     * public function addModule
     */
    public function addModule($position,$title,$content) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $cpt = count($this->modules[$position]);
        $this -> modules[$position][$cpt]['moduleTitle'] = $title;
        $this -> modules[$position][$cpt]['moduleContent'] = $content;
    }

    /**
     * public function getModules
     */
    public function getModules($position,$model) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if(is_array($this->modules[$position])) {
            $modules['MODULES'] = $this->modules[$position];
            return $this->renderer->render($model,'',$modules);
        }
        return '';
    }

    public function createPopupLink($link,$content,$width = 250,$height = 200) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $link = $this->linker->path->getLink($link);
        if(substr($link,0,4)!='http' && !($link)) {
            return $content;
        }
        $temp='<span class="falseLink" onclick="window.open(\''.$link.'\',\'Websailors\', config = \'height='.$height.', width='.$width.', toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, directories=no, status=no\')">';
        $temp.=$content.'</span>';
        return $temp;
    }

    public function createLink($link,$content,$opt=array()) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if(substr($link,0,4) != 'http' && strtolower(substr($link,0,7)) != 'mailto:') {
            $link = $this->linker->path->getLink($link);
            if(!$link) {
                return $content;
            }
        }
        $temp='<a href="'.$link.'"';
        foreach ($opt as $optName=>$optValue) {
            if($optame=="target") {
                $temp.=' onclick="window.open(this.href,\''.$optValue.'\');return false;"';
            }else {
                $temp.=' '.$optName.'="'.$optValue.'"';
            }
        }
        $temp.='>'.urldecode($content).'</a>';
        return $temp;
    }

    public function insert($content) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->bodyContent .= $content;
        if($this->caching) {
            echo $content;
        }
        return $content;
    }

    public function cache($status=false) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        $this->caching=$status;
    }


    public function addOtherContent($content,$type=null) {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if(!isset($type)) {
            $this->otherContent[]=$content;
        }else {
            $this->otherContent[$type]=$content;
        }
    }

    public function getOtherContent($type='all') {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        if($type=='all') {
            return implode("\n",$this->otherContent);
        }else {
            return $this->otherContent[$type];
        }
    }

    public function addMessage($message,$alert = true) {
        if($alert){
            $_SESSION[__CLASS__]['messages_alert'][]['content'] = $message;
        }else{
            $_SESSION[__CLASS__]['messages_normal'][]['content'] = $message;
        }
        return true;
    }
    
    public function useOtherTemplate($template){
        $this->specialTemplate = $template;
    }

    public function render() {
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        self::$willRender = false;

        // We check if there are classes to construct before managing the contents (as they may add some)
        $classes = $this->helper->getClassesSharedMethods(__CLASS__, 'construct');
        foreach($classes as $class){
            $tempClass = $this->linker->$class;
        }
        $tempClass = null;

        // We first prepare the common contents for mobile and complete versions
        $specialContentsClasses = $this->get_shared_methods('add_special_content');
        foreach($specialContentsClasses as $class){
            $this->linker->$class->add_special_content();
        }
        $data = $this->specialContents;
        $data['html']['isAdminPage'] = $this->helper->isAdminPage();
        if($data['html']['isAdminPage']){
            $data['html']['isAdminPage_class'] = 'this_is_an_admin_page';
        }else{
            $data['html']['isAdminPage_class'] = 'this_is_not_an_admin_page';
        }
        $data['body']['headline'] = $this->getHeadLine();
        $data['body']['title'] = $this->getTitle(true);
        $data['body']['content'] = '';
        $data['body']['beforeContent'] = '';
        if(!empty($_SESSION[__CLASS__]['messages_alert'])) {
            $values['messages'] = $_SESSION[__CLASS__]['messages_alert'];
            $data['body']['content'] .= parent::render('message_alert',$values,false,false);
            $data['body']['beforeContent'] .= parent::render('message_alert',$values,false,false);
            unset($_SESSION[__CLASS__]['messages_alert']);
        }
        if(!empty($_SESSION[__CLASS__]['messages_normal'])) {
            $values['messages'] = $_SESSION[__CLASS__]['messages_normal'];
            $data['body']['content'] .= parent::render('message_normal',$values,false,false);
            $data['body']['beforeContent'] .= parent::render('message_normal',$values,false,false);
            unset($_SESSION[__CLASS__]['messages_normal']);
        }
        $data['body']['onlyContent'] = $this->getBodyContent();
        $data['body']['content'] .= $data['body']['onlyContent'];
        $data['body']['logo'] = $this->linker->site->logo;
        $data['body']['copyrights'] = $this->linker->legacy->getLegacyLine();
        $data['body']['analytics'] = $this->linker->googleServices->getAnalytics(true);

        /*if($this->linker->session->checkIfIsMobileDevice()) {
            // We render using a special template
            // We only get the first menu for the mobile template
            $menuNumber = 1;
            $data['menu'] = $this->linker->menu->getForMobile($menuNumber);
            $data['language']['selector'] = $this->linker->i18n->getLanguageSelector(true);
            $data['head']['content'] = $this->getHead(true);
            // Sending to classic mode
            $uri = $this->linker->path->url;
            if(strpos($uri,'?') === false) {
                $linkerChar = '?';
            }else {
                $linkerChar = '&';
            }
            $data['mode']['classicLink'] = $uri.$linkerChar.'mode=classic';
            $variation = $this->linker->site->variation;
            $data['variation']['background'] = $this->getParam('variation_'.$variation);

            // Rendering
            $pageContent = parent::render('mobile_template',$data,false,false);
            $pageContent = str_replace('</body>',$data['body']['adminpanel'].$data['body']['analytics'].'</body>',$pageContent);
            $pageContent = $this->linker->renderer->toHtml($pageContent);

            $docType = '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.1//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile11.dtd">'."\n";

        }else {/**/
            // We render the complete version
            $this->linker->javascript->get(sh_javascript::SCRIPTACULOUS);
            $this->linker->javascript->get(sh_javascript::POPUPS);
            $this->linker->javascript->get(sh_javascript::WINDOW);
            $this->addCSS('main.css','MAIN','/templates/global/global.css','GLOBAL');
            $data['language']['selector'] = $this->linker->i18n->getLanguageSelector();


            $loadClasses = $this->linker->template->get('loadClasses',array());
            if(is_array($loadClasses)) {
                foreach($loadClasses as $loadClass) {
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

            for($menuNumber = 1;$menuNumber <= $this->menusNumber; $menuNumber++) {
                $data['body']['menu_'.$menuNumber] = $this->linker->menu->get($menuNumber);
            }
            $data['body']['global_image'] = str_replace(SH_ROOT_FOLDER,SH_ROOT_PATH,$this->global_image);
            $data['body']['global_image_text'] = $this->global_image_text;


            $data['diaporamas']['display'] = $this->linker->site->diaporamaDisplay;
            $data['body']['otherContents'] = $this->getOtherContent();
            $data['body']['otherContents'] .= '<script>$$(".toggle_next_element").each(function(el){el.next().addClassName("toggle_keep_hidden")});</script>';
            $data['body']['otherContents'] .= '<style>.toggle_keep_hidden{display:none;}</style>';

            //$data['body']['analytics'] = $this->linker->googleServices->getAnalytics(true);
            $ending = '';
            $data['body']['analytics'] .=
                '<script type="text/javascript">'.$this->endingScripts.'</script>';
                

            list($action,$link) = $this->linker->user->getConnectionLink();
            $data[$action]['link'] = $link;
            $data['profile']['link'] = $this->linker->user->getProfileLink();

            // These vars should be built just before rendering,
            // to be sure that they contain everything that is built
            $this->addToBody('class','shopsailors_navigator');
            $data['body']['data'] = $this->getBodyData();


            $data['body']['beginning'] = $this->getAfterBody();
            $data['head']['content'] = $this->getHead();

            // Renders the html document
            $template = $this->template.'/template.rf.xml';
            if(!empty($this->specialTemplate)){
                $template = $this->specialTemplate;
            }
            $pageContent = parent::render($template,$data,false,false);
            $pageContent = str_replace('</body>',$data['body']['adminpanel'].$data['body']['analytics'].'</body>',$pageContent);
            $pageContent = $this->linker->renderer->toHtml($pageContent);
            $docType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
        /*}/**/
        $cpt = 1;
        $pageContent = str_replace('<html ','<html xmlns="http://www.w3.org/1999/xhtml" ',$pageContent,$cpt);

        // xmlDeclaration
        $xmlTag = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
        // DocType


        $pageContent = $xmlTag.$docType.$pageContent;
        if($_SESSION['this_is_a_temp_session']){
            // We don't cache temp sessions pages
            $pageContent =preg_replace(
                array(
                    '`(src|href)="([^\?"]*)"`',
                    '`(src|href)="([^"]*)\?([^"]*)"`'
                ), 
                array(
                    '$1="$2?nothing_special=true"',
                    '$1="$2?$3&temp_session='.$_SESSION['temp_session'].'"'
                ), 
                $pageContent
            );
            echo $pageContent;
        }else{
            echo sh_cache::saveCache($pageContent);
        }
        
    }

    public function __tostring() {
        return get_class();
    }
}
