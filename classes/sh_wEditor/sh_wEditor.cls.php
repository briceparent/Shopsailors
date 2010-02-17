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
 * Class that creates WYSIWYG editors using tinyMCE.
 * It also may replace permalinks by calls to the flash objects.
 */
class sh_wEditor extends sh_core{
    protected $minimal = array('chooseLink'=>true,'addBlock'=>true,'image'=>true);
    const DEFAULT_TYPE = 'advanced';

    public function render_wEditor($attributes = array(),$content = ''){
        $singlePath = $this->getSinglePath();
        $this->links->html->addScript($singlePath.'tiny_mce/tiny_mce.js');
        $this->links->browser->insertScript();
        $lang = $this->links->i18n->getLang();
        $shortLang = substr($lang,0,strpos($lang,'_'));
        $this->links->html->addScript($singlePath.'all.js?lang='.$shortLang.'&#38;variation='.$this->links->site->variation);

        if(!isset($attributes['name'])){
            $attributes['name'] = 'wEditor';
        }
        if(isset($attributes['type'])){
            $attributes['class'] = 'tinyMCE_'.$attributes['type'].' '.$attributes['class'];
            unset($attributes['type']);
        }else{
            $attributes['class'] = 'tinyMCE_'.self::DEFAULT_TYPE.' '.$attributes['class'];
        }
        if(isset($attributes['content'])){
            $values['textarea']['content'] = $attributes['content'];
            unset($attributes['content']);
        }

        $args = '';
        foreach($attributes as $attributeName=>$attributeValue){
            if(strtolower($attributeName) != 'i18nclass'){
                $args = $attributeName.'="'.$attributeValue.'" '.$args;
            }
        }
        $values['textarea']['args'] = $args;

        return $this->render('wEditor',$values,false,false);
    }

    /**
     * Adds some css contents to the main.css file
     * @return str The css to add
     */
    public function addToMainCSS(){
        $file = $this->links->site->templateFolder.'/css/forum.css';
        if(file_exists($file)){
            return file_get_contents(
                $file
            );
        }else{
            return file_get_contents(
                $this->getSinglePath(true).'/forum.css'
            );
        }
    }

    public function addBlock(){
        echo '<table width="100"><tr><td>Block</td></tr><tr><td>Contenu</td></tr></table>';
    }

    public function image(){
        $datas['get']['folder'] = $_GET['folder'];
        echo $this->render('image',$datas,false,false);
        return true;
    }

    /**
     * public function chooseLink
     *
     */
    public function chooseLink(){
        $actualClass = $this->getParam('class');
        $actualAction = $this->getParam('action');
        $actualId = $this->getParam('id');

        $datas['classes'] = $this->links->helper->listLinks(
            $actualClass.'/'.$actualAction.'/'.$actualId,
            array(
                'templatesLister/show/*',
                'shop/showProduct/*'
            )
        );
        echo $this->render('link_chooser',$datas,false,false);
        return true;
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
