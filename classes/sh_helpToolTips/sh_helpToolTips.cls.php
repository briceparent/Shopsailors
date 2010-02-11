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
 * Class that builds help bubbles (tool tips), and their contents and js.
 */
class sh_helpToolTips extends sh_core{
    public function construct(){
        $this->addJavascript();
    }

    public function getJavascript(){
        $singlePath = $this->getSinglePath().'wz_tooltip/';
        return '<script type="text/javascript" src="'.$singlePath.'wz_tooltip.js"/>'.
            '<script type="text/javascript" src="'.$singlePath.'tip_balloon.js"/>';
    }

    public function addJavascript(){
        $singlePath = $this->getSinglePath().'wz_tooltip/';
        $this->links->html->addAfterBody(
            '<script type="text/javascript" src="'.$singlePath.'wz_tooltip.js"/>'
        );
        $this->links->html->addAfterBody(
            '<script type="text/javascript" src="'.$singlePath.'tip_balloon.js"/>'
        );
    }

    public function render_help($attributes = array()){
        if(isset($attributes['what'])){
            $values['help']['what'] = $attributes['what'];
        }else{
            return false;
        }$values['help']['id'] = substr(MD5(__CLASS__.microtime()),0,12);

        
        if(sh_html::$willRender){
            return $this->render('help',$values,false,false);
        }
        return $this->render('help_minimal',$values,false,false);
    }

    public function __tostring(){
        return get_class();
    }
}



