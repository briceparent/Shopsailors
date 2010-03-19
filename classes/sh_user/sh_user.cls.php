<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')) {header('location: directCallForbidden.php');}

class sh_user extends sh_core{
    protected $minimal = array(
        'tryToConnect'=>true,'getUserData'=>true,'getOneUserId'=>true,'passwordForgotten_master'=>true,
        'connection_step1_master'=>true,'connection_step2_master'=>true,'master_get_last_connection'=>true,
        'master_set_connection_status'=>true,'master_get_connection_failures'=>true,
        'master_clear_connection_failures'=>true
    );
    protected $connected = false;
    protected $allowed = '*';
    const WRONG_DATA_TEXT = 'WRONG_DATA';
    const SITE_NOT_ALLOWED_TEXT = 'SITE_NOT_ALLOWED';
    const ERROR_USING_FORM_TEXT = 'ERROR_USING_FORM';
    const WEBSAILORS_USERNAME_NOT_ALLOWED_TEXT = 'WEBSAILORS_USERNAME_NOT_ALLOWED';
    const ACCOUNT_NOT_ACTIVATED_TEXT = 'ACCOUNT_NOT_ACTIVATED';
    const USER_NAME = 'name';
    const USER_LOGIN = 'login';
    const LINE_SEPARATOR = '__NEW_LINE__';
    const NOT_CONNECTED = 'The user is not connected.';
    const USER_DATA_NOT_FOUND = 'This user data does not exist.';

    ////////////////////////////////////////////////////////////////////////////////
    //                        USED ON EVERY CLIENT                                //
    ////////////////////////////////////////////////////////////////////////////////
    public function cron_job($time){
        $this->debug(__FUNCTION__, 2, __LINE__);
        if($time == sh_cron::JOB_DAY){
            // Everyday, we should clean the connection table from the database
            $this->db_execute('clear_older_connections', array(),$qry);
        }
    }

    public function getMasterUrl($withEndSlash = true){
        if($withEndSlash){
            return $this->masterUrl;
        }else{
            return substr($this->masterUrl,0,-1);
        }
    }

    public function isConnected(){
        return $this->connected;
    }

    public function getUserId(){
        if($this->isConnected()){
            return $_SESSION[__CLASS__]['connected']['userId'];
        }
        return false;
    }

    /**
     * Returns a boolean telling if the access to the page is granted to connected users or not.
     * @see set_needs_connection()
     * @return boolean
     * True if the page needs a connected user.<br />
     * False if not.
     */
    public static function needs_connection(){
        if(!is_dir(SH_SITE_FOLDER.__CLASS__)){
            mkdir(SH_SITE_FOLDER.__CLASS__);
        }
        if(file_exists(SH_SITE_FOLDER.__CLASS__.'/needs_connection.php')){
            return true;
        }
        return false;
    }

    /**
     * Shows the form to edit a user account and treats its submitted values.
     */
    public function manage(){
        $this->onlyAdmin();
        if($this->formSubmitted('restrictionsEditor')){
            if(isset($_POST['needs_connection'])){
                $this->set_needs_connection(true);
            }else{
                $this->set_needs_connection(false);
            }
            $mails = str_replace(array(' ',',',';'),"\n",$_POST['allowedUsers']);
            $mailsList = explode("\n",$mails);
            if(is_array($mailsList)){
                if(file_exists(SH_SITE_FOLDER.__CLASS__.'/sentMails.php')){
                    include(SH_SITE_FOLDER.__CLASS__.'/sentMails.php');
                }
                if(!is_array($sentMails)){
                    $sentMails = array();
                }
                foreach($mailsList as $mail){
                    $mail = trim($mail);
                    $mailer = $this->linker->mailer->get();
                    if($mailer->checkAddress($mail)){
                        $userId = $this->prot_getOneUserId('mail',$mail);
                        if($userId == 0){
                            if(!in_array($mail,$sentMails)){
                                $values['websailors']['createAccountPage'] =
                                    'http://www.websailors.fr/connection/create_account.php';
                                $values['client']['site'] = 'http://'.$this->linker->path->getDomain();
                                $values['dest']['mail'] = $mail;

                                $content = $this->render('mailModel',$values,false,false);




                                $mailObject = $mailer->em_create();
                                // Creating and sending the email itself
                                $address = $user['mail'];

                                $mails = explode("\n",$this->getParam('command_mail'));
                                if(is_array($mails)){
                                    foreach($mails as $oneMail){
                                        $mailer->em_addBCC($mailObject,$oneMail);
                                    }
                                }

                                $mailer->em_addSubject(
                                    $mailObject,
                                    $this->getI18n('mail_authorization_title').'http://'.$this->linker->path->getDomain()
                                );
                                $mailer->em_addContent($mailObject,$content);

                                if(!$mailer->em_send($mailObject,array(array($mail)))){
                                    // Error sending the email
                                    echo 'Erreur dans l\'envoi du mail de validation...';
                                }

                                $sentMails[] = $mail;
                            }
                            $inexistantButAllowedUsers[] = $mail;
                        }else{
                            if(!in_array($mail,$sentMails)){
                                $values['websailors']['createAccountPage'] =
                                    'http://www.websailors.fr/connection/create_account.php';
                                $values['client']['site'] = 'http://'.$this->linker->path->getDomain();
                                $values['dest']['mail'] = $mail;

                                $content = $this->render('mailAccountAuthorized',$values,false,false);

                                // Creating and sending the email itself
                                $mailObject = $mailer->em_create();
                                $address = $user['mail'];

                                $mails = explode("\n",$this->getParam('command_mail'));
                                if(is_array($mails)){
                                    foreach($mails as $oneMail){
                                        $mailer->em_addBCC($mailObject,$oneMail);
                                    }
                                }

                                $mailer->em_addSubject(
                                    $mailObject,
                                    $this->getI18n('mail_authorization_title').'http://'.$this->linker->path->getDomain()
                                );
                                $mailer->em_addContent($mailObject,$content);

                                if(!$mailer->em_send($mailObject,array(array($mail)))){
                                    // Error sending the email
                                    echo 'Erreur dans l\'envoi du mail de validation...';
                                }

                                $sentMails[] = $mail;
                            }
                            $allowedUser[] = $userId;
                        }
                    }
                }
                $this->linker->helper->writeArrayInFile(
                    SH_SITE_FOLDER.__CLASS__.'/allowed.php',
                    'allowedUsers',
                    $allowedUser
                );
                $this->linker->helper->writeArrayInFile(
                    SH_SITE_FOLDER.__CLASS__.'/inexistantButAllowedUsers.php',
                    'inexistantButAllowedUsers',
                    $inexistantButAllowedUsers
                );
                $this->linker->helper->writeArrayInFile(
                    SH_SITE_FOLDER.__CLASS__.'/sentMails.php',
                    'sentMails',
                    $sentMails
                );
            }
        }

        if(self::needs_connection(true)){
            $values['form']['needs_connection'] = 'checked';
        }
        if(file_exists(SH_SITE_FOLDER.__CLASS__.'/allowed.php')){
            include(SH_SITE_FOLDER.__CLASS__.'/allowed.php');
            if(is_array($allowedUsers)){
                foreach($allowedUsers as $allowedUser){
                    $userData = $this->getOneUserData($allowedUser);
                    $values['allowed']['mails'] .= $separator.$userData['mail'];
                    $separator = "\n";
                }
            }
        }
        if(file_exists(SH_SITE_FOLDER.__CLASS__.'/inexistantButAllowedUsers.php')){
            include(SH_SITE_FOLDER.__CLASS__.'/inexistantButAllowedUsers.php');
            if(is_array($inexistantButAllowedUsers)){
                foreach($inexistantButAllowedUsers as $inexistantButAllowedUser){
                    $values['allowed']['mails'] .= $separator.$inexistantButAllowedUser;
                    $separator = "\n";
                }
            }
        }
        $this->render('manage', $values);
    }

