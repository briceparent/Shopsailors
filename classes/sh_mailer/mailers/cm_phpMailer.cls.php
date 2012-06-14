<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

define('SH_MAILER_IDENTIFICATION','sh_phpMailer');

include(dirname(__FILE__).'/phpMailer_v2.3/class.phpmailer.php');

class cm_phpMailer extends sh_mailsenders{
    const CLASS_VERSION = '1.1.11.03.29';

    protected $lastErrorId = 0;
    protected $replyToSet = array();
    const DEV_NAME = 'phpMailer / Shopsailors';
    const DEV_WEBSITE = 'http://wiki.websailors.fr';
    const DEV_EMAIL = 'brice.parent@websailors.fr';

    protected $mailers      = array();
    protected $newsletters  = array();
    protected $bcc          = array();

    public function construct(){
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            // The class datas are not in the same version as this file, or don't exist (installation)
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
        // We will write our params into the same files as sh_mailer
        $this->shareParamsFile('sh_mailer');
        $this->shareI18nFile('sh_mailer');
    }

    public function cron_job($time){
        return true;
    }

    public function checkAddress($address) {
        $this->debug(__METHOD__.' (Address to check : '.$address.')', 2, __LINE__);
        if(preg_match('`([[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4}))`', $address) ) {
            return true;
        }
        $this->debug('Bad address', 1, __LINE__);
        return false;
    }

    // E-MAILS
    public function em_create(){
        $this->debug(__METHOD__,2,__LINE__);
        $cpt = count($this->mailers);
        $this->debug('We get the mailing datas from the params file and construct the PHPMailer #'.$cpt, 3, __LINE__);

        $this->mailers[$cpt] = new PHPMailer();
        $this->mailers[$cpt]->IsSMTP();

        $this->mailers[$cpt]->SMTPAuth   = $this->getParam('SMTPAuth');
        $this->mailers[$cpt]->SMTPSecure = $this->getParam('SMTPSecure');
        $this->mailers[$cpt]->Host       = $this->getParam('host');
        $this->mailers[$cpt]->Port       = $this->getParam('port');
        $this->mailers[$cpt]->Username   = $this->getParam('username');
        $this->mailers[$cpt]->Password   = $this->getParam('password');

        $this->mailers[$cpt]->From       = $this->getParam('from');
        $this->mailers[$cpt]->FromName   = $this->getParam('fromName');

        return $cpt;
    }

    public function em_from($id,$from,$name = ''){
        $this->debug(__METHOD__,2,__LINE__);
        if(!self::checkAddress($from)){
            return $this->setError(self::ERROR_WRONGADDRESSFORMAT);
        }
        if(isset($this->mailers[$id])){
            $this->mailers[$id]->From = $from;
            if($name != ''){
                $this->mailers[$id]->FromName = $name;
            }
            return true;
        }else{
            return $this->setError(self::ERROR_EM_NOTCONSTRUCTED);
        }
    }

    public function em_replyTo($id,$replyTo,$name = ''){
        $this->debug(__METHOD__,2,__LINE__);
        if(!self::checkAddress($replyTo)){
            return $this->setError(self::ERROR_WRONGADDRESSFORMAT);
        }
        if(isset($this->mailers[$id])){
            $this->mailers[$id]->AddReplyTo($replyTo,$name);
            $this->replyToSet[$id] = true;
            return true;
        }else{
            return $this->setError(self::ERROR_EM_NOTCONSTRUCTED);
        }
    }

    public function em_addBCC($id,$bcc,$name = ''){
        $this->debug(__METHOD__,2,__LINE__);
        if(!self::checkAddress($bcc)){
            return $this->setError(self::ERROR_WRONGADDRESSFORMAT,$bcc);
        }
        if(isset($this->mailers[$id])){
            $this->mailers[$id]->AddBCC($bcc,$name);
            $this->bcc[$id] = $bcc;
            return true;
        }else{
            return $this->setError(self::ERROR_EM_NOTCONSTRUCTED);
        }
    }

    public function em_addAddress($id,$address,$name = ''){
        $this->debug(__METHOD__,2,__LINE__);
        if(!self::checkAddress($address)){
            return $this->setError(self::ERROR_WRONGADDRESSFORMAT,$address);
        }
        if(isset($this->mailers[$id])){
            $this->mailers[$id]->AddAddress($address,$name);
            $this->addresses[$id] = $address;
            return true;
        }else{
            return $this->setError(self::ERROR_EM_NOTCONSTRUCTED);
        }
    }

    public function em_addSubject($id,$subject,$isUTF8 = true){
        $this->debug(__METHOD__,2,__LINE__);
        if(isset($this->mailers[$id])){
            if($isUTF8){
                $subject = UTF8_decode($subject);
            }
            $this->mailers[$id]->Subject = $subject;
        }else{
            return $this->setError(self::ERROR_EM_NOTCONSTRUCTED);
        }
    }

