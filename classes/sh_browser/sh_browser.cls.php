<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')) {
    header('location: directCallForbidden.php');
}

/**
 * Class used to build and manage the file browser.
 */
class sh_browser extends sh_core {
    protected $minimal = array('showContent' => true,'show'=>true,'rename'=>true,'delete'=>true,
            'addFolder'=>true,'deleteFolder'=>true, 'addFile'=>true,'editImage'=>true);
    protected $width = 400;
    protected $height = 400;
    protected $userName = '';
    public $defaultImageSelectorImage = '';

    protected $goToFolder = '';

    protected $imagesExtensions = '`(jpe?g|png|gif)$`';
    protected $forbiddenExtensions = '`(php.*|exe|html?|^\..*)$`';

    // Write accesses. Bitwise
    const READ = 1;
    const ADDFOLDER = 2;
    const RENAMEFOLDER = 4;
    const DELETEFOLDER = 8;
    const ADDFILE = 16;
    const RENAMEFILE = 32;
    const DELETEFILE = 64;
    const ANYONE = 128; // For shared folders. Doesn't care about the connected admin
    // Combined write accesses
    const ADD = 18; // ADDFILE + ADDFOLDER
    const RENAME = 36; // RENAMEFILE + RENAMEFOLDER
    const DELETE = 72; // DELETEFILE + DELETEFOLDER
    const WRITE = 126; // ADD + RENAME + DELETE
    const ALL = 127; // READ + WRITE

    const DEFAULTCREATIONRIGHTS = 127;

    const DIMENSIONFILE = '.dimensions';
    const NOMARGINS = '.noMargins';
    const RIGHTSFILE = '.rights';
    const OWNERFILE = '.owner';
    const NOMAXSIZEFILE = '.noMaxSize';
    const MESSAGEFILE = '.message';
    const HIDDEN = '.hidden';
    const HIDDENFILES = '.hiddenFiles';

    // Events
    const ONCHANGE = '.onchange';
    const ONADD = '.onadd';
    const ONDELETE = '.ondelete';
    const ONRENAME = '.onrename';

    const ONCHANGEFOLDER = '.onChangeFolder';
    const ONADDFOLDER = '.onAddFolder';
    const ONRENAMEFOLDER = '.onRenameFolder';
    const ONDELETEFOLDER = '.onDeleteFolder';


    public function construct() {
        $this->userName = self::createUserName();
        $this->defaultImageSelectorImage = SH_SHAREDIMAGES_PATH.'default/defaultContentImage.png';
        if(!is_dir(SH_IMAGES_FOLDER.'temp/')){
            $this->createFolder(
                SH_IMAGES_FOLDER.'temp/',
                self::READ + self::DELETEFILE
            );
        }
    }

    public function insertScript(){
        $singlePath = $this->getSinglePath();
        $this->linker->html->addScript($singlePath.'getBrowser.js');
    }

    /**
     * public static function createUserName
     *
     */
    public static function createUserName($site = '') {
        if($site == '' || is_null($site)) {
            $site = SH_SITE;
        }
        return str_replace('/','',$site);
    }

    /**
     * public function create
     *
     */
    public function create($folder,$action,$types = 'images') {
        $this->debug(__METHOD__, 2, __LINE__);
        $uid = str_replace(array('.',' '),'',microtime());
        if(!defined($folder)) {
            $cpt = count($_SESSION[__CLASS__]['pathes']);
            $cpt += 1;
            $_SESSION[__CLASS__]['pathes'][$cpt] = $folder;
            $folder = $cpt;
        }

        $_SESSION[__CLASS__][$uid] = array(
                'folder'=>$folder,
                'action'=>$action,
                'types'=>$types
        );
        $_SESSION[__CLASS__]['lastBuilt'] = $uid;
        return $uid;
    }

    /**
     * public function show
     *
     */
    public function show() {
        $this->debug(__METHOD__, 2, __LINE__);
        $this->onlyAdmin();

        if(!isset($_GET['type'])){
            $_GET['type'] = 'url';
            $_GET['folder'] = '';
        }

        if(isset($_GET['type'])){
            // New version
            if($_GET['type'] == 'url'){
                $folderName = $_GET['folder'];
                $types = $_GET['types'];
                $action = $_GET['action'];
                $element = $_GET['element'];
                $session = $this->editBrowserSession(false, $folderName, $action, $types, $element);
                $_GET['type'] = 'session';
                $_GET['session'] = $session;
            }
            if($_GET['type'] == 'session'){
                $session = $_GET['session'];
                $folderName = $_SESSION[__CLASS__][$session]['folder'];
                $types = $_SESSION[__CLASS__][$session]['types'];
                $action = $_SESSION[__CLASS__][$session]['action'];
                $element = $_SESSION[__CLASS__][$session]['element'];
                $this->goToFolder = $_SESSION[__CLASS__][$session]['lastFolder'];
            }
        }else{
            if(isset($_GET['formSession'])) {
                $this->debug('We get the browser\'s datas from the session', 3, __LINE__);
                // The browser was called passing parametters threw session, so we get them
                $session = $_GET['fromSession'];
                $_GET['folder'] = $_SESSION[__CLASS__][$session]['folder'];
                $_GET['types'] = $_SESSION[__CLASS__][$session]['types'];
                $_GET['returnAction'] = $_SESSION[__CLASS__][$session]['returnAction'];
                $_GET['id'] = $_SESSION[__CLASS__][$session]['id'];
            }elseif($_GET['browser_type'] == '') {
                $this->debug('We get the browser\'s datas from the url', 3, __LINE__);
                // The browser was called without any parametters, so we create them
                $_GET['folder'] = 'SH_IMAGES_FOLDER';
                $_GET['types'] = 'images';
                $_GET['returnAction'] = null;
                $_GET['id'] = 0;
            }

            $browser_type = $_GET['browser_type'];
            if(!defined($_GET['folder'])) {
                if(file_exists(SH_CLASS_SHARED_FOLDER.__CLASS__.'/'.$_GET['folder'].'.browser.php')) {
                    $this->debug('We try to get the folder to show...', 3, __LINE__);
                    $class = trim(file_get_contents(SH_CLASS_SHARED_FOLDER.__CLASS__.'/'.$_GET['folder'].'.browser.php'));
                    $theClass = $this->linker->$class;
                    $this->debug('... in the class '.$class, 3, __LINE__);
                    $folderName = constant($_GET['folder']);
                    $this->debug('The folder is '.$folderName, 3, __LINE__);
                }elseif(isset($_SESSION[__CLASS__]['pathes'][$_GET['folder']])) {
                    $this->debug('We get the browser\'s datas from the session, in the "pathes" variable', 3, __LINE__);
                    $folderName = $_SESSION[__CLASS__]['pathes'][$_GET['folder']];
                }elseif(is_dir(SH_IMAGES_FOLDER.$_GET['folder'])) {
                    $this->debug('We get the browser\'s datas into the images path, in the subfolder '.$_GET['folder'], 3, __LINE__);
                    $folderName = SH_IMAGES_FOLDER.$_GET['folder'];
                }else {
                    $this->debug('We don\'t know which folder to show...', 3, __LINE__);
                    $this->debug('The file '.SH_CLASS_SHARED_FOLDER.__CLASS__.'/'.$_GET['folder'].'.browser.php does not exist!', 3, __LINE__);
                    $folderName='ERROR';
                }
            }else {
                $this->debug('We get the folder to show in the constant '.$_GET['folder'], 3, __LINE__);
                $folderName = constant($_GET['folder']);
            }

            $this->debug('The folder to show is '.$folderName, 2, __LINE__);
            //$types = $_GET['types'];
            $action = $_GET['returnAction'];
            $id = $_GET['id'];
            if(!isset($fromSession)){
                $session = md5(microtime());

                $_GET['folder'] = $_SESSION[__CLASS__][$session]['folder'];
                $_GET['types'] = $_SESSION[__CLASS__][$session]['types'];
                $_GET['returnAction'] = $_SESSION[__CLASS__][$session]['returnAction'];
                $_GET['id'] = $_SESSION[__CLASS__][$session]['id'];
            }
        }
        $this->allowedTypes = $types;

        if($action == 'null' || is_null($action) || $action == '') {
            $elements['browser']['doaction'] = 'false';
        }else {
            $elements['browser']['doaction'] = 'true';
        }

        $elements['browser']['action'] = $action;
        $elements['browser']['element'] = $element;

        $elements['browser']['action'] = $action;
        $elements['browser']['id'] = $id;
        $elements['browser']['session'] = $session;


        $elements['browser']['base'] = 'http://'.$_SERVER['SERVER_NAME'];
        if(is_dir($folderName)) {
            $this->debug('The folder exists', 3, __LINE__);
            $activeFolder2 = $this->getSubFolders($folderName,$elements['folder']);

            if(!isset($activeFolder)) {
                $activeFolder = $activeFolder2;
            }
            $this->debug('active folder is '.$activeFolder, 3, __LINE__);
            $elements['browser']['initfolder'] = $activeFolder;

            if(!$elements['browser']['initfolder']) {
                $this->debug('The access to the folder is restricted', 2, __LINE__);
                $vars['restrictions']['notallowed'] = true;
                $vars['restrictions']['base'] = $this->linker->path->protocol.'://'.$this->linker->path->getDomain().'/';
                $vars['i18n'] = $this->__tostring();
                echo $this->render('restriction',$vars,false,false);
                return false;
            }
        }else {
            $this->debug('The folder '.$folderName.' was not found', 2, __LINE__);
            $vars['restrictions']['nofolder'] = true;
            $vars['restrictions']['base'] = $this->linker->path->protocol.'://'.$this->linker->path->getDomain().'/';
            $vars['i18n'] = $this->__tostring();
            echo $this->render('restriction',$vars,false,false);
            return false;
        }
        if(isset($_SESSION[__CLASS__]['goToFolder'])) {
            unset($_SESSION[__CLASS__]['goToFolder']);
        }
        if(isset($_SESSION[__CLASS__]['openedFolder'])) {
            $elements['browser']['initFolder'] = $_SESSION[__CLASS__]['openedFolder'];
            unset($_SESSION[__CLASS__]['openedFolder']);
        }
        $path = $this->linker->path;
        $class = $this->shortClassName;
        $elements['links']['showContent'] = $path->getLink($class.'/showContent/');
        $elements['links']['addFolder'] = $path->getLink($class.'/addFolder/');
        $elements['links']['delete'] = $path->getLink($class.'/delete/');
        $elements['links']['deleteFolder'] = $path->getLink($class.'/deleteFolder/');
        $elements['links']['rename'] = $path->getLink($class.'/rename/');
        $elements['links']['renameFolder'] = $path->getLink($class.'/renameFolder/');
        echo $this->render('index',$elements,false,false);
        return true;
    }

