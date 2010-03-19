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
 * Class that displays and manages the contact page.
 */
class sh_contact extends sh_core {
    const I18N_CONTACTTITLE = 1;
    const I18N_CONTACTINTRO = 2;

    const SITEMAP_PRIORITY = 0.6;
    const SITEMAP_FREQUENCY = 'weekly';

    /**
     * Constructor
     */
    public function construct(){
        $this->renderingConstants = array(
            'I18N_CONTACTTITLE'=>self::I18N_CONTACTTITLE,
            'I18N_CONTACTINTRO'=>self::I18N_CONTACTINTRO
        );
        return true;
    }

    /**
     * protected function checkPhone
     *
     */
    protected function checkPhone($phone){
        if(preg_match('`\+?[ 0-9]{3,}`', $phone)){
            return true;
        }
        return false;
    }

    /**
     * public function show
     */
    public function show(){
        $this->debug('Show process started',1,__LINE__);
        if(!$this->getParam('activated',true)){
            return $this->linker->path->error(404);
        }

        $this->linker->cache->disable();
        $this->linker->html->browserCache(false);

        $this->debug('We verify if the form is submitted',3,__LINE__);

        $formSubmitted = $this->formSubmitted('sendMail',true);
        if($formSubmitted === true){
            $this->debug('It is',3,__LINE__);

            $mailer = $this->linker->mailer->get();

            $coordOK = true;
            $postedMail = trim($_POST['mail']);
            if(
                ($postedMail != '' && $mailer->checkAddress($postedMail))
                || $postedMail == ''
            ){
                $mailOK = true;
            }
            $postedPhone = trim($_POST['phone']);
            if(
                ($postedMail != '' && preg_match('`\+[ 0-9]{5+}`',$postedMail))
                || $postedMail == ''
            ){
                $phoneOK = true;
            }

            if($mailOK || $phoneOK){
                $this->debug('Mail address and/or phone number is valid',3,__LINE__);
                $recipients = $this->getParam('mail');
                $mailData['mail'] = array(
                    'company'=>stripslashes($_POST['company']),
                    'name'=>stripslashes($_POST['name']),
                    'phone'=>$_POST['phone'],
                    'mail'=>$_POST['mail'],
                    'content'=>stripslashes(nl2br($_POST['content'])),
                    'date'=>date('d/m/Y H:i:s')
                );

                $body = $this->render('mailModel',$mailData,false,false);

                $mailObject = $mailer->em_create();
                // Creating and sending the email itself
                $mailer->em_addSubject(
                    $mailObject,
                    $this->getI18n('mail_subject')
                );
                $mailer->em_addContent($mailObject,$body);

                if(!empty($_POST['mail'])){
                    $mailer->em_replyTo($mailObject,$_POST['mail'],$_POST['name']);
                }

                if(file_exists(SH_SITE_FOLDER.'mails/'.$filename)){
                    $mailContent = file_get_contents(
                        SH_SITE_FOLDER.'mails/'.$filename
                    );
                }

                $_POST = array();

                if($mailer->em_send($mailObject,$recipients)){
                    $content['sendmail']['mailSent'] = true;
                    $this->debug('Mail sent successfully!',3,__LINE__);
                    $datas['mail']['to'] = $mail->to;
                    $ret = $this->render('sent',$datas);
                    return true;
                }
                $content['sendmail']['mailNotSent'] = true;
            }else{
                $content['sendmail']['mailOrPhoneError'] = true;
            }
        }elseif($formSubmitted == sh_captcha::CAPTCHA_ERROR){
            $this->debug('The captcha was not verified',1,__LINE__);
            $content['sendmail']['captchaError'] = true;
            $content['captcha']['error'] = true;

        }else{
            $this->debug('It is not',3,__LINE__);
        }


        if($this->getParam('showPhone') && is_array($this->getParam('phone'))){
            $phone = $this->getParam('phone');
            $content['contact']['phone'] = true;
            $content['phone'] = $phone;
        }
        if(
            $this->getParam('showAddress')
            && is_array($this->getParam('address'))
        ){
            $address = implode('<br />',$this->getParam('address'));
            $content['contact']['address'] = $address;
        }
        if($this->getParam('showMail') && $this->getParam('mail') != ''){
            $mail = $this->getParam('mail');
            $content['contact']['mail'] = true;
            $content['mail'] = $mail;
        }
        if($this->getParam('sendMail') && $this->getParam('mail') != ''){
            $content['contact']['sendmail'] = true;

            $content['previous']['company'] = $_POST['company'];
            $content['previous']['name'] = $_POST['name'];
            $content['previous']['phone'] = $_POST['phone'];
            $content['previous']['mail'] = $_POST['mail'];
            $content['previous']['content'] = $_POST['content'];
        }
        $content['contact']['intro'] = nl2br(
            $this->getI18n(self::I18N_CONTACTINTRO)
        );
        $title = $this->getI18n(self::I18N_CONTACTTITLE);
        $this->linker->html->setTitle($title);

        $rf = $this->linker->template->get('contact>renderFile','show');
        $this->render($rf,$content);
    }

