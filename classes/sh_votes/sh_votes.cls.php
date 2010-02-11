<?php
/**
 * @author Brice PARENT for Shopsailors
 * @copyright Shopsailors 2009
 * @license http://www.cecill.info
 * @version See version in the params/global.params.php file.
 * @package Shopsailors Core Classes
 */
if(!defined('SH_MARKER')) {header('location: directCallForbidden.php');}

/**
 * class sh_book
 *
 */
class sh_votes extends sh_model {
    /**
     * public function __construct
     * Initiate the object
     */
    function __construct() {
        parent::__construct($this);
    }

    /**
     * public function get
     *
     */
    public function get($type,$id){
        $this->addScript();
        $ret['publicVote'] = '<span id="public_vote_'.$type.'_'.$id.'"></span>';
        $ret['publicNbVotes'] = '<span id="public_nbVotes_'.$type.'_'.$id.'"></span>';
        $ret['userVote'] = '<span id="inPlaceVote_'.$type.'_'.$id.'" class="falseLink"></span>';
        $ret['script'] = '
<script type="text/javascript">
    var availableNotes = new Array("Voter","0/10","1/10","2/10","3/10","4/10","5/10","6/10","7/10","8/10","9/10","10/10");
    getOriginalVote("'.$type.'","'.$id.'");
    new Ajax.InPlaceCollectionEditor( "inPlaceVote_'.$type.'_'.$id.'",
                                        "'.helper('path')->getLink('votes/changeUserVote/').'",
                                        { collection: availableNotes,
                                            cancelText: " Annuler",
                                            clickToEditText: "Cliquer pour voter",
                                            callback: function(form, value) {
                                                 return "type='.$type.'&id='.$id.'&value=" + value;
                                            },
                                            onComplete: function(t) {
                                                updatePublicVote("'.$type.'","'.$id.'");
                                                updatePublicVoteCount("'.$type.'","'.$id.'");
                                            }
                                        } );
</script>';
        return $ret;
    }

    /**
     * public function createMasterNote
     *
     */
    public function createMasterNote($id,$searcher){
        $this->addScript();
        $uid = 'note_'.MD5(microtime());
        $ret = '<span id="'.$uid.'">This is a test</span>';
        $ret .= '<script type="text/javascript">
updateMasterNote("'.$id.'","'.$searcher.'","'.$uid.'");
</script>';
        return $ret;
    }

    /**
     * public function updatePublicVote
     */
    public function updatePublicVote(){
        $id = $_POST['id'];
        $type = $_POST['type'];
        $this->db->replacements = array('id' => $id,
                                        'type' => $type);
        $ret = $this->db->execute('getPublicVote');
        echo $this->convertToStars($ret[0]['vote']);
        exit;
    }

    /**
     * public function convertToStars
     */
    public function convertToStars($note){
        if($note == ''){
            $note = 5;
        }
        $ret = '<img src="/images/icons/stars_'.$note.'.png" class="Stars" title="Note : '.$note.'" alt="Note : '.$note.'"/>';
        return $ret;
    }

    /**
     * public function updateVoteCount
     */
    public function updatePublicVoteCount(){
        $id = $_POST['id'];
        $type = $_POST['type'];
        $this->db->replacements = array('id' => $id,
                                        'type' => $type);
        $ret = $this->db->execute('getVoteCount');
        $ret = $ret[0]['count'];
        if($ret > 1){
            echo $ret . ' votes';
        }elseif($ret == 0){
            echo 'Aucun vote';
        }else{
            echo $ret . ' vote';
        }
        exit(1);
    }

    /**
    * public function updateMasterNote
     */
    public function updateMasterNote(){
        $id = $_POST['id'];
        $searcher = $_POST['searcher'];
        $this->db->replacements = array('searchType' => $searcher);
        list($rep) = $this->db->execute('getDataFromReader');
        $this->db->replacements = array('table' => $rep['table'],
                                        'idKey' => $rep['idKey'],
                                        'searchedKey' => $rep['searchedKey'],
                                        'value' => $id);
        $vote = $this->db->execute('getMasterVote');
        echo $this->convertToStars($vote[0]['note']);
        exit;
    }

    /**
     * public function showVote
     */
    public function updateUserVote(){
        $id = $_POST['id'];
        $type = $_POST['type'];
        $this->db->replacements = array('id' => $id,
                                        'type' => $type,
                                        'ip' =>$_SERVER['REMOTE_ADDR']);
        $vote = $this->db->execute('getMyVote');
        if(!isset($rep[0]['vote']) || $rep[0]['vote'] == -1){
            echo 'Voter';
            exit;
        }
        echo $vote[0]['vote'];
        exit;
    }


    /**
     * public function addVote
     */
    public function changeUserVote(){
        $id = $_POST['id'];
        $type = $_POST['type'];
        $vote = $_POST['value'];
        if($vote == 'Voter'){
            $realVote = '-1';
        }else{
            list($realVote) = explode('/',$vote);
        }
        $this->db->replacements = array('id' => $id,
                                        'type' => $type,
                                        'ip' =>$_SERVER['REMOTE_ADDR'],
                                        'vote' =>$realVote);

        $rep = $this->db->execute('getMyVote');
        if(isset($rep[0]['vote'])){
            $this->db->execute('changeVote');
        }else{
            $this->db->execute('addVote');
        }
        $this->db->execute('insertVotesCounts');
        $this->db->execute('updateVotesCounts');

        echo $vote;
        exit;
    }

    /**
     * public function addScript
     *
     */
    public function addScript(){
        helper('html')->addScript('sh_votes/singles/votes.js');
    }

    /**
     * public function __tostring
     * Returns the name of the class
     */
    public function __tostring(){
        return get_class();
    }
}