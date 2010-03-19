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
 * Class that creates slideshows, using Millstream Web Software's javascript Crossfade,
 * and Andrew Tetlaw's javascript Fastinit.
 */
class sh_diaporama extends sh_core{
    protected $minimal = array('getList'=>true);
    protected $acceptedTypes = array();
    const LIST_FILENAME = '.images';
    const defaultType = 'default';
    protected static $previews = array();
    protected $sizes = array(100,150,200,300,400,500);
    protected $jsAdded = false;

    public function construct(){
        $this->debug(__METHOD__, 2, __LINE__);
        $this->linker->html->addCSS('/templates/global/diaporama.css');
        $this->acceptedTypes = $this->getParam('acceptedTypes');
        $this->diapoFolder = SH_IMAGES_FOLDER.'diaporamas/';
    }

    /**
     * Shows the form and save the result for the diaporamas editor.
     */
    public function edit(){
        $this->debug(__METHOD__, 2, __LINE__);
        $id = (int) $this->linker->path->page['id'];
        $datas = $this->getList(true);

        $max = 0;
        foreach($datas['diaporamas'] as $oneData){
            if($oneData['id'] == $id){
                $diapoData = $oneData;
            }
            if($oneData['id']>$max){
                $max = $oneData['id'];
            }
            $max++;
        }
        $values['input'][0]['text'] = $this->getI18n('diapo_name');
        $values['input'][0]['input'] = true;
        $values['input'][0]['name'] = 'diapo_name';

        $values['input'][1]['title'] = true;
        $values['input'][1]['text'] = $this->getI18n('diapo_size');

        if($id == 0){
            // We create a new diaporama
            $datas['diapo']['name'] = $this->getI18n('diapo_editNew');
            $datas['input'][2]['checked'] = 'checked';
            $values['input'][0]['value'] = $max.'-diapo'.$max;
        }else{
            // We edit a diaporama
            $datas['diapo']['name'] = $this->getI18n('diapo_edit'). $diapoData['name'];
            $values['input'][0]['value'] = $diapoData['name'];
        }

        $cpt = 1;
        foreach($this->sizes as $size){
            $cpt ++;
            if($size == $reelSize){
                $datas['input'][$cpt]['checked'] = 'checked';
            }
            $values['input'][$cpt]['radio'] = true;
            $values['input'][$cpt]['name'] = 'diapo_size';
            $values['input'][$cpt]['text'] = $size.'x'.$size;
            $values['input'][$cpt]['help'] = true;
            $values['input'][$cpt]['help_id'] = 'diapo_size_help'.$size;
            $values['input'][$cpt]['help_content'] = '<div  style="width:'.$size.'px;"><img src="/templates/global/admin/diapo_'.$size.'x'.$size.'.png"/></div>';
        }
        $datas = array_merge($datas,$values);

        $datas['new_diaporama']['link'] = $this->linker->path->getLink('diaporama/edit/0');
        $this->render('add_edit',$datas);
        return true;
    }

    /**
     * Gets and eventually renders the list of diaporamas.
     * @param bool $inArray
     * <ul><li>false (default behaviour) for a rendering.</li>
     * <li>true to get the list in an array.</li></ul>
     * @return bool|array True if $inArray is "false", and the array containing
     * the list if $inArray is "true".
     */
    public function getList($inArray = false){
        $this->debug(__METHOD__, 2, __LINE__);
        $elements = scandir($this->diapoFolder);
        foreach($elements as $element){
            if(substr($element,0,1) != '.'){
                if(file_exists($this->diapoFolder.$element.'/'.sh_browser::DIMENSIONFILE)){
                    list($width,$height) = explode(
                        'x',
                        file_get_contents(
                            $this->diapoFolder.$element.'/'.sh_browser::DIMENSIONFILE
                        )
                    );
                    $id = array_shift(explode('-',$element));
                    $diapos['diaporamas'][] = array(
                        'name'=>$element,
                        'width'=>$width,
                        'height'=>$height,
                        'link'=>$this->linker->path->getLink('diaporama/edit/'.$id),
                        'id'=>$id
                    );
                }
            }
        }
        if($inArray){
            return $diapos;
        }
        echo $this->render('diaporamaInserter', $diapos,false,false);
        return true;
    }