    public function em_addContent($id,$content,$type = self::EM_CONTENTTYPE_HTML,$isUTF8 = true){
        $this->debug(__METHOD__,2,__LINE__);
        if(isset($this->mailers[$id])){
            if($isUTF8){
                $content = UTF8_decode($content);
            }
            $this->mailers[$id]->Body = $this->clean_html_content($content);
            $this->mailers[$id]->ContentType = $type;
            $this->mailers[$id]->AltBody = strip_tags($content);
        }else{
            return $this->setError(self::ERROR_EM_NOTCONSTRUCTED);
        }
    }

    public function em_attach($id,$fileToAttach,$name='',$encoding='base64',$type='application/octet-stream'){
        $this->debug(__METHOD__,2,__LINE__);
        if(isset($this->mailers[$id])){
            if(self::MAY_ADDATTACHMENT){
                if(file_exists($fileToAttach)){
                    return $this->mailers[$id]->AddAttachment(
                        $fileToAttach,
                        $name,
                        $encoding,
                        $type
                    );
                }
                return $this->setError(self::ERROR_EM_ATTACHMENTNOTFOUND,' (file: '.$path.')');
            }
            return $this->setError(self::ERROR_EM_MAYNOTADDATTACHMENT);
        }else{
            return $this->setError(self::ERROR_EM_NOTCONSTRUCTED);
        }
    }

    
    public function em_send($id,$addresses = array()){
        $this->debug(__METHOD__,2,__LINE__);
        if(isset($this->mailers[$id])){
            // We verify if everything has been filled
            $mail =& $this->mailers[$id];
            if(trim($mail->Subject) == ''){
                return $this->setError(self::ERROR_NL_NOSUBJECT);
            }
            if(trim($mail->Body) == ''){
                return $this->setError(self::ERROR_NL_NOCONTENT);
            }
            if((count($addresses) + count($this->addresses[$id]) + count($this->bcc[$id])) == 0){
                return $this->setError(self::ERROR_NL_NORECIPIENTS);
            }
            if(is_array($addresses)){
                foreach($addresses as $address){
                    if(isset($address['value'])){
                        $this->debug('Adding address '.$address['value'], 3, __LINE__);
                        $mail->AddAddress($address['value'], $address['name']);
                    }else{
                        $this->debug('Adding address '.$address[0], 3, __LINE__);
                        $mail->AddAddress($address[0], $address[1]);
                    }
                }
            }
            if($this->replyToSet[$id] !== true){
                $this->em_replyTo(
                    $id,
                    $this->getParam('replyTo'),
                    $this->getParam('replyToName')
                );
            }
            
            if($mail->send()){
                return self::SUCCESS;
            }
            return self::FAILURE;
        }else{
            return $this->setError(self::ERROR_EM_NOTCONSTRUCTED);
        }

    }


    // NEWSLETTER
    public function nl_create(){
        $this->debug(__METHOD__,2,__LINE__);
        $id = $this->em_create();
        $this->newsletters[$id]['id'] = $id;
        return $id;
    }

    public function nl_getContent($newsletter,$cleaned = true){
        $this->debug(__METHOD__, 2, __LINE__);
    }



    function nl_addFrom($id,$from,$name=''){
        $this->debug(__METHOD__, 2, __LINE__);

    }

    public function nl_addReplyTo($id,$replyTo,$name=''){
        $this->debug(__METHOD__, 2, __LINE__);

    }
    
    public function nl_addContent($newsletter,$content,$append = false){
        $this->debug(__METHOD__, 2, __LINE__);
        
    }

    public function nl_getTitle($newsletter){
        $this->debug(__METHOD__, 2, __LINE__);
    }
    
    public function nl_addTitle($newsletter,$title){
        $this->debug(__METHOD__, 2, __LINE__);

    }

    public function nl_getMailingLists($newsletter){
        $this->debug(__METHOD__, 2, __LINE__);

    }
    
    function nl_getAll($types = self::NL_SENT){
        $this->debug(__METHOD__, 2, __LINE__);

    }
    
    public function nl_addMailingList($newsletterId,$mailingListId){
        $this->debug(__METHOD__, 2, __LINE__);
        $this->newsletters[$newsletterId]['mailingLists'] = $mailingListId;

    }
    
    public function nl_removeMailingList($newsletterId,$mailingListId = self::REMOVE_ALL_MAILING_LISTS){
        $this->debug(__METHOD__, 2, __LINE__);
        $a = $this->newsletters[$newsletterId]['mailingLists'];
        $b = array($mailingListId);
        echo '<div><span class="bold">$a : </span>' . nl2br( str_replace( ' ', '&#160;',
                                                                            htmlentities( print_r( $a, true ) ) ) ) . '</div>';
          echo '<div><span class="bold">$b : </span>' . nl2br( str_replace( ' ', '&#160;',
                                                                              htmlentities( print_r( $b, true ) ) ) ) . '</div>';
        $this->newsletters[$newsletterId]['mailingLists'] = array_diff(
            $this->newsletters[$newsletterId]['mailingLists'],
            array($mailingListId)
        );
        return true;
    }

