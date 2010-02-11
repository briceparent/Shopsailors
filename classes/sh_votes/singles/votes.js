    function getOriginalVote(type,id){
        updateUserVote (type,id,'inPlaceVote_'+type+'_'+id);
        updatePublicVote (type,id);
        updatePublicVoteCount (type,id);
    }

    function updatePublicVote(type,id){
        uri = '/votes/updatePublicVote.php';
        post = 'type='+type+'&id='+id;
        element = 'public_vote_'+type+'_'+id;
        new Ajax.Updater(element,uri,{parameters:post,method:'post'});
    }

    function updatePublicVoteCount(type,id){
        uri = '/votes/updatePublicVoteCount.php';
        post = 'type='+type+'&id='+id;
        element = 'public_nbVotes_'+type+'_'+id;
        new Ajax.Updater(element,uri,{ parameters : post , method : 'post'  } );
    }
    
    function updateUserVote(type,id,uid){
        uri = "/votes/updateUserVote.php";
        post = "type="+type+"&id="+id;
        new Ajax.Updater(uid,uri,{parameters:post,method:"post"});
    }

    function updateMasterNote(id,searcher,uid){
        uri = "/votes/updateMasterNote.php";
        post = "id="+id+"&searcher="+searcher;
        new Ajax.Updater(uid,uri,{parameters:post,method:"post"});
    }