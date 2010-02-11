var showHideOk = true;
function dragAdminBox(posX,posY){
    /* Restores the position, and set the admin box as draggable */
    // new Effect.Move('admin_box', { x: posX, y: posY, mode: 'absolute' });
    $('admin_box').style.left = posX + "px";
    
    new Draggable('admin_box',{constraint: 'horizontal',onStart : noShowHide, onEnd : saveAdminBoxPos});
}

function saveAdminBoxPos(){
    /* Saves the admin box's position, and send it to the server. Should be stored in the session */
    posX = $('admin_box').offsetLeft;
    posY = $('admin_box').offsetTop;
    new Ajax.Request(
        '/sh_admin/singles/saveAdminBoxPos.php',
        {
            method:'get' ,
            parameters: {x: posX, y: posY}
        }
    );
    showHideOk = true;
    }
function noShowHide(){
    showHideOk = false;
}
function adminBoxShowHide(){
    if(showHideOk == true){
        Effect.toggle('admin_box_hideable', 'blind', { duration: 0.6 });
    }
}
function adminBoxCategoryShowHide(category){
    Effect.toggle(category, 'blind', { duration: 0.6 });
}
