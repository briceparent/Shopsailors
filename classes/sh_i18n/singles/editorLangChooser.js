function chooseLang(input, value){
    $(input + '_select').value = value;
    $$('.' + input).each(
        function(element){
            if(element.id != value && element.visible()){
                element.hide();
            }else if(element.id == value){
                element.show();
            }
        }
    );
}