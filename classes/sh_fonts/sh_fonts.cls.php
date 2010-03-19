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
 * Class that manages the fonts.
 */
class sh_fonts extends sh_core {
    protected $minimal = array('addThisFont' => true);
    protected $afterFlushValues = array();
    const FONT_THUMB_TEXT = 'abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ 0123456789 áàâäãåçéèêëíìîïñóòôöõúùûü';

    /**
     * protected function prepareFonts
     *
     */
    protected function prepareFonts(){
        $files = scandir(SH_FONTS_FOLDER);
        $imagesBuilder = $this->linker->imagesBuilder;
        echo 'Fonts that are already ready: <br />';
        $none = 'None';
        if(is_array($files)){
            foreach($files as $file){
                $parts = explode('.',$file);
                $ext = array_pop($parts);
                if(strtolower($ext) == 'ttf'){
                    if($ext != strtolower($ext)){
                        $file = implode('.',$parts);
                        rename(
                            SH_FONTS_FOLDER.$file.'.'.$ext,
                            SH_FONTS_FOLDER.$file.'.ttf'
                        );
                        $file .= '.ttf';
                    }
                    if(strpos($file,' ') !== false){
                        $newFileName = str_replace(' ','_',$file);
                        rename(
                            SH_FONTS_FOLDER.$file,
                            SH_FONTS_FOLDER.$newFileName
                        );
                        echo 'We renamed '.$file.' to '.$newFileName.'<br />';
                        $file = $newFileName;
                        $parts = explode('.',$file);
                        $ext = array_pop($parts);
                    }
                    $shortFileName = implode('.',$parts);
                    if(
                        !file_exists(SH_FONTS_FOLDER.$shortFileName.'.php')
                        || !file_exists(SH_FONTS_FOLDER.$shortFileName.'.png')
                    ){
                        $fonts[] = $shortFileName;
                    }else{
                        echo $separator.'"'.$shortFileName.'"';
                        flush();
                        $none = '';
                        $separator = ', ';
                    }
                }
            }
        }
        echo $none.'<br />';
        $total = count($fonts);
        set_time_limit(0);
        if(is_array($fonts)){
            foreach($fonts as $font){
                $shortFileName = $font;
                echo 'Building font "'.$font.'"<br />';
                flush();
                $boxes = array();
                for($a = 10;$a<=100;$a++){
                    list($fontSize,$tmp,$box) = $imagesBuilder->getFontSizeByTextHeight(
                        $shortFileName.' - '.self::FONT_THUMB_TEXT,
                        SH_FONTS_FOLDER.$shortFileName.'.ttf',
                        $a
                    );

                    $boxes[$a] = array(
                        'fontSize'=>$fontSize,
                        'top'=>$box['top'],
                        'left'=>$box['left']
                    );
                    echo '.';
                    flush();
                }

                $done++;
                $this->linker->helper->writeArrayInFile(
                    SH_FONTS_FOLDER.$shortFileName.'.php',
                    'boxes',
                    $boxes
                );

                $imagesBuilder->buildTextImage(
                    SH_FONTS_FOLDER.$shortFileName.'.png',
                    $shortFileName.' - '.self::FONT_THUMB_TEXT,
                    SH_FONTS_FOLDER.$shortFileName.'.ttf',
                    16
                );

                echo '<br />';
                flush();
            }
        }
    }

    public function render_fontSelector($attributes = array()){
        if(!isset($attributes['name']) && !isset($attributes['id'])){
            $attributes['name'] = $attributes['id'] = 'font';
        }elseif(!isset($attributes['name'])){
            $attributes['name'] = $attributes['id'];
        }elseif(!isset($attributes['id'])){
            $attributes['id'] = $attributes['name'];
        }

        $list = $this->getList();
        if(isset($attributes['csv'])){
            $entries = explode(',',$attributes['csv']);
            foreach($list as $key=>$oneFont){
                if(!in_array($oneFont['name'],$entries)){
                    unset($list[$key]);
                }
            }
            sort($entries);
            unset($attributes['csvEntries']);
        }

        if(isset($attributes['value'])){
           $default = $attributes['value'];
        }

        $defaultIsSet = false;
        foreach($list as &$oneFont){
            if($oneFont['name'] == $default){
                $oneFont['state'] = 'selected';
                $defaultIsSet = true;
                break;
            }
        }
        $values['font'] = $attributes;
        if(!$defaultIsSet){
            $default = $list[0]['name'];
            $list[0]['state'] = 'selected';
        }
        $values['font']['selected'] = $default;

        $values['fonts'] = $list;

        return $this->render('fontSelector', $values, false, false);
    }

