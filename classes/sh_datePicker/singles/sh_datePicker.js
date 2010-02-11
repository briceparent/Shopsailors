var datePickersParams = new Array();
var datePickerShown = new Array();

function dp_prepare(id,date,start,end){
    datePickersParams[id + '_start'] = start;
    datePickersParams[id + '_end'] = end;
}

function dp_showDatePicker(id,method){
    var div = $('div_' + id);
    if(datePickerShown[id] == true){
        datePickerShown[id] = false;
        Effect.BlindUp('div_' + id);
        return true;
    }
    datePickerShown[id] = true;
    Effect.Appear(div);
    var input = $(id);
    var btn = $('btn_' + id);
    var x = input.offsetLeft;
    var y = input.offsetTop;
    y += input.offsetHeight;
    var parent = input;
    while (parent.offsetParent) {
        parent = parent.offsetParent;
        x += parent.offsetLeft;
        y += parent.offsetTop;
    }
    div.style.position = "absolute";
    div.style.left = x + "px";
    div.style.top = y + "px";

    var minWidth = btn.offsetLeft + btn.offsetWidth - input.offsetLeft - 2;
    var actualWidth = div.offsetWidth;
    if(actualWidth < minWidth){
        div.style.width = minWidth + "px";
    }
    dp_drawDatePicker(id,method);
    return true;
}

function dp_showSelectMonth(id){
    $(id+'_month').hide();
    $(id+'_selMonth').show();
}

function dp_selectMonth(id){
    $(id+'_month').innerHTML = $(id+'_selMonth').options[$(id+'_selMonth').selectedIndex].innerHTML;
    $(id+'_month').show();
    $(id+'_selMonth').hide();
    dp_drawDatePicker(id,'separated');
}

function dp_showSelectYear(id){
    $(id+'_year').hide();
    $(id+'_selYear').show();
}

function dp_selectYear(id){
    $(id+'_year').innerHTML = $(id+'_selYear').options[$(id+'_selYear').selectedIndex].innerHTML;
    $(id+'_year').show();
    $(id+'_selYear').hide();
    dp_drawDatePicker(id,'separated');
}

function dp_showSelectMonthAndYear(id){
    $(id+'_monthAndYear').hide();
    $(id+'_selMonthAndYear').show();
}

function dp_selectMonthAndYear(id){
    $(id+'_monthAndYear').innerHTML = $(id+'_selMonthAndYear').options[$(id+'_selMonthAndYear').selectedIndex].innerHTML;
    $(id+'_monthAndYear').show();
    $(id+'_selMonthAndYear').hide();
    dp_drawDatePicker(id,'grouped');
}

function dp_showPreviousMonth(id){
    if($(id+'_selMonth').value == 1){
        $(id+'_selMonth').value = 12;
        $(id+'_selYear').value -= 1;
        dp_selectYear(id);
    }else{
        $(id+'_selMonth').value -= 1;
    }
    dp_selectMonth(id);
    return dp_drawDatePicker(id);
}

function dp_showNextMonth(id){
    if($(id+'_selMonth').value == 12){
        $(id+'_selMonth').value = 1;
        $(id+'_selYear').value += 1;
        dp_selectYear(id);
    }else{
        $(id+'_selMonth').value += 1;
    }
    dp_selectMonth(id);
}

function dp_setLoading(id,status){
    if(status){
        $(id + '_content').style.backgroundImage = 'url(/images/shared/icons/bank1/sh_loading.gif)';
        $(id + '_content').style.backgroundRepeat = 'no-repeat';
        $(id + '_content').style.backgroundPosition = 'center 50%';
    }else{
        $(id + '_content').style.backgroundImage = 'none';
    }
}

function dp_drawDatePicker(id,method){
    dp_setLoading(id,true);
    var getParameters = '';
    if(method == 'separated'){
        var month = $(id+'_selMonth').value;
        var year = $(id+'_selYear').value;
        getParameters = 'picker=' + id + '&year=' + year + '&month=' + month;
    }else{
        var monthAndYear = $(id+'_selMonthAndYear').value;
        getParameters = 'picker=' + id + '&monthAndYear=' + monthAndYear;
    }
    uri = "/datePicker/showMonth.php";
    new Ajax.Updater(
        id + '_content',
        uri,
        {
            parameters : getParameters,
            method : "get",
            evalScripts : true,
            onSuccess:function(){
                dp_setLoading(id,false);
            }
        }
    );
}

function dp_select(id,newDate,year,month,day){
    $(id).value = newDate;
    $(id + '_real').value = year+'-'+month+'-'+day;
    Effect.BlindUp('div_' + id);
    datePickerShown[id] = false;
}