    public function nl_sendNow($newsletterId){
        $this->debug(__METHOD__, 2, __LINE__);
    }

    public function nl_getPlannedDate($newsletter){
        $this->debug(__METHOD__, 2, __LINE__);

    }

    public function nl_sendPlanned($newsletterId,$date){
        $this->debug(__METHOD__, 2, __LINE__);
    }

    public function nl_hasBeenSent($newsletterId){
        $this->debug(__METHOD__, 2, __LINE__);

    }

    public function nl_delete($id){
        $this->debug(__METHOD__, 2, __LINE__);
    }

    public function nl_exists($id){
        $this->debug(__METHOD__, 2, __LINE__);

    }

    // MAILING LISTS
    public function ml_create($name,$description = ''){
        $this->debug(__METHOD__,2,__LINE__);
        $name = trim($name);
        $mailingLists = $this->getParam('mailingLists', array());
        foreach($mailingLists as $id=>$mailingList){
            if($mailingList['name'] == $name && !$mailingList['deleted']){
                return $this->setError(self::ERROR_ML_NAMEALREADYEXISTS,'name:'.$name);
            }
        }
        $id = count($mailingLists) + 1;
        $this->setParam(
            'mailingLists>'.$id,
            array(
                'id'=>$id,
                'name'=>$name,
                'description'=>$description,
                'date'=>date('Y-m-d')
            )
        );
        $this->writeParams();
        return $id;
    }

    public function ml_getName($mailingList){
        $this->debug(__METHOD__,2,__LINE__);
        $name = $this->getParam(
            'mailingLists>'.$mailingList.'>name',
            false
        );
        if($name){
            return $name;
        }
        $this->setError(self::ERROR_ML_DOESNTEXIST,'ml: '.$mailingList);
    }

    public function ml_edit($mailingList,$newName,$newDescription){
        $this->debug(__METHOD__.' ('.$mailingList.', '.$newName.', [desc])',2,__LINE__);
        $newName = trim($newName);
        $oldName = $this->getParam(
            'mailingLists>'.$mailingList.'>name',
            false
        );
        
        if($oldName === false){
            return $this->setError(self::ERROR_ML_DOESNTEXIST,'ml: '.$mailingList);
        }
        $alreadyExists = $this->ml__nameExists($newName);
        if($alreadyExists >= self::MAILER_MINIMUMERRORNUMBER){
            // The answer is an error number...
            return $alreadyExists;
        }
        $this->setParam(
            'mailingLists>'.$mailingList.'>name',
            $newName
        );
        $this->setParam(
            'mailingLists>'.$mailingList.'>description',
            $newDescription
        );
        $this->writeParams();
        return true;
    }

    /**
     * Returns false if the name isn't already used by any mailing list, or the
     * mailing list id that is named like that, or an error code.
     * @param str $name The name we are looking for
     *
     */
    protected function ml__nameExists($name){
        $this->debug(__METHOD__,2,__LINE__);
        $name = trim($name);
        if($name == ''){
            return $this->setError(self::ERROR_ML_EMPTYNAMEFORBIDDEN);
        }
        $mailingLists = $this->ml_getAll();
        foreach($mailingLists as $id=>$oneMailingList){
            if($newName == $oneMailingList['name']){
                return $id;
            }
        }
        return false;
    }

    public function ml_getByName($name){
        $this->debug(__METHOD__.'('.$name.')',2,__LINE__);
        return $this->ml__nameExists($name);
    }

    public function ml_delete($mailingList){
        $this->debug(__METHOD__,2,__LINE__);
        if($this->getParam('mailingLists>'.$mailingList,false) === false){
            return $this->setError(self::ERROR_ML_DOESNTEXIST,'ml:'.$mailingList);
        }
        $this->setParam('mailingLists>'.$mailingList.'>deleted', true);
        $this->writeParams();
        return true;
    }

    public function ml_get($mailingList){
        $this->debug(__METHOD__,2,__LINE__);
        $return = $this->getParam('mailingLists>'.$mailingList,false);
        if($return === false){
            return $this->setError(self::ERROR_ML_DOESNTEXIST,'ml:'.$mailingList);
        }
        return $return;
    }

    public function ml_getAll($alsoGetDeleted = false){
        $this->debug(__METHOD__,2,__LINE__);
        $mailingLists = $this->getParam('mailingLists', array());
        if(!$alsoGetDeleted && is_array($mailingLists)){
            foreach($mailingLists as $id=>$mailingList){
                if($mailingList['deleted']){
                    unset($mailingLists[$id]);
                }
            }
        }
        return $mailingLists;
    }

