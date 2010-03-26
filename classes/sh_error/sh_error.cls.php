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
 * Class that manages the various uri errors.
 */
class sh_error extends sh_core {

    /**
     * public function show
     */
    public function show(){
        $id = (int) $this->linker->path->page['id'];
        if($_SESSION[__CLASS__]['prepared']){
            $this->linker->cache->disable();
            $_SESSION[__CLASS__]['prepared'] = false;
            header("HTTP/1.0 ".$this->getI18n('header_'.$id));
            $id = (int) $this->linker->path->page['id'];
            $replacements['global'] = array(
                    'number' => $id,
                    'page' => $this->linker->path->getHistory(1),
                    'description' => $this->getI18n('error_'.$id),
                    'prepared' => true
            );
            // We remove the 2 last entries from the history (this page, and the one with the error
            $this->linker->path->removeFromHistory(2,true);
        }else{
            $replacements['global'] = array(
                    'number' => '200',
                    'page' => 'Error page',
                    'description' => 'You called directly this page'
            );
            // We only remove 1 entry, because the page has been called directly
            $this->linker->path->removeFromHistory(1,true);
        }
        for($a=0;$a<10;$a++){
            $history = $this->linker->path->getHistory($a);
            if($history != $this->linker->path->uri){
                $replacements['history'][] = array(
                    'link' => $history,
                    'shownLink' => str_replace(
                        '&',
                        '&#38;',
                        $history
                    )
                );
                $cpt++;
            }
            if($cpt == 3){
                break;
            }
        }
        $this->linker->html->setTitle($this->getI18n('errorTitle').$id);
        $this->render('error',$replacements);
    }

    public function prepare(){
        $_SESSION[__CLASS__]['prepared'] = true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        if(preg_match('`error/show/([0-9]+)`',$page,$matches)){
            $uri = '/error/'.$this->getI18n('uri_'.$matches[1]);
            return $uri;
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if(preg_match('`/error/([0-9]+)(-[^/]+)?\.php`',$uri,$matches)){
            $page = 'error/show/'.$matches[1];
            return $page;
        }
        return false;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}