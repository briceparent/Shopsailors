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
 * Class that creates and shows the menus.
 */
class sh_menu extends sh_core{
    protected $minimal = array(
        'verifyLength'=>true, 'addEntry'=>true,'chooseLink'=>true,'createTextPreview'=>true
    );
    private $items = array();

    /*
     * Construction of the class
     */
    public function construct(){
        $this->linker->html->addCSS(__CLASS__.'.css',__CLASS__);
        return true;
    }

    /**
     * Cleaning old images from disk and db
     */
    public function reset(){
        // So the folder in which the images are is :
        $this->linker->helper->deleteDir(SH_GENERATEDIMAGES_FOLDER);
    }

    /**
     * Changes an element's sitemap priority.
     * @see sh_sitemap for more details
     * @param str $page The page name
     * @return int The sitemap level
     */
    public function changeSitemapPriority($page){
        $ret = $this->db_execute('isInMenu',array('link'=>$page));
        if(is_array($ret) && $this->getParam('sitemap>priority') != sh_params::VALUE_NOT_SET){
            return $this->getParam('sitemap>priority');
        }
        return 0;
    }

    /**
     * Verifies if the menu is not too large for the template
     * @param bool $booleanReturn
     * <ul><li>if <b>true</b>, will return true if the length is good, false if not</li>
     * <li>if <b>false</b>, will echo "OK" if the length is good, and an error
     * message if not</li></ul>
     * @return bool|str See $booleanReturn
     */
    public function verifyLength($booleanReturn = false){
        $id = $_POST['id'];

        $sectionsCount = $_POST['sectionsCount'];

        $maxMenuWidth = $this->linker->template->get(
            'menuButtons>'.$id.'>maxWidth',
            false
        );
        // If there is no maximum limit to the width, we always answer OK
        if($maxMenuWidth === false){
            if(!$booleanReturn){
                echo 'OK';
            }
            return true;
        }

        // If there is no langs, we always answer OK
        if(!is_array($_POST['categories'])){
            if(!$booleanReturn){
                echo 'OK';
            }
            return true;
        }

        $entriesIds = array_keys($_POST['categories']);
        $langs = array_keys($_POST['categories'][$entriesIds[0]]['name']);

        $defaultLang = $this->linker->site->lang;

        foreach($_POST['categories'] as $key=>$value){
            foreach($langs as $lang){
                if(!empty($value['name'][$lang])){
                    $texts[$lang][] = $value['name'][$lang];
                }else{
                    $texts[$lang][] = $value['name'][$defaultLang];
                }
            }
        }

        $font = SH_FONTS_FOLDER.$_POST['font'];

        // Reads all the parameters from the params file
        $type = $this->linker->template->get('menuButtons>'.$id.'>type');
        $templateVariation = $this->linker->html->getVariation();

        // do we have to expand the menu to fit the $menuWidth size?
        $expand = $this->linker->template->get(
            'menuButtons>'.$id.'>expand',
            1
        );
        $menuWidth = $this->linker->template->get(
            'menuButtons>'.$id.'>totalWidth|width',
            900
        );
        $textHeight = $_POST['textHeight'];

        $builder = $this->linker->imagesBuilder;

        $echo = 'OK';
        $addToEcho = '';
        foreach($texts as $oneLang=>$oneLangTexts){
            $box = $builder->getMultipleImagesBox(
                $oneLangTexts,
                $font,
                $textHeight,
                $type
            );

            $calculatedWidth = $box['width'];
            // Verifies if the total generated width isn't too big
            if($calculatedWidth > $menuWidth){
                $addToEcho .= '<div style="color:red">'.$this->getI18n('tooMuchText').$oneLang.'.</div>';
                $echo = '';
                if($booleanReturn){
                    return false;
                }
            }
        }
        if(!$booleanReturn){
            echo $echo.$addToEcho;
        }
        return true;
    }

    /**
     * public function chooseLink
     *
     */
    public function chooseLink(){
        $datas['classes'] = $this->linker->helper->listLinks(
            $_GET['value']
        );
        $datas['category']['id'] = $_GET['id'];

        echo $this->render('link_chooser',$datas,false,false);
        return true;
    }