    /**
     * Sets the need connection state, adding or removing a file under<br />
     * SH_SITE_FOLDER/[class_name].<br />
     * This file name is needs_connection.php. If it exists, it means that the site
     * needs a connection.
     * @param boolean $state The state we want to set
     */
    public function set_needs_connection($state){
        if($state){
            $f = fopen(SH_SITE_FOLDER.__CLASS__.'/needs_connection.php','w+');
            fclose($f);
        }else{
            if(file_exists(SH_SITE_FOLDER.__CLASS__.'/needs_connection.php')){
                unlink(SH_SITE_FOLDER.__CLASS__.'/needs_connection.php');
            }
        }
        return true;
    }

    /**
     * Requests a user's id to the master website, using any field.<br />
     * Will call the url taken from the params file in master>getOneUserId.
     * @param string $field Mysql's field name in which we will look to find the value $value.
     * @param string $value Value that we are looking for in the database, in the field $field of the
     * table "users"
     * @return integer The id that have been found in the databse, or 0 if none were found.
     */
    protected function prot_getOneUserId($field,$value){
        $connectionPage = $this->masterUrl.$this->getParam('master>getOneUserId');
        $requestId = $this->linker->postRequest->create($connectionPage);
        $this->linker->postRequest->setData($requestId,'field',$field);
        $this->linker->postRequest->setData($requestId,'value',$value);
        $id = $this->linker->postRequest->send($requestId);
        return $id;
    }

    /**
     * Verifies that the site can be accessed by the current user.<br />
     * - If there is no need to be connected, it always returns true.<br />
     * - If there is a need to be connected, and the user is connected AND allowed
     * to access this site, it also returns true.<br />
     * - In any other cases, it returns false.
     */
    public function siteIsOpen(){
        if(!self::needs_connection() || $this->getConnection() === true){
            return true;
        }
        // Authorises the shared images
        if(substr($this->linker->path->uri,0,strlen(SH_SHAREDIMAGES_PATH)) == SH_SHAREDIMAGES_PATH){
            return true;
        }
        // Authorises the banner image
        if($this->linker->path->uri == $this->getParam('banner_image')){
            return true;
        }
        $this->linker->cache->disable();
        if($this->linker->path->page['page'] == $this->shortClassName.'/passwordForgotten/'){
            $values['page']['connectionForm'] =  $this->passwordForgotten(false);
        }else{
            $values['page']['connectionForm'] = $this->connect(false);

            if($this->getConnection()){
                $this->linker->path->refresh();
                return true;
            }
        }

        $values['banner']['image'] = $this->getParam('banner_image');

        $values['site']['name'] = $this->linker->path->getDomain();

        echo $this->render('index', $values, false, false);

        exit;
    }

    /**
     * Prepares and returns the link to access connection page and disconnection page
     * @return array(string,string) Returns an array containing the state of the link
     * (connect or disconnect) as first argument, and the link as second.
     */
    public function getConnectionLink(){
        if($this->isConnected()){
            return array('disconnect',$this->linker->path->getLink('user/disconnect/'));
        }
        return array('connect',$this->linker->path->getLink('user/connect/'));
    }

    /**
     *
     * @return boolean The status of the connection.<br />
     * True if the user is connected.<br />
     * False if not.
     */
    protected function getConnection(){
        if(isset($_SESSION[__CLASS__]['connected']['userId'])){
            $this->userId = $_SESSION[__CLASS__]['connected']['userId'];
            $this->connected = true;
            return true;
        }
        return false;
    }

