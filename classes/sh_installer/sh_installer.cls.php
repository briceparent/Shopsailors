<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

/**
 * Class that manages the index page of the Shopsailors engine.
 */
class sh_installer extends sh_core {
    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );

    public $callWithoutId = array();
    public $callWithId = array(
        'sites_manage'
    );

    public function construct() {

    }
    
    public function master_getMenuContent() {
        $masterMenu['Section Master'][] = array(
            'link'=>'installer/sites_manage/0','text'=>'Gérer les sites','icon'=>'picto_tool.png'
        );
        return $masterMenu;
    }
    public function admin_getMenuContent() {
        return array();
    }

    public function getPageName($action, $id = null,$forUrl = false) {
        return false;
    }

    public function install() {

    }

    /* SITES MANAGEMENT */

    public function sites_manage() {
        $id = (int) $this->linker->path->page['id'];

        if($this->formSubmitted('sites_manage')) {
            include(SH_SITES_FOLDER.'list.php');
            echo '<div>$_POST = '.nl2br(str_replace(' ','&#160;',htmlspecialchars(print_r($_POST,true)))).'</div>';
            // We check if there is a website being edited
            if(isset($_POST['editingAWebsite'])) {
                // We list the domains et rewrite them to be regular expressions
                $domains = explode("\n",trim($_POST['domains']));
                echo '<div>$domains = '.nl2br(str_replace(' ','&#160;',htmlspecialchars(print_r($domains,true)))).'</div>';
                foreach($domains as $domain) {

                }

            }

            // We check if we'll have to load the params for a special site because the action is to edit it
            if(isset($_POST['editSite'])) {
                $id = $_POST['editSite'];
            }
        }

        $values['existing'] = $this->sites_list();

        foreach($values['existing'] as $site) {
            if($site['id'] == $id) {
                // This is the site we are editing
                $values['selected'] = $site;
            }
        }

        $this->render('sites_manage', $values);
    }

    public function sites_add() {

    }

    protected function sites_list() {
        $list = scandir(SH_SITES_FOLDER);
        include(SH_SITES_FOLDER.'list.php');

        $cpt = 1;
        foreach($list as $site) {
            if(substr($site,0,1) != '.' && is_dir(SH_SITES_FOLDER.$site)) {
                $ret[$cpt]['id'] = $cpt;
                $ret[$cpt]['name'] = $site;
                $domains = array_keys($sites,$site);
                $allDomains = '';
                $separator = '';
                foreach($domains as $domain) {
                    $domainRewritten = str_replace(
                        array('`','(.*\.)?','\.'),
                        array('','*.','.'),
                        $domain
                    );
                    $ret[$cpt]['domains'][]['url'] = $domainRewritten;
                    $allDomains .= $separator.$domainRewritten;
                    $separator = "\n";
                }
                $ret[$cpt]['allDomains'] = $allDomains;

                $cpt++;
            }
        }
        return $ret;
    }

    public function sites_uninstall() {

    }

    /* CLASSES MANAGEMENT */

    public function classes_install() {

    }

    public function classes_list() {

    }

    public function classes_uninstall() {

    }

    /* TEMPLATES MANAGEMENT */

    public function templates_install() {

    }

    public function templates_list() {

    }

    public function templates_uninstall() {

    }


    public function __tostring() {
        return get_class();
    }
}
