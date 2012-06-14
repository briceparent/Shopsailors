var cf = new Array();
var startedDiapos = new Array();
function diapoButtonsAppear(contentRoot){
    $(contentRoot + '-previous').show();
    $(contentRoot + '-play').show();
    $(contentRoot + '-next').show();
    $(contentRoot + '-background').show();
}
function diapoButtonsFade(contentRoot){
    $(contentRoot + '-previous').hide();
    $(contentRoot + '-play').hide();
    $(contentRoot + '-next').hide();
    $(contentRoot + '-background').hide();
}
function diapoStartStop(diapo){
    if(startedDiapos[diapo]){
        startedDiapos[diapo] = false;
        cf[diapo].stop();
    }else{
        startedDiapos[diapo] = true;
        cf[diapo].start();
    }

}
function diapoNext(diapo){
    cf[diapo].next();
}
function diapoPrevious(diapo){
    cf[diapo].previous();
}