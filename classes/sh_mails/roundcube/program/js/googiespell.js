var SPELL_CUR_LANG=null;
var GOOGIE_DEFAULT_LANG="en";
function GoogieSpell(_1,_2){
var _3=this;
this.array_keys=function(_4){
var _5=[];
for(var _6 in _4){
_5.push([_6]);
}
return _5;
};
var _7=getCookie("language");
GOOGIE_CUR_LANG=_7!=null?_7:GOOGIE_DEFAULT_LANG;
this.img_dir=_1;
this.server_url=_2;
this.org_lang_to_word={"da":"Dansk","de":"Deutsch","en":"English","es":"Espa&#241;ol","fr":"Fran&#231;ais","it":"Italiano","nl":"Nederlands","pl":"Polski","pt":"Portugu&#234;s","fi":"Suomi","sv":"Svenska"};
this.lang_to_word=this.org_lang_to_word;
this.langlist_codes=this.array_keys(this.lang_to_word);
this.show_change_lang_pic=true;
this.change_lang_pic_placement="right";
this.report_state_change=true;
this.ta_scroll_top=0;
this.el_scroll_top=0;
this.lang_chck_spell="Check spelling";
this.lang_revert="Revert to";
this.lang_close="Close";
this.lang_rsm_edt="Resume editing";
this.lang_no_error_found="No spelling errors found";
this.lang_no_suggestions="No suggestions";
this.show_spell_img=false;
this.decoration=true;
this.use_close_btn=true;
this.edit_layer_dbl_click=true;
this.report_ta_not_found=true;
this.custom_ajax_error=null;
this.custom_no_spelling_error=null;
this.custom_menu_builder=[];
this.custom_item_evaulator=null;
this.extra_menu_items=[];
this.custom_spellcheck_starter=null;
this.main_controller=true;
this.lang_state_observer=null;
this.spelling_state_observer=null;
this.show_menu_observer=null;
this.all_errors_fixed_observer=null;
this.use_focus=false;
this.focus_link_t=null;
this.focus_link_b=null;
this.cnt_errors=0;
this.cnt_errors_fixed=0;
$(document).bind("click",function(e){
if($(e.target).attr("googie_action_btn")!="1"&&_3.isLangWindowShown()){
_3.hideLangWindow();
}
if($(e.target).attr("googie_action_btn")!="1"&&_3.isErrorWindowShown()){
_3.hideErrorWindow();
}
});
this.decorateTextarea=function(id){
this.text_area=typeof (id)=="string"?document.getElementById(id):id;
if(this.text_area){
if(!this.spell_container&&this.decoration){
var _a=document.createElement("table");
var _b=document.createElement("tbody");
var tr=document.createElement("tr");
var _d=document.createElement("td");
var _e=this.isDefined(this.force_width)?this.force_width:this.text_area.offsetWidth;
var _f=this.isDefined(this.force_height)?this.force_height:16;
tr.appendChild(_d);
_b.appendChild(tr);
$(_a).append(_b).insertBefore(this.text_area).width("100%").height(_f);
$(_d).height(_f).width(_e).css("text-align","right");
this.spell_container=_d;
}
this.checkSpellingState();
}else{
if(this.report_ta_not_found){
alert("Text area not found");
}
}
};
this.setSpellContainer=function(id){
this.spell_container=typeof (id)=="string"?document.getElementById(id):id;
};
this.setLanguages=function(_11){
this.lang_to_word=_11;
this.langlist_codes=this.array_keys(_11);
};
this.setCurrentLanguage=function(_12){
GOOGIE_CUR_LANG=_12;
var now=new Date();
now.setTime(now.getTime()+365*24*60*60*1000);
setCookie("language",_12,now);
};
this.setForceWidthHeight=function(_14,_15){
this.force_width=_14;
this.force_height=_15;
};
this.setDecoration=function(_16){
this.decoration=_16;
};
this.dontUseCloseButtons=function(){
this.use_close_btn=false;
};
this.appendNewMenuItem=function(_17,_18,_19){
this.extra_menu_items.push([_17,_18,_19]);
};
this.appendCustomMenuBuilder=function(_1a,_1b){
this.custom_menu_builder.push([_1a,_1b]);
};
this.setFocus=function(){
try{
this.focus_link_b.focus();
this.focus_link_t.focus();
return true;
}
catch(e){
return false;
}
};
this.setStateChanged=function(_1c){
this.state=_1c;
if(this.spelling_state_observer!=null&&this.report_state_change){
this.spelling_state_observer(_1c,this);
}
};
this.setReportStateChange=function(_1d){
this.report_state_change=_1d;
};
this.getUrl=function(){
return this.server_url+GOOGIE_CUR_LANG;
};
this.escapeSpecial=function(val){
return val.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
};
this.createXMLReq=function(_1f){
return "<?xml version=\"1.0\" encoding=\"utf-8\" ?>"+"<spellrequest textalreadyclipped=\"0\" ignoredups=\"0\" ignoredigits=\"1\" ignoreallcaps=\"1\">"+"<text>"+_1f+"</text></spellrequest>";
};
this.spellCheck=function(_20){
this.cnt_errors_fixed=0;
this.cnt_errors=0;
this.setStateChanged("checking_spell");
if(this.main_controller){
this.appendIndicator(this.spell_span);
}
this.error_links=[];
this.ta_scroll_top=this.text_area.scrollTop;
this.ignore=_20;
this.hideLangWindow();
if($(this.text_area).val()==""||_20){
if(!this.custom_no_spelling_error){
this.flashNoSpellingErrorState();
}else{
this.custom_no_spelling_error(this);
}
this.removeIndicator();
return;
}
this.createEditLayer(this.text_area.offsetWidth,this.text_area.offsetHeight);
this.createErrorWindow();
$("body").append(this.error_window);
try{
netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
}
catch(e){
}
if(this.main_controller){
$(this.spell_span).unbind("click");
}
this.orginal_text=$(this.text_area).val();
var _21=this.escapeSpecial(this.orginal_text);
var ref=this;
$.ajax({type:"POST",url:this.getUrl(),data:this.createXMLReq(_21),dataType:"text",error:function(o){
if(ref.custom_ajax_error){
ref.custom_ajax_error(ref);
}else{
alert("An error was encountered on the server. Please try again later.");
}
if(ref.main_controller){
$(ref.spell_span).remove();
ref.removeIndicator();
}
ref.checkSpellingState();
},success:function(_24){
var _25=_24;
ref.results=ref.parseResult(_25);
if(_25.match(/<c.*>/)!=null){
ref.showErrorsInIframe();
ref.resumeEditingState();
}else{
if(!ref.custom_no_spelling_error){
ref.flashNoSpellingErrorState();
}else{
ref.custom_no_spelling_error(ref);
}
}
ref.removeIndicator();
}});
};
this.parseResult=function(_26){
var _27=/\w+="(\d+|true)"/g;
var _28=/\t/g;
var _29=_26.match(/<c[^>]*>[^<]*<\/c>/g);
var _2a=new Array();
if(_29==null){
return _2a;
}
for(var i=0;i<_29.length;i++){
var _2c=new Array();
this.errorFound();
_2c["attrs"]=new Array();
var _2d=_29[i].match(_27);
for(var j=0;j<_2d.length;j++){
var _2f=_2d[j].split(/=/);
var val=_2f[1].replace(/"/g,"");
_2c["attrs"][_2f[0]]=val!="true"?parseInt(val):val;
}
_2c["suggestions"]=new Array();
var _31=_29[i].replace(/<[^>]*>/g,"");
var _32=_31.split(_28);
for(var k=0;k<_32.length;k++){
if(_32[k]!=""){
_2c["suggestions"].push(_32[k]);
}
}
_2a.push(_2c);
}
return _2a;
};
this.createErrorWindow=function(){
this.error_window=document.createElement("div");
$(this.error_window).addClass("googie_window").attr("googie_action_btn","1");
};
this.isErrorWindowShown=function(){
return $(this.error_window).is(":visible");
};
this.hideErrorWindow=function(){
$(this.error_window).css("visibility","hidden");
$(this.error_window_iframe).css("visibility","hidden");
};
this.updateOrginalText=function(_34,_35,_36,id){
var _38=this.orginal_text.substring(0,_34);
var _39=this.orginal_text.substring(_34+_35.length);
this.orginal_text=_38+_36+_39;
$(this.text_area).val(this.orginal_text);
var _3a=_36.length-_35.length;
for(var j=0;j<this.results.length;j++){
if(j!=id&&j>id){
this.results[j]["attrs"]["o"]+=_3a;
}
}
};
this.saveOldValue=function(elm,_3d){
elm.is_changed=true;
elm.old_value=_3d;
};
this.createListSeparator=function(){
var td=document.createElement("td");
var tr=document.createElement("tr");
$(td).html(" ").attr("googie_action_btn","1").css({"cursor":"default","font-size":"3px","border-top":"1px solid #ccc","padding-top":"3px"});
tr.appendChild(td);
return tr;
};
this.correctError=function(id,elm,_42,_43){
var _44=elm.innerHTML;
var _45=_42.nodeType==3?_42.nodeValue:_42.innerHTML;
var _46=this.results[id]["attrs"]["o"];
if(_43){
var _47=elm.previousSibling.innerHTML;
elm.previousSibling.innerHTML=_47.slice(0,_47.length-1);
_44=" "+_44;
_46--;
}
this.hideErrorWindow();
this.updateOrginalText(_46,_44,_45,id);
$(elm).html(_45).css("color","green").attr("is_corrected",true);
this.results[id]["attrs"]["l"]=_45.length;
if(!this.isDefined(elm.old_value)){
this.saveOldValue(elm,_44);
}
this.errorFixed();
};
this.showErrorWindow=function(elm,id){
if(this.show_menu_observer){
this.show_menu_observer(this);
}
var ref=this;
var pos=$(elm).offset();
pos.top-=this.edit_layer.scrollTop;
$(this.error_window).css({"visibility":"visible","top":(pos.top+20)+"px","left":(pos.left)+"px"}).html("");
var _4c=document.createElement("table");
var _4d=document.createElement("tbody");
$(_4c).addClass("googie_list").attr("googie_action_btn","1");
var _4e=false;
if(this.custom_menu_builder!=[]){
for(var k=0;k<this.custom_menu_builder.length;k++){
var eb=this.custom_menu_builder[k];
if(eb[0]((this.results[id]))){
_4e=eb[1](this,_4d,elm);
break;
}
}
}
if(!_4e){
var _51=this.results[id]["suggestions"];
var _52=this.results[id]["attrs"]["o"];
var len=this.results[id]["attrs"]["l"];
if(_51.length==0){
var row=document.createElement("tr");
var _55=document.createElement("td");
var _56=document.createElement("span");
$(_56).text(this.lang_no_suggestions);
$(_55).attr("googie_action_btn","1").css("cursor","default");
_55.appendChild(_56);
row.appendChild(_55);
_4d.appendChild(row);
}
for(i=0;i<_51.length;i++){
var row=document.createElement("tr");
var _55=document.createElement("td");
var _56=document.createElement("span");
$(_56).html(_51[i]);
$(_55).bind("mouseover",this.item_onmouseover).bind("mouseout",this.item_onmouseout).bind("click",function(e){
ref.correctError(id,elm,e.target.firstChild);
});
_55.appendChild(_56);
row.appendChild(_55);
_4d.appendChild(row);
}
if(elm.is_changed&&elm.innerHTML!=elm.old_value){
var _58=elm.old_value;
var _59=document.createElement("tr");
var _5a=document.createElement("td");
var _5b=document.createElement("span");
$(_5b).addClass("googie_list_revert").html(this.lang_revert+" "+_58);
$(_5a).bind("mouseover",this.item_onmouseover).bind("mouseout",this.item_onmouseout).bind("click",function(e){
ref.updateOrginalText(_52,elm.innerHTML,_58,id);
$(elm).attr("is_corrected",true).css("color","#b91414").html(_58);
ref.hideErrorWindow();
});
_5a.appendChild(_5b);
_59.appendChild(_5a);
_4d.appendChild(_59);
}
var _5d=document.createElement("tr");
var _5e=document.createElement("td");
var _5f=document.createElement("input");
var _60=document.createElement("img");
var _61=document.createElement("form");
var _62=function(){
if(_5f.value!=""){
if(!ref.isDefined(elm.old_value)){
ref.saveOldValue(elm,elm.innerHTML);
}
ref.updateOrginalText(_52,elm.innerHTML,_5f.value,id);
$(elm).attr("is_corrected",true).css("color","green").html(_5f.value);
ref.hideErrorWindow();
}
return false;
};
$(_5f).width(120).css({"margin":0,"padding":0});
$(_5f).val(elm.innerHTML).attr("googie_action_btn","1");
$(_5e).css("cursor","default").attr("googie_action_btn","1");
$(_60).attr("src",this.img_dir+"ok.gif").width(32).height(16).css({"cursor":"pointer","margin-left":"2px","margin-right":"2px"}).bind("click",_62);
$(_61).attr("googie_action_btn","1").css({"margin":0,"padding":0,"cursor":"default","white-space":"nowrap"}).bind("submit",_62);
_61.appendChild(_5f);
_61.appendChild(_60);
_5e.appendChild(_61);
_5d.appendChild(_5e);
_4d.appendChild(_5d);
if(this.extra_menu_items.length>0){
_4d.appendChild(this.createListSeparator());
}
var _63=function(i){
if(i<ref.extra_menu_items.length){
var _65=ref.extra_menu_items[i];
if(!_65[2]||_65[2](elm,ref)){
var _66=document.createElement("tr");
var _67=document.createElement("td");
$(_67).html(_65[0]).bind("mouseover",ref.item_onmouseover).bind("mouseout",ref.item_onmouseout).bind("click",function(){
return _65[1](elm,ref);
});
_66.appendChild(_67);
_4d.appendChild(_66);
}
_63(i+1);
}
};
_63(0);
_63=null;
if(this.use_close_btn){
_4d.appendChild(this.createCloseButton(this.hideErrorWindow));
}
}
_4c.appendChild(_4d);
this.error_window.appendChild(_4c);
if($.browser.msie){
if(!this.error_window_iframe){
var _68=$("<iframe>").css("position","absolute").css("z-index",0);
$("body").append(_68);
this.error_window_iframe=_68;
}
$(this.error_window_iframe).css({"visibility":"visible","top":this.error_window.offsetTop,"left":this.error_window.offsetLeft,"width":this.error_window.offsetWidth,"height":this.error_window.offsetHeight});
}
};
this.createEditLayer=function(_69,_6a){
this.edit_layer=document.createElement("div");
$(this.edit_layer).addClass("googie_edit_layer").width(_69-10).height(_6a);
if(this.text_area.nodeName.toLowerCase()!="input"||$(this.text_area).val()==""){
$(this.edit_layer).css("overflow","auto").height(_6a-4);
}else{
$(this.edit_layer).css("overflow","hidden");
}
var ref=this;
if(this.edit_layer_dbl_click){
$(this.edit_layer).bind("click",function(e){
if(e.target.className!="googie_link"&&!ref.isErrorWindowShown()){
ref.resumeEditing();
var fn1=function(){
$(ref.text_area).focus();
fn1=null;
};
window.setTimeout(fn1,10);
}
return false;
});
}
};
this.resumeEditing=function(){
this.setStateChanged("ready");
if(this.edit_layer){
this.el_scroll_top=this.edit_layer.scrollTop;
}
this.hideErrorWindow();
if(this.main_controller){
$(this.spell_span).removeClass().addClass("googie_no_style");
}
if(!this.ignore){
if(this.use_focus){
$(this.focus_link_t).remove();
$(this.focus_link_b).remove();
}
$(this.edit_layer).remove();
$(this.text_area).show();
if(this.el_scroll_top!=undefined){
this.text_area.scrollTop=this.el_scroll_top;
}
}
this.checkSpellingState(false);
};
this.createErrorLink=function(_6e,id){
var elm=document.createElement("span");
var ref=this;
var d=function(e){
ref.showErrorWindow(elm,id);
d=null;
return false;
};
$(elm).html(_6e).addClass("googie_link").bind("click",d).attr({"googie_action_btn":"1","g_id":id,"is_corrected":false});
return elm;
};
this.createPart=function(_74){
if(_74==" "){
return document.createTextNode(" ");
}
_74=this.escapeSpecial(_74);
_74=_74.replace(/\n/g,"<br>");
_74=_74.replace(/    /g," &nbsp;");
_74=_74.replace(/^ /g,"&nbsp;");
_74=_74.replace(/ $/g,"&nbsp;");
var _75=document.createElement("span");
$(_75).html(_74);
return _75;
};
this.showErrorsInIframe=function(){
var _76=document.createElement("div");
var _77=0;
var _78=this.results;
if(_78.length>0){
for(var i=0;i<_78.length;i++){
var _7a=_78[i]["attrs"]["o"];
var len=_78[i]["attrs"]["l"];
var _7c=this.orginal_text.substring(_77,_7a);
var _7d=this.createPart(_7c);
_76.appendChild(_7d);
_77+=_7a-_77;
var _7e=this.createErrorLink(this.orginal_text.substr(_7a,len),i);
this.error_links.push(_7e);
_76.appendChild(_7e);
_77+=len;
}
var _7f=this.orginal_text.substr(_77,this.orginal_text.length);
var _80=this.createPart(_7f);
_76.appendChild(_80);
}else{
_76.innerHTML=this.orginal_text;
}
$(_76).css("text-align","left");
var me=this;
if(this.custom_item_evaulator){
$.map(this.error_links,function(elm){
me.custom_item_evaulator(me,elm);
});
}
$(this.edit_layer).append(_76);
$(this.text_area).hide();
$(this.edit_layer).insertBefore(this.text_area);
if(this.use_focus){
this.focus_link_t=this.createFocusLink("focus_t");
this.focus_link_b=this.createFocusLink("focus_b");
$(this.focus_link_t).insertBefore(this.edit_layer);
$(this.focus_link_b).insertAfter(this.edit_layer);
}
};
this.createLangWindow=function(){
this.language_window=document.createElement("div");
$(this.language_window).addClass("googie_window").width(100).attr("googie_action_btn","1");
var _83=document.createElement("table");
var _84=document.createElement("tbody");
var ref=this;
$(_83).addClass("googie_list").width("100%");
this.lang_elms=new Array();
for(i=0;i<this.langlist_codes.length;i++){
var row=document.createElement("tr");
var _87=document.createElement("td");
var _88=document.createElement("span");
$(_88).text(this.lang_to_word[this.langlist_codes[i]]);
this.lang_elms.push(_87);
$(_87).attr("googieId",this.langlist_codes[i]).bind("click",function(e){
ref.deHighlightCurSel();
ref.setCurrentLanguage($(this).attr("googieId"));
if(ref.lang_state_observer!=null){
ref.lang_state_observer();
}
ref.highlightCurSel();
ref.hideLangWindow();
}).bind("mouseover",function(e){
if(this.className!="googie_list_selected"){
this.className="googie_list_onhover";
}
}).bind("mouseout",function(e){
if(this.className!="googie_list_selected"){
this.className="googie_list_onout";
}
});
_87.appendChild(_88);
row.appendChild(_87);
_84.appendChild(row);
}
if(this.use_close_btn){
_84.appendChild(this.createCloseButton(function(){
ref.hideLangWindow.apply(ref);
}));
}
this.highlightCurSel();
_83.appendChild(_84);
this.language_window.appendChild(_83);
};
this.isLangWindowShown=function(){
return $(this.language_window).is(":hidden");
};
this.hideLangWindow=function(){
$(this.language_window).css("visibility","hidden");
$(this.switch_lan_pic).removeClass().addClass("googie_lang_3d_on");
};
this.deHighlightCurSel=function(){
$(this.lang_cur_elm).removeClass().addClass("googie_list_onout");
};
this.highlightCurSel=function(){
if(GOOGIE_CUR_LANG==null){
GOOGIE_CUR_LANG=GOOGIE_DEFAULT_LANG;
}
for(var i=0;i<this.lang_elms.length;i++){
if($(this.lang_elms[i]).attr("googieId")==GOOGIE_CUR_LANG){
this.lang_elms[i].className="googie_list_selected";
this.lang_cur_elm=this.lang_elms[i];
}else{
this.lang_elms[i].className="googie_list_onout";
}
}
};
this.showLangWindow=function(elm){
if(this.show_menu_observer){
this.show_menu_observer(this);
}
this.createLangWindow();
$("body").append(this.language_window);
var pos=$(elm).offset();
var top=pos.top+$(elm).height();
var _90=this.change_lang_pic_placement=="right"?pos.left-100+$(elm).width():pos.left+$(elm).width();
$(this.language_window).css({"visibility":"visible","top":top+"px","left":_90+"px"});
this.highlightCurSel();
};
this.createChangeLangPic=function(){
var img=$("<img>").attr({src:this.img_dir+"change_lang.gif","alt":"Change language","googie_action_btn":"1"});
var _92=document.createElement("span");
var ref=this;
$(_92).addClass("googie_lang_3d_on").append(img).bind("click",function(e){
var elm=this.tagName.toLowerCase()=="img"?this.parentNode:this;
if($(elm).hasClass("googie_lang_3d_click")){
elm.className="googie_lang_3d_on";
ref.hideLangWindow();
}else{
elm.className="googie_lang_3d_click";
ref.showLangWindow(elm);
}
});
return _92;
};
this.createSpellDiv=function(){
var _96=document.createElement("span");
$(_96).addClass("googie_check_spelling_link").text(this.lang_chck_spell);
if(this.show_spell_img){
$(_96).append(" ").append($("<img>").attr("src",this.img_dir+"spellc.gif"));
}
return _96;
};
this.flashNoSpellingErrorState=function(_97){
this.setStateChanged("no_error_found");
var ref=this;
if(this.main_controller){
var _99;
if(_97){
var fn=function(){
_97();
ref.checkSpellingState();
};
_99=fn;
}else{
_99=function(){
ref.checkSpellingState();
};
}
var rsm=$("<span>").text(this.lang_no_error_found);
$(this.switch_lan_pic).hide();
$(this.spell_span).empty().append(rsm).removeClass().addClass("googie_check_spelling_ok");
window.setTimeout(_99,1000);
}
};
this.resumeEditingState=function(){
this.setStateChanged("resume_editing");
if(this.main_controller){
var rsm=$("<span>").text(this.lang_rsm_edt);
var ref=this;
$(this.switch_lan_pic).hide();
$(this.spell_span).empty().unbind().append(rsm).bind("click",function(){
ref.resumeEditing();
}).removeClass().addClass("googie_resume_editing");
}
try{
this.edit_layer.scrollTop=this.ta_scroll_top;
}
catch(e){
}
};
this.checkSpellingState=function(_9e){
if(_9e){
this.setStateChanged("ready");
}
if(this.show_change_lang_pic){
this.switch_lan_pic=this.createChangeLangPic();
}else{
this.switch_lan_pic=document.createElement("span");
}
var _9f=this.createSpellDiv();
var ref=this;
if(this.custom_spellcheck_starter){
$(_9f).bind("click",function(e){
ref.custom_spellcheck_starter();
});
}else{
$(_9f).bind("click",function(e){
ref.spellCheck();
});
}
if(this.main_controller){
if(this.change_lang_pic_placement=="left"){
$(this.spell_container).empty().append(this.switch_lan_pic).append(" ").append(_9f);
}else{
$(this.spell_container).empty().append(_9f).append(" ").append(this.switch_lan_pic);
}
}
this.spell_span=_9f;
};
this.isDefined=function(o){
return (o!="undefined"&&o!=null);
};
this.errorFixed=function(){
this.cnt_errors_fixed++;
if(this.all_errors_fixed_observer){
if(this.cnt_errors_fixed==this.cnt_errors){
this.hideErrorWindow();
this.all_errors_fixed_observer();
}
}
};
this.errorFound=function(){
this.cnt_errors++;
};
this.createCloseButton=function(_a4){
return this.createButton(this.lang_close,"googie_list_close",_a4);
};
this.createButton=function(_a5,_a6,_a7){
var _a8=document.createElement("tr");
var btn=document.createElement("td");
var _aa;
if(_a6){
_aa=document.createElement("span");
$(_aa).addClass(_a6).html(_a5);
}else{
_aa=document.createTextNode(_a5);
}
$(btn).bind("click",_a7).bind("mouseover",this.item_onmouseover).bind("mouseout",this.item_onmouseout);
btn.appendChild(_aa);
_a8.appendChild(btn);
return _a8;
};
this.removeIndicator=function(elm){
if(window.rcmail){
rcmail.set_busy(false);
}
};
this.appendIndicator=function(elm){
if(window.rcmail){
rcmail.set_busy(true,"checking");
}
};
this.createFocusLink=function(_ad){
var _ae=document.createElement("a");
$(_ae).attr({"href":"javascript:;","name":_ad});
return _ae;
};
this.item_onmouseover=function(e){
if(this.className!="googie_list_revert"&&this.className!="googie_list_close"){
this.className="googie_list_onhover";
}else{
this.parentNode.className="googie_list_onhover";
}
};
this.item_onmouseout=function(e){
if(this.className!="googie_list_revert"&&this.className!="googie_list_close"){
this.className="googie_list_onout";
}else{
this.parentNode.className="googie_list_onout";
}
};
};

