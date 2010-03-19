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
 * Class that verifies the forms (if they were submitted).
 * Does not extend anything, as it can be multi-instanced.
 */
class sh_form_verifier{
    const VERIFY_CAPTCHA = false;
    const ERASE = true;
    const CAPTCHA_ERROR = 'Captcha Error';

    public function __construct(){
        $this->linker = sh_linker::getInstance();
    }

    /**
     * public function create
     *
     */
    public function create($formName){
        $value = MD5('verifForm_'.microtime());
        $_SESSION[__CLASS__][$formName]['verif'] = $value;
        return $value;
    }

    /**
     * Verifies if the given form was submitted.<br />
     * Only works for forms built using <RENDER_FORM .../>
     * @param string $formName The name of the form, given as id to the tag RENDER_FORM
     * @param boolean $verifyCaptcha False (default) if there is no captcha to verify.<br />
     * True if there is one.
     * @param boolean $erase True (default) if we want to disable the form to prevent multisubmits cause by refreshes.<br />
     * False to enable the multi submit
     * @return boolean
     * True if a form was submitted an no error occured<br />
     * False in any other cases
     */
    public function submitted($formName, $verifyCaptcha = self::VERIFY_CAPTCHA, $erase = self::ERASE){
        $verif = $_SESSION[__CLASS__][$formName]['verif'];
        if($erase){
            unset($_SESSION[__CLASS__][$formName]['verif']);
        }
        if(trim($verif) == '' || !isset($_POST['verif']) || $_POST['verif'] != $verif){
            return false;
        }
        if($verifyCaptcha){
            if(!$this->linker->captcha->verify($formName)){
                return sh_captcha::CAPTCHA_ERROR;
            }
            return true;
        }
        return true;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}
