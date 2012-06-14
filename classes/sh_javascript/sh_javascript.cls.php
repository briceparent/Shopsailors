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
 * Class inserting the javascript libraries, such as scriptaculous and prototype
 */
class sh_javascript extends sh_core{
    const CLASS_VERSION = '1.1.11.03.29';

    public $shopsailors_dependencies = array(
        'sh_linker','sh_params','sh_db'
    );
    protected $libraries = array(
        'MODALBOX_JS',
        'SCRIPTACULOUSONLY',// These two classes are put at the beginning, and inverted,
        'PROTOTYPE',        //that's why scriptaculous comes before prototype
        'LIGHTWINDOW_JS',
        'LIGHTWINDOW_CSS',
        'MD5',
        'AES',
        'AESCTR',
        'BASE64',
        'UTF8',
        'MODALBOX_CSS',
        'MODALBOX',
        'SHOPSAILORS_JS',
        'POPUPS_JS',
        'LIGHTBOX_JS',
        'LIGHTBOX_CSS',
        'WINDOW',
    );
    const PROTOTYPE = 1;
    const SRC_1 = 'scriptaculous/lib/prototype.js';
    const SCRIPTACULOUSONLY = 2;
    const SRC_2 = 'scriptaculous/src/scriptaculous.js';
    const SCRIPTACULOUS = 3;
    
    const LIGHTWINDOW_JS = 4;
    const SRC_4 = 'lightwindow/javascript/lightwindow.js';
    const LIGHTWINDOW_CSS = 8;
    const SRC_8 = 'lightwindow/css/lightwindow_derived.css';
    const LIGHTWINDOW = 12;
    
    const MD5 = 16;
    const SRC_16 = 'md5/md5.js';

    const AES = 32;
    const SRC_32 = 'aes/aes.js';
    const AESCTR = 64;
    const SRC_64 = 'aes/aes-ctr.js';
    const BASE64 = 128;
    const SRC_128 = 'aes/base64.js';
    const UTF8 = 256;
    const SRC_256 = 'aes/utf8.js';
    const AES_COMPLETE = 480;

    const SRC_512 = 'modalbox/modalbox.js';
    const MODALBOX_JS = 512;
    const SRC_1024 = 'modalbox/modalbox.css';
    const MODALBOX_CSS = 1024;

    const SRC_2048 = 'shopsailors.js';
    const SHOPSAILORS_JS = 2048;
    const POPUPS_JS = 2048;
    const SHOPSAILORS = 3584;
    const POPUPS = 3584;

    const LIGHTBOX_JS = 4096;
    const SRC_4096 = '/lightbox/js/lightbox.js';
    const LIGHTBOX_CSS = 8192;
    const SRC_8192 = '/lightbox/css/lightbox.css';
    const LIGHTBOX = 12288;

    const WINDOW = 16384;
    const SRC_16384 = '/window/javascripts/window.js';

    public function construct(){
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->linker->renderer->add_render_tag('render_script',__CLASS__,'render_addScript');
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
    }

    public function get($library = self::SCRIPTACULOUS, $inHTMLClass = true){
        if($inHTMLClass){
            $return = true;
        }else{
            $return = array();
        }
        foreach($this->libraries as $oneLibrary){
            if(defined('self::'.$oneLibrary)){
                $id = constant('self::'.$oneLibrary);
                if($id & $library){
                    if($inHTMLClass){
                        if(substr($oneLibrary,-4) != '_CSS'){
                            if($library == self::SCRIPTACULOUS){
                                $return = $this->linker->html->addScript(
                                    $this->getSinglePath().constant('self::SRC_'.$id),
                                    sh_html::SCRIPT_FIRST
                                ) && $return;
                            }else{
                                $return = $this->linker->html->addScript(
                                    $this->getSinglePath().constant('self::SRC_'.$id)
                                ) && $return;
                            }
                        }else{
                            $return = $this->linker->html->addCSS(
                                $this->getSinglePath().constant('self::SRC_'.$id)
                            ) && $return;
                        }
                    }else{
                        $return[] = $this->inLine(
                            $this->getSinglePath().constant('self::SRC_'.$id),
                            (substr($oneLibrary,-4) == '_CSS')
                        );
                    }
                }
            }
        }
        return $return;
    }

    public function render_addScript($params,$contents){
        $this->debug('function : '.__FUNCTION__, 2, __LINE__);
        
        if(isset($params['src'])){
            $file = $params['src'];
        }elseif(isset($params['file']) && defined('self::'.strtoupper($params['file']))){
            $id = constant('self::'.strtoupper($params['file']));
            $file = $this->getSinglePath().constant('self::SRC_'.$id);
            $css = substr($params['file'],-4) == '_CSS';
        }else{
            return '';
        }
        if(isset($params['absolute']) && strtolower($params['absolute']) == 'absolute'){
            $file = $this->linker->path->getBaseUri().$file;
        }
        if(isset($params['direct']) && strtolower($params['direct']) == 'direct'){
            $return = $this->inLine($file,$css);
            return $return;
        }
        if(!$css){
            $this->linker->html->addScript($file);
        }else{
            $this->linker->html->addCSS($file);
        }
        return '';
    }

    protected function inLine($file,$css = false){
        if(!$css){
            return '<script type="text/javascript" src="'.$file.'"/>';
        }
        return '<link rel="stylesheet" type="text/css" href="'.$file.'"/>';
    }

    public function __tostring(){
        return get_class();
    }
}