    /**
     * Method called by sh_browser when some change is done on a diaporama folder
     * (adding, renaming or removing of an directory).
     * @param str $event The event that braught us to here.
     * @param str $parentFolder The folder in which something changed.
     * @param str $folder The name of the folder that changed.
     * @param str $newName The new name of the forlder.<br />
     * Only used if the action was a renaming.
     * @param array $elements An array containing all the arguments that had to be
     * passed to the function (here, nothing...).
     */
    public function folderEvent($event,$parentFolder,$folder,$newName,$elements){
        $this->debug(__METHOD__, 2, __LINE__);
        if($event == sh_browser::ONADDFOLDER){
            $this->debug('A folder named '.basename($folder).' was created in '.$parentFolder, 3, __LINE__);
        }
        if($event == sh_browser::ONRENAMEFOLDER){
            $this->debug('The folder '.$folder.' was renamed to '.$newName, 3, __LINE__);
        }
        if($event == sh_browser::ONDELETEFOLDER){
            $this->debug('The folder '.basename($folder).' was removed from '.$parentFolder, 3, __LINE__);
        }
    }

    /**
     * Method called by sh_browser when some change is done on the files of a diaporama
     * directory (adding, renaming or removing of an image).
     * @param str $event The event that braught us to here.
     * @param str $folder The name of the folder in which the change occured.
     * @return bool Always returns true.
     */
    public function onChange($event, $folder){
        $this->debug(__METHOD__, 2, __LINE__);
        $this->buildListFile(basename($folder));
        return true;
    }

    /**
     * Method that creates a new diaporama folder, and sets the folder rigths.
     * @param str $name Name of the diaporama.
     * @return bool True for success, false for failure (the folder could not be created).
     */
    public function newDiaporama($name,$num = 0){
        $this->debug(__METHOD__, 2, __LINE__);
        $name = $this->diapoFolder.sh_browser::modifyName($name);
        if(!is_dir($this->diapoFolder)){
            wz_browser::createFolder($this->diapoFolder,1);
        }
        if(!is_dir($name)){
            echo 'We have to create the folder '.$name.'<br />';
            $name = sh_browser::createFolder($name,113);
            if(!$name){
                return false;
            }
        }
        if(!file_exists($name.'/'.sh_browser::DIMENSIONFILE)){
            sh_browser::addDimension($name,500,500);
        }
        if(!file_exists($name.'/'.sh_browser::ONCHANGE)){
            sh_browser::addEvent(sh_browser::ONCHANGE, $name, __CLASS__, 'onChange');
        }
        return true;
    }

    /**
     * Method that creates the images list file in a diaporama directory.<br />
     * If there is already one, it is replaced.
     * @param str $name Name of the diaporama.
     * @return array An array containing the images list.
     */
    protected function buildListFile($name){
        $this->debug(__METHOD__, 2, __LINE__);
        $folder = $this->diapoFolder.sh_browser::modifyName($name);

        // We remove the old list file
        $file = $folder.'/'.self::LIST_FILENAME;
        if(file_exists($file)){
            unlink($file);
        }

        $elements = scandir($folder);
        foreach($elements as $element){
            if(substr($element,0,1) != '.'){
                // The file is neither ".", nor "..", nor any hidden file or folder
                $ext = array_pop(explode('.',$element));
                if(in_array(strtolower($ext),$this->acceptedTypes)){
                    $imagePath = $this->linker->path->changeToShortFolder($folder).'/'.$element;
                    $values['images'][]['src'] = $imagePath;
                }
            }
        }
        $this->linker->helper->writeArrayInFile($file,'values',$values);
        return $values;
    }

