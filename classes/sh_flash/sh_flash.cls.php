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
class sh_flash extends sh_core{
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
    public function render_flash($attributes = array()){
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
    
    public function shallWe_render_flash($attributes = array()){
        $this->isRenderingWEditor = $this->isRenderingWEditor || $this->linker->wEditor->isRendering();
        $rep = !$this->isRenderingWEditor;
        return $rep;
    }
    
    public function save($id,$code){
        $code = html_entity_decode($code);
        $code = utf8_encode($code);
        $code = str_replace('&','&#38;',$code);
        $xml = new DOMDocument('1.0', 'UTF-8');
        $model = '<content>'.$code.'</content>';
        // If the content of $model is not an xml string, we exit with an error
        if(!$xml->loadXML($model)){
            echo 'ERROR-Le code n\'a pas le bon format...';
            return false;
        }
                echo "object\n";
        $xml->resolveExternals = true;
        $racine = $xml->documentElement;
        foreach ($racine->childNodes as $item){
            // We only select the "object" nodes
            $width = 0;$height = 0;
            if($item->nodeName == 'object'){
                foreach ($item->attributes as $attribute){
                    if($attribute->name == 'width'){
                        $width = $attribute->value;
                        $attribute->value = '{flash:width}';
                    }elseif($attribute->name == 'height'){
                        $height = $attribute->value;
                        $attribute->value = '{flash:height}';
                        //$attribute->value = '{flash>height}';
                    }
                }
                foreach ($item->childNodes as $subNode){
                    // We only select the "embed" nodes
                    if($subNode->nodeName == 'embed'){
                        foreach ($subNode->attributes as $attribute){
                            if($attribute->name == 'width'){
                                $attribute->value = '{flash:width}';
                            }elseif($attribute->name == 'height'){
                                $attribute->value = '{flash:height}';
                            }
                        }
                    }
                }
                $id = $this->countParams('objects') + 1;
                $newCode = $xml->saveXML(
                    $item
                );
                $this->setParam(
                    'objects>'.$id,
                    array(
                        'code'=>$newCode,
                        'width'=>$width,
                        'height'=>$height
                    )
                );
                $this->writeParams();
                echo 'uhuhuhuh';
                return $id;
            }
        }
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
