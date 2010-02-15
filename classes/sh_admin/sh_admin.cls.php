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
 * Class that creates the Command Panel, and creates the connection form.
 */
class sh_admin extends sh_core{
    protected $admin = false;
    protected $master = false;
    protected $elements = array();

    const CONNECT_AS_ADMIN = 0;
    const CONNECT_AS_MASTER = 1;

    public function construct(){
        if(!$this->links->user->isConnected()){
            return true;
        }
        $userId = $this->links->user->userId;
        $this->admin = $this->isAdmin($userId);
        $this->master = $this->isMaster($userId);

        if(!$this->admin && !$this->master){
            return true;
        }
        // We should verify if the user is using firefox:
        $browser = get_browser(null, true);
        
        if(strtolower($browser['browser']) != 'firefox' && strtolower($browser['browser']) != 'mozilla'){
            if(!isset($_SESSION[__CLASS__]['notUsingFirefoxMessageShown'])){
                $this->links->cache->disable();
                $this->links->html->addToBody(
                    'onload',
                    'alert(\''.$this->getI18n('youShouldUseFirefox').'\');'
                );
                $_SESSION[__CLASS__]['notUsingFirefoxMessageShown'] = true;
            }
            return true;
        }

        sh_cache::removeCache();
        $this->links->cache->disable();

        if(isset($_SESSION[__CLASS__]['adminBoxPosX'])){
            $x = $_SESSION[__CLASS__]['adminBoxPosX'];
            $y = $_SESSION[__CLASS__]['adminBoxPosY'];
        }else{
            $x = '50px';
            $y = '50px';
        }
        $this->links->html->addCSS('/templates/global/admin.css','ADMINBOX');
        $this->links->html->addScript('/'.__CLASS__.'/singles/admin.js');
        $this->links->html->addToBody('onload','dragAdminBox(\''.$x.'\',\''.$y.'\');');
        return true;
    }

    public function getAdmins($andMasters = true){
        if($this->getParam('thisIsADemoSite',false)){
            return '*';
        }
        $admins = $this->getParam('admins', array());
        if($andMasters){
            $admins = array_merge($admins,$this->getMasters());
        }
        return $admins;
    }

    public function getMasters(){
        return $this->getParam('masters', array());
    }

    public function isAdmin($id = null, $alsoVerifyIfIsMaster = true){
        if(is_null($id)){
            return parent::isAdmin();
        }
        $admins = $this->getAdmins($alsoVerifyIfIsMaster);
        if($admins == '*'){
            return true;
        }
        return in_array($id,$admins);
    }

    public function isMaster($id = null){
        if(is_null($id)){
            return parent::isMaster();
        }
        return in_array($id,$this->getMasters());
    }

    /**
     * Redirects to an error page if the user is neither an admin nor a master
     */
    public function onlyAdmin($alsoVerifyIfIsMaster = true){
        $userId = $this->links->user->userId;
        if(!$this->isAdmin($userId,$alsoVerifyIfIsMaster)){
            $this->links->path->error(403);
        }
    }
    /**
     * Redirects to an error page if the user is not a master
     */
    public function onlyMaster($alsoVerifyIfIsMaster = true){
        $userId = $this->links->user->userId;
        if(!$this->isMaster($userId)){
            $this->links->path->error(403);
        }
    }

    public function connect($as = self::CONNECT_AS_ADMIN){
        sh_cache::removeCache();
        if($as == self::CONNECT_AS_MASTER){
            $_SESSION[__CLASS__]['admin'] = true;
            $_SESSION[__CLASS__]['master'] = true;
            $_SESSION[__CLASS__]['newConnexion'] = true;
            $this->admin = true;
            $this->master = true;
            $this->links->events->onMasterConnection();
            return true;
        }

        $_SESSION[__CLASS__]['admin']=true;
        $_SESSION[__CLASS__]['master'] = false;
        $_SESSION[__CLASS__]['newConnexion']=true;
        $this->master = false;
        $this->admin=true;
        $this->links->events->onAdminConnection();
        return true;
    }

    public function disconnect(){
        $_SESSION[__CLASS__]['admin']=false;
        $_SESSION[__CLASS__]['master'] = false;
        $_SESSION[__CLASS__]['newConnexion']=false;
        $this->master = false;
        $this->admin=false;
        $this->links->events->onAdminDisconnection();
        return true;
    }

/**
 * Creates the admin menu itself
 * @return str
 * Returns the html source of the admin menu
 */
    public function get(){
        if(!$this->isAdmin()){
            return '';
        }

        $this->links->session->sessionKeeper();

        $admin['admin']['paneltitle'] = SH_TEMPLATE_PATH.'/global/admin/pannel_title.png';
        $admin['admin']['closeimage'] = SH_TEMPLATE_PATH.'/global/admin/pannel_close.png';
        $admin['admin']['closehref'] = $this->links->path->getLink('user/disconnect/');


        $globalAdminFilesFolder = SH_CLASS_SHARED_FOLDER.__CLASS__.'/';
        foreach(scandir($globalAdminFilesFolder) as $file){
            if(substr($file,0,1) != '.' && !is_dir($globalAdminFilesFolder.$file)){
                $this->insertFromFile($globalAdminFilesFolder.$file);
            }
        }

        $adminFilesFolder = SH_SITE_FOLDER.__CLASS__.'/';
        if(is_dir($adminFilesFolder)){
            foreach(scandir($adminFilesFolder) as $file){
                if(substr($file,0,1) != '.' && !is_dir($adminFilesFolder.$file)){
                    $this->insertFromFile($adminFilesFolder.$file);
                }
            }
        }
        $masterCpt = 0;
        $adminCpt = 1000;

        foreach($this->elements as $category=>$contents){
            if(substr($category,0,1) == '['){
                $masterCpt++;
                $cpt = $masterCpt;
                $categoryOrder = '0'.$category;
                $admin['sections'][$categoryOrder]['number'] = 'master';
            }else{
                $categoryOrder = '1'.$category;
                $adminCpt++;
                $cpt = $adminCpt;
            }
            $admin['sections'][$categoryOrder]['elements'] = $contents;
            $admin['sections'][$categoryOrder]['name'] = $category;
            $admin['sections'][$categoryOrder]['id'] = $cpt;
        }

        ksort($admin['sections']);
        $sectionNumber = 1;
        foreach($admin['sections'] as &$category){
            if($category['number'] != 'master'){
                $category['number'] = $sectionNumber++;
                if($sectionNumber>5){
                    $sectionNumber = 1;
                }
            }
        }
        if($this->isMaster()){
            $admin['master']['on'] = true;
        }

        $ret = $this->render('interface',$admin,false,false);

        $root = $this->links->path->getBaseUri();
        $ret = str_replace(
            array(' href="/','window.open(\'/'),
            array(' href="'.$root.'/','window.open(\''.$root.'/'),
            $ret
        );
        return $ret;
    }


