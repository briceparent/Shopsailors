<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

class sh_mailer extends sh_core{
    public $minimal = array('cron_job'=>true,'getForSending'=>true);
    protected $internalMailer = null;
    protected $externalMailer = null;

    public function construct(){
        $internalMailerFile = dirname(__FILE__).'/mailers/'.$this->getParam('mailers>internal>file');
        if(file_exists($internalMailerFile)){
            include($internalMailerFile);
            $mailer = $this->getParam('mailers>internal>name');
            $this->internalMailer = $this->linker->$mailer;
        }else{
            echo 'the file '.$internalMailerFile.' does not exist<br />';
            return false;
        }

        $externalMailerFile = dirname(__FILE__).'/mailers/'.$this->getParam('mailers>external>file');
        if(file_exists($externalMailerFile)){
            if($externalMailerFile != $internalMailerFile){
                include($externalMailerFile);
            }
            $mailer = $this->getParam('mailers>external>name');
            $this->externalMailer = $this->linker->$mailer;
            return true;
        }
        // We didn't find the params for the external mailer, so we use the internal
        $this->externalMailer = $this->internalMailer;
        return false;
    }

    /**
     * This method gets and returns an instance of the mailer class. It may
     * return the internal mailer (which is phpMailer, for now), or the external,
     * which may be any mail senders (used for example for newsletters)
     * @param bool $externalMailer <b>false (default)</b> to get the internal
     * mailer<br />
     * <b>true</b> to get the external mailer
     * @return sh_mailsenders The mailer object
     */
    public function get($externalMailer = false){
        // By default, we return the internal mailer
        if(!$externalMailer){
            return $this->internalMailer;
        }
        return $this->externalMailer;
    }
    
    public function getForSending(){
        sh_cache::disable();
        $mailer = $_GET['mailer'];
        $id = $_GET['id'];
        $extMailer = $this->getParam('mailers>external>name','');
        $intMailer = $this->getParam('mailers>internal>name','');
        if($mailer == $extMailer || $mailer == SH_CUSTOM_PREFIX.$extMailer){
            $usedMailer = $this->get(true);
        }elseif($mailer == $intMailer || $mailer == SH_CUSTOM_PREFIX.$intMailer){
            $usedMailer = $this->get(false);
        }else{
            $this->linker->path->error(404);
        }
        // We decode in order to use iso-8859-1 charset
        echo $usedMailer->nl_getContent($id,false);
        return true;
    }

    public function cron_job($time){
        $externalMailer = $this->get(true);
        $rep = $externalMailer->cron_job($time);
        $internalMailer = $this->get(false);
        $rep = $internalMailer->cron_job($time) && $rep;
        return $rep;
    }

