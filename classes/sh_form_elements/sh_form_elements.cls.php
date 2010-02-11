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
 * Class that creates and manages forms elements, such as checkboxes and options
 */
class sh_form_elements extends sh_core {
    protected $minimal = array('change' => true);

    public function render_checkbox($attributes = array()){
        if(isset($attributes['text'])){
            $values['element']['text'] = $attributes['text'];
            unset($attributes['text']);
        }
        if(isset($attributes['disabled']) && $attributes['disabled'] == 'disabled'){
            $values['element']['disabled'] = true;
        }
        if(isset($attributes['readonly']) && $attributes['readonly'] == 'readonly'){
            $values['element']['disabled'] = true;
        }
        if(
            isset($attributes['state']) &&
            $attributes['state'] == 'disabled' ||
            $attributes['state'] == 'readonly'
        ){
            $values['element']['disabled'] = true;
        }
        if(isset($attributes['help'])){
            $values['help']['content'] = $attributes['help'];
            unset($attributes['help']);
        }

        $attributeString = $separator = '';
        foreach($attributes as $attributeName=>$attributeValue){
            if(strpos($attributeValue,'"') === false){
                $attributeString .= $separator.$attributeName.'="'.$attributeValue.'"';
            }else{
                $attributeString .= $separator.$attributeName."='".$attributeValue."'";
            }
            if($attributeName == 'id'){
                $uid = $attributeValue;
            }
            $separator = ' ';
        }
        if(!isset($uid)){
            $uid = 'cb_'.substr(md5(__CLASS__.microtime()),0,8);
            $attributeString .= ' id="'.$uid.'"';
        }
        $attributeString .= ' type="checkbox"';

        $values['element']['uid'] = $uid;
        $values['element']['attributes'] = $attributeString;
        $values['element']['jsmethod'] = 'cbox_'.$uid;
        $values['element']['checkUncheck'] = true;

        $browser = get_browser();
        $values['userAgent'][$browser->browser] = true;

        return $this->render('form_element', $values, false, false);
    }

    public function render_radiobox($attributes = array()){
        if(isset($attributes['text'])){
            $values['element']['text'] = $attributes['text'];
            unset($attributes['text']);
        }
        if(isset($attributes['disabled']) && $attributes['disabled'] == 'disabled'){
            $values['element']['disabled'] = true;
        }
        if(isset($attributes['readonly']) && $attributes['readonly'] == 'readonly'){
            $values['element']['disabled'] = true;
        }
        if(
            isset($attributes['state']) &&
            $attributes['state'] == 'disabled' ||
            $attributes['state'] == 'readonly'
        ){
            $values['element']['disabled'] = true;
        }
        if(isset($attributes['help'])){
            $values['help']['content'] = $attributes['help'];
            unset($attributes['help']);
        }

        $attributeString = $separator = '';
        foreach($attributes as $attributeName=>$attributeValue){
            if(strpos($attributeValue,'"') === false){
                $attributeString .= $separator.$attributeName.'="'.$attributeValue.'"';
            }else{
                $attributeString .= $separator.$attributeName."='".$attributeValue."'";
            }
            if($attributeName == 'id'){
                $uid = $attributeValue;
            }
            $separator = ' ';
        }
        if(!isset($uid)){
            $uid = 'rb_'.substr(md5(__CLASS__.microtime()),0,8);
            $attributeString .= ' id="'.$uid.'"';
        }
        $attributeString .= ' type="radio"';

        $values['element']['uid'] = $uid;
        $values['element']['attributes'] = $attributeString;
        $values['element']['jsmethod'] = 'rbox_'.$uid;

        $browser = get_browser();
        $values['userAgent'][$browser->browser] = true;

        return $this->render('form_element', $values, false, false);
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}