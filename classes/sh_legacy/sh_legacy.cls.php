<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')) {
    header('location: directCallForbidden.php');
}

/**
 * Class that display and manages html contents, like company presentation for example.
 */
class sh_legacy extends sh_core {
    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->helper->addClassesSharedMethods('sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__);
            $this->helper->addClassesSharedMethods('sh_contact', '', __CLASS__);
            $this->helper->addClassesSharedMethods('sh_sitemap', '', __CLASS__);
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
    }

    public function master_getMenuContent() {
        return array();
    }
    public function admin_getMenuContent() {
        $adminMenu['Contenu'][] = array(
            'link'=>'legacy/edit/',
            'text'=>'Modifier les mentions légales',
            'icon'=>'picto_modify.png'
        );

        return $adminMenu;
    }

    public function getLegacyLine() {
        $values['links'][] = array(
            'link'=>'http://www.websailors.fr',
            'textBefore'=>$this->getI18n('hosterBeforeText'),
            'text'=>$this->getI18n('hosterText'),
            'textAfter'=>$this->getI18n('hosterAfterText'),
            'target'=>'_blank'
        );
        $separator = $this->getI18n('separatorInLegacyLine');
        $values['links'][] = array(
            'link'=>$this->linker->path->getLink($this->shortClassName.'/show/'),
            'text'=>$this->getI18n('title'),
            'separator'=>$separator
        );
        $cpt = count($values['links']);

        $classes = $this->get_shared_methods();
        foreach($classes as $class) {
            $elements = $this->linker->$class->getLegacyEntries();
            foreach($elements as $element) {
                $val = $this->linker->$class->getLegacyEntry(trim($element));
                if($val) {
                    $values['links'][$cpt] = $val;
                    $values['links'][$cpt]['separator'] = $separator;
                    $cpt++;
                }
            }
        }
        
        $ret = $this->render('legacyLine', $values, false, false);
        return $ret;
    }

    /**
     * public function show
     */
    public function show() {
        $this->linker->html->setTitle($this->getI18n('title'));
        $values['legacy']['content'] = $this->getParam('content', '');
        $this->render('show', $values);
        return true;
    }

    /**
     * public function edit
     */
    public function edit() {
        $this->onlyAdmin(true);
        $this->linker->html->setTitle($this->getI18n('title'));

        if($this->formSubmitted('legacy_edit')) {
            $this->setParam('content', stripslashes($_POST['legacy']));
            $this->writeParams();
        }
        $values['legacy']['content'] = $this->getParam('content', '');
        $this->render('edit',$values);
    }

    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew() {
        $this->addToSitemap($this->shortClassName.'/show/', 0.2);
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page) {
        if($page == $this->shortClassName.'/edit/') {
            return '/'.$this->shortClassName.'/edit.php';
        }
        if($page == $this->shortClassName.'/show/') {
            return '/'.$this->shortClassName.'/show.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri) {
        if($uri == '/'.$this->shortClassName.'/edit.php') {
            return $this->shortClassName.'/edit/';
        }
        if($uri == '/'.$this->shortClassName.'/show.php') {
            return $this->shortClassName.'/show/';
        }
        return false;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }
}