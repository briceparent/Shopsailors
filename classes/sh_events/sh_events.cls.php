<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

/*
 * Calls events on every object in $this->links->helper->objects
 * if they exist
 */
class sh_events extends sh_core{
    protected $events = array();

    public function construct(){
        $this->events = array(
            'afterBaseConstruction',
            'beforeOutput',
            'afterOutput',
            'onAdminConnection',
            'onMasterConnection'
        );
    }

    /**
     *  __call
     * When we raise an event [event1], we first call the method [event1] on all loaded classes
     * Then, we loop and include all files in SH_CLASS_SHARED_FOLDER/sh_events/[event1]/
     */
    public function __call($event,$args){
        if(in_array($event, $this->events)){
            if(!$onUnloadedClasses){
                if(is_array($args) && isset($args[0])){
                    $isObject = is_object($this->links->helper->objects[$args[0]]);
                    $methodExists = method_exists($this->links->helper->objects[$args[0]],$event);
                    if($isObject && $methodExists){
                        return $this->links->helper->objects[$args[0]]->$event();
                    }else{
                        return false;
                    }
                }
                if(is_array($this->links->helper->objects)){
                    foreach($this->links->helper->objects as $object){
                        if(method_exists($object, $event)){
                            $ret[] = $object->$event();
                        }
                    }
                }
            }
            if(is_dir(SH_CLASS_SHARED_FOLDER.$this->className.'/'.$event)){
                $elements = scandir(SH_CLASS_SHARED_FOLDER.$this->className.'/'.$event);
                foreach($elements as $element){
                    if(substr($element,0,1) != '.'){
                        include(SH_CLASS_SHARED_FOLDER.$this->className.'/'.$event.'/'.$element);
                    }
                }
            }
            return $ret;
        }
    }

    public function __tostring(){
        return get_class();
    }
}