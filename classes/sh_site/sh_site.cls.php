<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')) {header('location: directCallForbidden.php');}

/**
 * Class that manages the params shared for all classes of a single website.
 */
class sh_site extends sh_core{
    const GOOGLE_FOR_WEBMASTERS = 'googleForWebmasters';
    const I18N_DEFAULTTITLE = 20;
    const I18N_SITENAME = 21;
    const I18N_DEFAULTHEADLINE = 22;
    const I18N_METADESCRIPTION = 23;

    /**
     * Constructor
     */
    public function construct(){
        $this->renderingConstants = array(
            'I18N_DEFAULTTITLE'=>self::I18N_DEFAULTTITLE,
            'I18N_SITENAME'=>self::I18N_SITENAME,
            'I18N_DEFAULTHEADLINE'=>self::I18N_DEFAULTHEADLINE,
            'I18N_METADESCRIPTION'=>self::I18N_METADESCRIPTION
        );
        $this->templateName = $this->getParam('template','12-boutique_clean');

        if(!$this->templateIsAuthorized($this->templateName)){
             $this->templateName = '12-boutique_clean';
        }

        $this->templateFolder = SH_TEMPLATE_FOLDER.$this->templateName.'/';

        $this->variation = $this->getParam('variation',0);
        $this->lang = $this->getParam('lang','fr_FR');
        $this->langs = $this->getParam('langs',array('fr_FR','en_GB'));

        $this->siteName = $this->getI18n(self::I18N_SITENAME);
        $this->defaultTitle = $this->getI18n(self::I18N_DEFAULTTITLE);
        $this->defaultHeadLine = $this->getI18n(self::I18N_DEFAULTHEADLINE);
        $this->metaDescription = $this->getI18n(self::I18N_METADESCRIPTION);

        $this->title = $this->getParam('title','');
        $this->companyName = $this->getParam('companyName','');
        $this->font = SH_FONTS_FOLDER.$this->getParam('font');
        $this->logo = $this->getParam('logo','');

        $this->diaporamaDisplay = $this->getParam('diaporamaDisplay', false);

        if(!is_dir(SH_LOGO_FOLDER)){
            mkdir(SH_LOGO_FOLDER);
            $this->links->helper->writeInFile(
                SH_LOGO_FOLDER.sh_browser::RIGHTSFILE,
                sh_browser::ADD
            );
            $this->links->helper->writeInFile(
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
    }

    public function templateIsAuthorized($template){
        if(file_exists(SH_TEMPLATE_FOLDER.$template.'/restricted.php')){
            $allowedTemplates = $this->getParam('allowedTemplates', array());
            if(!in_array($template,$allowedTemplates)){
                return false;
            }
        }
        return true;
    }

    /**
     * Method called by sh_browser when some change is done on the files of a
     * diaporama directory (adding, renaming or removing of an image).
     * @param str $event The event that braught us to here.
     * @param str $folder The name of the folder in which the change occured.
     * @return bool Always returns true.
     */public function onChangeLogo($event, $folder){
        $this->debug(__METHOD__, 2, __LINE__);
        if($folder != SH_IMAGES_FOLDER.'logo/'){
            return false;
        }
        $list = scandir(SH_IMAGES_FOLDER.'logo/');
        foreach($list as $image){
            if(substr($image,0,1) != '.'){
                $parts = explode('.',$image);
                $type = array_shift($parts);
                $ext = array_pop($parts);

                if(
                    !in_array($type,array('logo','banner','rectangular'))
                    || $ext != 'png'
                ){
                    $newImage = $image;
                    $newExt = $ext;
                }
            }
        }
        if(!isset($newImage)){
            // We have to find out which of the images is the new one

        }

        copy($folder.$newImage,$folder.'logo.'.$newExt);
        $this->links->browser->resample_image(
            $folder.'logo.'.$newExt,
            400,
            400
        );

        copy($folder.$newImage,$folder.'rectangular.'.$newExt);
        $this->links->browser->resample_image(
            $folder.'rectangular.'.$newExt,
            960,
            320
        );
        copy($folder.$newImage,$folder.'banner.'.$newExt);
        $this->links->browser->resample_image(
            $folder.'banner.'.$newExt,
            1200,
            240
        );
        unlink($folder.$newImage);
        return true;
    }

    /**
     * public function periodical_maintenance
     * @deprecated
     */
    public function periodical_maintenance(){
        $this->links->cache->disable();
        $this->links->user->isMasterServer();
        $this->onlyAdmin();
        include(SH_SITES_FOLDER.'list.php');
        $folders = scandir(SH_SITES_FOLDER);
        $uri = $this->links->path->getUri('pages/maintenance/');
        foreach($folders as $element){
            if(is_dir(SH_SITES_FOLDER.$element) && substr($element,0,1)!='.'){
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
                if(trim($domain) != ''){
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
    public function changeParams(){
        $this->links->cache->disable();
        $this->onlyAdmin();
        $formResult = $this->formSubmitted('paramsEditor');
        if($formResult === true){
            if($_POST['variation_value'] != $this->variation){
                $this->setParam('variation',$_POST['variation_value']);
                $this->links->menu->reset();
            }
            // Gets the new values for i18n
            $allowedI18n = $this->links->i18n->getSelectorValues(
                'change_site_languages'
            );
            list($defaultI18n) = $this->links->i18n->getSelectorValues(
                'change_site_defaultLanguage'
            );
            $this->setI18n(self::I18N_DEFAULTTITLE, $_POST['defaultTitle']);
            $this->setI18n(self::I18N_SITENAME, $_POST['siteName']);
            $this->setI18n(self::I18N_DEFAULTHEADLINE, $_POST['defaultHeadline']);
            $this->setI18n(self::I18N_METADESCRIPTION, $_POST['metaDescription']);

            // Writes whether the site needs connected users or not
            $this->setParam('variation',$_POST['variation_value']);
            $this->setParam('langs',$allowedI18n);
            $this->setParam('lang',$defaultI18n);
            $this->writeParams();
            // Loads the new values
            $this->siteName = $this->getParam('siteName');
            $this->defaultTitle = $this->getParam('defaultTitle');
            $this->defaultHeadLine = $this->getParam('defaultHeadLine');
            $this->metaDescription = $this->getParam('metaDescription');

            $this->links->googleServices->setAnalytics($_POST['analytics']);
            $this->links->googleServices->setGoogleForWebmasters(
                $_POST['googleForWebmasters']
            );

            $this->links->path->redirect();
        }
        $values['config']['logoPath'] = SH_IMAGES_FOLDER.'logo/';

        $values['i18nclass']['activeLanguages'] = $this->links->i18n->createSelector(
            'change_site_languages',
            $this->getParam('langs'),
            'checkbox',
            false
        );
        $values['i18nclass']['defaultLanguage'] =
            $this->links->i18n->createSelector(
                'change_site_defaultLanguage',
                array($this->getParam('lang')
            ),
            'radio',
            true
        );
        $values['favicon']['changer'] = $this->links->favicon->getChanger();

        $values['analytics']['code'] =
            $this->links->googleServices->getAnalytics(true);
        $values['googleForWebmasters']['link'] =
            $this->links->googleServices->getGoogleForWebmasters(true);

        $values['config']['variation_value'] = $this->links->site->variation;
        $this->render('changeParams',$values);
        return true;
    }

    /**
     * This method is automatically called by sh_template when the admin/master
     * changes the template he is using.<br />
     * It does everything that has to be done in this class when it occurs.
     * @param str $template The name of the template that will now be used.
     */
    public function changeTemplate($template){
        if(!$this->templateIsAuthorized($template)){
            return false;
        }
        $this->setParam('template', $template);
        $this->writeParams();
        return true;
    }

    /**
     * This method is automatically called by sh_template when the admin/master
     * changes the template he is using.<br />
     * It does everything that has to be done in this class when it occurs.
     * @param str $template The name of the template that will now be used.
     */
    public function template_change($template){

    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);
        if($method == 'changeParams'){
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
    public function translateUriToPage($uri){
        if(preg_match('`/'.$this->shortClassName.'/changeParams\.php`',$uri)){
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
    public function __get($name){
        if(isset($this->$name)){
            return $this->name;
        }
        echo 'We tried to access the value of '.$name.'<br />';
        return false;
    }

    public function __tostring(){
        return get_class();
    }
}