    public function ml__getId($mailingList,$address,$raiseErrorIfNotFound = true){
        $this->debug(__METHOD__,2,__LINE__);
        $exists = $this->getParam(
            'mailingLists>'.$mailingList,
            false
        );
        if($exists == false){
            return $this->setError(self::ERROR_ML_DOESNTEXIST,'ml:'.$mailingList);
        }

        $params = SH_SITEPARAMS_FOLDER.__CLASS__.'_'.$mailingList;
        $this->linker->params->addElement($params,true);
        $addresses = $this->linker->params->get($params,'addresses',array());
        $id = array_search($address,$addresses);
        if($id){
            return $id;
        }
        if($raiseErrorIfNotFound){
            return $this->setError(self::ERROR_ML_ADDRESSDOESNTEXIST,' address : '.$address);
        }
        return self::ERROR_ML_ADDRESSDOESNTEXIST;
    }

    public function ml_addAddress($mailingList,$address){
        $this->debug(__METHOD__,2,__LINE__);
        $exists = $this->getParam(
            'mailingLists>'.$mailingList,
            false
        );
        if($exists == false){
            return $this->setError(self::ERROR_ML_DOESNTEXIST,'ml:'.$mailingList);
        }
        $deleted = $this->getParam(
            'mailingLists>'.$mailingList.'>deleted',
            false
        );
        if($deleted === true){
            return $this->setError(self::ERROR_ML_HASBEENDELETED,'ml:'.$mailingList);
        }

        $params = SH_SITEPARAMS_FOLDER.__CLASS__.'_'.$mailingList.'.params.php';
        $this->linker->params->addElement($params,true);
        $count = $this->linker->params->get($params,'next',self::ADDRESSES_MINIMUMID);
        $this->linker->params->set($params,'addresses>'.$count,$address);
        $this->linker->params->set($params,'next',$count + 1);
        $this->linker->params->write($params);
        return $count;
    }

    public function ml_addAddresses($mailingList,$addressesArray){
        $ret = true;
        foreach($addressesArray as $address){
            $ret = $this->ml_addAddress($mailingList, $address) && $ret;
        }
        return $ret;
    }

    public function ml_getOneMailMailingLists($address){
        $this->debug(__METHOD__,2,__LINE__);
        $name = trim($name);
        $mailingLists = $this->getParam('mailingLists', array());
        foreach($mailingLists as $id=>$mailingList){
            $ret = $this->ml__getId($id,$address,false);
            if($ret >= self::ADDRESSES_MINIMUMID){
                $values[$id] = $mailingList;
            }
        }
        return $values;
    }

    public function ml_editAddress($mailingList,$oldAddress,$newAddress){
        $this->debug(__METHOD__.' ('.$mailingList.', '.$oldAddress.', '.$newAddress.')',2,__LINE__);

        $id = $this->ml__getId($mailingList,trim($oldAddress));
        if($id >= self::ADDRESSES_MINIMUMID){
            $this->debug($address.'\'s id is '.$id,3,__LINE__);
            if(!$this->checkAddress(trim($newAddress))){
                return $this->setError(self::ERROR_ML_ADDRESSERROR);
            }

            $params = SH_SITEPARAMS_FOLDER.__CLASS__.'_'.$mailingList;
            $this->linker->params->addElement($params,true);
            $this->linker->params->set($params,'addresses>'.$id,trim($newAddress));
            $this->linker->params->write($params);
            return true;
        }
        return $id;
    }

    public function ml_removeAddress($mailingList,$address){
        $this->debug(__METHOD__.' ('.$mailingList.', '.$address.')',2,__LINE__);
        
        $id = $this->ml__getId($mailingList,$address);
        if($id >= self::ADDRESSES_MINIMUMID){
            $this->debug($address.'\'s id is '.$id,3,__LINE__);

            $params = SH_SITEPARAMS_FOLDER.__CLASS__.'_'.$mailingList;
            $this->linker->params->addElement($params,true);
            $this->linker->params->remove($params,'addresses>'.$id);
            $this->linker->params->write($params);
            return true;
        }
        return $id;
    }

    // STATISTICS
    public function st_init($newsletterId){
        $this->debug(__METHOD__,2,__LINE__);
    }
    public function st_getAsPDF($newsletterId){
        $this->debug(__METHOD__,2,__LINE__);
    }
    public function st_getAsHTML($newsletterId){
        $this->debug(__METHOD__,2,__LINE__);
    }
    public function st_getErrors($newsletterId){
        $this->debug(__METHOD__,2,__LINE__);
    }

    // PROGRESS
    public function pg_get($newsletterId){
        $this->debug(__METHOD__,2,__LINE__);
    }


    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}
