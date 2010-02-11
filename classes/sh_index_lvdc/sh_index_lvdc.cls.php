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
 * Class that manages the index page of the Shopsailors engine.
 */
class sh_index_lvdc extends sh_core{

    /**
     * public function changeSitemapPriority
     *
     */
    public function changeSitemapPriority($page){
        if($page == $this->getParam('link')){
            return $this->getParam('sitemap>priority');
        }
        return 0;
    }

    /**
     * Lists the pages that can be called, which are found using the sitemap PHP file,
     * excepting the entry index/show/, which would loop infinitely
     */
    public function choose(){
        $this->onlyAdmin();
        $this->links->cache->disable();

        if($this->formSubmitted('chooseIndexPage')){
            list($class,$action,$id) = explode('/',$_POST['page']);
            $this->setParam('class',$class);
            $this->setParam('action',$action);
            $this->setParam('id',$id);
            $link = $this->links->path->getLink($class.'/'.$action.'/'.$id);
            $this->setParam('link',$link);
            $this->writeParams();
            $this->links->path->redirect(__CLASS__,'show');
        }

        $actualClass = $this->getParam('class');
        $actualAction = $this->getParam('action');
        $actualId = $this->getParam('id');

        $addresses = $this->links->sitemap->getSitemapPagesList();

        if(is_array($addresses['PAGES'])){
            foreach($addresses['PAGES'] as $page=>$address){
                $state = '';
                list($class,$action,$id) = explode('/',$page);
                if(!($class == 'index' && $action == 'show')){
                    if(
                        $class == $actualClass &&
                        $action == $actualAction &&
                        ($id == '' || $id == $actualId)
                    ){
                        $state='checked';
                    }

                    $value = $this->links->$class->getPageName($action, $id);
                    $elements[$class][$action.$id] = array(
                        'name' => $page,
                        'value' => $value,
                        'address'=> $address['address'],
                        'state'=>$state
                    );
                }
            }
        }
        $datas = array();
        $classId = 1;
        foreach($elements as $class=>$parts){
            if(count($parts) == 1){
                $class = $this->getI18n('singleEntry_title');
                $generalClass = true;
                $oldClassId = $classId;
                $classId = 0;
                $className = $class;
            }else{
                $className = $this->links->i18n->get($class,'className');
            }
            if($className == ''){
                $className = $class;
            }
            $datas['classes'][$classId]['name'] = $class;
            $datas['classes'][$classId]['description'] = $className;
            $partsId = 0;
            if(is_array($parts)){
                ksort($parts);
                foreach($parts as $part){
                    $datas['links'][] = $part;
                    $datas['classes'][$classId]['elements'][] = $part;
                    if($part['state'] == 'checked'){
                        $datas['class']['unfolded'] = $class;
                        $datas['classes'][$classId]['unfolded'] = true;
                    }
                    $partsId++;
                }
            }
            if($generalClass){
                $classId = $oldClassId;
                $generalClass = false;
            }else{
                $classId++;
            }
        }
        $datas['start']['value'] = $_GET['value'];
        $datas['category']['id'] = $_GET['id'];
        $this->render('link_chooser',$datas);
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        $indexPage = $this->getParam('class').'/'.$this->getParam('action').'/';
        $indexPage .= $this->getParam('id','');
        if($page == $indexPage || $page == $this->shortClassName.'/show/'){
            $uri = '/index.php';
            return $uri;
        }
        if($page == $this->shortClassName.'/choose/'){
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
    public function translateUriToPage($uri){
        $index = array('/','/index.php','/index.php3','/index.htm','/index.html');
        if(in_array($uri,$index)){
            $page = $this->getParam('class').'/'.$this->getParam('action').'/';
            $page .= $this->getParam('id','');
            return $page;
        }
        if($uri == '/'.$this->shortClassName.'/'.$this->getI18n('choose_uri').'.php'){
            $page = $this->shortClassName.'/choose/';
            return $page;
        }
        return parent::translatePageToUri($page);
    }

    public function rewritePage($page){
        if($page == $this->shortClassName.'/show/'){
            $page = $this->getParam('class').'/'.$this->getParam('action').'/';
            $page .= $this->getParam('id','');
        }
        return $page;
    }

    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew(){
        $this->addToSitemap('index/show/', 1);
        return true;
    }

    /**
     * public function getPageName
     *
     */
    public function getPageName($action, $id = null){
        return $this->getI18n('title');
    }

        public function __tostring(){
            return get_class();
        }
}