    /**
     * Saves the menu
     */
    protected function updateDB(){
        $id = (int) $this->linker->path->page['id'];

        $this->setParam('activated>'.$id, isset($_POST['menuState']));

        $sectionsCount = $_POST['sectionsCount'];

        $maxMenuWidth = $this->linker->template->get(
            'menuButtons>'.$id.'>maxWidth',false
        );
        $oneCategory = current($_POST['categories']);
        $langs = array_keys($oneCategory['name']);

        $defaultLang = $this->linker->site->lang;

        // Reads all the parameters from the params file and POST datas
        $textHeight = $_POST['textHeight'];
        $this->setParam('textHeight>'.$id, $textHeight);
        
        $this->writeParams();

        $type = $this->linker->template->get('menuButtons>'.$id.'>type');
        $templateVariation = $this->linker->html->getVariation();

        $images = $this->linker->images;
        $site = $this->linker->site;
        $variation = $site->variation;

        // Sets the font to use
        $fonts = $this->linker->template->fonts;
        if(in_array($_POST['font'],$fonts)){
            $this->linker->template->setMenuFont($id, $_POST['font']);
        }
        $font = $this->linker->template->get('menuButtons>'.$id.'>font');
        $font = SH_FONTS_FOLDER.$font;

        $path = 'menu_'.$id.'/';

        // Removes all images from a folder on the disk and in the db
        $this->linker->helper->deleteDir(SH_GENERATEDIMAGES_FOLDER.$path);
        $images->removeOneFolder(SH_GENERATEDIMAGES_PATH.$path);

        // Creates the images builder
        $imagesBuilder = $this->linker->imagesBuilder;

        // We remove from the i18n the old i18n menu entries
        $oldI18nEntries = $this->db_execute(
            'getMenusI18nsByMenuId',
            array('menuId'=>$id)
        );

        $this->db_execute('deleteMenuItems',array('menuId'=>$id));

        if(is_array($oldI18nEntries)){
            foreach($oldI18nEntries as $oldI18nEntry){
                $this->removeI18n($oldI18nEntry['title']);
            }
        }

        foreach($_POST['categories'] as $key=>$value){
             // Saves the texts in the i18n db
            $i18nIds[$key] = $this->setI18n(0, $value['name']);
            $menuLinks[$key] = $value['link'];
        }
        $cpt2 = 0;
        foreach($langs as $lang){
            $cpt = 0;
            foreach($i18nIds as $key => $oneI18nId){
                $menuEntries[$cpt2][$cpt] = $this->getI18n($oneI18nId, $lang);
                $texts[$lang][] = $menuEntries[$cpt2][$cpt];
                $cpt++;
            }
            $cpt2++;

            $cpt = 0;
            $box = $imagesBuilder->getMultipleImagesBox(
                $texts[$lang],
                $font,
                $textHeight,
                $type
            );
            // If needed, we expand the buttons to fit the total width
            if($this->linker->template->get('menuButtons>'.$id.'>expand')){
                $totalWidth = $this->linker->template->get(
                    'menuButtons>'.$id.'>totalWidth',900
                );
                $dispatch[$lang] = $totalWidth - $box['width'];
                $width = floor($dispatch[$lang] / count($texts[$lang]));
                $add1To = $dispatch[$lang] % count($texts[$lang]);
            }else{
                $add1To = 0;
                $width = 0;
            }
            foreach($box['images'] as $image){
                if(($add1To--)>0){
                    $add1 = 1;
                }else{
                    $add1 = 0;
                }
                $filename = SH_GENERATEDIMAGES_PATH.'menu_'.$id.'/'.$lang.'/image_'.$cpt;
                $generatedImages[$cpt] = $images->prepare(
                    $image['text'],                             // text
                    $font,                                      // font
                    $box['fontSize'],                           // fontsize
                    $filename,                                  // path
                    $type,                                      // type
                    $image['position'],                         // position
                    true,                                       // has3States
                    $image['width'] + $width + $add1,           // width
                    $box['height'],                             // height
                    $image['startX'] + ($width / 2),            // startX
                    $image['startY']                            // startY
                );
                $lastLang = $lang;
                $cpt++;
            }
        }
        foreach($generatedImages as $oneGeneratedImage){
            $image = str_replace(
                '/'.$lang.'/',
                '/'.sh_images::LANG_DIR,
                $oneGeneratedImage
            );
            // Adds the menu entry
            $this->db_execute(
                'addMenuItem',
                array(
                    'menuId'=>$id,
                    'category'=>$cpt++,
                    'link'=>array_shift($menuLinks),
                    'title'=>array_shift($i18nIds),
                    'position'=>0,
                    'image'=>$image
                )
            );
        }
    }

