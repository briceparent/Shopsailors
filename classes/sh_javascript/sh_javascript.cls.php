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
 * Class inserting the javascript libraries, such as scriptaculous and prototype
 */
class sh_javascript extends sh_core{
    protected $libraries = array(
        'SCRIPTACULOUSONLY',// These two classes are put at the beginning, and inverted,
        'PROTOTYPE',        //that's why scriptaculous comes before prototype
        'LIGHTWINDOW_JS',
        'LIGHTWINDOW_CSS',
        'MD5',
        'AES',
        'AESCTR',
        'BASE64',
        'UTF8'
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

    public function construct(){
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
                                $return = $return && $this->linker->html->addScript(
                                    $this->getSinglePath().constant('self::SRC_'.$id),
                                    sh_html::SCRIPT_FIRST
                                );
                            }else{
                                $return = $return && $this->linker->html->addScript(
                                    $this->getSinglePath().constant('self::SRC_'.$id)
                                );
                            }
                        }else{
                            $return = $return && $this->linker->html->addCSS(
                                $this->getSinglePath().constant('self::SRC_'.$id)
                            );
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
        }else{
            return '';
        }
        if(isset($params['absolute']) && strtolower($params['absolute']) == 'absolute'){
            $file = $this->linker->path->getBaseUri().$file;
        }
        if(isset($params['direct']) && strtolower($params['direct']) == 'direct'){
            return $this->inLine($file);
        }
        $this->linker->html->addScript($file);
        return '';
    }

    protected function inLine($file,$css = false){
        if(!$css){
            return '<script type="text/javascript" src="'.$file.'"/>';
        }
        return '<style type="text/css" src="'.$file.'"/>';
    }

    public function __tostring(){
        return get_class();
    }
}
