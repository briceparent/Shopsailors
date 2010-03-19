/*****************************/
// Parameters that allow to use this on a thumbnail
var crop_maxWidth = 375;
var crop_maxHeight = 375;
// End of the parameters
/*****************************/


/*****************************/
// Don't change these elements
var crop_factor = 1;
var crop_width = 0;var crop_height = 0;
var crop_x1 = 0;var crop_y1 = 0;var crop_x2 = 0;var crop_y2 = 0;
var crop_minX = 0;var crop_maxX = 0;var crop_minY = 0;var crop_maxY = 0;
var crop_startX = 0;var crop_startY = 0;
var crop_isDrawing = false;
var crop_isMoving = false;
var crop_isDrawn = false;
var crop_selectedCorner = '';
var crop_decayX = 0;var crop_decayY = 0;
var crop_forcedFactor = 0;var crop_tempForcedFactor = 0;

/*****************************/
/*
 * initialization of the events
 **/
function crop_init(){
    $("editedImage").onmousedown=crop_startRedimMask;
    $("editedImage").onmousemove=crop_redimMaskSelect;

    $("mask").onmousedown=crop_startMoveMask;
    $("mask").onmousemove=crop_moveMask;
    $("mask").onmouseup=crop_endRedimMask;

    $("tl").onmousedown=crop_startRedimMaskTL;
    $("tl").onmousemove=crop_redimMaskSelect;
    $("tl").onmouseup=crop_endRedimMask;

    $("tr").onmousedown=crop_startRedimMaskTR;
    $("tr").onmousemove=crop_redimMaskSelect;
    $("tr").onmouseup=crop_endRedimMask;

    $("bl").onmousedown=crop_startRedimMaskBL;
    $("bl").onmousemove=crop_redimMaskSelect;
    $("bl").onmouseup=crop_endRedimMask;

    $("br").onmousedown=crop_startRedimMaskBR;
    $("br").onmousemove=crop_redimMaskSelect;
    $("br").onmouseup=crop_endRedimMask;
}
function crop_startMoveMask(evt){
    if(crop_isDrawing || !crop_isDrawn){
        return false;
    }
    crop_isMoving = true;
    crop_decayX = evt.clientX-crop_x1;
    crop_decayY = evt.clientY-crop_y1;
    return false;
}
function crop_moveMask(evt){
    if(!crop_isMoving){
        crop_redimMaskSelect(evt);
        return false;
    }

    var crop_newX;
    crop_newX = evt.clientX - crop_decayX;
    
    var crop_maskWidth = crop_x2 - crop_x1;
    if(crop_newX >= crop_minX && crop_newX + crop_maskWidth <= crop_maxX){
        $("mask").style.left = crop_newX;
    }else if(crop_newX >= crop_minX){
        $("mask").style.left = crop_maxX - crop_maskWidth;
    }else{
        $("mask").style.left = crop_minX;
    }

    var crop_newY=0;
    crop_newY = evt.clientY - crop_decayY;
    var maskHeight = crop_y2 - crop_y1;
    if(crop_newY >= crop_minY && crop_newY + maskHeight <= crop_maxY){
        $("mask").style.top = crop_newY;
    }else if(crop_newY >= crop_minY){
        $("mask").style.top = crop_maxY - maskHeight;
    }else{
        $("mask").style.top = crop_minY;
    }

    crop_showRedimDatas(evt);
    return false;
}
function crop_redimMaskSelect(evt){
    if(!crop_isDrawing){
        return false;
    }
    return crop_redimMask(evt);
}
function crop_startRedimMaskTL(evt){
    crop_selectedCorner = 'TL';
    return crop_startRedimMaskFromCorners(evt);
}
function crop_startRedimMaskTR(evt){
    crop_selectedCorner = 'TR';
    return crop_startRedimMaskFromCorners(evt);
}
function crop_startRedimMaskBL(evt){
    crop_selectedCorner = 'BL';
    return crop_startRedimMaskFromCorners(evt);
}
function crop_startRedimMaskBR(evt){
    crop_selectedCorner = 'BR';
    return crop_startRedimMaskFromCorners(evt);
}
function crop_startRedimMaskFromCorners(evt){
    crop_isDrawing = true;
    crop_startX = evt.clientX;
    crop_startY = evt.clientY;
    return false;
}

function crop_startRedimMask(evt){
    $('mask').style.display="";
    crop_isDrawing = true;
    var crop_newX=0;
    var crop_newY=0;
    crop_newX = evt.clientX;
    crop_newY = evt.clientY;
    // placement du calque
    $("mask").style.top = crop_newY+"px";
    $("mask").style.left = crop_newX+"px";
    $("mask").style.width = "0px";
    $("mask").style.height = "0px";
    //position = Array(newX,newY,newX,newY);
    crop_startX = crop_newX;
    crop_startY = crop_newY;
    crop_x1 = crop_x2 = crop_newX;
    crop_y1 = crop_y2 = crop_newY;
    return false;
}