    /**
     * Inserts a page in the admin bar<br />
     * Manages with long lines
     * @param str $page
     * The page we want to insert
     * @param str $category
     * The category in which to insert the page $page
     * @param str $image
     * (optionnal)<br />
     * Path to the icon
     * @return bool
     * status of the operation<br />
     * (For now, it only returns true)
     */
    public function insertPage($page,$category,$image = ''){
        if($image != ''){
            $image = '<img src="/images/shared/icons/'. $image .'" alt="logo"/> ';
        }
        $pageName = basename($this->links->path->getLink($page));
        if(strlen($pageName)<18){
            $pageName = 'Modifier la page "'.$pageName.'"';
        }elseif(strlen($pageName)>28){
            $pageName = 'Modifier la page <br />"'.substr($pageName,0,25).'..."';
        }else{
            $pageName = 'Modifier la page <br />"'.$pageName.'"';
        }
        $this->elements[$category][]['element'] = $image . '<span>'.
        $this->links->html->createLink($page,$pageName).'</span>'."\n";

        return true;
    }


    /**
     * Inserts a page in the admin bar
     * @deprecated
     * @param str $page
     * The page we want to insert
     * @param str $category
     * The category in which to insert the page $page
     * @param str $image
     * (optionnal)<br />
     * Path to the icon
     * @return bool
     * status of the operation<br />
     * (For now, it only returns true)
     */
    public function insert($element,$category,$image = '',$position = "bottom"){
        if($image != ''){
            $root = $this->links->path->getBaseUri().'/';
            $image = '<img src="'.$root.'templates/global/admin/icons/'.$image.'" alt="logo"/> ';
        }
        if(!is_array($this->elements[$category])){
            $this->elements[$category] = array();
        }
        if(substr($position,0,3) == 'top'){
            array_unshift(
                $this->elements[$category],
                array(
                    'element' => $image.'<span>'.$element."</span>\n"
                )
            );
        }else{
            $this->elements[$category][]['element'] = $image.'<span>'.$element."</span>\n";
        }
        return true;
    }

    /**
     * Adds the $file's menu entries to the bar
     * @param str $file
     * File to take the menu entries from
     * @return bool
     * status of the operation<br />
     * False if the file doesn't exist<br />
     * else: True
     */
    protected function insertFromFile($file){
        if(!file_exists($file)){
            // The file doesn't exist, so we return false
            return false;
        }
        include($file);
        if(is_array($adminMenu)){
            foreach($adminMenu as $name=>$menu){
                foreach($menu as $position=>$menuElement){
                    if($menuElement['type'] != 'popup'){
                        $options=array();
                        if(isset($menuElement['target'])){
                            $options['target']=$menuElement['target'];
                        }
                        $this->insert(
                            $this->links->html->createLink(
                                $menuElement['link'],
                                $menuElement['text'],
                                $options
                            ),
                            $name,
                            $menuElement['icon'],
                            $position
                        );
                    }else{
                        $this->insert(
                            $this->links->html->createPopupLink(
                                $menuElement['link'],
                                $menuElement['text'],
                                $menuElement['width'],
                                $menuElement['height']
                            ),
                            $name,
                            $menuElement['icon'],
                            $position
                        );

                    }
                }
            }
        }
        if(is_array($masterMenu) && $this->master){
            foreach($masterMenu as $name=>$menu){
                foreach($menu as $position=>$menuElement){
                    if($menuElement['type'] != 'popup'){
                        $this->insert(
                            $this->links->html->createLink(
                                $menuElement['link'],
                                $menuElement['text']
                            ),
                            '['.$name.']',
                            $menuElement['icon'],
                            $position
                        );
                    }else{
                        $this->insert(
                            $this->links->html->createPopupLink(
                                $menuElement['link'],
                                $menuElement['text'],
                                $menuElement['width'],
                                $menuElement['height']
                            ),
                            '['.$name.']',
                            $menuElement['icon'],
                            $position
                        );

                    }
                }
            }
        }
        return true;
    }

    /**
     * Gets the source code from the connection form
     * @return str
     * The connection form's html
     */
    public function getConnectionForm(){
        $datas['form']['id'] = 'admin_connection';
        return $this->render('connection',$datas,false,false);
    }

    /**
     * public function connection
     *
     */
    public function connection(){
        if($this->formSubmitted('admin_connection', true) === true){
            return true;
        }
        return false;
    }


    public function __tostring(){
        return get_class();
    }
}