    public function cleanContent($content){
        $domain = $this->linker->path->getBaseUri();
        preg_match_all(
            '`( (src|background)="(https?://)?)(\.\.+\/)*([^"]*")`',
            $content,
            $matches,
            PREG_SET_ORDER
        );
        foreach($matches as $match){
            if(strlen($match[3]) == 0){
                $content = str_replace(
                    $match[0],
                    $match[1].$domain.$match[5],
                    $content
                );
            }
        }
        $preContent = '<html><head><META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1"/></head><body>';
        $postContent = '</body></html>';
        return utf8_decode($preContent.$content.$postContent);
    }
    
    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        list($class,$method,$id) = explode('/',$page);
        $unchanged = array(
            'manage','createNewsletter','sendTest','showPage',
            'isThereANewsletterWaiting','edit_newslettersList','subscribe',
            'confirmSubscription','unsubscribe','showInvisble','getForSending'
        );
        if(in_array($method,$unchanged)){
            return '/'.$this->shortClassName.'/'.$method.'.php';
        }
        $withId = array(
            'manageLists','removeList','createNewsletter','show','delete'
        );
        if(in_array($method,$withId)){
            return '/'.$this->shortClassName.'/'.$method.'/'.$id.'.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if(preg_match('`/'.$this->shortClassName.'/([^/]+)(/([0-9]+)(-[^/]+)?)?\.php`',$uri,$matches)){
            $unchanged = array(
                'manage','createNewsletter','sendTest','showPage',
                'isThereANewsletterWaiting','edit_newslettersList','subscribe',
                'confirmSubscription','unsubscribe','showInvisble','getForSending'
            );
            if(in_array($matches[1],$unchanged)){
                return $this->shortClassName.'/'.$matches[1].'/';
            }
            $withId = array(
                'manageLists','removeList','createNewsletter','show','delete'
            );
            if(in_array($matches[1],$withId)){
                return $this->shortClassName.'/'.$matches[1].'/'.$matches[3];
            }
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



/**
 * @abstract Class to be extended by most of all classes in the Shopsailors' engine.
 *
 */
abstract class sh_mailsenders extends sh_core{
    protected $lastErrorId = 0;
    const DEV_NAME = 'Shopsailors';
    const DEV_WEBSITE = 'http://wiki.websailors.fr';
    const DEV_EMAIL = 'brice.parent@websailors.fr';

    const YES = true;
    const NO = false;
    const SUCCESS = true;
    const FAILURE = false;

    const EM_CONTENTTYPE_HTML = 'text/html';
    const EM_CONTENTTYPE_PLAINTEXT = 'text/plain';

    const ADDRESSES_MINIMUMID = 10000;
    const MAILER_MINIMUMERRORNUMBER = 3000;

    const ERROR_RETURN = 'ERROR';

    const ERROR_IDONTUNDERSTAND = 3000;
    const ERROR_INEXISTANTFUNCTIONNALITY = 3001;
    const ERROR_WRONGADDRESSFORMAT = 3002;
    const ERROR_UNKNOWNEXTERNALERROR = 3003;

    const ERROR_EM_NOTCONSTRUCTED = 3100;
    const ERROR_EM_ATTACHMENTNOTFOUND = 3101;
    const ERROR_EM_MAYNOTADDATTACHMENT = 3102;
    const ERROR_EM_REPLYTOISFROM = 3103;

    const ERROR_ML_ADDRESSERROR = 3200;
    const ERROR_ML_DOESNTEXIST = 3201;
    const ERROR_ML_NAMEALREADYEXISTS = 3202;
    const ERROR_ML_EMPTYNAMEFORBIDDEN = 3203;
    const ERROR_ML_HASBEENDELETED = 3204;
    const ERROR_ML_ADDRESSDOESNTEXIST = 3205;
    const ERROR_ML_THEREISNOMAILINGLIST = 3206;
    const ERROR_ML_COUNLDNOTBECREATED = 3207;

    const ERROR_NL_NOCONTENT = 3300;
    const ERROR_NL_NOSUBJECT = 3301;
    const ERROR_NL_NORECIPIENTS = 3302;
    const ERROR_NL_NOTENOUGHCREDITS = 3303;
    const ERROR_NL_NOMORECREDITS = 3304;
    const ERROR_NL_REPLYTOISFROM = ERROR_EM_REPLYTOISFROM;
    const ERROR_NL_DOESNOTEXIST = 3305;

    const ERROR_NL_ADDRESSERROR = 3400;

    const ERROR_ST_NOTSENT = 3500;

    const ERROR_PG_NOTSENDING = 3600;
    const ERROR_PG_ALREADYSENT = 3601;

    // Parametters
    const MAY_ADDCC = true;
    const MAY_ADDBCC = true;
    const MAY_ADDATTACHMENT = true;
    const MAY_ACCUSEDERECEPTION = false;
    const FORCE_BCC_FOR_MAILING_LISTS = true;

    const REMOVE_ALL_MAILING_LISTS = -1;

    const NL_SENT = 1;
    const NL_PLANNED = 2;
    const NL_NOTPLANNED = 4;
    const NL_ALL = 7;
    
    protected function setError($id,$details = ''){
        $this->lastError = $this->getI18n('error_beginning').$id.$this->getI18n('error_ending');
        if(trim($details) != ''){
            $details = ' ('.$details.')';
        }
        $this->lastError .= $this->getI18n('error_'.$id).$details;
        $this->debug($this->lastError,0);
        return $id;
    }

    public function reinitError(){
        $this->lastError = '';
        return true;
    }

    public function getErrorMessage($id = 0){
        if($id == 0){
            return $this->lastError;
        }
        return $this->getI18n('error_'.$id);
    }

    abstract public function cron_job($time);
    
    // E-MAILS 3100-3199
    abstract public function em_create();
    abstract public function em_from($id,$from,$name='');
    abstract public function em_replyTo($id,$replyTo,$name='');
    abstract public function em_addBCC($id,$bcc,$name='');
    abstract public function em_addAddress($id,$address,$name='');
    abstract public function em_addSubject($id,$subject);
    abstract public function em_addContent($id,$content);
    abstract public function em_send($id,$addresses=array());
    abstract public function em_attach($id,$fileToAttach,$name='',$encoding='base64',$type='application/octet-stream');

    // NEWSLETTER 3200-3299
    abstract public function nl_create();
    abstract public function nl_addFrom($id,$from,$name='');
    abstract public function nl_addReplyTo($id,$replyTo,$name='');
    abstract public function nl_getContent($newsletter,$cleaned = true);
    abstract public function nl_addContent($newsletter,$content,$append = false);
    abstract public function nl_getTitle($newsletter);
    abstract public function nl_addTitle($newsletter,$title);
    abstract public function nl_getMailingLists($newsletter);
    abstract public function nl_addMailingList($newsletter,$mailingListId);
    abstract public function nl_removeMailingList($newsletter,$mailingListId = self::REMOVE_ALL_MAILING_LISTS);
    abstract public function nl_sendNow($newsletter);
    abstract public function nl_getPlannedDate($newsletter);
    abstract public function nl_sendPlanned($newsletter,$date);
    abstract public function nl_hasBeenSent($newsletter);
    abstract public function nl_getAll($types = self::NL_SENT);
    abstract public function nl_delete($id);
    abstract public function nl_exists($id);

    // MAILING LISTS 3300-3499 (ml:3300-3399, addresses:3400-3499)
    abstract public function ml_create($name,$description='');
    abstract public function ml_edit($mailingList,$newName,$newDescription);
    abstract public function ml_get($mailingList);
    abstract public function ml_getName($mailingList);
    abstract public function ml_getByName($name);
    abstract public function ml_delete($mailingList);
    abstract public function ml_addAddress($mailingList,$address);
    abstract public function ml_editAddress($mailingList,$oldAddress,$newAddress);
    abstract public function ml_removeAddress($mailingList,$address);
    abstract public function ml_getOneMailMailingLists($address);
    abstract public function ml_getAll();

    // STATISTICS 3500-3599
    abstract public function st_init($newsletterId);
    abstract public function st_getAsPDF($newsletterId);
    abstract public function st_getAsHTML($newsletterId);
    abstract public function st_getErrors($newsletterId);

    // PROGRESS 3600-3699
    abstract public function pg_get($newsletterId);

}