function crop_redimMask(evt){
    var crop_newX=0;
    var crop_newY=0;
    var crop_width = 0;
    var crop_height = 0;

    crop_newX = evt.clientX;
    crop_newY = evt.clientY;
    if(crop_isDrawn){
        if(crop_selectedCorner == 'TL'){
            crop_startX = crop_x2;
            crop_startY = crop_y2;
        }else if(crop_selectedCorner == 'TR'){
            crop_startX = crop_x1;
            crop_startY = crop_y2;
        }else if(crop_selectedCorner == 'BL'){
            crop_startX = crop_x2;
            crop_startY = crop_y1;
        }else if(crop_selectedCorner == 'BR'){
            crop_startX = crop_x1;
            crop_startY = crop_y1;
        }
    }
    // Defining new width and height
    if(crop_newX < crop_startX){
        // From right to left
        crop_width = crop_startX - crop_newX;
    }else{
        // From left to right
        crop_width = crop_newX - crop_startX;
    }
    if(crop_newY < crop_startY){
        // From bottom to top
        crop_height = crop_startY - crop_newY;
    }else{
        // From top to bottom
        crop_height = crop_newY - crop_startY;
    }

    // We check if we have to restrain the values
    if(crop_forcedFactor > 0){
        if((crop_width / crop_height) > crop_forcedFactor){
            crop_height = crop_width / crop_forcedFactor;
        }else{
            crop_width = crop_forcedFactor * crop_height;
        }
    }
    
    if(crop_newX < crop_startX){
        // From right to left
        $("mask").style.left = crop_newX+"px";
    }else{
        // From left to right
        $("mask").style.left = crop_startX+"px";
    }
    if(crop_newY < crop_startY){
        // From bottom to top
        $("mask").style.top = crop_newY+"px";
    }else{
        // From top to bottom
        $("mask").style.top = crop_startY+"px";
    }
    $("mask").style.width = crop_width+"px";
    $("mask").style.height = crop_height+"px";
    crop_showRedimDatas(evt);
    return false;
}

function crop_showRedimDatas(evt){
    /*var editedImage = $("editedImage");*/
    var mask = $("mask");
    // Positions on the screen
    crop_x1 = parseFloat(mask.style.left);
    crop_x2 = parseFloat(mask.style.width) + crop_x1;
    crop_y1 = parseFloat(mask.style.top);
    crop_y2 = parseFloat(mask.style.height) + crop_y1;
    // Positions on the image
    var crop_imageX1 = crop_x1 - crop_minX;
    var crop_imageX2 = crop_x2 - crop_minX;
    var crop_imageY1 = crop_y1 - crop_minY;
    var crop_imageY2 = crop_y2 - crop_minY;
    // Real positions (using the crop_factor)
    var crop_realX1 = Math.round(crop_imageX1 * crop_factor);
    var crop_realX2 = Math.round(crop_imageX2 * crop_factor);
    var crop_realY1 = Math.round(crop_imageY1 * crop_factor);
    var crop_realY2 = Math.round(crop_imageY2 * crop_factor);
    // Updating form fields
    $("startX").value = crop_realX1;
    $("stopX").value = crop_realX2;
    $("startY").value = crop_realY1;
    $("stopY").value = crop_realY2;
    // Updating shown datas
    $("textWidth").innerHTML = crop_realX2 - crop_realX1;
    $("textHeight").innerHTML = crop_realY2 - crop_realY1;
    $("shownDatas").style.display = 'block';

    if(crop_forcedFactorX > (crop_realX2 - crop_realX1)){
        $('crop_caution').style.display = '';
    }else{
        $('crop_caution').style.display = 'none';
    }
    
}

function crop_endRedimMask(evt){
    crop_isDrawing = false;
    crop_isMoving = false;
    crop_isDrawn = true;
    crop_showRedimDatas(evt);
}

function crop_GetRealOffsetLeft(ineditedImage){
    var oeditedImage = ineditedImage;
    var iVal = 0;
    while (oeditedImage && oeditedImage.tagName != "BODY") {
        iVal += oeditedImage.offsetLeft;
        oeditedImage = oeditedImage.offsetParent;
    }
    return iVal;
}


function crop_GetRealOffsetTop(ineditedImage){
    var oeditedImage = ineditedImage;
    var iVal = 0;
    while (oeditedImage && oeditedImage.tagName != "BODY") {
        iVal += oeditedImage.offsetTop;
        oeditedImage = oeditedImage.offsetParent;
    }
    return iVal;
}
function crop_prepareDrawing(img){
    crop_init();
    crop_width = img.width;
    crop_height = img.height;

    if(crop_width < crop_maxWidth && crop_height < crop_maxHeight){
        crop_factor = 1;
    }else{
        var crop_xFactor = 1;
        var crop_yFactor = 1;
        if(crop_maxWidth > 0 && (crop_width > crop_maxWidth)){
            crop_xFactor = crop_width / crop_maxWidth;
        }
        if(crop_maxHeight > 0 && crop_height > crop_maxHeight){
            crop_yFactor = crop_height / crop_maxHeight;
        }
        if(crop_xFactor > crop_yFactor){
            img.width = crop_maxWidth;
            crop_factor = crop_xFactor;
        }else{
            img.height = crop_maxHeight;
            crop_factor = crop_yFactor;
        }
        crop_width = crop_width / crop_factor;
        crop_height = crop_height / crop_factor;
    }

    // Now that the image is loaded, we also have to set the minimums
    var crop_offsetLeft = crop_GetRealOffsetLeft(editedImage);
    var crop_offsetTop = crop_GetRealOffsetTop(editedImage);
    crop_minX = crop_offsetLeft;
    crop_maxX = crop_offsetLeft + crop_width;
    crop_minY = crop_offsetTop;
    crop_maxY = crop_offsetTop + crop_height;
}

function crop_forceFactor(value){
    if(value){
        crop_forcedFactor = crop_tempForcedFactor;
    }else{
        crop_tempForcedFactor = crop_forcedFactor;
        crop_forcedFactor = 0;
    }
}