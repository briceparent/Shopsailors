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
 * Class that creates sound players
 */
class sh_sound extends sh_core{
    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );
    protected $minimal = array();
    protected $isRenderingWEditor = false;

    public function construct(){
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->linker->renderer->add_render_tag('render_sound',__CLASS__,'render_sound');
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
    }

    /**
     * Method called by the sh_render class to render the tag RENDER_SOUND.
     * @param array $attributes An associative array containing all the tag's attributes.
     * @return str The rendered html for the diaporama.
     */
    public function render_sound($attributes = array()){
        if(isset($attributes['file'])){
            $file = $attributes['file'];
        }else{
            return false;
        }

        $singlePath = $this->linker->path->getBaseUri().$this->getSinglePath();
        $ret = '
<object type="application/x-shockwave-flash" data="'.$singlePath.'player_mp3_maxi.swf" width="200" height="20">
    <param name="movie" value="'.$singlePath.'player_mp3_maxi.swf" />
    <param name="bgcolor" value="#ffffff" />
    <param name="FlashVars" value="mp3='.$file.'&#38;autoload=1&#38;showstop=1&#38;showinfo=1&#38;showvolume=1&#38;showloading=always&#38;buttonwidth=15&#38;sliderwidth=12" />
</object>
        ';

        return $ret;
    }
    
    public function shallWe_render_sound($attributes = array()){
        $this->isRenderingWEditor = $this->isRenderingWEditor || $this->linker->wEditor->isRendering();
        $rep = !$this->isRenderingWEditor;
        return $rep;
    }


    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        $this->debug(__METHOD__, 2, __LINE__);
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        $this->debug(__METHOD__, 2, __LINE__);
        return false;
    }

    public function __tostring(){
        return get_class();
    }
}
