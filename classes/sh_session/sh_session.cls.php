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
 * Class that manages the session
 */
class sh_session extends sh_core{
    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );
    private $items = array();
    private $order;

    protected $admin = false;
    protected $master = false;
    protected $langFile = false;

    protected $isMobile = false;

    /**
     * Contructor
     */
    public function construct(){
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->helper->addClassesSharedMethods('sh_legacy', '', __CLASS__);
            $this->helper->addClassesSharedMethods('sh_path','',$this->className);
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
        $_SESSION['SH_BUILT'] = true;
        if(isset($_GET['mode']) && $_GET['mode'] == 'classic'){
            $this->linker->html->addAfterBody(
                '<script type="text/javascript">sh_popup.alert("Si vous souhaitez retourner sur la version mobile, un lien est présent dans les mentions légales");</script>'
            );
            $this->linker->session->setSessionIsMobile(false);
        }elseif(isset($_GET['mode']) && $_GET['mode'] == 'mobile' && $_SESSION['SH_SESSION_WASMOBILE']){
            $this->linker->session->setSessionIsMobile(true);
        }
        if(!isset($_SESSION['SH_SESSION_ISMOBILE'])){
            // We check if the user is using a mobile device
            $this->checkIfIsMobileDevice();
        }
        $this->isMobile = $_SESSION['SH_SESSION_ISMOBILE'];
        $order = array('_SESSION','_POST','_GET');
        $this->changeOrder($order);
        $this->admin = self::staticIsAdmin();
        $this->master = self::staticIsMaster();
    }

    public function getLegacyEntries(){
        return array('mobile');
    }

    public function getLegacyEntry($element){
        if($element == 'mobile' && $_SESSION['SH_SESSION_WASMOBILE']){
            // Sending to classic mode
            $uri = $this->linker->path->url;
            if(strpos($uri,'?') === false){
                $linkerChar = '?';
            }else{
                $linkerChar = '&';
            }
            $link = $uri.$linkerChar.'mode=mobile';

            return array(
                'link'=>$link,
                'textBefore'=>'',
                'text'=>'Version mobile du site',
                'textAfter'=>''
            );
        }
        return false;
    }

    public function checkIfIsMobileDevice(){
        if(!$this->getParam( 'allowMobileVersion', false)){
            return false;
        }
        if(!empty($_GET['mobile'])){
            $_SESSION['SH_SESSION_ISMOBILE'] = strtolower($_GET['mobile']);
            return $_SESSION['SH_SESSION_ISMOBILE'];
        }
        if(isset($_SESSION['SH_SESSION_ISMOBILE'])){
            return $_SESSION['SH_SESSION_ISMOBILE'];
        }
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $accept     = $_SERVER['HTTP_ACCEPT'];
        
        if(
            preg_match('/(ip[oa]d)/i',$user_agent,$match)
            || preg_match('/(android)/i',$user_agent,$match)
            || preg_match('/(blackberry)/i',$user_agent,$match)
            || preg_match('/(opera mini)/i',$user_agent,$match)
            || preg_match('/(palm|pre\/|palm os|hiptop|avantgo|plucker|xiino|blazer|elaine)/i',$user_agent,$match)
            || preg_match('/(iris|3g_t|windows ce|opera mobi)/i',$user_agent,$match)
            || preg_match('/(mobile|psp|phone)/i',$user_agent,$match)
            || preg_match('/(application\/vnd.wap.xhtml+xml)/i',$accept,$match)
        ){
            $_SESSION['SH_SESSION_ISMOBILE'] = strtolower($match[1]);
        }elseif(
            isset($_SERVER['HTTP_X_WAP_PROFILE'])
            || isset($_SERVER['HTTP_PROFILE'])
        ){
            $_SESSION['SH_SESSION_ISMOBILE'] = 'mobile';
        }else{
            $_SESSION['SH_SESSION_ISMOBILE'] = false;
        }
        return $_SESSION['SH_SESSION_ISMOBILE'];
    }
    
    public function setSessionIsMobile($value = true){
        if($this->isMobile){
            $_SESSION['SH_SESSION_WASMOBILE'] = true;
        }
        if($value){
            $_SESSION['SH_SESSION_WASMOBILE'] = false;
        }
        $this->isMobile = $value;
        $_SESSION['SH_SESSION_ISMOBILE'] = $this->isMobile;
    }

    /**
     * Inserts a javascript file that will call a routine frequently to keep the session open.
     * @return boolean Always returns true
     * @todo Raise an error if the head of the document has already been sent.<br />
     * It occurs when the class is called for first by the template.rf.xml .
     */
    public function sessionKeeper(){
        $this->linker->html->addScript($this->getSinglePath(true).'sessionKeeper.js');
        return true;
    }

    /**
     * Verifies if an admin session is opened.<br />
     * If so, disables the cache.
     * @static
     * @return boolean
     * True if an admin session has been opened.<br />
     * False if not.
     */
    public static function staticIsAdmin(){
        if($_SESSION[__CLASS__]['admin']){
            sh_cache::disable();
            return true;
        }
        return false;
    }

    /**
     * Verifies if a master session is opened.<br />
     * If so, disables the cache.
     * @static
     * @return boolean
     * True if a master session has been opened.<br />
     * False if not.
     */
    public static function staticIsMaster(){
        if($_SESSION[__CLASS__]['master']){
            sh_cache::disable();
            return true;
        }
        return false;
    }

    /**
     * Reads and returns the value of $this->admin
     * @return boolean The satus of the connection
     */
    public function isAdmin($alsoVerifyIfMaster = true){
        if($this->admin || ($alsoVerifyIfMaster && $this->isMaster())){
            return true;
        }
        return false;
    }

    /**
     * Reads and returns the value of $this->master
     * @return boolean The satus of the connection
     */
    public function isMaster(){
        return $this->master;
    }

    /**
     * Redirects to an error page if the user is neither an admin nor a master
     */
    public function onlyAdmin(){
        if(!$this->admin){
            $this->linker->path->error(403);
        }
    }

    /**
     * Manages the results of the form shown by public function connect().
     * @return boolean
     */
    protected function priv_connect(){
        $allowedAdmins = $this->getParam('allowedAdmins',array());
        $allowedMasters = $this->getParam('allowedMasters',array());
        if(isset($allowedMasters[$_POST['id']]) && $allowedMasters[$_POST['id']] == MD5('bRiCe'.$_POST['pass'])){
            $_SESSION[__CLASS__]['admin'] = true;
            $_SESSION[__CLASS__]['master'] = true;
            $_SESSION[__CLASS__]['newConnexion'] = true;
            $this->admin = true;
            $this->master = true;
            $this->linker->events->onMasterConnection();
            $this->linker->path->redirect('sh_index','show');
            return true;
        }
        if(isset($allowedAdmins[$_POST['id']]) && $allowedAdmins[$_POST['id']] == MD5('bRiCe'.$_POST['pass'])){
            $_SESSION[__CLASS__]['admin']=true;
            $_SESSION[__CLASS__]['master'] = false;
            $_SESSION[__CLASS__]['newConnexion']=true;
            $this->master = false;
            $this->admin=true;
            $this->linker->events->onAdminConnection();
            $this->linker->path->redirect('sh_index','show');
            return true;
        }
        return false;
    }

    /**
     * Disconnects the user
     */
    /*public function disconnect(){
        $this->admin = false;
        $this->master = false;
        unset($_SESSION[__CLASS__]);
        $this->linker->path->redirect('sh_index','show');
    }*/

    /**
     * Shows the Admin/Master connection form, and sends the results to priv_connect().
     */
