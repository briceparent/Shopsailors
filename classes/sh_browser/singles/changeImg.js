var imgEdited= new Array();
var imgEditedId= new Array();

function changeImg(img,id,folder){
    cpt = imgEdited.length;
    imgEdited[cpt] = img;
    imgEditedId[cpt] = $(id);
    window.open(
        '/browser/' + folder + '/images/replaceImg/' + cpt + '/show.php?cpt=',
        'sh_browser',
        config='height=410, width=750, toolbar=no, menubar=no'
    );
}

function replaceImg(imgFile,cpt){
    /* alert(imgEditedId[cpt].value + ' -> ' + imgFile); */
    imgEdited[cpt].src = imgFile;
    imgEditedId[cpt].value = imgFile;
}
