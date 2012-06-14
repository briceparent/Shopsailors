<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')) {
    header('location: directCallForbidden.php');
}

/**
 * Class that manages the index page of the Shopsailors engine.
 */
class sh_index extends sh_core {
    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->helper->addClassesSharedMethods('sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__);
            $this->helper->addClassesSharedMethods('sh_sitemap', '', __CLASS__);
            $this->helper->addClassesSharedMethods('sh_path','',$this->className);
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
    }

    public function master_getMenuContent() {
        return array();
    }
    
    public function admin_getMenuContent() {
        $adminMenu['Contenu'][] = array(
            'link'=>'index/choose/',
            'text'=>'Choisir la page d\'accueil',
            'icon'=>'picto_modify.png'
        );

        return $adminMenu;
    }

    /**
     * public function changeSitemapPriority
     *
     */
    public function changeSitemapPriority($page) {
        if($page == $this->getParam('link')) {
            return $this->getParam('sitemap>priority');
        }
        return 0;
    }

    /**
     * Lists the pages that can be called, which are found using the sitemap PHP file,
     * excepting the entry index/show/, which would loop infinitely
     */
    public function choose() {
        $this->onlyAdmin(true);
        $this->linker->cache->disable();

        if($this->formSubmitted('chooseIndexPage')) {
            list($class,$action,$id) = explode('/',$_POST['page']);
            $this->setParam('class',$class);
            $this->setParam('action',$action);
            $this->setParam('id',$id);
            $link = $this->linker->path->getLink($class.'/'.$action.'/'.$id);
            $this->setParam('link',$link);

            if(isset($_POST['activate_intro'])){
                $this->setParam('intro>activated', true);
            }else{
                $this->setParam('intro>activated', false);
            }
            $this->setParam('intro>intro', $_POST['intro']);
            list($class,$method,$id) = explode('/',$_POST['intro']);
            $this->setParam('intro>class', $class);
            $this->setParam('intro>method', $method);
            $this->setParam('intro>id', $id);
            
            $this->writeParams();
        }

        $actualClass = $this->getParam('class');
        $actualAction = $this->getParam('action');
        $actualId = $this->getParam('id');

        $datas['classes'] = $this->helper->listLinks(
            $actualClass.'/'.$actualAction.'/'.$actualId
        );

        if($this->getParam('intro>activated', false)){
            $datas['intro']['activate'] = 'checked';
        }
        // We list the available intro pages
        $actualIntroPage = $this->getParam('intro>intro', '');
        $introPages = $this->get_shared_methods('intro_page');
        foreach($introPages as $class){
            $pages = $this->linker->$class->getIntroPages();
            foreach($pages as $page){
                if($page['value'] == $actualIntroPage){
                    $state = 'checked';
                }else{
                    $state = '';
                }
                $datas['introPages'][] = array(
                    'value' => $page['value'],
                    'name' => $page['name'],
                    'uid' => substr(md5(microtime()),0,8),
                    'state' => $state
                );

                $datas['intro']['available'] = true;
            }
        }

        $this->render('link_chooser',$datas);
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page) {
        $indexPage = $this->getParam('class').'/'.$this->getParam('action').'/';
        $indexPage .= $this->getParam('id','');
        if($page == $indexPage || $page == $this->shortClassName.'/show/') {
            $uri = '/index.php';
            return $uri;
        }
        if($page == $this->shortClassName.'/choose/') {
            $uri = '/'.$this->shortClassName.'/'.$this->getI18n('choose_uri').'.php';
            return $uri;
        }
        return parent::translatePageToUri($page);
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri) {
        $index = array('/','/index.php','/index.php3','/index.htm','/index.html');
        if(in_array($uri,$index)) {
            // We check if there is an history
            if(!isset($_SESSION[__CLASS__]['introPageShown'])){
                // We check if there is an intro page
                if($this->getParam( 'intro>activated',false)){
                    sh_cache::disable();
                    $page = $this->getParam('intro>class').'/'.$this->getParam('intro>method').'/';
                    $page .= $this->getParam('intro>id','');
                    $_SESSION[__CLASS__]['introPageShown'] = true;
                    return $page;
                }
                $_SESSION[__CLASS__]['introPageShown'] = true;
            }

            $page = $this->getParam('class').'/'.$this->getParam('action').'/';
            $page .= $this->getParam('id','');
            return $page;
        }
        if($uri == '/'.$this->shortClassName.'/'.$this->getI18n('choose_uri').'.php') {
            $page = $this->shortClassName.'/choose/';
            return $page;
        }
        return parent::translatePageToUri($page);
    }

    public function rewritePage($page) {
        if($page == $this->shortClassName.'/show/') {
            $page = $this->getParam('class').'/'.$this->getParam('action').'/';
            $page .= $this->getParam('id','');
        }
        return $page;
    }

    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew() {
        $this->addToSitemap('index/show/', 1);
        return true;
    }

    /**
     * public function getPageName
     *
     */
    public function getPageName($action, $id = null) {
        return $this->getI18n('title');
    }

    public function __tostring() {
        return get_class();
    }
}