/*    public function connect(){
        sh_cache::disable();
        //delays the script also to prevent brutforce attacks
        sleep(2);
        if($this->linker->admin->connection()){
            $_SESSION['verif_connexion']='';
            if(!$this->priv_connect()){
                $connectForm = 'Le pseudonyme ou le mot de passe est incorrect...<br /><br />';
            }
        }
        // Shows connection form
        $form = $this->linker->admin->getConnectionForm();
        $this->linker->html->insert($form);
    }*/

    public function changeOrder($order){
        $this->order=$order;
        $this->rebuild();
    }

    private function rebuild(){
        foreach($this->order as $category){
            global ${$category};
            foreach(${$category} as $id => $value){
                $this->insert($id,$value,$category);
            }
        }
    }

    public function insert($id,$value,$category='_SESSION'){
        $this->items[$category][$id]=$value;
    }

    public function get($id,$default='',$category=null){
        static $recursive;
        if($category==null){
            $category=$this->order[0];
            $recursive=true;
        }
        if(isset($this->items[$category][$id]))
        return $this->items[$category][$id];
        if($recursive==true){
            if($category!==$this->order[count($this->order)-1]){
                return $this->get($id,$default,$this->order[array_search($category,$this->order)+1]);
            }
        }
        $recursive=false;
        return $default;
    }

    public function isConnectionPage(){
        $page = $this->linker->path->page['page'].'/';
        if($page == $this->shortClassName.'/connect/'){
            return true;
        }
        return false;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
    /*    if($page == $this->shortClassName.'/connect/'){
            return $this->getI18n('connectLink');
        }
        if($page == $this->shortClassName.'/disconnect/'){
            return $this->getI18n('disconnectLink');
        }*/
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        /*if($uri == $this->getI18n('connectLink')){
            return $this->shortClassName.'/connect/';
        }
        if($uri == $this->getI18n('disconnectLink')){
            return $this->shortClassName.'/disconnect/';
        }*/
        return false;
    }

    public function __tostring(){
        return get_class();
    }
}