    /**
     * Get all one user's params from the database, using the master site.<br />
     * The url that is called is set in the params under master>getUserData.
     * @param integer $id Id of the user we want the params.
     * @return array() All the user's datas as $field=>$value entries in the array.
     */
    public function getOneUserData($id){
        $connectionPage = $this->masterUrl.$this->getParam('master>getUserData');
        $requestId = $this->linker->postRequest->create($connectionPage);
        $this->linker->postRequest->setData($requestId,'user',$id);
        $response = $this->linker->postRequest->send($requestId);
        $entries = explode("\n".self::LINE_SEPARATOR."\n", $response);
        foreach($entries as $entry){
            list($fieldName,$fieldValue) = explode("\n",$entry);
            if($fieldName != ''){
                $ret[$fieldName] = $fieldValue;
            }
        }
        return $ret;
    }

    /**
     * Calls getOneUserData and returns its results with the user id stored in the session,
     * if the user is connected.<br />
     * If not, returns false.
     */
    public function getData(){
        if($this->getConnection()){
            return $this->getOneUserData($_SESSION[__CLASS__]['connected']['userId']);
        }
        return false;
    }

    public function passwordForgotten_master(){
        $site = $this->getFromAnyServer('site');
        $mail = $this->linker->crypter->uncrypt($this->getFromAnyServer('mail'),$site);

        $user = $this->db_execute(
            'getOneUserId',
            array(
                'field'=>'mail',
                'value'=>$mail
            )
        );
        $id = $user[0]['id'];
        $newPassword = substr(md5(__CLASS__.microtime()),0,8);
        $dbPassword = $this->preparePassword($newPassword);
        $this->db_execute('addTemporaryPassword', array(
            'id'=>$id,
            'temporaryPassword'=>$dbPassword
        ));
    
        $values['password']['new'] = $newPassword;
        $content = $this->render('mailTemporaryPassword',$values,false,false);
    
    

        $mailer = $this->linker->mailer->get();
        // Creating and sending the email itself
        $mailObject = $mailer->em_create();
        $address = $user['mail'];

        $mails = explode("\n",$this->getParam('command_mail'));
        if(is_array($mails)){
            foreach($mails as $oneMail){
                $mailer->em_addBCC($mailObject,$oneMail);
            }
        }

        $mailer->em_addSubject(
            $mailObject,
            $this->getI18n('mail_temporaryPassword_title').'http://'.$this->linker->path->getDomain()
        );
        $mailer->em_addContent($mailObject,$content);

        if(!$mailer->em_send($mailObject,array(array($mail)))){
            // Error sending the email
            echo 'Erreur dans l\'envoi du mail...';
            return false;
        }
        echo 'OK';
        return true;
    }

    public function passwordForgotten($sendToHtml = true){
        $this->linker->cache->disable();
        //delays the script also to prevent brutforce attacks
        if($this->formSubmitted('passwordForgotten')){

            $mailer = $this->linker->mailer->get();
            if($mailer->checkAddress($_POST['mail'])){
                // Gets and prepares the data
                $mail = trim($_POST['mail']);
                $site = $this->clearData(SH_SITE);

                // Crypts it
                $mail = $this->linker->crypter->crypt($mail,$site);

                // Sends it
                $connectionPage = $this->masterUrl.$this->getParam('master>passwordForgotten');
                $requestId = $this->linker->postRequest->create($connectionPage);
                $this->linker->postRequest->setData($requestId,'mail',urlencode($mail));
                $this->linker->postRequest->setData($requestId,'site',urlencode($site));
                $response = $this->linker->postRequest->send($requestId);
                if($response == 'OK'){
                    return $this->render('passwordForgotten_response',$values,false,$sendToHtml);
                }
            }
            $values['error']['message'] = $this->getI18n('passwordForgotten_response_text_notfound');
        }
        if($sendToHtml){
            $this->render('passwordForgotten',$values);
            return false;
        }
        return $this->render('passwordForgotten',$values,false,false);
    }

    protected function get_last_connection($user){
        $this->debug(__FUNCTION__.'('.$user.')', 2, __LINE__);
        $connectionPage = $this->masterUrl.$this->getParam('master>get_last_connection');
        $this->debug('Connection page is '.$connectionPage, 3, __LINE__);
        $requestId = $this->linker->postRequest->create($connectionPage);
        $this->linker->postRequest->setData($requestId,'user',$user);
        $response = $this->linker->postRequest->send($requestId);
        $ret = $this->splitReturn($response);
        return $ret;
    }

    public function master_get_last_connection(){
        $this->checkIntegrity();
        $user = $this->getFromAnyServer('user');
        // It is a successfull connection
        list($ret) = $this->db_execute(
            'get_connection_successfull',
            array('user'=>$user),
            $qry
        );
        if(isset($ret['date'])){
            echo 'site'."\n".$ret['site']."\n".self::LINE_SEPARATOR."\n";
            echo 'date'."\n".$ret['date']."\n";
        }else{
            echo 'No return '."\n".'qry : '.str_replace("\n",' ',$qry)."\n";
        }
    }

    protected function splitReturn($return){
        $entries = explode("\n".self::LINE_SEPARATOR."\n", $return);
        if(is_array($entries)){
            foreach($entries as $entry){
                list($fieldName,$fieldValue) = explode("\n",$entry,2);
                if($fieldName != ''){
                    $ret[$fieldName] = $fieldValue;
                }
            }
            return $ret;
        }
        return array();
    }

    protected function get_connection_failures($user){
        $this->debug(__FUNCTION__.'('.$user.')', 2, __LINE__);
        $connectionPage = $this->masterUrl.$this->getParam('master>get_connection_failures');
        $requestId = $this->linker->postRequest->create($connectionPage);
        $this->linker->postRequest->setData($requestId,'user',$user);
        $response = $this->linker->postRequest->send($requestId);
        $ret = $this->splitReturn($response);
        return $ret;
    }

