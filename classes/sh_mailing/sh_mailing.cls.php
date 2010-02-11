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
 * Class that manages mailings.
 */
class sh_mailing extends sh_model{
    public $nbMails=2;

    public function __construct(){
        parent::__construct();
        include(dirname(__FILE__).'/class.mail5.php');
   }

    protected function getFolder(){
        return dirname(__FILE__).'/mail/';
    }

    public function doTask(){
        $folder=$this->getFolder();
        $listFile=$folder.'list.php';
        //Checks if there is any content to send, and gets it if needed
        if(!file_exists($listFile) || filesize($listFile)==0)
          return true;
        $file=file($listFile);
        //Gets the task and writes the rest to the next task
        list($id,$mails)=explode('|',array_shift($file));
        if(count($file) == 0){
            $fileContent = '';
        }else{
            $fileContent = implode('',$file);
        }

        $f=fopen($listFile,'w+');
        fwrite($f,$fileContent);
        fclose($f);

        //Gets the mail to send and complete it with the BCC input
        include($folder.'/'.$id.'.php');
        $mails=str_replace("\n",'',$mails);
        $myMail['BCC']=explode(',',$mails);
        //Sets automatic actions to do
        $actions=array('To'=>1,'BCC'=>3,'From'=>1,'ReplyTo'=>1,'HTML'=>2,'HTMLfile'=>4,'PlainText'=>2,'File'=>3,'Subject'=>2);
        //Makes the mail
        $mail = new mailMain;
        foreach($actions as $action=>$type){
            if(isset($myMail[$action])){
                $addAction='add'.$action;
                if($type==1){
                  $mail->model->$addAction($myMail[$action][0],$myMail[$action][1]);
                }elseif($type==2){
                  $mail->model->$addAction($myMail[$action]);
                }elseif($type==3){
                    foreach($myMail[$action] as $element){
                      $mail->model->$addAction($element);
                    }
                }elseif($type==4){
                    foreach($myMail[$action] as $element){
                      $mail->model->$addAction($element[0],$element[1],$element[2]);
                    }
                }
            }
        }
        $mail -> sender -> set_mode = 'php';
        //And send it
        if ( $mail -> sender -> send() ) {
          return true;
        } else {
          return false;
        }
    }

    public function addTask($args){
        //clears entries and verify if the required inputs are filled
        array_walk_recursive  ( $args ,'trim');
        if($args['To']=='' || $args['From']=='')
          return false;

        //Checks and eventually makes the working folder
        $folder=$this->getFolder($this);
        if(!is_dir($folder)){
          mkdir($folder);
        }

        //Prepares and appends the task(s) to the file
        $uid=md5('ws'.microtime());
        $fileName=$folder.$uid.'.php';
        while(count($args['BCC'])>0){
            $parts[]=implode(',',array_slice($args['BCC'],0,$this->nbMails));
            $args['BCC']=array_slice($args['BCC'],$this->nbMails);
        }

        $f=fopen($folder.'list.php','a');
        foreach($parts as $element)
          fwrite($f,$uid.'|'.$element."\n");
        fclose($f);

        //Saves the mail to send
        $f=fopen($fileName,'w+');
        fwrite($f,'<?php'."\n".'$myMail = '.var_export($args,true)."\n".'?>');
        fclose($f);
        return true;
    }

    public function addRecipient($name,$email,$verif,$category = 1){
        $this->getDB($this);
        list($present) = $this->db->select('`id`','###newsletter_recipients',array('name'=>$name,'email'=>$email,'verif'=>1));

        if(isset($present['id']))
            return 'already_present';

        $filter =  new mailFieldFilter();
        if(!$filter->checkAddress($email))
            return 'wrong_address';

        if(strlen($name)<2){
            return 'wrong_name';
        }

        return $this->db->insert(array('name'=>$name,'email'=>$email,'category'=>$category, 'verif'=>$verif),'###newsletter_recipients');
    }

    public function checkMail($email){
        $filter =  new mailFieldFilter();
        if(!$filter->checkAddress($email))
            return 'wrong_address';
        return true;

    }

    public function __tostring(){
      return get_class();
    }
}

