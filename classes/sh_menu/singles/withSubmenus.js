function show_submenus(submenu,menu){
    var aeiou = $("AEIOUY");
    aeiou.innerHTML = $(submenu).innerHTML;
    aeiou.style.display = 'block';
    aeiou.style.top = getRealOffsetTop(menu)+'px';
    aeiou.style.margin = '0';
    aeiou.style.padding = '0';




    $(submenu).style.display='block';
    /*alert('TOP : '+$(submenu).offsetTop+' - '+menu.offsetBottom);*/
    $(submenu).style.top=(menu.offsetTop + menu.offsetHeight)+"px";
    $(submenu).style.left=menu.offsetLeft+"px";
}
function hide_submenus(submenu){
    var aeiou = $("AEIOUY");
    /*aeiou.innerHTML = submenu.innerHTML;*/
    aeiou.style.display = '';
}



function getRealOffsetLeft(ineditedImage){
    var oeditedImage = ineditedImage;
    var iVal = 0;
    while (oeditedImage && oeditedImage.tagName != "BODY") {
        iVal += oeditedImage.offsetLeft;
        oeditedImage = oeditedImage.offsetParent;
    }
    return iVal;
}


function getRealOffsetTop(ineditedImage){
    var oeditedImage = ineditedImage;
    var iVal = 0;
    while (oeditedImage && oeditedImage.tagName != "BODY") {
        iVal += oeditedImage.offsetTop;
        oeditedImage = oeditedImage.offsetParent;
    }
    return iVal;
}