<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')) {header('location: directCallForbidden.php');}

/**
 * class sh_templatesLister
 *
 */
class sh_templatesLister extends sh_core {
    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );

    public function construct() {
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->helper->addClassesSharedMethods('sh_contact', '', __CLASS__);
            $this->helper->addClassesSharedMethods('sh_sitemap', '', __CLASS__);
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
    }

    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew(){
        if($this->getParam('active', false)){
            $list = $this->getList();
            $priority = $this->getParam('sitemap>priority',0.4);
            $this->addToSitemap('templatesLister/showList/', $priority, 'weekly');
            foreach($list as $element){
                $this->addToSitemap($element['page'], $priority, 'monthly');
            }
        }
        return true;
    }

    /**
     * public function getTitle
     * Return the name of the template which was passed as argument
     */
    public function getTitle($id){
        // Static shortcut not to loop too many times
        static $names = array();
        if(isset($names[$id])){
            return $names[$id];
        }
        $scan =  scandir(SH_TEMPLATE_FOLDER.'preview/');
        if(is_array($scan)){
            foreach($scan as $element){
                $folder = SH_TEMPLATE_FOLDER.'preview/'.$element.'/';
                if(substr($element,0,1) != '.' && is_dir($folder)){
                    if(preg_match('`((sh_|cm_)[1-9][0-9]*)-(.+)`', $element, $matches)){
                        // Adds this value to the shortcuts
                        $names[$matches[1]] = $matches[3];
                        if($id == $matches[1]){
                            return $matches[3];
                        }
                    }
                }
            }
        }
        return false;
    }

     /**
     * public function showList
     */
    public function showList(){
        if(!$this->getParam('active', false)){
            $this->linker->path->redirect(404);
        }
        $this->linker->html->setTitle($this->getI18n('list_title'));

        $templates = $this->getList();

        ksort($templates);
        $values['templates'] = $templates;
        $this->render('list',$values);
    }

    public function getSubmenus($method,$id){
        $ret = array();
        if($method == 'showList'){
            $templates = $this->getList();

            ksort($templates);
            
            if(is_array($templates)){
                foreach($templates as $templateId=>$template){
                    $ret[$templateId] = array(
                        'title' => $template['name'],
                        'link' => $template['link']
                    );
                }
            }
            
        }
        return $ret;
    }

    public function getList($alsoListRestricted = false){
        $elements = scandir(SH_TEMPLATE_FOLDER);
        $templates = array();
        foreach($elements as $element){
            if(preg_match('`((sh_|cm_)[1-9][0-9]*)-(.+)`', $element, $matches)){
                if($alsoListRestricted || !file_exists(SH_TEMPLATE_FOLDER.$element.'/restricted.php')){
                    // This is a template folder
                    $template = $this->linker->template->getTemplateDescription($element);
                    if($template){
                        $templates[$matches[1]] = $template;
                    }
                }
            }
        }
        return $templates;
    }

    public function getRestrictedList(){
        return $this->linker->template->restricted_getAll();
    }

    public function build(){
        $this->onlyMaster();
        $formId = 'buildVariations';
        if($this->formSubmitted($formId)){
            $templateName = $_POST['template'];
            $baseVariation = $_POST['variation'];
            $variationFolder = SH_TEMPLATE_FOLDER.$templateName.'/images/variations/';

            if(!is_dir($variationFolder.$baseVariation.'/')){
                $values['variation']['folder'] = $variationFolder.$baseVariation.'/';
                $this->render('noBaseFolder',$values);
            }else{
                if($baseVariation != 'default'){
                    // If needed, we empty the "default" variation folder
                    if(is_dir(SH_TEMPLATE_FOLDER.$templateName.'/images/variations/default/')){
                        $this->helper->emptyDir(SH_TEMPLATE_FOLDER.$templateName.'/images/variations/default/');
                    }
                    // And copy the base images to that folder
                    $this->helper->moveDirContent(
                        SH_TEMPLATE_FOLDER.$templateName.'/images/variations/'.$baseVariation.'/',
                        SH_TEMPLATE_FOLDER.$templateName.'/images/variations/default/'
                    );
                }
                flush();

                $this->buildVariations($variationFolder,$templateName,$baseVariation);
                exit;
            }
        }

        $this->linker->html->setTitle($this->getI18n('variationsCreatorTitle'));
        $values['form']['id'] = $formId;
        $scan =  scandir(SH_TEMPLATE_FOLDER);
        $areNotTemplates = array('builder','fonts','global','preview','variations');
        if(is_array($scan)){
            foreach($scan as $element){
                if(!in_array($element,$areNotTemplates)){
                    $folder = SH_TEMPLATE_FOLDER.$element.'/';

                    if(substr($element,0,1) != '.' && is_dir($folder)){
                        if(preg_match('`((sh_|cm_)([1-9][0-9]*))-(.+)`', $element, $matches)){
                            list($all,$id,$type,$num,$name) = $matches;

                            $longId = str_pad($num, 6, "0", STR_PAD_LEFT);
                            $values['templates'][$longId] = array(
                                'name' => $id.' - '.$name,
                                'id' => $element
                            );
                       }
                   }
                }
            }
        }
        ksort($values['templates']);
        $values['variations'][] = array(
            'name' => 'default',
            'state' => 'selected'
        );
        for($a=0;$a<=360;$a+=20){
            $values['variations'][]['name'] = $a;
        }

        $this->render('build_variations', $values);
        return true;
    }

    /**
     * protected function buildVariations
     *
     */
    protected function buildVariations($variationFolder,$templateName='',$baseVariation=''){
        flush();
        set_time_limit(0);

        echo '<html><head>
<title>Shopsailors - Variations builder</title>
<link rel="shortcut icon" href="'.$this->linker->favicon->getPath().'"></link>';
        echo '</head><body>';
        echo '<div style="font-weight:bold">Building the variations for the template '.$templateName.'</div>';
        echo 'Base variation : '.$baseVariation;

        $filesList = $this->getFilesList($variationFolder.'default/');

        $hexColors = array(
            '#cb0000','#cb4200','#cb8700','#cbcb00','#87cb00','#42cb00','#00cb00',
            '#00cb43','#00cb86','#00cbcb','#0086cb','#0042cb','#0000cb','#4200cb',
            '#8700cb','#cb00cb','#cb0087','#cb0043','#cbcbcb'
        );

        if(is_array($filesList)){
            $total = 0;
            for($degree = 0; $degree<=360; $degree+=20){
                $this->helper->emptyDir($variationFolder.$degree);

                if(!is_dir($variationFolder.$degree)){
                    mkdir($variationFolder.$degree);
                }
            }
            echo '<style>table td{background-color:white;text-align:center;font-size:70%;padding:2px;}</style>';
            echo '<table cellpadding="0" cellspacing="1" style="background-color:black;"><tr><td>#</td>';
            foreach($filesList as $file){
                echo '<td style="background-color:orange;">'.str_replace('.png','',$file['name']).'</td>';
            }
            echo '<td>Time</td><td>Total</td>';
            echo '</tr>';
            flush();
            for($degree = 0; $degree<=360; $degree+=20){
                $mtime = explode(" ",microtime());
                $starttime = array_sum($mtime);
                $allowedExts = array('png','jpg','jpeg');
                echo '<tr>';
                echo '<td style="background-color:'.$hexColors[($degree / 20)].'">'.$degree.'</td> ';
                foreach($filesList as $file){
                    $ext = strtolower(array_pop(explode('.',$file['name'])));
                    if(in_array($ext,$allowedExts)){
                        if(!is_dir($variationFolder.$degree.'/'.$file['folder'].'/')){
                            mkdir($variationFolder.$degree.'/'.$file['folder'].'/',0777,true);
                        }
                        $destImage = $variationFolder.$degree.'/'.$file['folder'].'/'.$file['name'];
                        $destImage = str_replace('//','/',$destImage);
                        $srcImage = $variationFolder.'default/'.$file['folder'].'/'.$file['name'];
                        $srcImage = str_replace('//','/',$srcImage);
                        $mtime2 = microtime(true);
                        flush();
                        if($degree != 360){
                            sh_colors::setHueToImage($srcImage,$destImage,$degree);
                        }else{
                            sh_colors::setHueToImage($srcImage,$destImage,0,-100);
                        }
                        echo '<td>'.substr((microtime(true) - $mtime2),0,5).'s</td>';
                        flush();
                    }
                }
                $mtime = explode(" ",microtime());
                $thisTime = array_sum($mtime)-$starttime;
                $total += $thisTime;
                echo '<td>'.substr($thisTime,0,5).'s</td><td>'.substr($total,0,5).'s</td>';
                echo '</tr>';

                flush();
            }
            echo '</table>';
            echo 'All variations were built successfully in '.floor($total / 60)." minutes and ".($total % 60).' seconds<br />';
            echo 'Press F5 to continue (and press OK if needed).';
            echo '</body></html>';
        }
    }

    /**
     * protected function getFilesList
     *
     */
    protected function getFilesList($initFolder,$folder = ''){
        if($folder == ''){
            $folder = $initFolder;
        }
        $elements = scandir($folder);
        if(is_array($elements)){
            foreach($elements as $element){
                if(substr($element,0,1) != '.' && is_dir($folder.$element)){
                    $files = array_merge(
                        $files,
                        $this->getFilesList($initFolder,$folder.$element)
                    );
                }elseif(substr($element,0,1) != '.'){
                    $files[] = array(
                        'folder' => str_replace($initFolder,'',$folder),
                        'name'=>$element
                    );
                }
            }
        }
        return $files;
    }

     /**
     * public function show
     */
    public function show(){
        if(!$this->getParam('active', false)){
            $this->linker->path->redirect(404);
        }
        
        $id = $_GET['template'];

        $realName = $this->getTemplateRealName($id);
        $errorRestricted = file_exists(SH_TEMPLATE_FOLDER.$realName.'/restricted.php');
        $template = $this->linker->template->getTemplateDescription($realName);
        if($errorRestricted || !$template){
            $this->linker->path->error(404);
        }

        $template['template']['description'] = $template['description'];
        $template['template']['variations'] = $template['variations'];
        $template['template']['background'] = $template['background'];

        $this->render('show', $template);
        return true;
    }

    protected function getTemplateRealName($id){
        $elements = scandir(SH_TEMPLATE_FOLDER);
        foreach($elements as $template){
            if(substr($template,0,strlen($id) + 1) == $id.'-'){
                return $template;
            }
        }
        return false;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);
        if($method == 'show'){
            return '/'.$this->shortClassName.'/show.php';
        }
        if($method == 'showList'){
            return '/'.$this->shortClassName.'/showList.php';
        }
        if($method == 'build'){
            return '/'.$this->shortClassName.'/build.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if($uri == '/'.$this->shortClassName.'/show.php'){
            return $this->shortClassName.'/show/';
        }
        if(preg_match('`/'.$this->shortClassName.'/([^/]+)(/([0-9]+)(-[^/]+)?)?\.php`',$uri,$matches)){
            if($matches[1] == 'show'){
                return $this->shortClassName.'/show/'.$matches[3];
            }
        }
        if($uri == '/'.$this->shortClassName.'/showList.php'){
            return $this->shortClassName.'/showList/';
        }
        if($uri == '/'.$this->shortClassName.'/build.php'){
            return $this->shortClassName.'/build/';
        }
        return false;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}