    public function master_get_connection_failures(){
        $this->checkIntegrity();
        $user = $this->getFromAnyServer('user');
        // It is a successfull connection
        $ret = $this->db_execute(
            'get_connection_failures',
            array('user'=>$user),
            $qry
        );
        $cpt = 0;
        if(is_array($ret)){
            list($number) = $this->db_execute(
                'get_connection_failures_number',
                array('user'=>$user),
                $qry
            );
            $number = $number['count'];
            foreach($ret as $oneFailure){
                echo 'failure_'.$cpt.'_date'."\n".$oneFailure['date']."\n".self::LINE_SEPARATOR."\n";
                echo 'failure_'.$cpt.'_site'."\n".$oneFailure['site']."\n".self::LINE_SEPARATOR."\n";
                echo 'failure_'.$cpt.'_ip'."\n".$oneFailure['ip']."\n".self::LINE_SEPARATOR."\n";
                $cpt++;
            }
            echo 'number'."\n".$number;
        }else{
            echo 'number'."\n".'0';
        }
    }

    protected function set_connection_status($site,$user,$status){
        $this->debug(__FUNCTION__.'('.$site.', '.$user.', '.$status.')', 2, __LINE__);
        $connectionPage = $this->masterUrl.$this->getParam('master>set_connection_status');
        $requestId = $this->linker->postRequest->create($connectionPage);
        $this->linker->postRequest->setData($requestId,'site',$site);
        $this->linker->postRequest->setData($requestId,'user',$user);
        $this->linker->postRequest->setData($requestId,'status',$status);
        $this->linker->postRequest->setData($requestId,'ip',$_SERVER['REMOTE_ADDR']);
        $response = $this->linker->postRequest->send($requestId);
        $entries = explode("\n".self::LINE_SEPARATOR."\n", $response);
        foreach($entries as $entry){
            list($fieldName,$fieldValue) = explode("\n",$entry);
            if($fieldName != ''){
                $ret[$fieldName] = $fieldValue;
            }
        }
        return $ret;
    }

    public function master_set_connection_status(){
        $this->checkIntegrity();
        $site = $this->getFromAnyServer('site');
        $user = $this->getFromAnyServer('user');
        $status = $this->getFromAnyServer('status');
        $ip = $this->getFromAnyServer('ip');
        if($status){
            // It is a successfull connection
            list($ret) = $this->db_execute(
                'get_connection_successfull',
                array('user'=>$user),
                $qry
            );
            if(isset($ret['date'])){
                // There is already an entry, so we modify it
                $this->db_execute(
                    'update_connection_successfull',
                    array('site'=>$site,'user'=>$user),
                    $qry
                );
                echo 'action'."\n".'Success - Entry updated';
            }else{
                // It's the first connection, so we add an entry
                $this->db_execute(
                    'add_connection_successfull',
                    array('site'=>$site,'user'=>$user),
                    $qry
                );
                echo 'action'."\n".'Success - Entry added';
            }
        }else{
            // It is a connection failure
            list($ret) = $this->db_execute(
                'add_connection_failure',
                array('site'=>$site,'user'=>$user,'ip'=>$ip),
                $qry
            );
            echo 'action'."\n".'Failure - Entry added';
        }
    }

    protected function clear_connection_failures($user){
        $this->debug(__FUNCTION__.'('.$site.', '.$user.')', 2, __LINE__);
        $connectionPage = $this->masterUrl.$this->getParam('master>clear_connection_failures');
        $requestId = $this->linker->postRequest->create($connectionPage);
        $this->linker->postRequest->setData($requestId,'user',$user);
        $response = $this->linker->postRequest->send($requestId);
        return true;
    }

    public function master_clear_connection_failures(){
        $this->checkIntegrity();
        $user = $this->getFromAnyServer('user');
        $this->db_execute(
            'clear_connections_failures',
            array('user'=>$user)
        );
        echo 'OK';
    }

    public function connection_step1_master(){
        $this->checkIntegrity();
        sleep(0.5);
        $site = $this->getFromAnyServer('site');
        $userName = $this->linker->crypter->uncrypt($this->getFromAnyServer('user'),$site);
        list($user) = $this->db_execute('getOneUserVerification', array('userName'=>$userName),$qry);
        if(isset($user['verification']) && $user['active'] == '1'){
            echo 'id'."\n".$user['id']."\n".self::LINE_SEPARATOR."\n";
            echo 'verification'."\n".$user['verification'];
            return true;
        }
        return false;
    }

    public function connection_step2_master(){
        $this->checkIntegrity();
        sleep(0.5);
        $site = $this->getFromAnyServer('site');
        $userName = $this->linker->crypter->uncrypt($this->getFromAnyServer('user'),$site);
        $password = $this->preparePassword($this->linker->crypter->uncrypt($this->getFromAnyServer('password'),$site));
        $verifPhrase = $this->linker->crypter->uncrypt($this->getFromAnyServer('verifPhrase'),$site);
        list($user) = $this->db_execute(
            'connectOneUser',
            array(
                'userName'=>$userName,
                'password'=>$password,
                'verification'=>$verifPhrase
            )
        );
        if(isset($user['id'])){
            echo $user['id'];
            return true;
        }
        list($user) = $this->db_execute(
            'connectOneUserWithNewPassword',
            array(
                'userName'=>$userName,
                'temporaryPassword'=>$password,
                'verification'=>$verifPhrase
            )
        );
        if(isset($user['id'])){
            $this->db_execute('changePassword', array('id'=>$user['id'],'newPassword'=>$password));
            echo $user['id'];
            return true;
        }
        return false;
    }

    public function connection_step1($userName){
        if(!isset($_SESSION[__CLASS__]['delay'])){
            $_SESSION[__CLASS__]['delay'] = 0.5;
        }
        sleep($_SESSION[__CLASS__]['delay']);
        // Gets and prepares the data
        $userName = $this->clearData($userName);
        $site = $this->clearData(SH_SITE);

        // Crypts it
        $userName = $this->linker->crypter->crypt($userName,$site);

        // Sends it
        $uri = $this->shortClassName.'/connection_step1_master.php';
        $connectionPage = $this->masterUrl.$uri;
        $requestId = $this->linker->postRequest->create($connectionPage);
        $this->linker->postRequest->setData($requestId,'user',urlencode($userName));
        $this->linker->postRequest->setData($requestId,'site',urlencode($site));
        $response = $this->linker->postRequest->send($requestId);
        $ret = $this->splitReturn($response);
        return $ret;
    }