    /**
     * Edits a menu
     * @return bool always returns true
     */
    public function edit(){
        $this->linker->admin->onlyAdmin();
        $id = (int) $this->linker->path->page['id'];
        if($this->formSubmitted('menuEditor')){
            // We verify another time that all is good in the form
            if($this->verifyLength(true)){
                $this->updateDB();
            }
        }

        if(isset($_SESSION[__CLASS__]['addEntry'])){
            foreach($_SESSION[__CLASS__]['addEntry'] as $id){
                list($max) = $this->db_execute(
                    'getNewElementPosition',
                    array('menuId' => $id)
                );
                $newCategory = $max['max'] + 1;

                $this->db_execute(
                    'insertElement',
                    array(
                        'menuId' => $id,
                        'link'=>'',
                        'category'=> $newCategory,
                        'position'=>'0',
                        'title'=>$this->getI18n('newMenu'),
                        'image'=>''
                    ),
                    $qry
                );
            }
            unset($_SESSION[__CLASS__]['addEntry']);
        }

        //loads the external files (JS & CSS)
        $this->linker->html->addScript('/sh_menu/singles/menuEditor.js');
        $this->linker->html->addToBody('onLoad', 'createSortables();');

        // Reads button type's params to get the available fonts
        $type = $this->linker->template->get('menuButtons>'.$id.'>type');

        $_SESSION[__CLASS__] = array();
        $_SESSION[__CLASS__]['links']['menuId'] = $id;

        //Removes the cache
        $menuForCache = array('module'=>get_class(),'type'=>$id);
        sh_cache::removeCache();
        //Gets the menus in the db
        $values['sections'] = $this->getForRenderer($id);
        $values['menu']['id'] = $id;
        $values['menu']['type'] = $type;
        $values['menu']['modifylink'] = $this->linker->path->getUri(
            'menu/modifyLink/'
        );

        $fonts = $this->linker->template->fonts;
        $values['fonts']['allowed'] = implode(',',$fonts);

        $font = $this->linker->template->get(
            'menuButtons>'.$id.'>font',
            null
        );
        if(in_array($font,$fonts)){
            $values['font']['actual'] = $font;
        }

        $values['menu']['id'] = $id;

        $values['menu']['state'] = $this->linker->helper->addChecked(
            $this->getParam('activated>'.$id, true)
        );

        // Gets the actual text height from the user's params or the template's params
        $actualTextHeight = $this->getParam('textHeight>'.$id, false);
        if(!$actualTextHeight){
            $actualTextHeight = $this->linker->template->get(
                'menuButtons>'.$id.'>textHeight',20
            );
        }
        $textHeights = array(16,18,20,25,30,35,40,50,60,70,80);
        foreach($textHeights as $textHeight){
            if($textHeight == $actualTextHeight){
                $values['textHeights'][] = array(
                    'height' => $textHeight,
                    'state' => 'selected'
                );
            }else{
                $values['textHeights'][]['height'] = $textHeight;
            }
        }

        $this->render('simple',$values);
        return true;
    }

    /**
     * addEntry adds an entry to the menu
     * @return str status
     * Echoes 'OK' if the entry was added successfully
     */
    public function addEntry(){
        $menuId = $_POST['menuId'];
        $_SESSION[__CLASS__]['addEntry'][] = $menuId;
        echo 'OK';
        return 1;
    }

