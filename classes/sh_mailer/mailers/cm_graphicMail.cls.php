<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

class cm_graphicMail extends sh_mailsenders{
    const CLASS_VERSION = '1.1.11.03.29';

    protected $lastErrorId = 0;
    protected $replyToSet = array();
    const DEV_NAME = 'graphicMail / Shopsailors';
    const DEV_WEBSITE = 'http://wiki.websailors.fr';
    const DEV_EMAIL = 'brice.parent@websailors.fr';
    const WESHOULDMANAGETHESTATISTICSMANUALY = false;
    const DELETED = 'deleted';

    protected $mailers      = array();
    protected $newsletters  = array();
    protected $bcc          = array();

    //@todo Remove the next line if unused
    /*private $baseUri = 'https://www.graphicmail.fr/api.aspx?Username=your@mail.here&Password=yourpasswordhere&SID=5&';*/

    public function construct(){
        $installedVersion = $this->getClassInstalledVersion();
        if($installedVersion != self::CLASS_VERSION){
            // The class datas are not in the same version as this file, or don't exist (installation)
            if(!is_dir(SH_SITE_FOLDER.__CLASS__)){
                mkdir(SH_SITE_FOLDER.__CLASS__);
            }
            $this->setClassInstalledVersion(self::CLASS_VERSION);
        }
        // We will write our params into the same files as sh_mailer
        $this->shareParamsFile('sh_mailer');
        $this->shareI18nFile('sh_mailer');
    }

    public function checkAddress($address) {
        $this->debug(__METHOD__.' (Address to check : '.$address.')', 2, __LINE__);
        if(preg_match('`([[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-_.]?[[:alnum:]])*\.([a-z]{2,4}))`', $address) ) {
            return true;
        }
        $this->debug('Bad address', 1, __LINE__);
        return false;
    }

    public function cron_job($time){
        //$this->debugging(3);
        $this->debug(__FUNCTION__, 2, __LINE__);
        if($time == sh_cron::JOB_QUARTERHOUR){
            /*$sent = file_get_contents(
                'http://graphicMail.websailors.fr/update_ip.php'
            );*/
            return true;
        }

        $ret = true;
        if($time == sh_cron::JOB_DAY){
            return true;
            //$this->debugging(3);
            // Daily jobs
            // We look if there are any newsletters to send
            $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
            $this->linker->params->addElement($newsletters);
            $allNewsletters = $this->linker->params->get(
                $newsletters,
                '',
                array()
            );
            $now = date('Ymd');
            
            foreach($allNewsletters as $id=>$newsletter){
                $this->debug('Newsletter #'.$id, 2, __LINE__);
                if($newsletter['sent'] !== true && $newsletter['date']){
                    // We do nothing on newsletters that have already been sent
                    // and we only sent the ones that have been planned
                    $nlDate = str_replace('-','',$newsletter['date']);
                    if($now >= $nlDate){
                        $this->debug('We should send the newsletter #'.$id,3,__LINE__);
                        $ret = $this->nl_sendNow($id) && $ret;
                        /*
                        $this->addToSitemap(
                            $this->shortClassName.'/show/'.$id,
                            0.4,
                            sh_sitemap::FREQUENCY_MONTHLY
                        );
                         *
                         */
                    }
                }
            }
            
        }elseif($time == sh_cron::JOB_HOUR){
            // We empty the csv files for new mailing lists

        }
        return true;
    }

    private function graphicMail_send($method){
        $this->debug(__FUNCTION__, 2, __LINE__);
        //$address = $this->baseUri.$address;
        $this->linker->postRequest->debugging($this->debugging());
        $requestId = $this->linker->postRequest->create(
            'http://graphicMail.websailors.fr/queries_to_graphicMail.php'
        );
        $this->linker->postRequest->setData(
            $requestId,
            'transfert_password',
            md5('jkjsdqhff54sdf2fs4f5dg2sdf'.date('Ymd'))
        );
        $this->linker->postRequest->setData($requestId,'transfert_address',$method);

//exit;
        $ret = $this->linker->postRequest->send($requestId);
        $this->debug('Address : '.$method,3,__LINE__);
        $this->debug('Return : '.$ret,3,__LINE__);

        return $ret;
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
            $this->mailers[$id]->from = $from;
            if($name != ''){
                $this->mailers[$id]->fromName = $name;
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


    // NEWSLETTERS
    public function nl_create(){
        $this->debug(__METHOD__,2,__LINE__);
        // Loading the params file
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters,true);

        // We get the next newsletter id
        $max = $this->linker->params->get(
            $newsletters,
            'count',
            0
        );
        $id = $max + 1;

        // We set the counter to the new id
        $this->linker->params->set(
            $newsletters,
            'count',
            $id
        );
        $this->linker->params->write($newsletters);
        return $id;
    }

    public function nl_addFrom($id,$from,$name=''){
        $this->debug(__METHOD__, 2, __LINE__);
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters,true);
        $this->linker->params->set(
            $newsletters,
            $id.'>from',
            array('from'=>$from,'name'=>$name)
        );
        $this->linker->params->write($newsletters);
    }

