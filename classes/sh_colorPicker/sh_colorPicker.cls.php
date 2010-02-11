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
 * Class that builds colorpickers, using the refresh_web's javascript colorPicker.
 */
class sh_colorPicker extends sh_core{
    const DEFAULTCOLOR = '6699CC';

    public function construct(){
        $this->links->html->addScript($this->getSinglePath(true).'colorPicker.js');
    }

    public function render_colorPicker($attributes = array()){
        if(isset($attributes['name'])){
            $name = $attributes['name'];
        }else{
            return false;
        }
        if(isset($attributes['id'])){
            $id = $attributes['id'];
        }else{
            $id = null;
        }
        if(isset($attributes['value'])){
            $value = $attributes['value'];
        }else{
            $value = self::DEFAULTCOLOR;
        }
        return $this->getOne($name,$value,$id);
    }

    /**
     * public function getOne
     *
     */
    public function getOne($name,$value = '336699',$id = null){
        if($value == '' || $value == 'default'){
            $text = 'default';
            $value = 'transparent';
        }else{
            $value = str_replace('#','',$value);
            $text = $value;
        }
        if(is_null($id)){
            $id = md5('colorpicker'.microtime());
        }
        $values['colorPicker'] = array(
            'id' => $id,
            'name' => $name,
            'text' => $text,
            'default' => $value
        );
        $ret = $this->render('colorPicker',$values,false,false);
        return $ret;
    }

    public function __tostring(){
        return get_class();
    }
}