    /**
     * protected function getSubFolders
     *
     */
    protected function getSubFolders($folder,&$folders,$indent = '') {
        $this->debug(__METHOD__, 2, __LINE__);
        if(!file_exists($folder.'/'.self::HIDDEN)) {
            if(self::getRights($folder, self::READ)) {
                $this->debug('The user is allowed to access the folder '.$folder, 3, __LINE__);
                $folder = str_replace('//','/',$folder.'/');

                $uid = MD5('sh_browser_show'.microtime());
                $_SESSION[__CLASS__][$uid]['path'] = $folder;
                $_SESSION[__CLASS__][$uid]['types'] = $this->allowedTypes;
                if($this->goToFolder == $folder) {
                    $_SESSION[__CLASS__]['openedFolder'] = $uid;
                }

                $count = count($folders);

                $folders[$count]['indent']=$indent;
                $folders[$count]['image']='/templates/global/admin/sh_browser/folder.png';
                $folders[$count]['name']=basename($folder);
                $folders[$count]['path']=$uid;
                $scan =  scandir($folder);
                if(is_array($scan)) {
                    foreach($scan as $element) {
                        if(substr($element,0,1) != '.' && is_dir($folder.$element)) {
                            if(self::getRights($folder.$element,self::READ)) {
                                $this->getSubFolders($folder.$element,$folders,$indent.'<img src="/images/shared/icons/transparent.png" style="width:8px" alt=""/>');
                            }
                        }
                    }
                }
                return $uid;
            }else {
                $this->debug('The user is not allowed to access the folder '.$folder, 0, __LINE__);
            }
        }else {
            $this->debug('The folder '.$folder.' is hidden, so the user may not access it', 0, __LINE__);
        }
        return false;
    }
    protected function getArbo($folder,&$arbo,$spacer = '') {
        //$this->debug(__METHOD__.'('.$folder.')', 2, __LINE__);
        if(!file_exists($folder.'/'.self::HIDDEN)) {
            if(self::getRights($folder, self::READ)) {
                $folder = str_replace('//','/',$folder.'/');
                $this->debug($spacer.basename($folder), 3, __LINE__);

                $uid = MD5('sh_browser_show'.microtime());
                $_SESSION[__CLASS__][$uid]['path'] = $folder;
                $_SESSION[__CLASS__][$uid]['types'] = $this->allowedTypes;
                if($_SESSION[__CLASS__]['goToFolder'].'/' == $folder) {
                    $_SESSION[__CLASS__]['openedFolder'] = $uid;
                }

                $arbo['name']=basename($folder);
                $arbo['path']=$uid;
                $scan =  scandir($folder);
                $cpt = 0;
                if(is_array($scan)) {
                    $sons = array();
                    $id = 0;
                    foreach($scan as $element) {
                        if(substr($element,0,1) != '.') {
                            if(is_dir($folder.$element)) {
                                //$this->debug($spacer.'adding $arbo['.$id.'] = '.$element.' in '.basename($folder), 3, __LINE__);
                                if(self::getRights($folder.$element,self::READ)) {
                                    $id++;
                                    $this->debug($spacer.'entering [folders]['.$id.']', 3, __LINE__);
                                    $this->getArbo($folder.$element,$sons['folders'][$id],$spacer.'    ');
                                    $arbo['arbo']['hasSubFolders'] = true;
                                }
                            }
                        }
                    }
                    $this->debug('', 3, __LINE__);
                    $this->debug('', 3, __LINE__);
                    $this->debug($spacer.'Sons : '.print_r($sons,true), 3, __LINE__);
                    //$this->render('oneFolder', $sons);
                    $arbo['content'] = $this->render('oneFolder', $sons, SH_TEMP_FOLDER.'arbo_loops.php', false);
                    $this->debug($spacer.'rendered : '.print_r($arbo,true), 3, __LINE__);
                    $this->debug('', 3, __LINE__);
                    $this->debug('', 3, __LINE__);
                }
                return $uid;
            }else {
                $this->debug('The user is not allowed to access the folder '.$folder, 0, __LINE__);
            }
        }else {
            $this->debug('The folder '.$folder.' is hidden, so the user may not access it', 0, __LINE__);
        }
        return false;
    }