    public function connection_step2($password){
        if(!isset($_SESSION[__CLASS__]['delay'])){
            $_SESSION[__CLASS__]['delay'] = 0.5;
        }
        sleep($_SESSION[__CLASS__]['delay']);
        $_SESSION[__CLASS__]['delay'] *= 2;
        // Gets and prepares the data
        $password = $this->clearData($password);
        $verifPhrase = $_SESSION[__CLASS__]['identification']['verifPhrase'];
        $userName = $_SESSION[__CLASS__]['identification']['userName'];
        $site = $this->clearData(SH_SITE);

        // Crypts it
        $userName = $this->linker->crypter->crypt($userName,$site);
        $password = $this->linker->crypter->crypt($password,$site);
        $verifPhrase = $this->linker->crypter->crypt($verifPhrase,$site);

        // Sends it
        $uri = $this->shortClassName.'/connection_step2_master.php';
        $connectionPage = $this->masterUrl.$uri;
        $requestId = $this->linker->postRequest->create($connectionPage);
        $this->linker->postRequest->setData($requestId,'user',urlencode($userName));
        $this->linker->postRequest->setData($requestId,'password',urlencode($password));
        $this->linker->postRequest->setData($requestId,'verifPhrase',urlencode($verifPhrase));
        $this->linker->postRequest->setData($requestId,'site',urlencode($site));
        $response = $this->linker->postRequest->send($requestId);
        return $response;
    }

    /**
     * Creates the connection form, and manages with its submitted values.<br />
     * @param boolean $sendToHtml
     * If set to true (default behaviour), send the contents to the sh_html class.<br />
     * If set to false, returns the contents.
     */
    public function connect($sendToHtml = true){
        $this->linker->cache->disable();
        //$this->linker->javascript->get(sh_javascript::AES_COMPLETE);
        /*
        include(dirname(__FILE__).'/aes.php');
$key256 = '603deb1015ca71be2b73aef0857d77811f352c073b6108d72d9810a30914dff4';
// =====================================================================================================
$Cipher = new AES(AES::AES256);

$content = "Alice has a fucking cat";
$values['aes']['original'] = $content;
$values['aes']['phptest'] .= $content.'<br />';

$content = utf8_decode($content);
$content = base64_encode($content);
echo 'In 64 : '.$content;
$content = $Cipher->encrypt($content, $key256);
$end = microtime(true);
$values['aes']['phptest'] .=  $content.'<br />';
$values['aes']['crypted'] = $content;
$values['aes']['pass'] = $key256;
$content = $Cipher->decrypt($content, $key256);-
$end = microtime(true);
$values['aes']['phptest'] .=  $content.'<br />';
*/

        if($this->isConnected()){
            $_SESSION[__CLASS__]['delay'] = 0.5;
            $userId = $_SESSION[__CLASS__]['connected']['userId'];
            if($this->isMaster()){
                $values['connected']['asMaster'] = true;
            }elseif($this->isAdmin()){
                $values['connected']['asAdmin'] = true;
            }else{
                $values['connected']['asUser'] = true;
            }
            $values['user']['name'] = $_SESSION[__CLASS__]['connected']['name'];
            $values['user']['lastName'] = $_SESSION[__CLASS__]['connected']['lastName'];
            // We get the last connection date
            //$this->debugging(3);
            $user = $_SESSION[__CLASS__]['connected']['id'];

            $failures = $this->get_connection_failures($user);
            if(is_array($failures)){
                $count = $failures['number'];
                
                for($a = 0; $a < $count; $a++){
                    $dateAndTime = $this->linker->datePicker->dateAndTimeToLocal(
                        $failures['failure_'.$a.'_date']
                    );
                    $values['show']['failures'] = true;
                    $values['count']['failures'] = $failures['number'];
                    $values['failures'][] = array(
                        'date' => $dateAndTime['date'],
                        'time' => $dateAndTime['time'],
                        'site' => $failures['failure_'.$a.'_site'],
                        'ip' => $failures['failure_'.$a.'_ip']
                    );
                }
                if($this->clear_connection_failures($user)){
                    // There was an error...
                }
            }
            $lastConnection = $this->get_last_connection($user);
            if(is_array($lastConnection)){
                $dateAndTime = $this->linker->datePicker->dateAndTimeToLocal(
                    $lastConnection['date']
                );
                $values['user']['hasBeenConnected'] = true;

                $values['lastConnection'] = array(
                    'date'=>$dateAndTime['date'],
                    'time'=>$dateAndTime['time'],
                    'site'=>$lastConnection['site']
                );
            }
            $this->set_connection_status(SH_SITENAME, $user, true);
            return $this->render('connected',$values,false,$sendToHtml);
        }
        if($result = $this->formSubmitted('user_connection_step1', true)){
            if($result === true){
                $datas = $this->connection_step1($_POST['userName']);
                
                $userId = $datas['id'];
                $verification = $datas['verification'];
                if($userId){
                    $_SESSION[__CLASS__]['identification']['verifPhrase'] = $verification;
                    $_SESSION[__CLASS__]['identification']['userName'] = $_POST['userName'];
                    $values['verif']['phrase'] = $verification;
                    $values['user']['name'] = $_POST['userName'];
                    $values['link']['passPhrase_link'] = $this->translatePageToUri(
                        $this->shortClassName.'/passwordForgotten/'
                    );
                    return $this->render('connection_step2', $values,false,$sendToHtml);
                }
                $values['error']['message'] = $this->getI18n('loginNotFound');

            }elseif($result == sh_captcha::CAPTCHA_ERROR){
                $values['old']['userName'] = $_POST['userName'];
                $values['captcha']['error'] = 'true';
            }
        }else{
            $values['captcha']['error'] = '';
        }
        if($result = $this->formSubmitted('user_connection_step2')){
            $userId = $this->connection_step2($_POST['password']);

            if(!empty($userId)){
                $isAdmins = $this->linker->admin->isAdmin($userId,false);
                $isMaster = $this->linker->admin->isMaster($userId);
                // We don't check if the site is restricted if the user is an admin
                // or a master
                $userData = $this->getOneUserData($userId);
                if(!$this->needs_connection() || $isAdmin || $isMaster){
                    $connected = true;
                }else{
                    if(file_exists(SH_SITE_FOLDER.__CLASS__.'/allowed.php')){
                        include(SH_SITE_FOLDER.__CLASS__.'/allowed.php');
                        if(is_array($allowedUsers)){
                            if(in_array($userData['id'],$allowedUsers)){
                                $connected = true;
                            }
                        }
                    }
                    if(file_exists(SH_SITE_FOLDER.__CLASS__.'/inexistantButAllowedUsers.php')){
                        include(SH_SITE_FOLDER.__CLASS__.'/inexistantButAllowedUsers.php');
                        if(is_array($inexistantButAllowedUsers)){
                            if(in_array($userData['mail'],$inexistantButAllowedUsers)){
                                $connected = true;
                            }
                        }
                    }
                }
                if($connected === true){
                    // Connection was successfull
                    $this->connected = true;
                    $_SESSION[__CLASS__]['connected'] = $userData;
                    $_SESSION[__CLASS__]['connected']['userId'] = $userId;

                    if($isAdmin){
                        $this->linker->admin->connect();
                    }
                    if($isMaster){
                        $this->linker->admin->connect(sh_admin::CONNECT_AS_MASTER);
                    }
                    $this->linker->path->refresh();
                    return true;
                }else{
                    $values['error']['message'] = $this->getI18n(
                        self::WEBSAILORS_USERNAME_NOT_ALLOWED_TEXT
                    );
                }
            }else{
                $datas = $this->connection_step1($_SESSION[__CLASS__]['identification']['userName']);

                $this->set_connection_status(SH_SITENAME, $datas['id'], false);
                $values['error']['message'] = $this->getI18n('WRONG_DATA');
            }
        }
        $masterUrl = $this->linker->user->getMasterUrl(false);
        $values['createAccount']['link'] = $masterUrl.$this->linker->path->getLink('user/createAccount/');
        return $this->render('connection_step1', $values,false,$sendToHtml);
    }