    public function nl_addReplyTo($id,$replyTo,$name=''){
        $this->debug(__METHOD__, 2, __LINE__);
        return $this->setError(self::ERROR_NL_REPLYTOISFROM);
    }

    public function nl_getContent($newsletter,$cleaned = true){
        $this->debug(__METHOD__, 2, __LINE__);
        if(file_exists(SH_SITE_FOLDER.__CLASS__.'/'.$newsletter.'.content.php')){
            $content = file_get_contents(
                SH_SITE_FOLDER.__CLASS__.'/'.$newsletter.'.content.php'
            );
            if(!$cleaned){
                $content = $this->linker->mailer->cleanContent($content);
            }
            return $content;
        }
        return $this->setError(self::ERROR_NL_DOESNOTEXIST);
    }

    public function nl_addContent($newsletterId,$content,$append = false){
        $this->debug(__METHOD__, 2, __LINE__);

        // We write the content of the mail
        $this->helper->writeInFile(
            SH_SITE_FOLDER.__CLASS__.'/'.$newsletterId.'.content.php',
            $content,
            $append
        );
        return true;
    }

    public function nl_getTitle($newsletter){
        $this->debug(__METHOD__, 2, __LINE__);
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters,true);
        return $this->linker->params->get(
            $newsletters,
            $newsletter.'>title',
            'NO TITLE'
        );
    }

    public function nl_addTitle($newsletter,$title){
        $this->debug(__METHOD__, 2, __LINE__);
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters,true);
        $this->linker->params->set(
            $newsletters,
            $newsletter.'>title',
            $title
        );
        $this->linker->params->write($newsletters);
        return true;
    }

    public function nl_getMailingLists($newsletter){
        $this->debug(__METHOD__, 2, __LINE__);
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters,true);
        return $this->linker->params->get(
            $newsletters,
            $newsletterId.'>mailingLists',
            array()
        );
    }

    public function nl_addMailingList($newsletter,$mailingListId){
        $this->debug(__METHOD__.' ('.$newsletter.', '.$mailingListId.')', 2, __LINE__);
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters,true);
        $mailingLists = $this->linker->params->get(
            $newsletters,
            $newsletter.'>mailingLists',
            array()
        );
        $mailingLists[$mailingListId] = $mailingListId;
        $this->linker->params->set(
            $newsletters,
            $newsletter.'>mailingLists',
            $mailingLists
        );
        
        $this->linker->params->write($newsletters);
        return true;
    }
    
    public function nl_removeMailingList($newsletterId,$mailingListId = self::REMOVE_ALL_MAILING_LISTS){
        $this->debug(__METHOD__, 2, __LINE__);
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters,true);
        if($mailingListId == self::REMOVE_ALL_MAILING_LISTS){
            $mailingLists = array();
        }else{
            $mailingLists = $this->linker->params->get(
                $newsletters,
                $newsletterId.'>mailingLists',
                array()
            );
            if(isset($mailingLists[$mailingListId])){
                unset($mailingLists[$mailingListId]);
            }
        }
        $this->linker->params->set(
            $newsletters,
            $newsletterId.'>mailingLists',
            $mailingLists
        );
        $this->linker->params->write($newsletters);
        return true;
    }

    public function nl_sendNow($newsletterId){
        $this->debug(__METHOD__, 2, __LINE__);
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters);
        // We first verify if the newsletter hasn't already been sent
        $alreadySent = $this->linker->params->get(
            $newsletters,
            $newsletterId.'>sent',
            false
        );
        if($alreadySent){
            // It has already been sent, so we don't do it a second time
            return $this->setError(
                self::ERROR_NL_ALREADYSENT,'Newsletter id : '.$newsletterId
            );
        }
        
        // We first create the newsletter on GraphicMail servers
        $method = 'Function=post_import_newsletter&NewExisting=New&ReplaceLinks=false';
        // And the name
        $name = SH_SITENAME.'_'.$newsletterId;
        $method .= '&NewsletterName='.$name;
        // We create the url
        $url = $this->linker->path->getBaseUri();
        $url .= $this->linker->path->getLink('mailer/getForSending/');
        $url .= urlencode('?mailer='.__CLASS__.'&id='.$newsletterId);
        $method .= '&HtmlURL='.$url;
        list($ret,$gm_newsletterId,$message) = explode(
            '|',
            $this->graphicMail_send(
                $method
            )
        );
        // And we send it
        $method = 'Function=post_sendmail&TextOnly=0&NewsletterID='.$gm_newsletterId;

        $from = $this->linker->params->get(
            $newsletters,
            $newsletterId.'>from',
            $this->getParam('from', '')
        );
        $fromName = $this->linker->params->get(
            $newsletters,
            $newsletterId.'>fromName',
            $this->getParam('fromName', '')
        );
        $method .= '&FromEmail='.$from.'&FromName='.$fromName;
        $subject = $this->linker->params->get(
            $newsletters,
            $newsletterId.'>title',
            ''
        );
        $method .= '&Subject='.urlencode($subject);
        $mailingLists = $this->linker->params->get(
            $newsletters,
            $newsletterId.'>mailingLists',
            false
        );
        if(!$mailingLists){
            return $this->setError(self::ERROR_NL_NORECIPIENTS);
        }
        $ret = array();
        foreach($mailingLists as $mailingList){
            $ret[$mailingList] = $this->graphicMail_send(
                $method.'&MailinglistID='.$mailingList
            );
        }
        echo '<div>$ret = '.nl2br(str_replace(' ','&#160;',htmlspecialchars(print_r($ret,true)))).'</div>';
        $this->linker->params->set(
            $newsletters,
            $newsletterId.'>sent',
            true
        );
        $this->linker->params->write($newsletters);
    }

    public function nl_getPlannedDate($newsletterId){
        $this->debug(__METHOD__, 2, __LINE__);
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters);
        return $this->linker->params->get(
            $newsletters,
            $newsletterId.'>date',
            false
        );
    }
    
    public function nl_sendPlanned($newsletterId,$date){
        $this->debug(__METHOD__.' ('.$newsletterId.', '.$date.')', 2, __LINE__);
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters,true);
        $this->linker->params->set(
            $newsletters,
            $newsletterId.'>date',
            $date
        );
        $this->linker->params->write($newsletters);
        return true;
    }

    public function nl_hasBeenSent($newsletterId){
        $this->debug(__METHOD__, 2, __LINE__);
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters,true);
        if($this->linker->params->get($newsletters,$newsletterId.'>sent',false)){
            return true;
        }
        $ret = $this->linker->params->get(
            $newsletters,
            $newsletterId,
            self::ERROR_NL_DOESNOTEXIST
        );
        if(is_array($ret)){
            return false;
        }
        return $this->setError($ret);
    }
    
    function nl_getAll($types = self::NL_SENT){
        $this->debug(__METHOD__, 2, __LINE__);
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters,true);
        $list = $this->linker->params->get($newsletters,'',array());
        
        if($types & self::NL_SENT && $types & self::NL_PLANNED && $types & self::NL_NOTPLANNED){
            // We return every single one
            return $list;
        }
        $ret = array();
        foreach($list as $id=>$nl){
            if($id != 'count'){
                if($nl != self::DELETED){
                    if(($types & self::NL_SENT) && $nl['sent']){
                        $ret[$id] = $nl;
                    }elseif(($types & self::NL_PLANNED) && !$nl['sent'] && $nl['date']){
                        $ret[$id] = $nl;
                    }elseif(($types & self::NL_NOTPLANNED) && !$nl['sent'] && !$nl['date']){
                        $ret[$id] = $nl;
                    }
                }
            }

        }
        return $ret;
    }

    public function nl_delete($id){
        $this->debug(__METHOD__, 2, __LINE__);
        if(!$this->nl_exists($id)){
            return self::ERROR_NL_DOESNOTEXIST;
        }
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters,true);
        $nl = $this->linker->params->get($newsletters,$id,NULL);
        $this->linker->params->set($newsletters,$id,self::DELETED);
        if(file_exists(SH_SITE_FOLDER.__CLASS__.'/'.$id.'.content.php')){
            unlink(SH_SITE_FOLDER.__CLASS__.'/'.$id.'.content.php');
        }
        $this->linker->params->write($newsletters);
        return true;
    }
    
    public function nl_exists($id){
        $this->debug(__METHOD__, 2, __LINE__);
        $newsletters = SH_SITE_FOLDER.__CLASS__.'/list.params.php';
        $this->linker->params->addElement($newsletters,true);
        $nl = $this->linker->params->get($newsletters,$id,NULL);
        return is_array($nl);
    }

    // MAILING LISTS
    public function ml_create($name,$description = ''){
        $this->debug(__METHOD__,2,__LINE__);
        
        $name = trim($name);
        // We first verify if the name doesn't already exist
        $id = $this->ml__nameExists($name);
        if($id === self::ERROR_RETURN){
            // We return the error
            return $id;
        }
        if($id !== false){
            // The name is already in use
            $this->setError(self::ERROR_ML_NAMEALREADYEXISTS,$name);
            return self::ERROR_RETURN;
        }
        // We then order graphic mail to create it
        $mailingLists = $this->getParam('mailingLists', array());
        $count = count($mailingLists);
        $nlName = SH_SITENAME.'_'.$count;
        $create = 'Function=post_create_mailinglist&ReturnMailingListID=true&NewMailinglist='.urlencode($nlName);
        $ret = $this->graphicMail_send($create);
        list($rep,$id,$textRep) = explode('|',$ret);

        if($rep == 1){
            // on success, we save it
            $this->debug('GraphicMail has saved the new mailing list. So we do it on local server too (id is '.$id.')',2,__LINE__);
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
        }else{
            $this->setError(self::ERROR_ML_COUNLDNOTBECREATED,'(error = "'.$id.'")');
            return false;
        }
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
        // We just have to verify if the new name is not already the name of another ML
        $newName = trim($newName);
        $oldName = $this->getParam(
            'mailingLists>'.$mailingList.'>name',
            false
        );
        
        if($oldName === false){
            // The ML doesn't exist, so we can't modify it
            return $this->setError(self::ERROR_ML_DOESNTEXIST,'ml: '.$mailingList);
        }
        $rep = $this->ml__nameExists($newName);
        if($rep == self::ERROR_RETURN){
            // We return an error
            return $rep;
        }

        if($rep !== false && $rep !== $mailingList){
            // The name is already in use for another mailing list
            return $this->setError(self::ERROR_ML_NAMEALREADYEXISTS);
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
     * @return int|bool|str An integer corresponding to the id of the found
     * element, false if it isn't found, or ERROR_RETURN if there is an error.
     */
    protected function ml__nameExists($name){
        $this->debug(__METHOD__,2,__LINE__);
        $name = trim($name);
        if($name == ''){
            $this->setError(self::ERROR_ML_EMPTYNAMEFORBIDDEN);
            return self::ERROR_RETURN;
        }
        $mailingLists = $this->ml_getAll();
        foreach($mailingLists as $id=>$oneMailingList){
            if($name == $oneMailingList['name'] && $id != $elseThanThisId){
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
        $method = 'Function=post_delete_mailinglist&MailinglistID='.$mailingList;
        $ret = $this->graphicMail_send($method);
        if(substr($ret,0,1) == '1'){
            $this->setParam('mailingLists>'.$mailingList.'>removed', true);
            $this->writeParams();
            return true;
        }
        $error = substr($ret,3);
        $this->setError(self::ERROR_UNKNOWNEXTERNALERROR, $error);
        return self::ERROR_RETURN;
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
                if($mailingList['removed']){
                    unset($mailingLists[$id]);
                }
            }
        }
        return $mailingLists;


        $address = 'Function=get_mailinglists';
        $ret = $this->graphicMail_send($address);
        if(substr($ret,0,1) == '0'){
            // There was an error
            $error = substr($ret,2);
            if($error == 'You don\'t have any mailing lists in this account.'){
                $this->setError(self::ERROR_ML_THEREISNOMAILINGLIST);
            }else{
                $this->setError(self::ERROR_UNKNOWNEXTERNALERROR);
            }
            return array();
        }
        $dom = new DOMDocument();
        $dom->loadXML($ret);
        $dom->preserveWhiteSpace = false;
        $mls = $dom->getElementsByTagName('mailinglist');
        foreach($mls as $ml){
            $id = $ml->getElementsByTagName('mailinglistid')->item(0)->nodeValue;
            $name = $ml->getElementsByTagName('description')->item(0)->nodeValue;
            $mailingLists[$id] = array(
                'name' => $name,
                'id' => $id
            );
            // We have to get the name and desc using the id

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

    public function ml_addAddresses($mailingList,$addressesArray){
        $this->debug(__METHOD__,2,__LINE__);
        // We create a csv file
        $unicName = date('YmdHis').rand(100,999);
        $this->helper->writeInFile(
            SH_SITE_FOLDER.__CLASS__.'/csv/'.$unicName.'.csv',
            implode($addressesArray,"\n")
        );

        // 24 hours
        $delay = 24*60*60;
        
        $exportsFile = SH_SITE_FOLDER.__CLASS__.'/csvMailingLists.params.php';
        $this->linker->params->addElement($exportsFile,true);
        $timeStamp = date('U',mktime(date('H'),date('i'),date('s') + $delay,date('m'),date('d'),date('Y')));
        $count = $this->linker->params->count($exportsFile,$timeStamp) + 1;
        $this->linker->params->set(
            $exportsFile,
            $timeStamp.'>'.$count,
            $unicName
        );
        $this->linker->params->write($exportsFile);
        
        
        $url = $this->linker->path->getBaseUri();
        $url .= $this->linker->mailer->setCallPage(
            'cm_graphicMail',
            'getCSVFile',
            $unicName,
            $delay
        );
        $method = 'Function=post_import_mailinglist&MailinglistID='.$mailingList;
        $method .= '&FileUrl='.urlencode($url).'&IsCsv=true';

        list($ret,$message) = explode(
            '|',
            $this->graphicMail_send(
                $method
            )
        );
        echo $method.'<hr />';
        echo $ret.'<br />';
        echo $message;

        return true;
    }

    public function getCSVFile($id){
        $this->debug(__METHOD__,2,__LINE__);
        $exportsFile = SH_SITE_FOLDER.__CLASS__.'/csvMailingLists.params.php';
        $this->linker->params->addElement($exportsFile,true);
        $params = $this->linker->params->get($exportsFile);
        if(file_exists(SH_SITE_FOLDER.__CLASS__.'/csv/'.$id.'.csv')){
            echo file_get_contents(SH_SITE_FOLDER.__CLASS__.'/csv/'.$id.'.csv');
            exit;
        }
        $this->linker->path->error(404);
    }

    public function ml_addAddress($mailingList,$address){
        $this->debug(__METHOD__,2,__LINE__);
        $method = 'Function=post_subscribe&Email='.$address.'&MailinglistID='.$mailingList;
        $ret = $this->graphicMail_send($method);
        return true;
    }

    public function ml_getOneMailMailingLists($address){
        $this->debug(__METHOD__,2,__LINE__);
        $method = 'Function=get_subscriber_info&Email='.$address;
        $ret = $this->graphicMail_send($method);

        if(substr($ret,0,1) == '0'){
            // There was an error in the query
            $this->setError(self::ERROR_UNKNOWNEXTERNALERROR);
            return self::ERROR_RETURN;
        }

        $dom = new DOMDocument();
        $dom->loadXML($ret);
        $dom->preserveWhiteSpace = false;
        $mls = $dom->getElementsByTagName('mailinglists');
        if($mls->item(0)->nodeValue == 'None'){
            return array();
        }
        $mlArray = $dom->getElementsByTagName('mailinglist');
        foreach($mlArray as $ml){
            $id = $ml->getElementsByTagName('mailinglistid')->item(0)->nodeValue;
            $status = $ml->getElementsByTagName('status')->item(0)->nodeValue;
            if($status == 'S'){
                $name = $this->getParam('mailingLists>'.$id.'>name',false);
                $description = $this->getParam('mailingLists>'.$id.'>description');
                if($name !== false){
                    $rep[$id] = array(
                        'id'=>$id,
                        'name'=>$name,
                        'description'=>$description
                    );
                }
            }
        }

        return $rep;
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
        $method = 'Function=post_unsubscribe&Email='.$address.'&MailinglistID='.$mailingList;
        $ret = $this->graphicMail_send($method);
        if(substr($ret,0,1) == 1){
            return true;
        }elseif(substr($ret,0,1) == 2){
            $this->setError(self::ERROR_ML_HASBEENDELETED, 'ML : '.$mailingList.' - Email : '.$address);
            return true;
        }
        $this->setError(self::ERROR_UNKNOWNEXTERNALERROR);
        return self::ERROR_RETURN;
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