    /**
     * Method called by the sh_render class to render the tag RENDER_DIAPORAMA.
     * @param array $attributes An associative array containing all the tag's attributes.
     * @return str The rendered html for the diaporama.
     */
    public function render_diaporama($attributes = array()){
        $name = $attributes['name'];
        $folder = $this->diapoFolder.sh_browser::modifyName($name);
        if(!is_dir($folder)){
            return false;
        }
        $id = substr(md5(microtime()),0,10);

        if(isset($attributes['first'])){
            $first = $attributes['first'];
        }else{
            $first = 1;
        }
        $file = $folder.'/'.self::LIST_FILENAME;
        if(!file_exists($file)){
            $this->buildListFile($name);
        }

        include($file);

        if(isset($attributes['manual'])){
            $values['diapo']['manual'] = true;
            $attributes['commands'] = true;
        }
        if(isset($attributes['commands'])){
            $values['diapo']['commands'] = true;
        }

        if(isset($attributes['shuffle']) && is_array($values['images'])){
            // 2 shuffles to really shuffle the array
            shuffle($values['images']);
            shuffle($values['images']);
        }

        $values['diapo']['id'] = 'd_'.$id;
        $values['diapo']['class'] = $attributes['class'];
        if(isset($values['images'][$first]['src'])){
            $values['defaultImage']['src'] = $values['images'][$first]['src'];
        }else{
            $values['defaultImage']['src'] = $values['images'][0]['src'];
        }
        $values['js']['dir'] = $this->getSinglePath(false);

        if($this->jsAdded){
            $values['js']['added'] = true;
        }elseif(sh_html::$willRender){
            $values['js']['added'] = true;
            $this->linker->javascript->get(sh_javascript::SCRIPTACULOUS);
            $this->linker->html->addScript($this->getSinglePath().'fastinit.js');
            $this->linker->html->addScript($this->getSinglePath().'crossfade.js');
            $this->linker->html->addScript($this->getSinglePath().'actions.js');
            $this->jsAdded = true;
        }else{
            $this->jsAdded = true;
        }

        return $this->render('diaporama',$values,false,false);
    }

    public function render_diaporamaFromList($attributes, $content){
        $name = $attributes['name'];
        unset($attributes['name']);
        if(isset($attributes['id'])){
            unset($attributes['id']);
        }
        if(isset($attributes['background'])){
            $values['diapo']['background'] = $attributes['background'];
        }

        $images = explode('|',$content);

        foreach($images as $oneImage){
            if(trim($oneImage) != ''){
                $values['images'][]['src'] = trim($oneImage);
            }
        }

        $id = substr(md5(microtime()),0,10);

        if(isset($attributes['manual'])){
            $values['diapo']['manual'] = true;
            $attributes['commands'] = true;
        }
        if(isset($attributes['commands'])){
            $values['diapo']['commands'] = true;
        }

        if(isset($attributes['first'])){
            $first = $attributes['first'];
            unset($attributes['first']);
        }else{
            $first = 1;
        }

        if(isset($attributes['shuffle']) && is_array($values['images'])){
            // 2 shuffles to really shuffle the array
            shuffle($values['images']);
            shuffle($values['images']);
        }

        $values['diapo']['id'] = 'd_'.$id;
        $values['diapo']['class'] = $attributes['class'];
        if(isset($values['images'][$first]['src'])){
            $values['defaultImage']['src'] = $values['images'][$first]['src'];
        }else{
            $values['defaultImage']['src'] = $values['images'][0]['src'];
        }

        if($this->jsAdded){
            $values['js']['added'] = true;
        }elseif(sh_html::$willRender){
            $values['js']['added'] = true;
            $this->linker->html->addScript($this->getSinglePath().'fastinit.js');
            $this->linker->html->addScript($this->getSinglePath().'crossfade.js');
            $this->linker->html->addScript($this->getSinglePath().'actions.js');
            $this->jsAdded = true;
        }else{
            $this->jsAdded = true;
        }

        return $this->render('diaporama',$values,false,false);
    }

    /**
     * public function addToPreviews
     *
     */
    public static function addToPreviews($id){
        self::$previews[] = $id;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        $this->debug(__METHOD__, 2, __LINE__);
        if($page == $this->shortClassName.'/edit/'){
            return '/'.$this->shortClassName.'/edit.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        $this->debug(__METHOD__, 2, __LINE__);
        if($uri == '/'.$this->shortClassName.'/edit.php'){
            return $this->shortClassName.'/edit/';
        }
        return false;
    }

    public function __tostring(){
        return get_class();
    }
}