    public function get($what,$booleanError = false){
        if(!isset($_SESSION[__CLASS__]['connected'])){
            if($returnFalseOnNotConnected){
                return false;
            }
            return self::NOT_CONNECTED;
        }
        if(isset($_SESSION[__CLASS__]['connected'][$what])){
            return $_SESSION[__CLASS__]['connected'][$what];
        }
        if($returnFalseOnNotConnected){
            return false;
        }
        return self::USER_DATA_NOT_FOUND;
    }

    /**
     * Disconnects the user, removing it's connection data from the session, and
     * redirects to the index page.
     */
    public function disconnect(){
        $this->linker->cache->disable();
        $this->connected = false;
        unset($_SESSION[__CLASS__]['connected']);
        $this->linker->admin->disconnect();
        header('location: /');
    }

    /**
     * Returns the uri from the given page
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translatePageToUri($page){
        $methods = array (
            'manage',
            'disconnect',
            'connect',
            'getOneUserId',
            'getUserData',
            'tryToConnect',
            'createAccount',
            'confirmAccountCreation',
            'createWebsite',
            'passwordForgotten_master',
            'passwordForgotten',
            'master_get_last_connection',
            'master_set_connection_status',
            'master_get_connection_failures',
            'master_clear_connection_failures',
            'connection_step1',
            'connection_step2',
            'connection_step1_master',
            'connection_step2_master'
        );
        list($class,$method,$id) = explode('/',$page);
        if(in_array($method,$methods)){
            return '/'.$this->shortClassName.'/'.$method.'.php';
        }
        return false;
    }

    /**
     * Returns the page from the given uri
     * @param string $page The page we want to translate to uri
     * @return string|bool The uri, or false
     */
    public function translateUriToPage($uri){
        if(preg_match('`/'.$this->shortClassName.'/([^/]+)\.php`',$uri,$matches)){
            $methods = array (
                'manage',
                'disconnect',
                'connect',
                'getOneUserId',
                'getUserData',
                'tryToConnect',
                'createAccount',
                'confirmAccountCreation',
                'createWebsite',
                'passwordForgotten_master',
                'passwordForgotten',
                'master_get_last_connection',
                'master_set_connection_status',
                'master_get_connection_failures',
                'master_clear_connection_failures',
                'connection_step1',
                'connection_step2',
                'connection_step1_master',
                'connection_step2_master'
            );
            if(in_array($matches[1],$methods)){
                return $this->shortClassName.'/'.$matches[1].'/';
            }
        }
        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////
    //                        USED ON MASTER SITE ONLY                            //
    ////////////////////////////////////////////////////////////////////////////////
    /**
     * USED ON MASTER SITE ONLY<br />
     * This method is mainly called by customers' websites to get some users ids.<br />
     * it gets the data from the database, and returns it as a string.
     * @param string|null $field
     * The field name to search in the database.<br />
     * If null (default behaviour), gets it from $_POST.
     * @param string|null $value
     * The field value to search in the database.<br />
     * If null (default behaviour), gets it from $_POST.
     * @return string
     * The id found as a string, or "0" if one was found.
     */
    public function getOneUserId($field = null,$value = null){
        $this->isMasterServer();
        if($field == null || $value == null){
            $echo = true;
            $field = stripslashes($_POST['field']);
            $value = stripslashes($_POST['value']);
        }
        list($element) = $this->db_execute(
            'getOneUserId',
            array(
                'field'=>$field,
                'value'=>$value
            ),$qry
        );
        if($element['id'] > 0){
            if($echo){
                echo $element['id'];
            }
            return $element['id'];
        }else{
            if($echo){
                echo '0';
            }
            return false;
        }
        return true;
    }

    /**
     * Gets a user's datas.
     * @return string
     * The return is a text in which:<br />
     * - all %3 lines are field names.<br />
     * - all %3 + 1 lines are their values.<br />
     * - all %3 + 2 lines are just separator (self::LINE_SEPARATOR).
     */
    public function getUserData($id = null){
        $this->isMasterServer();
        if($id == null){
            $id = $this->getFromAnyServer('user');
            $echo = true;
        }
        list($user) = $this->db_execute('getUserData',array('id'=>$id),$qry);

        if(!$echo){
            return $user;
        }
        foreach($user as $name=>$value){
            echo $name."\n";
            echo $value."\n";
            echo self::LINE_SEPARATOR."\n";
        }
        return true;
    }

    /**
     * Checks if that function is called by the master website.<br />
     * May send an error page if not, depending on $raiseErrorIfNotMaster
     * @param boolean $raiseErrorIfNotMaster
     * In case that this function is called from any other server than the master:<br />
     * - Raises a 404 error if set to true (default behaviour)<br />
     * - Returns false if set to false
     */
    public function isMasterServer($raiseErrorIfNotMaster = true){
        $site = str_replace('/','',SH_SITE);
        if($site == $this->getParam('master>site')){
            return true;
        }
        if($raiseErrorIfNotMaster){
            $this->linker->path->error(404);
        }
        return false;
    }

    /**
     * protected function checkIntegrity
     *
     */
    protected function checkIntegrity(){
        $site = str_replace('/','',SH_SITE);
        if($this->isMasterServer(false)){
            if(in_array($_SERVER['REMOTE_ADDR'],$this->getParam('master>allowedSites'))){
                return true;
            }
            echo self::SITE_NOT_ALLOWED_TEXT;
        }else{
            echo self::ERROR_USING_FORM_TEXT;
        }
        exit;
    }

    /**
     * public function tryToConnect
     *
     */
    public function tryToConnect(){
        $this->linker->cache->disable();
        $this->checkIntegrity();
        // We get the data
        $cryptedUserName = $this->getFromAnyServer('user');
        $cryptedPassword = $this->getFromAnyServer('password');
        $site = $this->getFromAnyServer('site');
        // We uncrypt it
        $userName = $this->linker->crypter->uncrypt($cryptedUserName, $site);
        $password = $this->preparePassword($this->linker->crypter->uncrypt($cryptedPassword, $site));
        // We verify if the account exists
        list($user) = $this->db_execute(
            'verify',
            array(
                'name'=>$userName,
                'password'=>$password
            )
        );
        if(isset($user['id'])){
            if($user['active'] == 1){
                echo $user['id'];
            }else{
                echo self::ACCOUNT_NOT_ACTIVATED_TEXT;
            }
        }else{
            // We verify if the account exists
            list($user) = $this->db_execute(
                'verifyByTemporaryPassword',
                array(
                    'name'=>$userName,
                    'temporaryPassword'=>$password
                ),
                $qry
            );
            if(isset($user['id'])){
                if($user['active'] == 1){
                    $this->db_execute('changePassword', array(
                        'newPassword'=>$password,
                        'id'=>$user['id']
                    ));
                    echo $user['id'];
                }else{
                    echo self::ACCOUNT_NOT_ACTIVATED_TEXT;
                }
            }else{
                echo self::WRONG_DATA_TEXT;
            }
        }
        return true;
    }

    /**
     * public function createAccount
     *
     */
    public function createAccount(){
        $this->linker->cache->disable();
        $this->isMasterServer();

        // Checks if the form has been submitted
        $formSubmitted = $this->formSubmitted('createAccountForm', true);
        if($formSubmitted === true){
            $error = false;

            // Name
            $name = trim(stripslashes($_POST['name']));
            if(strlen($name)<2){
                $error = true;
                $values['name']['error'] = 'error';
                $name = '';
            }

            // Last name
            $lastName = trim(stripslashes($_POST['lastName']));
            if(strlen($lastName)<2){
                $error = true;
                $values['lastName']['error'] = 'error';
                $lastName = '';
            }

            // Phone
            $phone = trim(stripslashes($_POST['phone']));
            $phone = str_replace(
                array('-','_',' ','.',"'",'"'),
                '',
                $phone
            );
            if(preg_match('`([^0-9+]+)`',$phone)){
                $error = true;
                $values['phone']['error'] = 'error';
                $phone = '';
            }

            // Email
            $mail = trim(stripslashes($_POST['mail']));
            $mailer = $this->linker->mailer->get();
            if(!$mailer->checkAddress($mail)){
                $error = true;
                $values['mail']['error'] = 'error';
                $mail = '';
            }else{
                // We check if it is not already used
                list($rep) = $this->db_execute(
                    'getOneUserId',
                    array(
                        'field'=>'mail',
                        'value'=>$mail
                    )
                );
                if($rep['id'] > 0){
                    $error = true;
                    $values['mail']['error'] = 'error';
                    $mail = '';
                    $values['message']['error'] .= $this->getI18n('mail_already_used');
                }
            }

            // Address
            $address = trim(stripslashes($_POST['address']));

            // Login
            $login = trim(stripslashes($_POST['login']));
            $login = $this->clearData($login);
            if(strlen($login)<5){
                $error = true;
                $values['login']['error'] = 'error';
                $login = '';
            }else{
                // We check if it is not already used
                list($rep) = $this->db_execute(
                    'getOneUserId',
                    array(
                        'field'=>'login',
                        'value'=>$login
                    )
                );
                if($rep['id'] > 0){
                    $error = true;
                    $values['login']['error'] = 'error';
                    $login = '';
                    $values['message']['error'] .= $this->getI18n('login_already_used');
                }
            }

            // Password
            $password = trim(stripslashes($_POST['password']));
            $passwordConfirm = trim(stripslashes($_POST['passwordConfirm']));
            if(strlen($password)<5 || $password != $passwordConfirm){
                $error = true;
                $values['password']['error'] = 'error';
                $password = '';
            }

            $verification = $_POST['verification'];

            // Checks if errors have occured
            if(!$error){
                //We set the auto-increment to the first value of today
                $this->db_execute(
                    'createAccount_setIncrement',
                    array('increment'=>date('ymd').'00001')
                );
                // Creates the user in the database
                $this->db_execute(
                    'createAccount',
                    array(
                        'name'=>$name,
                        'lastName'=>$lastName,
                        'phone'=>$phone,
                        'mail'=>$mail,
                        'login'=>$login,
                        'password'=>$this->preparePassword($password),
                        'address' => $address,
                        'verification' => $verification
                    )
                );

                // Prepares the confirmation on the server by adding the necessary file
                $link = $this->linker->path->getLink('user/confirmAccountCreation/');
                $key = MD5(__CLASS__.microtime());
                $link .= '?key='.$key;
                $this->linker->helper->writeInFile(SH_SITE_FOLDER.__CLASS__.'/'.$key.'.php', $mail);

                // Renders the content of the mail
                $values['confirmation']['link'] = str_replace('//','/',$this->masterUrl.$link);
                $values['user']['mail'] = $mail;
                $values['user']['login'] = $login;
                $content = $this->render('mail_newAccount', $values, false, false);

                // Preparation of the confirmation mail
                $mailer = $this->linker->mailer->get();


                $mailObject = $mailer->em_create();
                // Creating and sending the email itself
                $address = $user['mail'];

                $mails = explode("\n",$this->getParam('command_mail'));
                if(is_array($mails)){
                    foreach($mails as $oneMail){
                        $mailer->em_addBCC($mailObject,$oneMail);
                    }
                }

                $mailer->em_addSubject(
                    $mailObject,
                    $this->getI18n('mail_confirmation_title')
                );
                $mailer->em_addContent($mailObject,$content);

                if($mailer->em_send($mailObject,array(array($mail)))){
                    $this->render('confirmationSent',$values);
                    return true;
                }
                // The mail was not sent (why???) so we send an error message
                $values['message']['error'] .= $this->getI18n('error_sending_mail');
            }
        }elseif($formSubmitted == sh_captcha::CAPTCHA_ERROR){
            echo 'ERREUR DANS LA CAPTCHA!!!';
        }

        if(empty($mail) && isset($_GET['mail'])){
            $mail = $_GET['mail'];
        }

        // Prepares the old entries to pre-fill the form
        $values['old'] = array(
            'name'=>$name,
            'lastName'=>$lastName,
            'phone'=>$phone,
            'mail'=>$mail,
            'login'=>$login,
            'password'=>$password,
            'address'=>$address,
            'verification'=>$verification
        );
        // Renders the form
        $this->render('createAccount',$values);
        return true;
    }

    /**
     * public function confirmAccountCreation
     *
     */
    public function confirmAccountCreation(){
        $this->linker->cache->disable();
        $this->isMasterServer();
        $key = stripslashes($_GET['key']);
        if(file_exists(SH_SITE_FOLDER.__CLASS__.'/'.$key.'.php')){
            $mail = file_get_contents(SH_SITE_FOLDER.__CLASS__.'/'.$key.'.php');
            $this->db_execute('activateAccount',array('mail'=>$mail));
            $id = $this->getOneUserId('mail',$mail);
            if($id){
                $values['user'] = $this->getUserData($id);
                $this->render('accountCreationConfirmed', $values);
                unlink(SH_SITE_FOLDER.__CLASS__.'/'.$key.'.php');
                return true;
            }
        }
        $this->render('accountCreationAborted', $values);
        return true;
    }

    /**
     * Creates a new Website
     */
    public function createWebsite(){
        $this->isMasterServer();
        if($this->formSubmitted('createWebsite')){

            if(isset($_POST['login'])){
                $login = trim(stripslashes($_POST['login']));
                if(!preg_match('`^[a-zA-Z0-9-_]{5,}$`',$login)){
                    echo 'L\'identifiant n\'est pas bon!!!<br />';
                }
            }
            //domain
            //siteName
            echo 'The form was submitted<br />';
        }
        $this->render('createWebsite', array());
    }

    ////////////////////////////////////////////////////////////////////////////////
    //                        USED ON BOTH                                        //
    ////////////////////////////////////////////////////////////////////////////////
    public function construct(){
        $this->getConnection();
        $this->allowed = $this->getParam('allowed');

        if(SH_GLOBAL_DEBUG === true){
            $this->masterUrl = $this->getParam('master>devUrl');
        }else{
            $this->masterUrl = $this->getParam('master>prodUrl');
        }
        $this->masterSite = $this->getParam('master>site');
        return true;
    }

    /**
     * protected function preparePassword
     *
     */
    protected function preparePassword($original){
        return MD5('ws_user'.$original);
    }

    /**
     * protected function getFromAnyServer
     *
     */
    protected function getFromAnyServer($argName){
        return urldecode(stripslashes($_POST[$argName]));
    }

    /**
     * protected function clearData
     *
     */
    protected function clearData($dirty){
        $dirty = stripslashes($dirty);
        $remove =  array('à','á','â','ã','ä','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ');
        $replace = array('a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y');
        $dirty =  str_replace($remove,$replace,$dirty);
        $dirty = preg_replace(
            '`([^a-zA-Z0-9_]+)`',
            '_',
            $dirty
        );
        return $dirty;
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }

}