    public function edit(){
        $this->onlyAdmin();
        if($this->formSubmitted('contactEditor')){
            $this->saveParams();
            $this->linker->path->redirect(
                $this->translatePageToUri($this->shortClassName.'/show/')
            );
        }
        $site = $this->linker->site;
        $content['form_contact'] = array(
            'showPhone' => 'contact[showPhone]',
            'showAddress' => 'contact[showAddress]',
            'showMail' => 'contact[showAddress]',
            'phone' => 'contact[phone]',
            'address' => 'contact[address]',
            'mail' => 'contact[mail]',
            'sendMail' => 'contact[sendMail]',
            'name'=>'contact[name]',
            'contact_title'=>'contact[title]',
            'contact_intro'=>'contact[intro]'
        );
        foreach($this->getParam('mail') as $mail){
            $mailText .= $separator.$mail['name'].': '.$mail['value'];
            $separator = "\n";
        }
        $separator = "";
        foreach($this->getParam('phone') as $phone){
            $phoneText .= $separator.$phone['name'].': '.$phone['value'];
            $separator = "\n";
        }
        $param = $this->getParam('');

        if($this->getParam('activated') === true){
            $activated = 'checked';
        }
        if($this->getParam('showPhone') === true){
            $showPhone = 'checked';
        }
        if($this->getParam('showMail') === true){
            $showMail = 'checked';
        }
        if($this->getParam('showAddress') === true){
            $showAddress = 'checked';
        }
        if($this->getParam('sendMail') === true){
            $sendMail = 'checked';
        }

        $content['contact'] = array(
            'activated' => $activated,
            'showPhone' => $showPhone,
            'showAddress' => $showAddress,
            'showMail' => $showMail,
            'phone' => $phoneText,
            'address' => implode("\n",$this->getParam('address')),
            'mail' => $mailText,
            'sendMail' => $sendMail,
            'name'=>$site->siteName
        );
        $this->render('edit',$content);
        return true;
    }

    /**
     * public function saveParams
     *
     */
    public function saveParams(){
        //Activation
        $this->removeFromSitemap('contact/show/');
        if(isset($_POST['contact']['activated'])){
            $this->setParam('activated',true);
            $this->addToSitemap(
                'contact/show/',
                self::SITEMAP_PRIORITY,
                self::SITEMAP_FREQUENCY
            );
        }else{
            $this->setParam('activated',false);
        }

        //showPhone
        if(isset($_POST['contact']['showPhone'])){
            $showPhone = true;
        }else{
            $showPhone = false;
        }
        //showAddress
        if(isset($_POST['contact']['showAddress'])){
            $showAddress = true;
        }else{
            $showAddress = false;
        }
        $this->setParam('showAddress',$showAddress);
        //showMail
        if(isset($_POST['contact']['showMail'])){
            $showMail = true;
        }else{
            $showMail = false;
        }
        $this->setParam('showMail',$showMail);
        //sendMail
        if(isset($_POST['contact']['sendMail'])){
            $sendMail = true;
        }else{
            $sendMail = false;
        }
        $this->setParam('sendMail',$sendMail);
        //Mail
        $splitted = explode("\n",$_POST['contact']['mail']);
        foreach($splitted as $element){
            list($name,$value) = explode(':',stripslashes($element),2);
            $mail[] = array('name'=>trim($name),'value'=>trim($value));
        }
        $this->setParam('mail',$mail);
        //Phone
        $splitted = explode("\n",$_POST['contact']['phone']);
        foreach($splitted as $element){
            list($name,$value) = explode(':',stripslashes($element),2);
            $phone[] = array('name'=>trim($name),'value'=>trim($value));
        }
        $this->setParam('phone',$phone);
        //Addresses
        $addresses = str_replace(
            array("\r\n","\r"),
            "\n",
            stripslashes($_POST['contact']['address'])
        );
        $addresses = explode("\n",$addresses);
        $this->setParam('address',$addresses);
        //Intro
        $this->setI18n(self::I18N_CONTACTINTRO,$_POST['contact']['intro']);
        //Title
        $this->setI18n(self::I18N_CONTACTTITLE,$_POST['contact']['title']);

        // Saves the datas
        $this->writeParams();
    }

    /**
     * protected function save
     */
    protected function save($id){
        if(isset($_POST['showDate'])){
            $showDate = '1';
        }else{
            $showDate = '0';
        }
        if(isset($_POST['showTitle'])){
            $showTitle = '1';
        }else{
            $showTitle = '0';
        }
        $replacements = array('id' => $id,
                                'title' => $_POST['title'],
                                'showTitle' => $showTitle,
                                'showDate' => $showDate,
                                'active' => $_POST['active'],
                                'content' => $_POST['content']);

        $this->db_execute('save',$replacements);
        if($_POST['active'] == true){
            header(
                'location: '.$this->linker->path->getLink('content/show/'.$id)
            );
        }
    }

    /**
     * protected function addSelected
     */
    protected function addSelected($list,$input,$sel){
        if(is_array($list)){
            foreach($list as $key=>$element){
                if($element[$input] == $sel){
                    $list[$key][strtoupper($input).'_SELECTED'] = 'selected="selected"';
                }else{
                    $list[$key][strtoupper($input).'_SELECTED'] = '';
                }
            }
        }
        return $list;
    }

    /**
     * public function sitemap_renew
     *
     */
    public function sitemap_renew(){
        if($this->getParam('activated',true)){
            $this->addToSitemap($this->shortClassName.'/show/', 0.6);
        }
        return true;
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        if($page == $this->shortClassName.'/show/'){
            return '/'.$this->shortClassName.'/show.php';
        }
        if($page == $this->shortClassName.'/edit/'){
            return '/'.$this->shortClassName.'/edit.php';
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
        if($uri == '/'.$this->shortClassName.'/edit.php'){
            return $this->shortClassName.'/edit/';
        }
        return false;
    }

    /**
     * public function getPageName
     *
     */
    public function getPageName($action, $id = null){
        if($action == 'show'){
            return $this->getI18n('title_for_listing');
        }
        return $this->__toString().'->'.$action.'->'.$id;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}