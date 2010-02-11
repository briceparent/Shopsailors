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
 * Class that serves images, and asks to build them if possible.
 */
class sh_images extends sh_core{
    protected $minimal = array('get' => true);
    const LANG_DIR = 'SH_LANG/';

    const PREVIEW_TEXT = 'Shopsailors';

    public function getFavicon(){
        $type = $_GET['favicon_type'];
        $favicon = SH_IMAGES_FOLDER.'favicon'.$type;
        if(!file_exists($favicon)){
            $favicon = SH_SHAREDIMAGES_FOLDER.'icons/favicon.png'.$type;
        }
        // It does so, so we send it with the apropriate header
        $contentType = mime_content_type($favicon);
        header('Content-type: '.$contentType);
        readfile($favicon);
        return true;
    }

    public function imageExists($image){
        $realImage = $this->links->path->changeToRealFolder(
            $image
        );
        return (file_exists($realImage) || file_exists(SH_ROOT_FOLDER.$realImage));
    }

    /**
     * Gets an image, and send it with it's headers.
     * If it has to be built, launches the building process.
     * If the image isn't found, sends a replacement image.
     * @access Accessed directly from the url. This method shouldn't be called
     * by any other function, because it changes the headers and outputs the image.
     * @return bool true on success
     */
    public function get(){
        $this->links->cache->disable();
        // we verify if the session exists.
        if(isset($_SESSION['SH_BUILT'])){
            // It does, so we verify if the image file exists
            $askedFolder = $_GET['folder'];
            $file = $_GET['file'];
            $buttonType=$_GET['button_type'];
            if($file == 'createPreview'){
                $this->createPreview($_GET['font'], $_GET['height']);
                return true;
            }
            // We translate path to folder
            $folder = $this->links->path->changeToRealFolder($askedFolder,$file,$buttonType);
            // we verify if the image file exists
            if(file_exists($folder.$file)){
                // It does, so we send it with the apropriate header
                $contentType = mime_content_type($folder.$file);
                header('Content-type: '.$contentType);
                readfile($folder.$file);
                return true;
            }
            // It doesn't, so we verify if the image has to be generated
            if(
                $folder == SH_TEMP_FOLDER
                || substr($askedFolder.$file,0,strlen(SH_GENERATEDIMAGES_PATH)) == SH_GENERATEDIMAGES_PATH
            ){
                // We have to generate the image
                $newFile = $this->create($askedFolder.$file);
                if($newFile !== false){
                    $contentType = mime_content_type($newFile);
                    header('Content-type: '.$contentType);
                    readfile($newFile);
                    if($folder == SH_TEMP_FOLDER){
                        unlink($folder.$file);
                    }
                    return true;
                }
                echo basename(__FILE__).':'.__LINE__.' - There was an error!!!';
            }
        }

        // We send the replacement image (picture not found)
        header('Content-type: image/png');
        readfile(SH_SHAREDIMAGES_FOLDER.'icons/picture_not_found.png');
        return false;
    }

    protected function createPreview($font,$height){
        $font = SH_FONTS_FOLDER.$font;
        $text = self::PREVIEW_TEXT;
        $image = SH_TEMPIMAGES_FOLDER.md5(microtime());
        $imageBuilder = $this->links->imagesBuilder;
        list($size) = $imageBuilder->getFontSizeByTextHeight(
            sh_fonts::FONT_THUMB_TEXT,$font,$height
        );
        $dims = $imageBuilder->getDimensions($text, $size, $font);

        $imageBuilder->createImageWithBackground(
            $image,
            sh_colors::RGBStringToRGBArray('FFFFFF'),
            $dims['width'],
            $dims['height']
        );

        $imageBuilder->addText(
            $text,
            $image,
            $dims['left'],
            $dims['top'],
            $font,
            $size,
            '000000'
        );

        $contentType = mime_content_type($image);
        header('Content-type: '.$contentType);
        readfile($image);
        unlink($image);
        return true; 
    }

    /**
     * Creates an image using the parametters read from the database
     * @param string $path The path of the image to create
     * @return string The name of the image that was created
     */
    public function create($path){
        $oldPath = $path;
        $path = str_replace(
            self::LANG_DIR,
            $this->links->i18n->getLang().'/',
            $path
        );
        list($image) = $this->db_execute('getImage',array('path'=>$path));
        if(!isset($image['type']) && $oldPath != $path){
            $path = str_replace(
                self::LANG_DIR,
                $this->links->i18n->getDefaultLang().'/',
                $oldPath
            );
            return $this->create($path);
        }

        $imageBuilder = $this->links->imagesBuilder;
        $destImage = str_replace(
            array(SH_GENERATEDIMAGES_PATH,SH_TEMPIMAGES_PATH),
            array(SH_GENERATEDIMAGES_FOLDER,SH_TEMPIMAGES_FOLDER),
            $image['path']
        );

        $imageBuilder->stretchImage(
            $image['type'],
            $image['position'],
            $image['state'],
            $destImage,
            $image['width'],
            $image['height']
        );

        // In the case we have to add text
        if(trim($image['text']) != ''){
            $imageBuilder->tagImage(
                $image['type'],
                $image['position'],
                $image['state'],
                $destImage,
                $image['text'],
                $image['font'],
                $image['fontsize'],
                $image['startX'],
                $image['startY']
            );
        }

        return $destImage;//false;
    }

