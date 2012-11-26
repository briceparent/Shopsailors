<?php
/**
 * @author Brice PARENT (Websailors) for Shopsailors
 * @copyright Shopsailors 2012
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')){header('location: directCallForbidden.php');}

class cm_php_mail_function extends sh_mailsenders{
    const CLASS_VERSION = '1.1.12.11.24';

    protected $lastErrorId = 0;
    protected $replyToSet = array();
    const DEV_NAME = 'PHP mail function / Shopsailors';
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
        $this->debug('We get the mailing datas from the params file and construct the PHP Mail #'.$cpt, 3, __LINE__);

        $this->mailers[$cpt]['From']        = $this->getParam('from');
        $this->mailers[$cpt]['FromName']    = $this->getParam('fromName');
        $this->mailers[$cpt]['ReplyTo'][$this->getParam('replyTo')] = $this->getParam('replyToName');
	
        $this->mailers[$cpt]['Attachment']  = array();
        return $cpt;
    }

    public function em_from($id,$from,$name = ''){
        $this->debug(__METHOD__,2,__LINE__);
        if(!self::checkAddress($from)){
            return $this->setError(self::ERROR_WRONGADDRESSFORMAT);
        }
        if(isset($this->mailers[$id])){
            $this->mailers[$id]['From'] = $from;
            if($name != ''){
                $this->mailers[$id]['FromName'] = str_replace(array('<','>'),'',$name);
            }else{
                $this->mailers[$id]['FromName'] = '';
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
            $this->mailers[$id]['ReplyTo'][$replyTo] = str_replace(array('<','>'),'',$name);
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
            $this->mailers[$id]['BCC'][$bcc] = str_replace(array('<','>'),'',$name);
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
            $this->mailers[$id]['Address'][$address] = str_replace(array('<','>'),'',$name);
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
            $this->mailers[$id]['Subject'] = $subject;
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
            $this->mailers[$id]['Body'] = $this->clean_html_content($content);
            $this->mailers[$id]['ContentType'] = $type;
        }else{
            return $this->setError(self::ERROR_EM_NOTCONSTRUCTED);
        }
    }
    
    public function em_attach($id,$fileToAttach,$name='',$encoding='base64',$type='application/octet-stream'){
        $this->debug(__METHOD__,2,__LINE__);
        if(isset($this->mailers[$id])){
            if(self::MAY_ADDATTACHMENT){
                if(file_exists($fileToAttach)){
                    return $this->mailers[$id]['Attachment'][$fileToAttach] = array(
                        $fileToAttach,
                        $name,
                        $encoding,
                        $type
                    );
                }
                return $this->setError(self::ERROR_EM_ATTACHMENTNOTFOUND,' (file: '.$fileToAttach.')');
            }
            return $this->setError(self::ERROR_EM_MAYNOTADDATTACHMENT);
        }else{
            return $this->setError(self::ERROR_EM_NOTCONSTRUCTED);
        }
    }




function toAddress($mail, $name){
	if(empty($name)){
		return $mail;
	}else{
		return $name.' <'.$mail.'>';
	}
}

function toAddresses($addresses){
	$ret = array();
	if(is_array($addresses)){
		foreach($addresses as $mail=>$name){
			$ret[] = $this->toAddress($mail,$name);
		}
	}
	return implode(', ',$ret);
}

function mail_send( $id ) {
	// Creating the mail content with its headders
        $uid = md5(uniqid(time()));
	$h = 'From: '.$this->toAddress($this->mailers[$id]['From'],$this->mailers[$id]['FromName'])."\r\n";
	$h .= "Reply-To: ".$this->toAddresses($this->mailers[$id]['ReplyTo'])."\r\n";
	$h .= "BCC: ".$this->toAddresses($this->mailers[$id]['BCC'])."\r\n";
	$to = $this->toAddresses($this->mailers[$id]['Address']);
	
	$subject = $this->mailers[$id]['Subject'];
	
        $h .= "MIME-Version: 1.0\r\n";
        $h .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
	
        $h .= "This is a multi-part message in MIME format.\r\n";
	
	if($this->mailers[$id]['ContentType'] == self::EM_CONTENTTYPE_HTML){
		$h .= "--".$uid."\r\n";
		$h .= "Content-Type: multipart/alternative; boundary=\"".'2'.$uid."\"\r\n\r\n";
		$plainTextMessage = strip_tags(
			str_replace(
				array('<br />','<div','</div>','<p','</p>'),
				array("\n","<div\n","</div>\n","<p\n","</p>\n"),
				$this->mailers[$id]['Body']
			)
		);
		
		$h .= "--".'2'.$uid."\r\n";
		$h .= 'Content-type:'.self::EM_CONTENTTYPE_PLAINTEXT.'; charset=iso-8859-1'."\r\n";
		$h .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$h .= $plainTextMessage."\r\n\r\n";
	
		$h .= "--".'2'.$uid."\r\n";
		$h .= 'Content-type:'.$this->mailers[$id]['ContentType'].'; charset=iso-8859-1'."\r\n";
		$h .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$h .= $this->mailers[$id]['Body']."\r\n\r\n";
		$h .= "--".'2'.$uid."--\r\n";
		
		$plainTextMessage = '';
	}else{
		$plainTextMessage = $this->mailers[$id]['Body'];
	}
	
	foreach($this->mailers[$id]['Attachment'] as $attachment){
		$h .= "--".$uid."\r\n";
		echo 'Attaching file '.$attachment[0].' with the name '.$attachment[1].' and tht type '.$attachment[3].'<br />';
		// read file into $data var
		$file = fopen($attachment[0], "rb");
		$data = fread($file,  filesize( $attachment[0] ) );
		fclose($file);
	 
		// split the file into chunks for attaching
		$content = chunk_split(base64_encode($data));
	 
		// build the headers for attachment and html
		$h .= "Content-Type: ".$attachment[3]."; name=\"".$attachment[1]."\"\r\n";
		$h .= "Content-Transfer-Encoding: base64\r\n";
		$h .= "Content-Disposition: attachment; filename=\"".$attachment[1]."\"\r\n\r\n";
		$h .= $content."\r\n\r\n";
	}
	$h .= "--".$uid."--";
 
        // send mail
        return mail( $to, $subject, $plainTextMessage, str_replace("\r\n","\n",$h) ) ;
    }



    
    public function em_send($id,$addresses = array()){
        $this->debug(__METHOD__,2,__LINE__);
        if(isset($this->mailers[$id])){
            // We verify if everything has been filled
            $mail =& $this->mailers[$id];
            if(trim($mail['Subject']) == ''){
                return $this->setError(self::ERROR_NL_NOSUBJECT);
            }
            if(trim($mail['Body']) == ''){
                return $this->setError(self::ERROR_NL_NOCONTENT);
            }
            if((count($addresses) + count($mail['Address']) + count($mail['BCC'])) == 0){
                return $this->setError(self::ERROR_NL_NORECIPIENTS);
            }
            if(is_array($addresses)){
                foreach($addresses as $address){
                    if(isset($address['value'])){
                        $this->debug('Adding address '.$address['value'], 3, __LINE__);
                        $this->em_addAddress($id,$address['value'], $address['name']);
                    }else{
                        $this->debug('Adding address '.$address[0], 3, __LINE__);
                        $this->em_addAddress($id,$address[0], $address[1]);
                    }
                }
            }
            
            if($this->mail_send($id)){
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
    }
    
    public function nl_removeMailingList($newsletterId,$mailingListId = self::REMOVE_ALL_MAILING_LISTS){
        $this->debug(__METHOD__, 2, __LINE__);
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
    }

    public function ml_getName($mailingList){
        $this->debug(__METHOD__,2,__LINE__);
    }

    public function ml_edit($mailingList,$newName,$newDescription){
        $this->debug(__METHOD__.' ('.$mailingList.', '.$newName.', [desc])',2,__LINE__);
    }

    /**
     * Returns false if the name isn't already used by any mailing list, or the
     * mailing list id that is named like that, or an error code.
     * @param str $name The name we are looking for
     *
     */
    protected function ml__nameExists($name){
        $this->debug(__METHOD__,2,__LINE__);
    }

    public function ml_getByName($name){
        $this->debug(__METHOD__.'('.$name.')',2,__LINE__);
    }

    public function ml_delete($mailingList){
        $this->debug(__METHOD__,2,__LINE__);
    }

    public function ml_get($mailingList){
        $this->debug(__METHOD__,2,__LINE__);
    }

    public function ml_getAll($alsoGetDeleted = false){
        $this->debug(__METHOD__,2,__LINE__);
    }

    public function ml__getId($mailingList,$address,$raiseErrorIfNotFound = true){
        $this->debug(__METHOD__,2,__LINE__);
    }

    public function ml_addAddress($mailingList,$address){
        $this->debug(__METHOD__,2,__LINE__);
    }

    public function ml_addAddresses($mailingList,$addressesArray){
        $this->debug(__METHOD__,2,__LINE__);
    }

    public function ml_getOneMailMailingLists($address){
        $this->debug(__METHOD__,2,__LINE__);
    }

    public function ml_editAddress($mailingList,$oldAddress,$newAddress){
        $this->debug(__METHOD__.' ('.$mailingList.', '.$oldAddress.', '.$newAddress.')',2,__LINE__);
    }

    public function ml_removeAddress($mailingList,$address){
        $this->debug(__METHOD__.' ('.$mailingList.', '.$address.')',2,__LINE__);
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
