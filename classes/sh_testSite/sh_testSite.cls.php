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
 * Class that builds tests sites in order to allow people to try the solution
 */
class sh_testSite extends sh_core{
    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );
    protected $dbName = '';
    protected $dbUser = '';
    protected $dbPassword = '';

    public function construct(){

    }

    public function startPage(){
        sh_cache::disable();
        $this->linker->html->setTitle($this->getI18n('startPage_title'));
        $values = array(
            'login' => $this->getParam('models>0>login')
        );
        $values['links']['try'] = $this->getParam('models>0>link');
        $values['links']['createAccount'] = $this->linker->path->getLink('user/createAccount/');
        $this->render('startPage',$values);

        return true;
    }

    public function request(){
        /*
        for($a = ord('a');$a<=ord('z');$a++){
            $val = (1824805501200 / $a);
            if($val == round($val)){
                $ok[chr($a)] = $val;
                $values[] = $a;
            }
        }
        $cpt =0;
        while($cpt < 8){
            foreach($ok as $key=>$oneOk){
                if($oneOk > 1){
                    foreach($values as $oneValue){
                        $val = ($oneOk / $oneValue);
                        if($val == round($val)){
                            $newOk[$key.chr($oneValue)] = $val;
                        }

                    }
                }else{
                    $newOk[$key] = $oneOk;
                }
            }
            $ok = $newOk;
            $newOk = array();
            $cpt++;
        }
        $shown = array();
        echo 'Résultats dans le désordre:<br />';
        foreach($ok as $keys=>$rest){
            $letters = str_split($keys);
            sort($letters,SORT_STRING);
            $keys = implode('',$letters);
            if($rest == 1 && !in_array($keys,$shown)){
                echo $keys.'<br />';
                $shown[] = $keys;
            }
        }
        echo 'Avec les différents ordres, ça donne '.count($ok).' possibilités:<br />';
        foreach($ok as $keys=>$rest){
            if($rest == 1){
                echo $keys.'<br />';
            }
        }
        exit;
         *
         */
        // As only masters are able to add new sites, this page can be shown only on master server
        $this->linker->user->isMasterServer();
        $this->linker->html->setTitle($this->getI18n('request_title'));
        if(!$this->linker->user->isConnected()){
            $masterUrl = $this->linker->user->getMasterUrl(false);
            $values['links']['createAccount'] = $masterUrl.$this->linker->path->getLink('user/createAccount/');
            $this->render('request_intro',$values);
            $ret = $this->linker->user->connect(true,true);
            if(!$ret){
                return true;
            }
        }
        $values['form']['login'] = $this->linker->user->get('login');
        $values['form']['name'] = $this->linker->user->get('name');
        $values['form']['lastName'] = $this->linker->user->get('lastName');
        $values['form']['mail'] = $this->linker->user->get('mail');
        $this->render('request_connected', $values);
    }

    public function reinit(){

    }

    public function create(){

        $path = dirname(__FILE__).'/';
        $file = $path.$this->getParam('model>baseFile', 'base.sql');
        if($file === false || ! file_exists($file)){
            return false;
        }
        $splittedFile = $file.'.php';
        if(file_exists($splittedFile)){
            include($splittedFile);
        }else{
            $lines = file($file);
            $queries = array();
            $tempQuery = '';
            foreach($lines as $line){
                if(trim($line) != '' && substr($line,0,2) != '--'){
                    $line = trim($line);
                    if(substr($line,-1) == ';'){
                        $queries[] = $tempQuery.$line;
                        $tempQuery = '';
                    }else{
                        $tempQuery .= $line.' ';
                    }
                }
            }
            $this->linker->helper->writeArrayInFile($splittedFile,'queries',$queries);
        }
        return true;
    }

    protected function _db_connect(){
        $this->connection = mysql_connect(
            'localhost',
            $this->getParam('db>user'),
            $this->getParam('db>password')
        );
    }

    protected function _db_execute($query){
        return mysql_query($query,$this->connection);
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        $shortClassName = $this->shortClassName;
        if($page == $shortClassName.'/request/'){
            return '/'.$shortClassName.'/request.php';
        }
        if($page == $shortClassName.'/startPage/'){
            return '/'.$shortClassName.'/startPage.php';
        }
        if($page == $shortClassName.'/reinit/'){
            return '/'.$shortClassName.'/reinit.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        $shortClassName = $this->shortClassName;
        if($uri == '/'.$shortClassName.'/request.php'){
            return $shortClassName.'/request/';
        }
        if($uri == '/'.$shortClassName.'/startPage.php'){
            return $shortClassName.'/startPage/';
        }
        if($uri == '/'.$shortClassName.'/reinit.php'){
            return $shortClassName.'/reinit/';
        }
        return false;
    }

    public function __tostring(){
        return get_class();
    }
}



