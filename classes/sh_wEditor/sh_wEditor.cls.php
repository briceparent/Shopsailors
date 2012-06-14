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
 * Class that creates WYSIWYG editors using tinyMCE.
 * It also may replace permalinks by calls to the flash objects.
 */
class sh_wEditor extends sh_core{
    const CLASS_VERSION = '1.1.11.04.18';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );
    protected $minimal = array(
        'chooseLink'=>true,'addBlock'=>true,'image'=>true,'addStyle'=>true,'getStyles'=>true,
        'insertVideo'=>true,'insertDiaporama'=>true,'insertFlash'=>true,'chooseStyle'=>true,
        'getNewStyleId'=>true,'getStyle'=>true,'chooseCalendar'=>true
    );

    public $callWithId = array('updateStyle');
    public $callWithoutId = array(
        'addStyle','getStyles','getNewStyleId','getStyle','chooseCalendar'
    );
    const DEFAULT_TYPE = 'advanced';
    protected $rendering = false;
    protected $jsonStylesSent = false;

    public function construct(){
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            // The class datas are not in the same version as this file, or don't exist (installation)
            if(version_compare($installedVersion,'1.1.11.04.02') < 0){
                $this->helper->addClassesSharedMethods('sh_css', 'addToMainCSS', __CLASS__);
                $this->linker->renderer->add_render_tag('render_wEditor',__CLASS__,'render_wEditor');
            }
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
    }

    public function render_wEditor($attributes = array(),$content = ''){
        $this->rendering = true;
        $singlePath = $this->getSinglePath();
        $this->linker->html->addScript($singlePath.'tiny_mce/tiny_mce.js');
        $this->linker->browser->insertScript();
        $lang = $this->linker->i18n->getLang();
        $shortLang = substr($lang,0,strpos($lang,'_'));
        $this->linker->html->addScript($singlePath.'all.js?lang='.$shortLang.'&#38;variation='.$this->linker->site->variation);

        if(!isset($attributes['name'])){
            $attributes['name'] = 'wEditor';
        }
        if(isset($attributes['type']) && !empty($attributes['type'])){
            $attributes['class'] = 'tinyMCE_'.$attributes['type'].' '.$attributes['class'];
            if($attributes['type'] == 'minimal'){
                $isSimple = true;
            }elseif($attributes['type'] == 'forum'){
                $isSimple = true;
            }
            unset($attributes['type']);
        }else{
            $attributes['class'] = 'tinyMCE_'.self::DEFAULT_TYPE.' '.$attributes['class'];
            if(isset($attributes['type'])){
                unset($attributes['type']);
            }
        }
        if(isset($attributes['content'])){
            $values['textarea']['content'] = $attributes['content'];
            unset($attributes['content']);
        }else{
            $values['textarea']['content'] = $content;
        }
        if(empty($values['textarea']['content'])){
            $values['textarea']['content'] = '<p></p>';
        }

        $args = '';
        foreach($attributes as $attributeName=>$attributeValue){
            if(strtolower($attributeName) != 'i18nclass'){
                $args = $attributeName.'="'.$attributeValue.'" '.$args;
            }
        }
        $values['textarea']['name'] = $attributes['name'];

        $values['textarea']['args'] = $args;
        if(!$this->jsonStylesSent){
            $this->linker->html->addTextScript('tinymce_styles_json = '.$this->getStyles(false).';');
            $this->jsonStylesSent = true;
        }

        $stylesFile = SH_SITE_FOLDER.'/css/generatedStyles.css';
        if(!$isSimple && file_exists($stylesFile)){
            $values['styles']['created'] = file_get_contents($stylesFile);
        }

        $return = $this->render('wEditor',$values,false,false);
        $this->rendering = false;
        return $return;
    }

    public function form_verifier_content($data){
        $data = preg_replace('`(<style.*[^<]+</style>)`','',$data);
        if($data == '<p></p>'){
            $data = '';
        }
        return $data;
    }

    public function isRendering(){
        return $this->rendering;
    }

    /**
     * Adds some css contents to the main.css file
     * @return str The css to add
     */
    public function addToMainCSS(){
        if(file_exists(SH_SITE_FOLDER.'/css/generatedStyles.css')){
            return file_get_contents(SH_SITE_FOLDER.'/css/generatedStyles.css');
        }
        return '';
    }

    public function addBlock(){
        echo '<table width="100"><tr><td>Block</td></tr><tr><td>Contenu</td></tr></table>';
    }

    public function image(){
        $datas['get']['folder'] = $_GET['folder'];
        $datas['head']['tooltip'] = $this->linker->helpToolTips->getJavascript();
        echo $this->render('image',$datas,false,false);
        return true;
    }

    public function insertVideo(){
        $datas['head']['tooltip'] = $this->linker->helpToolTips->getJavascript();
        echo $this->render('insertVideo',$datas,false,false);
        return true;
    }

    public function insertDiaporama(){
        $datas = $this->linker->diaporama->getList(true);
        $datas['head']['tooltip'] = $this->linker->helpToolTips->getJavascript();
        echo $this->render('insertDiaporama',$datas,false,false);
        return true;
    }

    public function chooseStyle(){
        $values['styles'] = $this->getParam('css', array());
        $datas['head']['tooltip'] = $this->linker->helpToolTips->getJavascript();

        echo $this->render('style_chooser',$values,false,false);
        return true;
    }
    
    public function getStyle(){
        $this->onlyAdmin();
        $class = $_GET['class'];
        $params = $this->getParam('css>'.$class, array());
        $styles = explode(';',$params['css']);
        foreach($styles as $css){
            list($key,$value) = explode(':',$css);
            $style['css'][str_replace('-','_',trim($key))] = trim($value);
        }
        $style['name'] = $params['name'];
        
        echo json_encode($style);
    }
    
    public function getNewStyleId(){
        $this->onlyAdmin();
        $classes = $this->getParam('css',array());
        $max = count($classes);
        echo 'sh_gen_'.($max + 1);
        
    }

    public function addStyle(){
        $this->onlyAdmin();
        $this->setParam('css>'.$_POST['cssName'], $_POST);
        $this->writeParams();
        $classes = $this->getParam('css',array());
        $css = '';
        foreach($classes as $class){
            $css .= '.'.$class['cssName'].'{'.$class['css'].'}'."\n";
        }
        $cssFile = SH_SITE_FOLDER.'/css/generatedStyles.css';
        if(!is_dir(dirname($cssFile))){
            mkdir(dirname($cssFile));
        }
        $this->helper->writeInFile(
            $cssFile,
            $css
        );
        echo 'OK';
    }

    public function getStyles($echo = true){
        $classes = $this->getParam('css',array());
        $json = json_encode($classes, JSON_FORCE_OBJECT);
        if(empty($json)){
            $json = '[]';
        }
        if($echo){
            echo $json;
            return true;
        }
        return $json;
    }
    
    public function chooseCalendar(){
        $values['calendars'] = $this->linker->calendar->getList();
        
        echo $this->render('calendar_chooser', $values, false, false);
        return true;
    }

    /**
     * public function chooseLink
     *
     */
    public function chooseLink(){
        $datas['head']['tooltip'] = $this->linker->helpToolTips->getJavascript();
        $actualClass = $this->getParam('class');
        $actualAction = $this->getParam('action');
        $actualId = $this->getParam('id');

        $datas['classes'] = $this->helper->listLinks(
            $actualClass.'/'.$actualAction.'/'.$actualId,
            array(
                'templatesLister/show/*',
                'shop/showProduct/*'
            )
        );
        echo $this->render('link_chooser',$datas,false,false);
        return true;
    }
    
    public function insertFlash(){
        $this->onlyAdmin();
        $code = stripslashes($_POST['code']);
        $id = $this->linker->flash->save(0,$code);
        echo $id;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);
        if($method == 'chooseLink'){
            // To change this, we also have to do it in menuEditor.js
            return '/menu/chooseLink.php';
        }
        if($method == 'insertVideo'){
            // To change this, we also have to do it in menuEditor.js
            return '/menu/insertVideo.php';
        }
        if($method == 'insertDiaporama'){
            // To change this, we also have to do it in menuEditor.js
            return '/menu/insertDiaporama.php';
        }
        if($method == 'image'){
            // To change this, we also have to do it in menuEditor.js
            return '/menu/image.php';
        }
        if($method == 'addBlock'){
            // To change this, we also have to do it in menuEditor.js
            return '/menu/addBlock.php';
        }

        return parent::translatePageToUri($page);
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if($uri == '/menu/image.php'){
            // To change this, we also have to do it in menuEditor.js
            return 'menu/image/';
        }
        if($uri == '/menu/insertVideo.php'){
            // To change this, we also have to do it in menuEditor.js
            return 'menu/insertVideo/';
        }
        if($uri == '/menu/insertDiaporama.php'){
            // To change this, we also have to do it in menuEditor.js
            return 'menu/insertDiaporama/';
        }
        if($uri == '/menu/chooseLink.php'){
            // To change this, we also have to do it in menuEditor.js
            return 'menu/chooseLink/';
        }
        if($uri == '/menu/addBlock.php'){
            // To change this, we also have to do it in menuEditor.js
            return 'menu/addBlock/';
        }

        return parent::translateUriToPage($uri);
    }

    public function __tostring(){
        return get_class();
    }
}
