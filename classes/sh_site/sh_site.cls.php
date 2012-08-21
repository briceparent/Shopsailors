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
 * Class that manages the params shared for all classes of a single website.
 */
class sh_site extends sh_core {
    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );
    const GOOGLE_FOR_WEBMASTERS = 'googleForWebmasters';

    public $templateName = '';
    public $templateFolder = '';
    public $variation = '';
    public $saturation = '';
    public $images_suffix = '';
    public $lang = '';
    public $langs = '';
    public $defaultTitle = '';
    public $siteName = '';
    public $defaultHeadLine = '';
    public $metaDescription = '';
    public $title = '';
    public $companyName = '';
    public $font = '';
    public $logo = '';

    /**
     * Constructor
     */
    public function construct() {
        //echo __CLASS__.':'.__LINE__.'<br />';
        
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION) {
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->helper->addClassesSharedMethods('sh_admin', sh_admin::ADMINMENUENTRIES, __CLASS__);
            $this->helper->addClassesSharedMethods('sh_template', 'change', __CLASS__);

            if(!is_dir(SH_LOGO_FOLDER)) {
                if(!is_dir(SH_IMAGES_FOLDER)) {
                    $this->helper->createDir(SH_IMAGES_FOLDER);
                    $this->helper->writeInFile(
                        SH_IMAGES_FOLDER.sh_browser::OWNERFILE,
                        sh_browser::createUserName()
                    );
                    $this->helper->writeInFile(
                        SH_IMAGES_FOLDER.sh_browser::RIGHTSFILE,
                        sh_browser::READ
                    );
                }
                mkdir(SH_LOGO_FOLDER);
                $this->helper->writeInFile(
                    SH_LOGO_FOLDER.sh_browser::RIGHTSFILE,
                    sh_browser::ADD + sh_browser::READ
                );
                $this->helper->writeInFile(
                    SH_LOGO_FOLDER.sh_browser::OWNERFILE,
                    sh_browser::createUserName()
                );
                sh_browser::addEvent(
                    sh_browser::ONCHANGE,
                    SH_LOGO_FOLDER,
                    __CLASS__,
                    'onChangeLogo'
                );
            }
            $this->setClassInstalledVersion(self::CLASS_VERSION);
            $this->writeParams();
            // Constructing classes that are not called anywhere else (but by the user) in order to have them upgraded to
            $this->linker->flash;
            $this->linker->video;
            $this->linker->sound;
            $this->linker->dev;
            $this->linker->fonts;
        }
        if($this->getParam('title', false) === false) {
            $lang = $this->linker->i18n->getLang();
            $this->setParam('title',$this->setI18n(0, '',$lang));
            $this->setParam('siteName',$this->setI18n(0, '',$lang));
            $this->setParam('headline',$this->setI18n(0, '',$lang));
            $this->setParam('meta',$this->setI18n(0, '',$lang));
            $this->writeParams();
        }
        $this->renderingConstants = array(
            'I18N_DEFAULTTITLE'=>$this->getParam('title'),
            'I18N_SITENAME'=>$this->getParam('siteName'),
            'I18N_DEFAULTHEADLINE'=>$this->getParam('headline'),
            'I18N_METADESCRIPTION'=>$this->getParam('meta')
        );
        $this->templateName = $this->getParam('template','sh_2-boutique_clean');

        if(!$this->templateIsAuthorized($this->templateName)) {
            $this->templateName = 'sh_2-boutique_clean';
        }

        $this->templateFolder = SH_TEMPLATE_FOLDER.$this->templateName.'/';

        $this->variation = $this->getParam('variation',0);
        $this->saturation = $this->getParam('saturation','normal');

        $this->images_suffix = 'suf='.substr(md5($this->templateName.$this->variation.$this->saturation),0,5);

        $this->lang = $this->getParam('lang','fr_FR');
        $this->langs = $this->getParam('langs',array('fr_FR','en_GB'));

        $this->defaultTitle = $this->getI18n($this->getParam('title'));
        $this->siteName = $this->getI18n($this->getParam('siteName'));
        $this->defaultHeadLine = $this->getI18n($this->getParam('headline'));
        $this->metaDescription = $this->getI18n($this->getParam('meta'));

        $this->title = $this->getParam('title','');
        $this->companyName = $this->getParam('companyName','');
        $this->font = SH_FONTS_FOLDER.$this->getParam('font');
        $this->logo = $this->getParam('logo','');

        $this->diaporamaDisplay = $this->getParam('diaporamaDisplay', false);

    }

    public function master_getMenuContent() {
        return array();
    }
    public function admin_getMenuContent() {
        $adminMenu['Assistance'][] = array(
            'target'=>'_blank',
            'link'=>'http://wiki.shopsailors.org',
            'text'=>'Aide en ligne Shopsailors Wiki',
            'icon'=>'picto_contactus.png'
        );
        $adminMenu['Assistance'][] = array(
            'link'=>'mailto:assistance@websailors.fr',
            'text'=>'Contacter le service technique',
            'icon'=>'picto_contactus.png'
        );

        $adminMenu['Contenu']['top'] = array(
            'link'=>'site/changeParams/','text'=>'Gérer le site','icon'=>'picto_tool.png'
        );

        return $adminMenu;
    }

    public function templateIsAuthorized($template) {
        if(!is_dir(SH_TEMPLATE_FOLDER.$template)) {
            return false;
        }
        if(file_exists(SH_TEMPLATE_FOLDER.$template.'/restricted.php')) {
            return sh_template::restricted_isAuthorized($template);
        }
        return true;
    }

    /**
     * Method called by sh_browser when some change is done on the files of a
     * diaporama directory (adding, renaming or removing of an image).
     * @param str $event The event that braught us to here.
     * @param str $folder The name of the folder in which the change occured.
     * @return bool Always returns true.
     */
    public function onChangeLogo($event, $folder) {
        $this->debug(__METHOD__, 2, __LINE__);
        if($folder != SH_IMAGES_FOLDER.'logo/') {
            return false;
        }
        $list = scandir(SH_IMAGES_FOLDER.'logo/');
        foreach($list as $image) {
            if(substr($image,0,1) != '.') {
                $parts = explode('.',$image);
                $type = array_shift($parts);
                $ext = array_pop($parts);

                if($type != 'logo' || $ext != 'png') {
                    $newImage = $image;
                    $newExt = $ext;
                    break;
                }
            }
        }
        if(isset($newImage)) {
            rename($folder.$newImage,$folder.'logo.'.$newExt);

        }
        
        // We should also delete the resized versions
        foreach (glob($folder.'*.resized.*') as $filename) {
            unlink($filename);
        }

        return true;
    }

    /**
     * public function periodical_maintenance
     * @deprecated
     */
    public function periodical_maintenance() {
        $this->linker->cache->disable();
        $this->linker->user->isMasterServer();
        $this->onlyAdmin();
        include(SH_SITES_FOLDER.'list.php');
        $folders = scandir(SH_SITES_FOLDER);
        $uri = $this->linker->path->getUri('pages/maintenance/');
        foreach($folders as $element) {
            if(is_dir(SH_SITES_FOLDER.$element) && substr($element,0,1)!='.') {
                $domain = array_search($element,$sites);
                $domain = str_replace(
                    array(
                    '.*',   '\.',   '.+',   '(',    '?',   ')',    '`'
                    ),
                    array(
                    'www',   '.',    'aa',  '',     '',     '',     ''
                    ),
                    $domain
                );
                if(trim($domain) != '') {
                    echo 'http://'.$domain.$uri.'<br />';
                    //echo file_get_contents('http://'.$domain.$uri);
                }
            }
        }
    }

    /**
     * Function displaying the form to change the params, and to save them.
     * @access Allowed only to admins and masters
     */
    public function changeParams() {
        $this->linker->cache->disable();
        $this->onlyAdmin(true);
        $formResult = $this->formSubmitted('paramsEditor');
        if($formResult === true) {
            $formClasses = $this->helper->getClassesSharedMethods(__CLASS__, 'sharedSettings');
            foreach($formClasses as $class){
                $formContent = $this->linker->$class->setSharedSetting();
            }
            // Gets the new values for i18n
            $allowedI18n = array_keys($_POST['change_site_languages_enabled']);
            $defaultI18n = $_POST['change_site_languages_default'];

            $this->setI18n($this->getParam('title'), $_POST['defaultTitle']);
            $this->setI18n($this->getParam('siteName'), $_POST['siteName']);
            $this->setI18n($this->getParam('headline'), $_POST['defaultHeadline']);
            $this->setI18n($this->getParam('meta'), $_POST['metaDescription']);

            $this->setParam('langs',$allowedI18n);
            $this->setParam('lang',$defaultI18n);
            $this->setParam('variation',$_POST['variation_value']);
            $this->setParam('saturation',$_POST['saturation']);
            $this->writeParams();

            // Loads the new values
            $this->siteName = $this->getParam('siteName');
            $this->defaultTitle = $this->getParam('defaultTitle');
            $this->defaultHeadLine = $this->getParam('defaultHeadLine');
            $this->metaDescription = $this->getParam('metaDescription');

            $this->linker->googleServices->setAnalytics($_POST['analytics']);
            $this->linker->googleServices->setGoogleForWebmasters(
                $_POST['googleForWebmasters']
            );

            $this->linker->variation->prepare($this->templateName, $_POST['variation_value'], $_POST['saturation']);
            $this->linker->html->addMessage($this->getI18n('settings_have_been_changed'));

            $this->linker->path->redirect();
        }
        $values['config']['logoPath'] = SH_IMAGES_FOLDER.'logo/';

        $values['i18nclass']['activeLanguages'] = $this->linker->i18n->createDoubleSelector(
            'change_site_languages',
            $this->getParam('langs'),
            $this->getParam('lang')
        );
        
        $formClasses = $this->helper->getClassesSharedMethods(__CLASS__, 'sharedSettings');
        foreach($formClasses as $class){
            $formContent = $this->linker->$class->getSharedSettings();
            if(!empty($formContent)){
                $values['modules'][] = $formContent;
            }
        }

        $values['favicon']['changer'] = $this->linker->favicon->getChanger();

        $values['analytics']['code'] =
            $this->linker->googleServices->getAnalytics(true);
        $values['googleForWebmasters']['link'] =
            $this->linker->googleServices->getGoogleForWebmasters(true);

        $values['config']['variation_value'] = $this->linker->site->variation;
        $values['config']['saturation_'.$this->linker->site->saturation] = 'checked';
        $miniature_image = $this->linker->site->variation.'_'.$this->linker->site->saturation.'.png';
        $values['config']['variations_miniatures_root'] = $this->linker->path->changeToShortFolder(
            $this->templateFolder.'images/miniatures/'
        );
        $values['config']['variation_miniature'] = $values['config']['variations_miniatures_root'].$miniature_image;

        $this->render('changeParams',$values);
        return true;
    }

    /**
     * This method is automatically called by sh_template when the admin/master
     * changes the template he is using.<br />
     * It does everything that has to be done in this class when it occurs.
     * @param str $template The name of the template that will now be used.
     */
    public function changeTemplate($template) {
        if(!$this->templateIsAuthorized($template)) {
            return false;
        }
        $this->setParam('template', $template);
        $this->writeParams();

        $this->templateName = $template;

        if(!$this->templateIsAuthorized($this->templateName)) {
            $this->templateName = 'sh_2-boutique_clean';
        }

        $this->templateFolder = SH_TEMPLATE_FOLDER.$this->templateName.'/';

        return true;
    }

    /**
     * This method is automatically called by sh_template when the admin/master
     * changes the template he is using.<br />
     * It does everything that has to be done in this class when it occurs.
     * @param str $template The name of the template that will now be used.
     */
    public function template_change($template) {
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page) {
        list($class,$method,$id) = explode('/',$page);
        if($method == 'changeParams') {
            $uri = '/'.$this->shortClassName.'/changeParams.php';
            return $uri;
        }

        return parent::translatePageToUri($page);
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri) {
        if(preg_match('`/'.$this->shortClassName.'/changeParams\.php`',$uri)) {
            $page = $this->shortClassName.'/changeParams/';
            return $page;
        }

        return parent::translateUriToPage($uri);
    }

    /**
     * Returns a site's parametter.
     * @example
     * <code>
     * echo $sh_site->defaultTitle;<br />
     * // Will echo the content of $this->defaultTitle,<br />
     * // even if it is protected or private.<br />
     * // In that cases, it allows to get the values, <br />
     * // but not to set them.
     * </code>
     * @param string $name The name of the param that should be read
     * @return mixed
     * Returns the value of the param, if found, or false
     */
    public function __get($name) {
        if(isset($this->$name)) {
            return $this->name;
        }
        echo 'We tried to access the value of '.$name.'<br />';
        return false;
    }

    public function __tostring() {
        return get_class();
    }
}