    /**
     * Stores an images' datas into the database, for it to be built later, on its first use.
     * @param string $text Text of the button.<br />
     * Returns false if empty string
     * @param string $font The font that should be used to display $text.<br />
     * Returns false if empty string
     * @param string $fontsize The font size of $text.<br />
     * Returns false if empty string
     * @param string $path Path of the image to create (Will be used to call this image
     * from the html).<br />
     * Creates one if empty string (default).
     * @param string $type Name of the button's model to be used, or empty string ("") for
     * the default type taken from the "defaultBuilder" param of the template's params file.
     * @param string $position Position of the button in a buttons array.<br />
     * Can be set to the value of:
     * <ul><li>sh_imagesBuilder::NORMAL (default), for any image and for images that are neither first or last ones
     * of the array</li>
     * <li>sh_imagesBuilder::FIRST, for the first image of an array</li>
     * <li>sh_imagesBuilder::LAST, for the last image of an array</li>
     * </ul>
     * @param boolean $has3States
     * <ul><li>False (default) for images that don't need ACTIVE and SELECTED states</li>
     * <li>True for images that should change on focus and/or when active</li></ul>
     * @param integer $width The width of the image, in pixels, or 0 (default) for automatic
     * @param integer $height The height of the image, in pixels, or 0 (default) for automatic
     * @param integer $startX The text starting point on X, or null (default) for none
     * @param integer $startY The text starting point on Y, or null (default) for none
     * @return string The path of the image added to the database
     */
    public function prepare($text,$font,$fontsize,$path='',$type='',$position=sh_imagesBuilder::NORMAL,$has3States=false,$width=0,$height=0,$startX=null,$startY=null){
        if($text == '' || $font == '' || $fontsize == ''){
            return false;
        }

        if($type == ''){
            $type = $this->links->template->defaultBuilder;
        }
        // Prepares a loop to build the states, if needed
        if($has3States){
            $loops = array(
                sh_imagesBuilder::PASSIVE,
                sh_imagesBuilder::ACTIVE,
                sh_imagesBuilder::SELECTED
            );
            $imageReelState = $loops;
            if(!file_exists(SH_BUILDER_FOLDER.$type.'/active.php')){
                $imageReelState[1] = $loops[0];
            }
            if(!file_exists(SH_BUILDER_FOLDER.$type.'/selected.php')){
                $imageReelState[2] = $imageReelState[1];
            }
            $spacer = '_';
        }else{
            $loops = array('');
        }
        $explodedPath = explode('|',$path);
        if($explodedPath[0] == 'folder' || $path == ''){
            $path = SH_GENERATEDIMAGES_PATH.$explodedPath[1].MD5(
                $text.$font.$fontsize.$type.$element.$position.$width.$height.$startX.$startY
            );
        }
        // Formats the pathes to web accessible mode
        $path = str_replace(SH_ROOT_FOLDER,SH_ROOT_PATH,$path);
        $font = str_replace(SH_ROOT_FOLDER,SH_ROOT_PATH,$font);

        // Prepares the text starting point, if needed
        if(is_null($startX)){
            $startX = 'NULL';
        }else{
            $startX = '"'.$startX.'"';
        }
        if(is_null($startY)){
            $startY = 'NULL';
        }else{
            $startY = '"'.$startY.'"';
        }
        // Prepares the images
        foreach($loops as $num=>$element){
            $thisPath = $path.$spacer.$element.sh_imagesBuilder::DEFAULTEXT;
            $count++;
            $this->db_execute(
                'insertImage',
                array(
                    'text'=>$text,
                    'font'=>$font,
                    'fontsize'=>$fontsize,
                    'path'=>$thisPath,
                    'type'=>$type,
                    'state'=>$imageReelState[$num],
                    'position'=>$position,
                    'width'=>$width,
                    'height'=>$height,
                    'startX'=>$startX,
                    'startY'=>$startY
                ),
                $debug
            );
        }

        if($count == 1){
            return $thisPath;
        }
        return $path;
    }

    /**
     * Deletes from the database all the images that should be created into the folder
     * given as parametter.<br />
     * Does nothing to the file system, so the images that had already been generated
     * will still exist and may be shown.
     * @param string $folder The folder name
     * @return true
     */
    public function removeOneFolder($folder){
        $folder = str_replace(SH_ROOT_FOLDER,'/',$folder);
        $this->db_execute('deleteOneFolder',array('folder'=>$folder));
        return true;
    }

    public function __tostring(){
        return get_class();
    }
}