    /**
     * public static function getRights
     *
     */
    public static function getRights($folder, $type = self::READ) {
        $linker = sh_linker::getInstance();
        if(file_exists($folder.'/'.self::RIGHTSFILE)) {
            $rights = (int) file_get_contents($folder.'/'.self::RIGHTSFILE);
            if(!($rights & self::ANYONE) && file_exists($folder.'/'.self::OWNERFILE)) {
                $owner = trim(file_get_contents($folder.'/'.self::OWNERFILE));
            }
            if((self::createUserName() == $owner) || ($rights & self::ANYONE)) {
                if($type & $rights) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * public function createFolder
     *
     */
    public function createFolder($folder,$rights = self::READ, $owner = '') {
        $this->debug(__METHOD__, 2, __LINE__);
        $container = dirname($folder);
        $folderName = self::modifyName(basename($folder));
        $newName = $container.'/'.$folderName;
        if($rights=='' || is_null($rights)) {
            $rights = self::READ;
        }
        if($owner=='' || is_null($owner)) {
            $owner = self::createUserName();
        }
        if(is_dir($folder)){
            $this->linker->helper->writeInFile($newName.'/'.self::RIGHTSFILE, $rights);
            $this->linker->helper->writeInFile($newName.'/'.self::OWNERFILE, $owner);
            return true;
        }elseif(mkdir($newName)) {
            $this->linker->helper->writeInFile($newName.'/'.self::RIGHTSFILE, $rights);
            $this->linker->helper->writeInFile($newName.'/'.self::OWNERFILE, $owner);
            return $newName;
        }
        echo 'We couldn\'t build the directory '.$newName.'<br />';
        return false;
    }

    /**
     * public function showContent
     */
    public function showContent() {
        $this->debug(__METHOD__, 2, __LINE__);
        if(!$this->isAdmin()) {
            $this->linker->path->error('403');
        }
        $uid = $_GET['folder'];
        $folder = $_SESSION[__CLASS__][$uid]['path'];
        // We check for the types list in the session, in the get args, and at last we give a default to 'images'
        $types = $_SESSION[__CLASS__][$_GET['folder']]['types'];
        if(!is_array($types)){
            $types = $this->getParam('types>'.$_SESSION[__CLASS__][$_GET['folder']]['types'],false);
            if(!$types && isset($_GET['types'])) {
                $types = $this->getParam('types>'.$_GET['types'],false);
            }
            if(!$types) {
                $types = $this->getParam('types>medias',array());
            }
        }
        $shownAsImage = $this->getParam('shownAsImage');

        $shortFolder = str_replace(SH_ROOT_FOLDER,'/',$folder);

        $vars['folder']['uid'] = $uid;
        $vars['folder']['folder'] = $folder;
        $_SESSION[__CLASS__][$_GET['browserSession']]['lastFolder'] = $folder;
        $vars['folder']['basename'] = basename($folder);
        $message = $_SESSION[__CLASS__]['showContentMessage'];
        if(empty($message) && file_exists($folder.'/.message')) {
            include($folder.'/.message');
            $lang = $this->linker->i18n->getLang();
            if(isset($message[$lang])) {
                $message = $message[$lang];
            }else {
                $message = array_shift($message);
            }
        }
        $_SESSION[__CLASS__]['showContentMessage'] = '';
        $vars['actions']['addfile'] = $this->linker->path->getUri('browser/addFile/');


        if(self::getRights(dirname($folder),self::DELETEFOLDER)) {
            $vars['folder']['deletePreviousFolder'] = true;
        }else {
            $vars['folder']['nodeletePreviousFolder'] = true;
        }
        if(self::getRights(dirname($folder),self::RENAMEFOLDER)) {
            $vars['folder']['renamePreviousFolder'] = true;
        }else {
            $vars['folder']['norenamePreviousFolder'] = true;
        }
        if(self::getRights($folder,self::ADDFOLDER)) {
            $vars['folder']['addFolder'] = true;
        }else {
            $vars['folder']['noaddFolder'] = true;
        }

        $scan =  scandir($folder);

        $addFile = self::getRights($folder,self::ADDFILE);
        $renameFile = self::getRights($folder,self::RENAMEFILE);
        $deleteFile = self::getRights($folder,self::DELETEFILE);
        if($addFile) {
            $vars['folder']['addFile'] = true;
        }else {
            $vars['folder']['noAddFile'] = true;
        }
        if(self::getRights($folder,self::READ)) {
            $vars['folder']['style'] = 'width:80px;height:80px;text-align:center;';
            $renamePage = $this->linker->path->getLink('browser/rename/');
            $cpt = 0;
            if(file_exists($folder.'/'.self::HIDDENFILES)) {
                $hiddenFiles = file($folder.'/'.self::HIDDENFILES);
                $hiddenFiles = array_map('trim',$hiddenFiles);
            }else {
                $hiddenFiles = array();
            }
            foreach($scan as $element) {
                if(substr($element,0,1) != '.' && !is_dir($folder.$element)) {
                    if(!in_array($element,$hiddenFiles)) {
                        $ext = strtolower(array_pop(explode('.',$element)));
                        $file = $shortFolder.$element;
                        if(in_array($ext,$types)) {
                            $cpt++;
                            $theFile = SH_ROOT_FOLDER.$file;
                            if(in_array($ext,$shownAsImage)) {
                                $imageSize = getimagesize($folder.$element);
                                $w = $imageSize[0];
                                $h = $imageSize[1];
                                if($w > $h) {
                                    $vars['pictures'][$cpt]['imagestyle'] = 'width:80px;';
                                }else {
                                    $vars['pictures'][$cpt]['imagestyle'] = 'height:80px;';
                                }
                                $icon = SH_ROOT_FOLDER.$file;
                                $description = $w.'x'.$h.' px<br />';
                            }else{
                                if(file_exists(SH_SHAREDIMAGES_FOLDER.'/icons/'.$ext.'.png')) {
                                    $icon = SH_SHAREDIMAGES_PATH.'/icons/'.$ext.'.png';
                                }else {
                                    $icon = SH_SHAREDIMAGES_PATH.'/icons/default.png';
                                }
                            }
                            if(strlen(basename($file)) <= 30) {
                                $shownName = basename($file);
                            }else {
                                $shownName = substr(basename($file),0,12).' [...] '.substr(basename($file),-12);
                                $shownName = basename($file);
                            }


                            if($renameFile) {
                                $vars['pictures'][$cpt]['renameFile'] = true;
                            }else {
                                $vars['pictures'][$cpt]['noRenameFile'] = true;
                            }

                            if($deleteFile) {
                                $vars['pictures'][$cpt]['deleteFile'] = true;
                            }else {
                                $vars['pictures'][$cpt]['noDeleteFile'] = true;
                            }

                            $vars['pictures'][$cpt]['description'] = $description.(round(filesize($folder.$element) / 1024)).'kio';
                            $vars['pictures'][$cpt]['imagestyle'] .= 'cursor:pointer;';
                            $vars['pictures'][$cpt]['icon'] = $this->linker->path->changeToShortFolder($icon);
                            $vars['pictures'][$cpt]['file'] = $this->linker->path->changeToShortFolder($theFile);
                            $vars['pictures'][$cpt]['basename'] = basename($file);
                            $vars['pictures'][$cpt]['showedname'] = $shownName;
                            $vars['pictures'][$cpt]['folder'] = $folder;
                            $vars['pictures'][$cpt]['element'] = $element;
                            if($ext == 'mp3'){
                                $vars['pictures'][$cpt]['playSound'] = true;
                            }
                            $containsSomething = true;
                        }
                    }
                }
            }
        }else {
            $vars['restrictions']['base'] = $this->linker->path->protocol.'://'.$this->linker->path->getDomain().'/';
            echo $this->render('restriction',$vars,false,false);
            return false;
        }
        if(!$containsSomething && empty($message)) {
            $message = $this->getI18n('emptyFolder_message');
        }

        $vars['browser']['session'] = $_GET['browserSession'];
        $vars['folder']['message'] = $message;
        $vars['called']['page'] = $_SERVER['REQUEST_URI'];
        $vars['i18n'] = $this->__tostring();
        echo $this->render('folder_content',$vars,false,false);
        return true;
    }

    public function resize_image($img, $dstx, $dsty, $forceBoth = true) {
        $this->debug(__METHOD__, 2, __LINE__);
        $parts = explode('.',$img);
        $fileExtension = strtolower(array_pop($parts));
        if($fileExtension != 'png') {
            $dest = implode('.',$parts).'.png';
        }else {
            $dest = $img;
        }

        list($width,$height) = getImageSize($img);

        if($fileExtension == "jpg" || $fileExtension=='jpeg') {
            $from = ImageCreateFromJpeg($img);
        }elseif ($fileExtension == 'png') {
            $from = imageCreateFromPNG($img);
        }elseif ($fileExtension == 'gif') {
            $from = imageCreateFromGIF($img);
        }

        $xRate = $width / $dstx;
        $yRate = $height / $dsty;
        $biggestRate = max(array($xRate,$yRate));

        if($biggestRate < 1) {
            $biggestRate = 1;
        }

        if($forceBoth) {
            $startX = ($dstx - ($width / $biggestRate)) / 2;
            $startY = ($dsty - ($height / $biggestRate)) / 2;
            $newImage = ImageCreateTrueColor ($dstx,$dsty);
            $trans = imagecolorallocatealpha(
                    $newImage,
                    $trans['R'],
                    $trans['G'],
                    $trans['B'],
                    127
            );
            imagefill($newImage, 0, 0, $trans);
            imagecolortransparent($newImage,$trans);
            imageSaveAlpha($newImage, true);
            ImageAlphaBlending($newImage, false);
            imagecopyresized (
                    $newImage,
                    $from,
                    $startX, $startY,
                    0, 0,
                    $dstx - 2 * $startX, $dsty - 2 * $startY,
                    $width, $height
            );
        }else {
            if($xRate>$yRate) {
                $newWidth = $width / $xRate;
                $newHeight = $height / $xRate;
                //echo $newWidth.'x'.$newHeight.' = '.$width.' / '.$xRate. ' x '.$height.' / '.$xRate. ' x <br />';
            }else {
                $newWidth = $width / $yRate;
                $newHeight = $height / $yRate;
                //echo $newWidth.'x'.$newHeight.' = '.$width.' / '.$yRate. ' x '.$height.' / '.$yRate. ' x <br />';
            }
            $newImage = ImageCreateTrueColor($newWidth,$newHeight);
            $trans = imagecolorallocatealpha($newImage,$trans['R'],$trans['G'],$trans['B'],127);
            imagefill($newImage, 0, 0, $trans);
            imagecolortransparent($newImage,$trans);
            imageSaveAlpha($newImage, true);
            ImageAlphaBlending($newImage, false);

            imagecopyresized (
                    $newImage,
                    $from,
                    0, 0,
                    0, 0,
                    $newWidth, $newHeight,
                    $width, $height
            );
        }

        unlink($img);
        imagepng($newImage, $dest);
        imagedestroy($from);
        imagedestroy($newImage);

        return $dest;
    }

    function fastimagecopyresampled (&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
        // Plug-and-Play fastimagecopyresampled function replaces much slower imagecopyresampled.
        // Just include this function and change all "imagecopyresampled" references to "fastimagecopyresampled".
        // Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
        // Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable - These comments must remain.
        //
        // Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
        // Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
        // 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
        // 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
        // 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
        // 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
        // 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.

        if (empty($src_image) || empty($dst_image) || $quality <= 0) {
            return false;
        }
        if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
            $temp = imagecreatetruecolor ($dst_w * $quality + 1, $dst_h * $quality + 1);
            imagecopyresized ($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
            imagecopyresampled ($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
            imagedestroy ($temp);
        } else {
            imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        }
        return true;
    }

    public function resample_image($img, $dstx, $dsty, $forceBoth = true) {
        $this->debug(__METHOD__, 2, __LINE__);
        $parts = explode('.',$img);
        $fileExtension = strtolower(array_pop($parts));
        if($fileExtension != 'png') {
            $dest = implode('.',$parts).'.png';
        }else {
            $dest = $img;
        }

        list($width,$height) = getImageSize($img);

        if($fileExtension == "jpg" || $fileExtension=='jpeg') {
            $from = ImageCreateFromJpeg($img);
        }elseif($fileExtension == 'png') {
            $from = imageCreateFromPNG($img);
        }

        $xRate = $width / $dstx;
        $yRate = $height / $dsty;
        $biggestRate = max(array($xRate,$yRate));

        if($forceBoth) {
            $startX = ($dstx - ($width / $biggestRate)) / 2;
            $startY = ($dsty - ($height / $biggestRate)) / 2;
            $newImage = ImageCreateTrueColor ($dstx,$dsty);
            $trans = imagecolorallocatealpha($newImage,$trans['R'],$trans['G'],$trans['B'],127);
            imagefill($newImage, 0, 0, $trans);
            imagecolortransparent($newImage,$trans);
            imageSaveAlpha($newImage, true);
            ImageAlphaBlending($newImage, false);
            imagecopyresampled (
                    $newImage,
                    $from,
                    $startX, $startY,
                    0, 0,
                    $dstx - 2 * $startX, $dsty - 2 * $startY,
                    $width, $height
            );
        }else {
            if($xRate>$yRate) {
                $newWidth = $width / $xRate;
                $newHeight = $height / $xRate;
                echo $newWidth.'x'.$newHeight.' = '.$width.' / '.$xRate. ' x '.$height.' / '.$xRate. ' x <br />';
            }else {
                $newWidth = $width / $yRate;
                $newHeight = $height / $yRate;
                echo $newWidth.'x'.$newHeight.' = '.$width.' / '.$yRate. ' x '.$height.' / '.$yRate. ' x <br />';
            }
            $newImage = ImageCreateTrueColor($newWidth,$newHeight);
            $trans = imagecolorallocatealpha($newImage,$trans['R'],$trans['G'],$trans['B'],127);
            imagefill($newImage, 0, 0, $trans);
            imagecolortransparent($newImage,$trans);
            imageSaveAlpha($newImage, true);
            ImageAlphaBlending($newImage, false);

            imagecopyresampled (
                    $newImage,
                    $from,
                    0, 0,
                    0, 0,
                    $newWidth, $newHeight,
                    $width, $height
            );
        }

        unlink($img);
        imagepng($newImage, $dest);
        imagedestroy($from);
        imagedestroy($newImage);

        return $dest;
    }

    /**
     * Calls a method from an external class if a file corresponding to the
     * called event is found in the folder containing the changed file.
     * @param str $event The name of the event, which should be a constant.
     * @param str $folder The name of the folder in which the change occured.
     */
    protected function raiseEvent($event,$folder) {
        $this->debug(__METHOD__, 2, __LINE__);
        if(file_exists($folder.'/'.$event)) {
            $elements = explode('|',file_get_contents($folder.'/'.$event));
            $class = array_shift($elements);
            $method = array_shift($elements);
            $this->linker->$class->$method($event,$folder,$elements);
        }
        if(file_exists($folder.'/'.self::ONCHANGE)) {
            $elements = explode('|',file_get_contents($folder.'/'.self::ONCHANGE));
            $class = array_shift($elements);
            $method = array_shift($elements);
            $this->linker->$class->$method($event,$folder,$elements);
        }
    }

    /**
     * Calls a method from en external class if a file corresponding to the
     * called event is found in the folder.
     * The parametters are given into that file.
     * @param str $event The name of the event, which should be a constant.
     * @param str $parentFolder The name of the parent folder.<br />
     * The one in which a folder is renamed, added, or deleted.
     * @param str $folder The name of the folder that has been changed, added, or removed.
     * @param str $newName Used only if the event is self::ONRENAMEFOLDER.
     * Contains the new name of the renamed folder.
     */
    protected function raiseFolderEvent($event,$parentFolder,$folder,$newName = '') {
        $this->debug(__METHOD__, 2, __LINE__);
        if(file_exists($parentFolder.'/'.$event)) {
            $elements = explode('|',file_get_contents($parentFolder.'/'.$event));
            $class = array_shift($elements);
            $method = array_shift($elements);
            $this->linker->$class->$method($event,$parentFolder,$folder,$newName,$elements);
        }
        if(file_exists($parentFolder.'/'.self::ONCHANGEFOLDER)) {
            $elements = explode('|',file_get_contents($parentFolder.'/'.self::ONCHANGEFOLDER));
            $class = array_shift($elements);
            $method = array_shift($elements);
            $this->linker->$class->$method($event,$parentFolder,$folder,$newName,$elements);
        }

    }

    /**
     * Adds an event (on files) to a folder.<br />
     * Is static to be accessible from anywhere without having to build this class.
     * @param str $event The name of the event, which should be a constant.
     * @param str $parentFolder The name of the folder in which the event should be listened to.
     * @param str $class The full name of the class to call (like "sh_diaporama").
     * @param str $method The name of the method to call.
     * @param array $params An array containing all the params to give to the method.
     */
    public static function addEvent($event,$folder,$class,$method,$params = array()) {
        $content = $class.'|'.$method.'|'.implode('|',$params);
        $linker = sh_linker::getInstance();
        $linker->helper->writeInFile($folder.'/'.$event, $content);
    }


    /**
     * Adds an event (on folders) to a folder.<br />
     * Calls $this->addEvent using the same parametters.<br />
     * Is static to be accessible from anywhere without having to build this class.
     * @param str $event The name of the event, which should be a constant.
     * @param str $parentFolder The name of the folder in which the event should be listened to.
     * @param str $class The full name of the class to call (like "sh_diaporama").
     * @param str $method The name of the method to call.
     * @param array $params An array containing all the params to give to the method.
     */
    public static function addFolderEvent($event,$folder,$class,$method,$params = array()) {
        return self::addEvent($event, $folder, $class, $method, $params);
    }
    
    /**
     * public function addFile
     *
     */
    public function addFile() {
        $this->debug(__METHOD__, 2, __LINE__);
        $folder = $_POST['folder'];
        $fullFolder = str_replace('//','/',$folder.'/');
        if(self::getRights($fullFolder, self::ADDFILE)) {
            $file = $_FILES['file']['name'];
            $file = self::modifyName($file);
            $weight = $_FILES['file']['size'];
            if ($file != ''){
                //Creates the real path
                $filePath = $fullFolder.$file;
                if(preg_match($this->imagesExtensions,strtolower($file))){
                    move_uploaded_file($_FILES['file']['tmp_name'], SH_IMAGES_FOLDER.'temp/'.$file);
                    $count = count($_SESSION[__CLASS__]['uploaded_images']);
                    $_SESSION[__CLASS__]['uploaded_images'][$count] = array(
                        'id' => $count,
                        'name' => $file,
                        'src' => SH_IMAGES_FOLDER.'temp/',
                        'destination' => $fullFolder,
                        'browserSession' => $_POST['browserSession']
                    );
                    $this->linker->path->redirect(__CLASS__,'editImage',$count);
                    $added = true;
                    return true;
                }elseif(preg_match($this->forbiddenExtensions,strtolower($file))){
                    $forbiddenFileType = true;
                }else{
                    // The file is not an image, so we just copy it
                    move_uploaded_file($_FILES['file']['tmp_name'], $filePath);
                    $added = true;
                }

                if($added && file_exists($fullFolder.self::ONADD.self::MESSAGEFILE)) {
                    include($fullFolder.self::ONADD.self::MESSAGEFILE);
                    $lang = $this->linker->i18n->getLang();
                    if(isset($message[$lang])) {
                        $message = $message[$lang];
                    }else {
                        $message = array_shift($message);
                    }
                }elseif($added){
                    $message = $this->getI18n('file_sent_successfully');
                }elseif($forbiddenFileType){
                    $message = $this->getI18n('file_forbiddenFileType');
                }else{
                    $message = $this->getI18n('file_notSent');
                }
            }
        }
        $_SESSION[__CLASS__]['showContentMessage'] = $message;
        $_SESSION[__CLASS__]['openedFolder'] = $_POST['folderUid'];
        $datas['addfile']['message'] = $message;
        $datas['addfile']['folder'] = $folder;
        $datas['addfile']['filePath'] = $filePath;
        $this->raiseEvent(self::ONADD,$fullFolder);
        echo $this->render('addFile',$datas,false,false);
    }

    public function editImage(){
        $this->debug(__METHOD__, 2, __LINE__);
        sh_cache::disable();
        $id = (int) $this->linker->path->page['id'];
        $name = $_SESSION[__CLASS__]['uploaded_images'][$id]['name'];
        $srcFolder = $_SESSION[__CLASS__]['uploaded_images'][$id]['src'];
        $destFolder = $_SESSION[__CLASS__]['uploaded_images'][$id]['destination'];
        $filePath = $srcFolder.$name;
        $values['img']['src'] = $this->linker->path->changeToShortFolder($filePath);
        if(file_exists($destFolder.self::DIMENSIONFILE)) {
            // The file has to be resized
            $dims = file_get_contents($destFolder.self::DIMENSIONFILE);
            $margins = !(file_exists($destFolder.self::NOMARGINS));
            list($width,$height) = explode('x',$dims);
            $hasMaxDims = true;
        }

        if(isset($_GET['cancel'])){
            $this->linker->path->redirect(__CLASS__,__FUNCTION__,$id);
        }
        
        if(isset($_GET['crop'])){
            $filePath = $this->crop_image(
                $filePath,
                $_GET['startX'], $_GET['startY'],
                $_GET['stopX'], $_GET['stopY']
            );
            $name = baseName($filePath);
            $_SESSION[__CLASS__]['uploaded_images'][$id]['name'] = $name;
            $this->linker->path->redirect(__CLASS__,__FUNCTION__,$id);
        }elseif(isset($_GET['rotation'])){
            $rotation = $_GET['rotation'];
            $filePath = $this->rotateImage($filePath,$rotation);
            $_SESSION[__CLASS__]['uploaded_images'][$id]['name'] = basename($filePath);
            $this->linker->path->redirect(__CLASS__,__FUNCTION__,$id);
        }

        if(isset($_GET['action'])){
            $action = $_GET['action'];
            if($action == 'crop'){
                if($margins){
                    $values['dimensions']['forced'] = true;
                    $values['dimensions']['forcedX'] = $width;
                    $values['dimensions']['forcedY'] = $height;
                }elseif($hasMaxDims){
                    $values['dimensions']['max'] = true;
                    $values['dimensions']['maxX'] = $width;
                    $values['dimensions']['maxY'] = $height;
                }
                echo $this->render('editor/crop',$values,false,false);
            }elseif($action == 'rotate'){
                $ext = '.'.array_pop(explode('.',$name));
                $miniPath = $filePath.'.mini';
                copy($filePath,$miniPath.$ext);
                $newFile = $this->resize_image($miniPath.$ext, 100, 100, true);
                $ext = '.png';
                copy($newFile,$miniPath.'.90'.$ext);
                $this->rotateImage($miniPath.'.90'.$ext,90);
                copy($newFile,$miniPath.'.180'.$ext);
                $this->rotateImage($miniPath.'.180'.$ext,180);
                copy($newFile,$miniPath.'.270'.$ext);
                $this->rotateImage($miniPath.'.270'.$ext,270);
                $values['images']['path'] = $this->linker->path->changeToShortFolder($miniPath);
                echo $this->render('editor/rotate',$values,false,false);
            }elseif($action == 'validate'){
                list($originalWidth,$originalHeight) = getImageSize($filePath);
                if($hasMaxDims) {
                    $filePath = $this->resize_image($filePath, $width, $height,$margins);
                }elseif(
                    ($originalWidth > 900 || $originalHeight > 900)
                    && !file_exists($destFolder.self::NOMAXSIZEFILE)
                ){
                    $filePath = $this->resize_image($filePath, 900, 900, false);
                }
                $name = basename($filePath);
                rename($filePath, $destFolder.$name);
                $this->raiseEvent(self::ONADD,$destFolder);
                $session = $_SESSION[__CLASS__]['uploaded_images'][$id]['browserSession'];
                unset($_SESSION[__CLASS__]['uploaded_images'][$id]);
                header('location: /browser/show.php?type=session&session='.$session);
                return true;
            }
        }else{
            list($originalWidth,$originalHeight) = getImageSize($filePath);
            if($originalWidth > $originalHeight){
                $values['img']['direction'] = 'hImage';
            }else{
                $values['img']['direction'] = 'vImage';
            }
            $actions = scandir(SH_CLASS_FOLDER.$this->__tostring().'/renderFiles/editor/');
            foreach($actions as $action){
                if(substr($action,0,1) != '.'){
                    $name = substr($action,0,-7);
                    $values['actions'][] = array(
                        'name' => $name,
                        'description' => $this->getI18n('editor_'.$name)
                    );
                }
            }
            echo $this->render('editImage',$values,false,false);
        }
        return true;
    }

    public function crop_image($img,$startX,$startY,$stopX,$stopY){
        $this->debug(__METHOD__.'('.$startX.', '.$startY.', '.$stopX.', '.$stopY.')', 2, __LINE__);
        $parts = explode('.',$img);
        $fileExtension = strtolower(array_pop($parts));
        if($fileExtension != 'png') {
            $dest = implode('.',$parts).'.png';
        }else {
            $dest = $img;
        }

        list($width,$height) = getImageSize($img);
        $fromX = min($startX,$stopX);
        $toX = max($startX,$stopX);
        $fromY = min($startY,$stopY);
        $toY = max($startY,$stopY);
        $newWidth = $toX - $fromX;
        $newHeight = $toY - $fromY;

        if($fileExtension == "jpg" || $fileExtension=='jpeg') {
            $from = ImageCreateFromJpeg($img);
        }elseif ($fileExtension == 'png') {
            $from = imageCreateFromPNG($img);
        }elseif ($fileExtension == 'gif') {
            $from = imageCreateFromGIF($img);
        }
        $newImage = ImageCreateTrueColor ($newWidth,$newHeight);
        $trans = imagecolorallocatealpha(
                $newImage,
                $trans['R'],
                $trans['G'],
                $trans['B'],
                127
        );
        imagefill($newImage, 0, 0, $trans);
        imagecolortransparent($newImage,$trans);
        imageSaveAlpha($newImage, true);
        ImageAlphaBlending($newImage, false);
        imagecopy (
            $newImage,
            $from,
            0, 0,
            $fromX,$fromY,
            $newWidth,$newHeight
        );

        unlink($img);
        imagepng($newImage, $dest);
        imagedestroy($from);
        imagedestroy($newImage);

        return $dest;
    }

    public function rotateImage($img,$rotation = 90){
        $this->debug(__METHOD__, 2, __LINE__);
        $rotation = $rotation % 360;
        if(!in_array($rotation,array(0,90,180,270))){
            $this->debug('The only allowed values for an image rotation are 0, 90, 180, and 270 (and multiples)', 2, __LINE__);
            return false;
        }
        $parts = explode('.',$img);
        $fileExtension = strtolower(array_pop($parts));
        if($fileExtension != 'png') {
            $dest = implode('.',$parts).'.png';
        }else {
            $dest = $img;
        }

        if($fileExtension == "jpg" || $fileExtension=='jpeg') {
            $from = ImageCreateFromJpeg($img);
        }elseif ($fileExtension == 'png') {
            $from = imageCreateFromPNG($img);
        }elseif ($fileExtension == 'gif') {
            $from = imageCreateFromGIF($img);
        }
        
        $width = imagesx($from);
        $height = imagesy($from);
        if($rotation == 0){
            return $dest;
        }elseif($rotation == 90){
            $newimg= imagecreatetruecolor($height , $width );
        }elseif($rotation == 180){
            $newimg= imagecreatetruecolor($width , $height );
        }else{
            $newimg= imagecreatetruecolor($height , $width );
        }
        // Setting transparency
        $trans = imagecolorallocatealpha(
                $newimg,
                $trans['R'],
                $trans['G'],
                $trans['B'],
                127
        );
        imagefill($newimg, 0, 0, $trans);
        imagecolortransparent($newimg,$trans);
        imageSaveAlpha($newimg, true);
        ImageAlphaBlending($newimg, false);

        for($i = 0;$i < $width ; $i++) {
            for($j = 0;$j < $height ; $j++) {
                $reference = imagecolorat($from,$i,$j);
                if($rotation == 90){
                    imagesetpixel($newimg, ($height - 1) - $j, $i, $reference );
                }elseif($rotation == 180){
                    imagesetpixel($newimg, $width - $i - 1, ($height - 1) - $j, $reference );
                }else{
                    imagesetpixel($newimg, $j, $width - $i - 1, $reference );
                }
            }
        }
        unlink($img);
        imagepng($newimg, $dest);
        imagedestroy($from);
        imagedestroy($newimg);

        return $dest;
    }

    /**
     * public function delete
     *
     */
    public function delete() {
        $this->debug(__METHOD__, 2, __LINE__);
        $dir = dirname($_GET['element']);
        $reason = 'get rights on '.$dir;
        if(self::getRights($dir,self::DELETEFILE)) {
            $element = $_GET['element'];
            $element = $this->getFile($element);
            if(file_exists($element)) {
                if(unlink($element)) {
                    $this->raiseEvent(self::ONDELETE,$dir);
                    echo $dir;
                    return true;
                }
            }
        }
        echo 'ERROR '.$reason;
        return true;
    }

    /**
     * Creates a folder, adds the owner and rights, and eventually the dimensions
     * and the noMargins flag.
     * Every parametter is optionnal, because the may be passed by the browser
     * by a GET request.
     * @param str $parentFolderName The folder in which to create it
     * @param str $folderName The name of the new folder
     * @param int $rights The rights on this folder (using the rights constants)
     * @param str $dimension The dimensions the images are resized to (ex: "800x600")
     * @param str $owner The name of the owner. Most of the time, is the name of
     * the site (default behaviour). Changing this will disallow the admin to
     * access this folder in a browser, but not the users to see the images
     * which are in.
     * @param bool $margins Whether to add transparent margins or not, if there
     * are dimensions, and the w/h ratio isn't the same as the image's one.
     * @return bool|string True if it is a success, or a string explaining the
     * error.
     */
    public function addFolder($parentFolderName = '',$folderName = '', $rights = self::ALL, $dimension = '500x500', $owner = '', $margins = true) {
        if(empty($owner)) {
            $owner = $this->userName;
        }
        $this->debug(__METHOD__, 2, __LINE__);
        if(empty($parentFolderName) && empty($folderName)) {
            list($parentFolderName,$folderName) = explode('|',$_GET['element'],2);
            $echoName = true;
            if(file_exists($parentFolderName.self::RIGHTSFILE)) {
                $rights = file_get_contents($parentFolderName.self::RIGHTSFILE);
            }
            if(file_exists($parentFolderName.self::DIMENSIONFILE)) {
                $dimension = file_get_contents($parentFolderName.self::DIMENSIONFILE);
            }
            if(file_exists($parentFolderName.self::OWNERFILE)) {
                $owner = file_get_contents($parentFolderName.self::OWNERFILE);
            }

        }
        $error = 'You are not allowed to do this...';
        if(self::getRights($parentFolderName,self::ADDFOLDER)) {
            $folderName = self::modifyName($folderName);
            if(mkdir($parentFolderName.'/'.$folderName)) {
                $this->linker->helper->writeInFile($parentFolderName.'/'.$folderName.'/'.self::RIGHTSFILE,$rights);
                $this->linker->helper->writeInFile($parentFolderName.'/'.$folderName.'/'.self::DIMENSIONFILE,$dimension);
                $this->linker->helper->writeInFile($parentFolderName.'/'.$folderName.'/'.self::OWNERFILE,$owner);
                if($echoName) {
                    echo $parentFolderName.'/'.$folderName;
                }
                $this->raiseFolderEvent(self::ONADDFOLDER,$parentFolderName,$folderName);
                $_SESSION[__CLASS__]['goToFolder'] = $parentFolderName.$folderName;
                self::setNoMargins($parentFolderName.$folderName,$margins);
                return true;
            }
            $error = 'There was a problem.';
        }
        echo 'ERROR'.$error;
        return false;
    }


    /**
     * Deletes the folder that is in the $_GET['element'] variable
     * @return bool|string True if it is a success, or a string explaining the
     * error.
     */
    public function deleteFolder() {
        $this->debug(__METHOD__, 2, __LINE__);
        $element = $_GET['element'];
        $error = 'You are not allowed to do this...';
        if(self::getRights(dirname($element),self::DELETEFOLDER)) {
            $this->raiseFolderEvent(self::ONDELETEFOLDER,dirname($element),dirname($element));
            $this->linker->helper->deleteDir($element);
            $_SESSION[__CLASS__]['goToFolder'] = dirname($element);
            return true;
        }
        echo 'ERROR'.$error;
        return false;
    }

    /**
     * Modifies the name to remove the forbidden chars.
     * Caution: May fail with php6, because of arg 2 of str_split, which will
     * be unnecessary.
     * @param str $value The name to clean up
     * @return str The cleaned up name
     */
    public static function modifyName($value) {
        // TODO : WILL CAUSE AN ERROR WITH PHP6. TAKE OUT THE SECOND ARGUMENT WHEN UPGRADING
        $value = str_replace(
                str_split('ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',2),
                str_split('AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy'),
                $value
        );
        return preg_replace('/([^.a-z0-9-]+)/i', '_', $value);
    }

    /**
     * Renames the file given in the $_GET['element'] parametter
     * @return bool|string True if it is a success, or a string explaining the
     * error.
     */
    public function rename() {
        $this->debug(__METHOD__, 2, __LINE__);
        list($element,$value) = explode('|',$_GET['element'],2);
        $ext = array_pop(explode('.',$element));
        $newExt = array_pop(explode('.',$value));
        if(strtolower($ext) != strtolower($newExt)) {
            $value .= '.'.$ext;
        }
        $dir = dirname($element);
        if(self::getRights($dir,self::RENAMEFILE)) {
            $value = self::modifyName($value);

            $newName = $dir.'/'.$value;

            $oldName = $this->getFile($element);

            if($value != '' && $oldName && rename($oldName,$newName)) {
                echo $value;
                // Stores the new value, because the form is not sent another time, so if
                // the user wants to rename the file another time, he will ask to rename it using
                // it's old name.
                $_SESSION[__CLASS__]['renamed'][$element] = $newName;
                $this->raiseEvent(self::ONRENAME,$dir);
                return true;
            }
        }
        echo '<script type="text/javascript">alert("An error occured... The file was not renamed.");inPlaceRenameCancel("'.$element.'")</script>';
        return false;
    }

    /**
     * Renames the folder given in the $_GET['element'] parametter
     * @return bool|string True if it is a success, or a string explaining the
     * error.
     */
    public function renameFolder() {
        $this->debug(__METHOD__, 2, __LINE__);
        list($element,$value) = explode('|',$_GET['element'],2);

        $dir = dirname($element);
        if(self::getRights($dir,self::RENAMEFOLDER)) {
            $value = self::modifyName($value);

            $newName = $dir.'/'.$value;

            $oldName = $this->getFile($element);

            if($value != '' && $oldName && rename($oldName,$newName)) {
                echo $value;
                // Stores the new value, because the form is not sent another time, so if
                // the user wants to rename the file another time, he will ask to rename it using
                // it's old name.
                $_SESSION[__CLASS__]['renamed'][$element] = $newName;
                $this->raiseFolderEvent(self::ONRENAMEFOLDER,$dir,$oldName,$newName);
                $_SESSION[__CLASS__]['goToFolder'] = $newName;

                return true;
            }
        }
        echo '<script type="text/javascript">alert("An error occured... The file was not renamed.");inPlaceRenameCancel("'.$element.'")</script>';
        return false;
    }

    /**
     * This methods help having the real name of a file, even if it has been renamed
     * @param str $element The [old] file name
     * @return str The actual file name
     */
    protected function getFile($element) {
        $this->debug(__METHOD__, 2, __LINE__);
        if(file_exists($element)) {
            return $element;
        }elseif(isset($_SESSION[__CLASS__]['renamed'][$element]) && file_exists($_SESSION[__CLASS__]['renamed'][$element])) {
            return $_SESSION[__CLASS__]['renamed'][$element];
        }
        return false;
    }

    /**
     * Replaces an image in the page using javascript.
     * @param str $id The id of the input element that should change too (if we need one, of course).
     * @param str $folder The base folder of the browser. Could be either a folder constant (like
     * SH_IMAGES_FOLDER) or a path (like SH_IMAGES_FOLDER/some_path/)
     * @return str The javascript to put in the onclick argument.
     */
    public function getOnClickReplaceImage($id,$folder = 'SH_IMAGES_FOLDER') {
        $this->debug(__METHOD__, 2, __LINE__);
        /*if(!defined($folder)) {
            if(is_dir(SH_ROOT_FOLDER.$folder)) {
                $folder = SH_ROOT_FOLDER.$folder;
            }elseif(is_dir(SH_IMAGES_FOLDER.$folder)) {
                $folder = SH_IMAGES_FOLDER.$folder;
            }
            if(is_dir($folder)) {
                $cpt = count($_SESSION[__CLASS__]['pathes']);
                $cpt += 1;
                $_SESSION[__CLASS__]['pathes'][$cpt] = $folder;
                $folder = $cpt;
            }
        }*/
        $this->debug('The method is browser_changeImg(this,\''.$id.'\',\''.$folder.'\')', 3, __LINE__);
        $this->linker->html->addScript('/sh_browser/singles/getBrowser.js');
        //$this->linker->html->addScript('/sh_browser/singles/changeImg.js');
        return 'browser_changeImg(this,\''.$id.'\',\''.$folder.'\')';
    }

    /**
     * Replaces an image in the page using javascript.
     * @param str $id The id of the input element that should change too (if we need one, of course).
     * @param str $folder The base folder of the browser. Could be either a folder constant (like
     * SH_IMAGES_FOLDER) or a path (like SH_IMAGES_FOLDER/some_path/)
     * @return str The javascript to put in the onclick argument.
     */
    public function getOnClickShowBrowser($folder = 'SH_IMAGES_FOLDER') {
        $this->debug(__METHOD__, 2, __LINE__);
        $this->linker->html->addScript('/sh_browser/singles/getBrowser.js');
        if(!defined($folder)) {
            $cpt = count($_SESSION[__CLASS__]['pathes']);
            $cpt += 1;
            $_SESSION[__CLASS__]['pathes'][$cpt] = $folder;
            $folder = $cpt;
        }
        return 'showBrowser(\''.$folder.'\')';
        //$this->linker->html->addScript('/sh_browser/singles/showBrowser.js');
        //return 'showBrowser(this,\''.$folder.'\')';
    }

    /**
     * Renders an image selector.
     * @param array $attributes
     * @return <type>
     */
    public function render_imageSelector($attributes = array()) {
        $this->debug(__METHOD__, 2, __LINE__);
        $this->linker->html->addScript('/sh_browser/singles/getBrowser.js');
        //$this->linker->html->addScript('/sh_browser/singles/changeImg.js');
        if(empty($attributes['name'])) {
            $this->debug('There is no atribute name, so we don\'t create the imageSelector', 2, __LINE__);
            return false;
        }
        $name = $attributes['name'];
        unset($attributes['name']);
        if(!empty($attributes['value'])) {
            $value = $attributes['value'];
            unset($attributes['value']);
        }else {
            $value = $this->defaultImageSelectorImage;
        }
        if(!empty($attributes['folder'])) {
            $folder = $attributes['folder'];
            unset($attributes['folder']);
        }else {
            $folder = SH_IMAGES_FOLDER;
        }
        if(!empty($attributes['types'])) {
            $types = $attributes['types'];
            unset($attributes['types']);
        }else {
            $types = 'images';
        }
        $argsList = '';
        foreach($attributes as $attributeName=>$attributeValue) {
            $argsList .= ' '.$attributeName.'="'.$attributeValue.'"';
        }
        if(!isset($attributes['alt'])) {
            $argsList .= ' alt="'.$this->getI18n('replaceImage_alt').'"';
        }
        if(!isset($attributes['title'])) {
            $argsList .= ' title="'.$this->getI18n('replaceImage_alt').'"';
        }

        $inputId = substr(md5(microtime()),0,8);
        $onClick = $this->getOnClickReplaceImage($inputId,$folder);

        $argsList .= ' src="'.$value.'"';
        $argsList .= ' onclick="'.$onClick.'"';

        $values['args']['list'] = $argsList;
        $values['selector']['inputId'] = $inputId;
        $values['selector']['value'] = $value;
        $values['selector']['name'] = $name;


        // Renders the element
        return $this->render('imageSelector', $values, false, false);
    }

    protected function saveFolderInSession($folder = 'SH_IMAGES_FOLDER') {
        if(is_dir(SH_IMAGES_FOLDER.$folder)) {
            $folder = SH_IMAGES_FOLDER.$folder;
        }elseif(is_dir(SH_ROOT_FOLDER.$folder)) {
            $folder = SH_ROOT_FOLDER.$folder;
        }
        $cpt = count($_SESSION[__CLASS__]['pathes']);
        $cpt += 1;
        $_SESSION[__CLASS__]['pathes'][$cpt] = $folder;
        return $cpt;
    }

    public function render_multipleImagesSelector($attributes = array()) {
        $this->debug(__METHOD__, 2, __LINE__);
        $this->linker->html->addScript('/sh_browser/singles/getBrowser.js');
        //$this->linker->html->addScript('/sh_browser/singles/changeImg.js');
        if(empty($attributes['name'])) {
            $this->debug('There is no atribute name, so we don\'t create the imageSelector', 2, __LINE__);
            return false;
        }
        $name = $attributes['name'];
        unset($attributes['name']);
        if(!empty($attributes['value'])) {
            $value = $attributes['value'];
            unset($attributes['value']);
            $images = explode('|',$value);
        }
        if(!empty($attributes['folder'])) {
            if(!defined($attributes['folder']) && !is_dir($attributes['folder'])) {
                $folder = $this->saveFolderInSession($attributes['folder']);
            }else {
                $folder = $attributes['folder'];
            }
            unset($attributes['folder']);
        }else {
            $folder = 'SH_IMAGES_FOLDER';
        }

        $values['mis']['value'] = $value;
        $values['mis']['uid'] = substr(md5(microtime()),0,5);
        $values['mis']['name'] = $name;
        $values['mis']['folder'] = $folder;

        if(is_array($images)) {
            $images = array_unique($images);
            foreach($images as $image) {
                $values['images'][]['src'] = $image;
            }
        }

        // Renders the element
        return $this->render('multipleImagesSelector', $values, false, false);
    }
    
    /**
     * Creates or edits a browser session, which may be called using RENDER_BROWSER,
     * or directly using a ling created with the javascript method browser_show
     * (in getBrowser.js).
     * @param int|bool $idIfExisting May be the integer id of the browser we are
     * editing, or false for a new one.
     * @param str $folder The folder that will be the root of the browser. May be
     * a constant (like SH_IMAGES_FOLDER), a short path from SH_IMAGES_FOLDER
     * (like site/mini), or empty to show SH_IMAGES_FOLDER.
     * @param array $action An array (which may be empty for no action) containg
     * a "name" key, which is the javascript method that will be called after an
     * image selection, and any other arguments that will be given to that method.
     * @param array $types An array (which may be empty for default values)
     * of lowered case extensions (without the dot) of all the files that could
     * be shown in that browser.
     * @return int The id that should be given to access that browser.
     */
    public function editBrowserSession($idIfExisting = false,$folder = '',$action = array(),$types = array(),$element = 0){
        // Eventually creating the unic id
        if(!$idIfExisting){
            $idIfExisting = count($_SESSION[__CLASS__]);
            $idIfExisting += 1;
        }

        // Setting the folder and the others params in session
        if(defined($folder)){
            $_SESSION[__CLASS__][$idIfExisting]['folder'] = constant($folder);
        }elseif(is_dir(SH_IMAGES_FOLDER.$folder)){
            $_SESSION[__CLASS__][$idIfExisting]['folder'] = SH_IMAGES_FOLDER.$folder;
        }elseif(is_dir(SH_ROOT_FOLDER.$folder)){
            $_SESSION[__CLASS__][$idIfExisting]['folder'] = SH_ROOT_FOLDER.$folder;
        }else{
            $_SESSION[__CLASS__][$idIfExisting]['folder'] = SH_IMAGES_FOLDER;
        }
        if(!empty($action)){
            $_SESSION[__CLASS__][$idIfExisting]['action'] = $action;
        }
        $_SESSION[__CLASS__][$idIfExisting]['element'] = $element;
        if(!empty($types)){
            if(is_array($types)){
                $_SESSION[__CLASS__][$idIfExisting]['types'] = $types;
                $typesSet = true;
            }
        }else{
            // By default, we authorize "any" kind of files
            $types = 'any';
        }
        if(!$typesSet){
            //echo 'Get param : '.'types>'.$types.'|types>any<br />';
            $types = $this->getParam('types>'.$types.'|types>any');
            $_SESSION[__CLASS__][$idIfExisting]['types'] = $types;
        }

        // Returning the session id
        return $idIfExisting;
    }
    
    public function render_browser($attributes = array()){
        // Inserting the javascript file
        if(!sh_html::$willRender){
            // We should insert the javascript file directly, because
            // sh_html::render will not be called
            $values['js']['insert'] = true;
        }else{
            $this->linker->html->addScript('/sh_browser/singles/getBrowser.js');
        }

        // Getting the folder
        $folder = $attributes['folder'];

        // Getting the action
        if(!empty($attributes['action'])) {
            $action['name'] = $attributes['action'];
            $action['params'] = explode('|',$attributes['params']);
        }else {
            $action = array();
        }

        // Gettng the images types
        if(!empty($attributes['types'])) {
            $types = $attributes['types'];
            unset($attributes['types']);
        }elseif(!empty($attributes['typesList'])){
            $types = explode(',',$attributes['typesList']);
        }else{
            $types = array();
        }

        // Creating the browser in session
        $values['session']['id'] = $this->editBrowserSession(false, $folder, $action, $types);

        // Getting the text
        if(!empty($attributes['text'])){
            $values['link']['text'] = $attributes['text'];
        }else{
            $values['link']['text'] = $this->getI18n('showBrowser');
        }

        // Rendering
        return $this->render('createBrowserLink', $values, false, false);
    }
    
    public function render_showBrowser($attributes = array()) {

        //$this->linker->html->addScript('/sh_browser/singles/changeImg.js');
        $this->linker->html->addScript('/sh_browser/singles/getBrowser.js');
        if(!empty($attributes['folder'])) {
            $folder = $attributes['folder'];
            unset($attributes['folder']);
        }else {
            $folder = SH_IMAGES_FOLDER;
        }
        if(!empty($attributes['types'])) {
            $types = $attributes['types'];
            unset($attributes['types']);
        }else {
            $types = 'images';
        }
        $argsList = '';

        $inputId = substr(md5(microtime()),0,8);
        $onClick = $this->getOnClickReplaceImage($inputId,$folder);

        $argsList .= ' onclick="'.$onClick.'"';

        $values['args']['list'] = $argsList;

        // Renders the element
        return $this->render('showBrowser', $values, false, false);
    }

    /**
     * public static function addDimension
     *
     */
    public static function addDimension($folder,$w,$h) {
        return sh_linker::getInstance()->helper->writeInFile(
                $folder.'/'.self::DIMENSIONFILE, $w.'x'.$h
        );
    }

    /**
     * public static function setNoMargins
     *
     */
    public static function setNoMargins($folder,$status = true) {
        if($status) {
            sh_linker::getInstance()->helper->writeInFile(
                    $folder.'/'.self::NOMARGINS , ''
            );
        }elseif(file_exists($folder.'/'.self::NOMARGINS)) {
            unlink($folder.'/'.self::NOMARGINS);
        }
        return true;
    }

    /**
     * public static function setHidden
     *
     */
    public static function setHidden($folder,$status = true) {
        if($status) {
            sh_linker::getInstance()->helper->writeInFile(
                    $folder.'/'.self::HIDDEN , ''
            );
        }elseif(file_exists($folder.'/'.self::HIDDEN)) {
            unlink($folder.'/'.self::HIDDEN);
        }
        return true;
    }

    /**
     * public static function setRights
     *
     */
    public static function setRights($folder,$rights) {
        return sh_linker::getInstance()->helper->writeInFile(
                $folder.'/'.self::RIGHTSFILE, $rights
        );
    }

    /**
     * public static function setRights
     *
     */
    public static function setOwner($folder,$owner = null) {
        if(is_null($owner)) {
            $owner = file_get_contents(dirname($folder).'/'.self::OWNERFILE);
        }
        return sh_linker::getInstance()->helper->writeInFile(
                $folder.'/'.self::OWNERFILE, $owner
        );
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);
        if($method == 'editImage'){
            return '/'.$this->shortClassName.'/editImage/'.$id.'.php';
        }
        return parent::translatePageToUri($page);
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if(preg_match('`/'.$this->shortClassName.'/([^/]+)(/([0-9]+)(-[^/]+)?)?\.php`',$uri,$matches)){
            if($matches[1] == 'editImage'){
                return $this->shortClassName.'/editImage/'.$matches[3];
            }
        }
        return parent::translateUriToPage($uri);
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring() {
        return get_class();
    }

}
