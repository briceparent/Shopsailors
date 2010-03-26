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
 * Class that creates video players
 */
class sh_video extends sh_core{
    protected $minimal = array();
    protected $isRenderingWEditor = false;

    public function construct(){
        $this->debug(__METHOD__, 2, __LINE__);
    }



    /**
     * Method called by the sh_render class to render the tag RENDER_SOUND.
     * @param array $attributes An associative array containing all the tag's attributes.
     * @return str The rendered html for the diaporama.
     */
    public function render_video($attributes = array()){
        if(isset($attributes['file'])){
            $file = $attributes['file'];
        }else{
            return false;
        }
        $title = urlencode($attributes['title']);
        $width = $attributes['width'];
        $height = $attributes['height'];

        $singlePath = $this->linker->path->getBaseUri().$this->getSinglePath();
        $ret = '
<object type="application/x-shockwave-flash" data="'.$singlePath.'player_flv_maxi.swf" width="'.$width.'" height="'.$height.'">
    <param name="movie" value="'.$singlePath.'player_flv_maxi.swf" />
    <param name="allowFullScreen" value="true" />
    <param name="FlashVars" value="flv='.$file.'&#38;autoload=1&#38;showstop=1&#38;showvolume=1&#38;showtime=1&#38;showloading=always&#38;showfullscreen=1&#38;playeralpha=50&#38;titlesize=18&#38;showmouse=autohide&#38;showtitleandstartimage=1" />
</object>
        ';

        return $ret;
    }
    
    public function shallWe_render_video($attributes = array()){
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