    /**
     * public function addThisFont
     *
     */
    public function addThisFont(){
        $this->onlyMaster();
        if($this->formSubmitted('addFont')){
            $fileName = $_FILES["font"]['name'];
            if(move_uploaded_file($_FILES["font"]['tmp_name'], SH_TEMP_FOLDER.$fileName)){
                // Deletes the folder if it already exists
                $sentFile = substr($fileName,0,-4);
                $table = explode('.',$_FILES["font"]['name']);
                $last = strtolower($table[count($table)-1]);
                if($last == 'zip'){
                    echo 'On dézippe<br />';
                    $this->unzip(SH_TEMP_FOLDER.$fileName,SH_FONTS_FOLDER,array('ttf','TTF'));
                }elseif(strtolower($last) == 'ttf'){
                    rename(SH_TEMP_FOLDER.$fileName,SH_FONTS_FOLDER.basename($fileName));
                }
            }else{
                $this->linker->html->insert('Il y a eu une erreur lors de l\'envoi du fichier. Si le problème persiste, contactez l\'administrateur du site.<br />');
                return false;
            }
            $this->prepareFonts();
            echo 'Done!<br />Press "F5" to exit.';
        }else{
            header('location: '.$this->linker->path->getLink('fonts/add/'));
        }
        return true;
    }

    /**
     * public function add
     *
     */
    public function add(){
        $this->onlyMaster();
        $this->linker->html->setTitle($this->getI18n('title_add'));
        $vars['font']['addlink'] = $this->linker->path->getLink('fonts/addThisFont/');
        $this->render('add',$vars);
        return true;
    }

    /**
     * loadZip
     * Load the zip, unzip the php and png files, deletes the zip, and copy the
     * default params file, if needed
     */
    protected function unzip($from,$to,$types = array('.*')){
        // Unzips the archive
        if(!is_dir(dirname($to))){
            mkdir(dirname($to));
        }
        $this->linker->zipper->extract($from,$to,$types);
/*        $typesReg = '('.implode('|',$types).')';
        require_once('include/libphp-pclzip/pclzip.lib.php');
        $archive = new PclZip($from);
        if($archive->extract(PCLZIP_OPT_PATH,$to,
                PCLZIP_OPT_BY_PREG, $typesReg) == 0) {
            die("Error : ".$archive->errorInfo(true));
        }
 *
 */
        // Deletes the archive
        unlink($from);
        return true;
    }

     /**
     * public function showList
     */
    public function showList(){
        if(!$this->isMaster()){header('location: access_forbiden.php');}
        $this->linker->html->setTitle('Polices');
        $loop['fonts'] = $this->getList();
        $loop['add']['link'] = $this->linker->path->getLink('fonts/add/');
        $this->render('list',$loop);
    }

    /**
     * public function getList
     *
     */
    public function getList(){
        $scan =  scandir(SH_FONTS_FOLDER);
        if(is_array($scan)){
            foreach($scan as $element){
                if(substr($element,0,1) != '.' && substr($element,-4) == '.ttf'){
                    $ret[] = array(
                        'name' => $element,
                        'preview' => SH_FONTS_PATH.substr($element,0,-4).'.png'
                    );
                }
            }
        }
        return $ret;
    }

    /**
     * public function modify
     *
     */
    public function modify(){
        if(!$this->isMaster()){header('location: access_forbiden.php');}
        $buttonName = $_GET['name'];
        $this->linker->html->setTitle('Modification du bouton "'.$buttonName.'"');
        $values = new sh_params(SH_TEMPLATE_FOLDER.'builder/'.$buttonName.'/params.php');
        $template['GET'][0] = array(
            'width' => $values->get('width'),
            'menuWidth'=>$values->get('menuButtons>totalWidth'),
            'expandYes'=>($values->get('menuButtons>expand')?'selected="selected"':''),
            'expandNo'=>($values->get('menuButtons>expand')?'':'selected="selected"')
        );
        foreach($values->get('variations') as $variationName=>$variation){
            $cpt++;
            $loops['VARIATIONS'][$cpt]['name'] = $variationName;
            $loops['VARIATIONS'][$cpt]['color'] = str_replace('#','',$variation['color']);
            $loops['VARIATIONS'][$cpt]['selectedColor'] = str_replace('#','',$variation['selectedColor']);
            $loops['VARIATIONS'][$cpt]['activeColor'] = str_replace('#','',$variation['activeColor']);
        }
        $cpt = 0;
        foreach(array_keys($values->get('fonts')) as $fontName){
            $cpt++;
            $loops['FONTS'][$cpt]['name'] = $fontName;
        }
        $this->linker->html->insert($this->renderer->render(dirname(__FILE__).'/modify.php',$template,$loops));
    }


    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}