    /**
     * public function getForRenderer
     */
    public function getForRenderer($menuId){
        $elements = $this->db_execute(
            'getMenusByMenuId',
            array('menuId'=>$menuId)
        );
        if(is_array($elements)){
            foreach($elements as &$element){
                $sections .= ',\'category_'.$element['category'].'\'';
            }
            $this->linker->html->addTextScript(
'sections = [\'absent\''.$sections.'];
    function createSortables(){
        Sortable.create(\'container\',{tag:\'div\',only:\'section\',handle:\'handle\'});
    }'
            );
        }
        return $elements;
    }

    public function getInArray($menuId){
        $elements = $this->db_execute(
            'getMenusByMenuId',
            array('menuId' => $menuId)
        );
        foreach($elements as $element){
            $cat = $element['category'];
            $pos = $element['position'];
            $ret[$cat][$pos]['title'] = $element['title'];
            $ret[$cat][$pos]['image'] = $element['image'];
            $ret[$cat][$pos]['link'] = $element['link'];
        }
        return $ret;
    }

    /** function  insert
     * Description:  Inserts an element to the breadcrumbs' list
     * if no target is given, there will be no link...
    */
    public function insert($name,$target = ''){
        $this->items[]=array('name'=>$name,'target'=>$target);
    }

    public function get($menuId,$menuType = ''){
        $activated = $this->getParam('activated>'.$menuId, true);
        if(strtolower($menuType) == 'array'){
            if(!$activated){
                $elements['sections'] = array();
                return $elements['sections'];
            }
            $elements['sections'] = $this->getInArray($menuId);
            return $elements;
        }

        $elements['menu']['container'] = 'myMenu_'.$menuId;

        if($activated){
            $elements['sections'] = $this->db_execute(
                'getMenusByMenuId',
                array('menuId' => $menuId)
            );

            if(is_array($elements['sections'])){
                $thisPage = $this->linker->path->getPage();
                $cpt = 1;
                foreach($elements['sections'] as &$section){
                    $section['title'] = ' '.$this->getI18n($section['title']).' ';
                    $section['id'] = $cpt++;
                    // We have to check if the page is the index page
                    $rewrittenLink = $this->linker->index->rewritePage($section['link']);
                    if($section['link'] == $thisPage || $rewrittenLink == $thisPage){
                        $section['state'] .= '_active';
                        $section['selected'] .= '_active';
                    }else{
                        $section['state'] .= '_passive';
                        $section['selected'] .= '_selected';
                    }
                    if(strtolower(substr($section['link'],0,4)) != 'http'){
                        $section['href'] = $this->linker->path->getUri(
                            $section['link']
                        );
                    }else{
                        $section['href'] = $section['link'];
                    }
                }
            }
        }

        $file = $this->getParam('renderFiles>'.$menuId);
        $rendered = $this->render($file,$elements,false,false);
        return $rendered;
    }

    /**
     * This method is automatically called by sh_template when the admin/master
     * changes the template he is using.<br />
     * It does everything that has to be done in this class when it occurs.
     * @param str $template The name of the template that will now be used.
     */
    public function template_change($template){
        // We remove the generated images from the disk
        $this->reset();

        //Removes the cache
        sh_cache::removeCache();

        // We disable all the menus
        $menus = array_keys($this->linker->template->get('menuButtons'));

        foreach($menus as $menu){
            if(is_int($menu)){
                $this->setParam('activated>'.$menu, false);
            }
        }
        $this->writeParams();
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);
        if($method == 'edit'){
            return '/menu/edit/'.$id.'.php';
        }
        if($method == 'verifyLength'){
            // To change this, we also have to do it in menuEditor.js
            return '/menu/verifyLength.php';
        }
        if($method == 'addEntry'){
            // To change this, we also have to do it in menuEditor.js
            return '/menu/addEntry.php';
        }
        if($method == 'chooseLink'){
            // To change this, we also have to do it in menuEditor.js
            return '/menu/chooseLink.php';
        }

        return parent::translatePageToUri($page);
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if(preg_match('`/menu/edit/([0-9]+)\.php`',$uri,$matches)){
            return 'menu/edit/'.$matches[1];
        }
        if($uri == '/menu/verifyLength.php'){
            // To change this, we also have to do it in menuEditor.js
            return 'menu/verifyLength/';
        }
        if($uri == '/menu/addEntry.php'){
            // To change this, we also have to do it in menuEditor.js
            return 'menu/addEntry/';
        }
        if($uri == '/menu/chooseLink.php'){
            // To change this, we also have to do it in menuEditor.js
            return 'menu/chooseLink/';
        }

        return parent::translateUriToPage($uri);
    }

    public function __tostring(){
        return get_class();
    }
}
