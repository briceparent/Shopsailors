<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')) {header('location: directCallForbidden.php');}

/* class  sh_variations
 *
 */
class sh_variations extends sh_model{

    /* function  __construct
    */
    public function __construct(){
        parent::__construct($this);

    }

    /**
     * public function showList
     *
     */
    public function showList(){
        if(!$this->isMaster()){header('location: access_forbiden.php');}
        $templateName = $this->links->html->getTemplateName();
        $this->links->html->setTitle('Liste des variations pour le template '.$templateName);
        $list = $this->getList();
        $this->render('add.rf.xml','','');
    }

    /**
     * public function getList
     *
     */
    public function getList(){

    }

    public function __tostring(){
        return get_class();
    }
}
