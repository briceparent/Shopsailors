function colorPicker_changeColor(element,color){
    $(element + '_preview').style.backgroundColor = "#" + color;
    $(element).value = color;
}
function colorPicker_directChangeColor(element,color){
    $(element + '_preview').style.backgroundColor = color;
}
function colorPicker_button_showPopup(element,color){
    window.open(
        '/sh_colorPicker/singles/color.php?element=' + element + '&color=' + color,
        'colorPicker',
        config='height=340,width=460,toolbar=no,menubar=no'
    );
}
