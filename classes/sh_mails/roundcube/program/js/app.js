function rcube_webmail(){
this.env=new Object();
this.labels=new Object();
this.buttons=new Object();
this.buttons_sel=new Object();
this.gui_objects=new Object();
this.gui_containers=new Object();
this.commands=new Object();
this.command_handlers=new Object();
this.onloads=new Array();
this.ref="rcmail";
var _1=this;
this.dblclick_time=500;
this.message_time=3000;
this.identifier_expr=new RegExp("[^0-9a-z-_]","gi");
this.mimetypes=new Array("text/plain","text/html","text/xml","image/jpeg","image/gif","image/png","application/x-javascript","application/pdf","application/x-shockwave-flash");
this.env.keep_alive=60;
this.env.request_timeout=180;
this.env.draft_autosave=0;
this.env.comm_path="./";
this.env.bin_path="./bin/";
this.env.blankpage="program/blank.gif";
jQuery.ajaxSetup({cache:false,error:function(_2,_3,_4){
_1.http_error(_2,_3,_4);
},beforeSend:function(_5){
_5.setRequestHeader("X-RoundCube-Request",_1.env.request_token);
}});
this.set_env=function(p,_7){
if(p!=null&&typeof (p)=="object"&&!_7){
for(var n in p){
this.env[n]=p[n];
}
}else{
this.env[p]=_7;
}
};
this.add_label=function(_9,_a){
this.labels[_9]=_a;
};
this.register_button=function(_b,id,_d,_e,_f,_10){
if(!this.buttons[_b]){
this.buttons[_b]=new Array();
}
var _11={id:id,type:_d};
if(_e){
_11.act=_e;
}
if(_f){
_11.sel=_f;
}
if(_10){
_11.over=_10;
}
this.buttons[_b][this.buttons[_b].length]=_11;
};
this.gui_object=function(_12,id){
this.gui_objects[_12]=id;
};
this.gui_container=function(_14,id){
this.gui_containers[_14]=id;
};
this.add_element=function(elm,_17){
if(this.gui_containers[_17]&&this.gui_containers[_17].jquery){
this.gui_containers[_17].append(elm);
}
};
this.register_command=function(_18,_19,_1a){
this.command_handlers[_18]=_19;
if(_1a){
this.enable_command(_18,true);
}
};
this.add_onload=function(f){
this.onloads[this.onloads.length]=f;
};
this.init=function(){
var p=this;
this.task=this.env.task;
if(!bw.dom||!bw.xmlhttp_test()){
this.goto_url("error","_code=0x199");
return;
}
for(var n in this.gui_containers){
this.gui_containers[n]=$("#"+this.gui_containers[n]);
}
for(var n in this.gui_objects){
this.gui_objects[n]=rcube_find_object(this.gui_objects[n]);
}
this.init_buttons();
if(this.env.framed&&parent.rcmail&&parent.rcmail.set_busy){
parent.rcmail.set_busy(false);
}
this.enable_command("logout","mail","addressbook","settings",true);
if(this.env.permaurl){
this.enable_command("permaurl",true);
}
switch(this.task){
case "mail":
if(this.gui_objects.messagelist){
this.message_list=new rcube_list_widget(this.gui_objects.messagelist,{multiselect:true,draggable:true,keyboard:true,dblclick_time:this.dblclick_time});
this.message_list.row_init=function(o){
p.init_message_row(o);
};
this.message_list.addEventListener("dblclick",function(o){
p.msglist_dbl_click(o);
});
this.message_list.addEventListener("keypress",function(o){
p.msglist_keypress(o);
});
this.message_list.addEventListener("select",function(o){
p.msglist_select(o);
});
this.message_list.addEventListener("dragstart",function(o){
p.drag_start(o);
});
this.message_list.addEventListener("dragmove",function(e){
p.drag_move(e);
});
this.message_list.addEventListener("dragend",function(e){
p.drag_end(e);
});
document.onmouseup=function(e){
return p.doc_mouse_up(e);
};
this.message_list.init();
this.enable_command("toggle_status","toggle_flag",true);
if(this.gui_objects.mailcontframe){
this.gui_objects.mailcontframe.onmousedown=function(e){
return p.click_on_list(e);
};
}else{
this.message_list.focus();
}
}
if(this.env.coltypes){
this.set_message_coltypes(this.env.coltypes);
}
this.enable_command("list","checkmail","compose","add-contact","search","reset-search","collapse-folder",true);
if(this.env.search_text!=null&&document.getElementById("quicksearchbox")!=null){
document.getElementById("quicksearchbox").value=this.env.search_text;
}
if(this.env.action=="show"||this.env.action=="preview"){
this.enable_command("show","reply","reply-all","forward","moveto","delete","open","mark","edit","viewsource","download","print","load-attachment","load-headers",true);
if(this.env.next_uid){
this.enable_command("nextmessage",true);
this.enable_command("lastmessage",true);
}
if(this.env.prev_uid){
this.enable_command("previousmessage",true);
this.enable_command("firstmessage",true);
}
if(this.env.blockedobjects){
if(this.gui_objects.remoteobjectsmsg){
this.gui_objects.remoteobjectsmsg.style.display="block";
}
this.enable_command("load-images","always-load",true);
}
}
if(this.env.trash_mailbox&&this.env.mailbox!=this.env.trash_mailbox){
this.set_alttext("delete","movemessagetotrash");
}
if(this.env.action=="preview"&&this.env.framed&&parent.rcmail){
this.enable_command("compose","add-contact",false);
parent.rcmail.show_contentframe(true);
}
if(this.env.action=="compose"){
this.enable_command("add-attachment","send-attachment","remove-attachment","send",true);
if(this.env.spellcheck){
this.env.spellcheck.spelling_state_observer=function(s){
_1.set_spellcheck_state(s);
};
this.set_spellcheck_state("ready");
if($("input[name='_is_html']").val()=="1"){
this.display_spellcheck_controls(false);
}
}
if(this.env.drafts_mailbox){
this.enable_command("savedraft",true);
}
document.onmouseup=function(e){
return p.doc_mouse_up(e);
};
this.init_messageform();
}
if(this.env.messagecount){
this.enable_command("select-all","select-none","expunge",true);
}
if(this.purge_mailbox_test()){
this.enable_command("purge",true);
}
this.set_page_buttons();
if(this.env.action=="print"){
window.print();
}
if(this.gui_objects.mailboxlist){
this.env.unread_counts={};
this.gui_objects.folderlist=this.gui_objects.mailboxlist;
this.http_request("getunread","");
}
if(this.env.mdn_request&&this.env.uid){
var _29="_uid="+this.env.uid+"&_mbox="+urlencode(this.env.mailbox);
if(confirm(this.get_label("mdnrequest"))){
this.http_post("sendmdn",_29);
}else{
this.http_post("mark",_29+"&_flag=mdnsent");
}
}
break;
case "addressbook":
if(this.gui_objects.contactslist){
this.contact_list=new rcube_list_widget(this.gui_objects.contactslist,{multiselect:true,draggable:true,keyboard:true});
this.contact_list.row_init=function(row){
p.triggerEvent("insertrow",{cid:row.uid,row:row});
};
this.contact_list.addEventListener("keypress",function(o){
p.contactlist_keypress(o);
});
this.contact_list.addEventListener("select",function(o){
p.contactlist_select(o);
});
this.contact_list.addEventListener("dragstart",function(o){
p.drag_start(o);
});
this.contact_list.addEventListener("dragmove",function(e){
p.drag_move(e);
});
this.contact_list.addEventListener("dragend",function(e){
p.drag_end(e);
});
this.contact_list.init();
if(this.env.cid){
this.contact_list.highlight_row(this.env.cid);
}
if(this.gui_objects.contactslist.parentNode){
this.gui_objects.contactslist.parentNode.onmousedown=function(e){
return p.click_on_list(e);
};
document.onmouseup=function(e){
return p.doc_mouse_up(e);
};
}else{
this.contact_list.focus();
}
this.gui_objects.folderlist=this.gui_objects.contactslist;
}
this.set_page_buttons();
if(this.env.address_sources&&this.env.address_sources[this.env.source]&&!this.env.address_sources[this.env.source].readonly){
this.enable_command("add",true);
}
if(this.env.cid){
this.enable_command("show","edit",true);
}
if((this.env.action=="add"||this.env.action=="edit")&&this.gui_objects.editform){
this.enable_command("save",true);
}else{
this.enable_command("search","reset-search","moveto","import",true);
}
if(this.contact_list&&this.contact_list.rowcount>0){
this.enable_command("export",true);
}
this.enable_command("list",true);
break;
case "settings":
this.enable_command("preferences","identities","save","folders",true);
if(this.env.action=="identities"){
this.enable_command("add",this.env.identities_level<2);
}else{
if(this.env.action=="edit-identity"||this.env.action=="add-identity"){
this.enable_command("add",this.env.identities_level<2);
this.enable_command("save","delete","edit",true);
}else{
if(this.env.action=="folders"){
this.enable_command("subscribe","unsubscribe","create-folder","rename-folder","delete-folder",true);
}
}
}
if(this.gui_objects.identitieslist){
this.identity_list=new rcube_list_widget(this.gui_objects.identitieslist,{multiselect:false,draggable:false,keyboard:false});
this.identity_list.addEventListener("select",function(o){
p.identity_select(o);
});
this.identity_list.init();
this.identity_list.focus();
if(this.env.iid){
this.identity_list.highlight_row(this.env.iid);
}
}else{
if(this.gui_objects.sectionslist){
this.sections_list=new rcube_list_widget(this.gui_objects.sectionslist,{multiselect:false,draggable:false,keyboard:false});
this.sections_list.addEventListener("select",function(o){
p.section_select(o);
});
this.sections_list.init();
this.sections_list.focus();
this.sections_list.select_first();
}else{
if(this.gui_objects.subscriptionlist){
this.init_subscription_list();
}
}
}
break;
case "login":
var _34=$("#rcmloginuser");
_34.bind("keyup",function(e){
return rcmail.login_user_keyup(e);
});
if(_34.val()==""){
_34.focus();
}else{
$("#rcmloginpwd").focus();
}
$("#rcmlogintz").val(new Date().getTimezoneOffset()/-60);
this.enable_command("login",true);
break;
default:
break;
}
this.loaded=true;
if(this.pending_message){
this.display_message(this.pending_message[0],this.pending_message[1]);
}
if(this.gui_objects.folderlist){
this.gui_containers.foldertray=$(this.gui_objects.folderlist);
}
this.triggerEvent("init",{task:this.task,action:this.env.action});
for(var i=0;i<this.onloads.length;i++){
if(typeof (this.onloads[i])=="string"){
eval(this.onloads[i]);
}else{
if(typeof (this.onloads[i])=="function"){
this.onloads[i]();
}
}
}
this.start_keepalive();
};
this.start_keepalive=function(){
if(this.env.keep_alive&&!this.env.framed&&this.task=="mail"&&this.gui_objects.mailboxlist){
this._int=setInterval(function(){
_1.check_for_recent(false);
},this.env.keep_alive*1000);
}else{
if(this.env.keep_alive&&!this.env.framed&&this.task!="login"){
this._int=setInterval(function(){
_1.send_keep_alive();
},this.env.keep_alive*1000);
}
}
};
this.init_message_row=function(row){
var uid=row.uid;
if(uid&&this.env.messages[uid]){
row.deleted=this.env.messages[uid].deleted?true:false;
row.unread=this.env.messages[uid].unread?true:false;
row.replied=this.env.messages[uid].replied?true:false;
row.flagged=this.env.messages[uid].flagged?true:false;
row.forwarded=this.env.messages[uid].forwarded?true:false;
}
if(row.icon=row.obj.getElementsByTagName("td")[0].getElementsByTagName("img")[0]){
var p=this;
row.icon.id="msgicn_"+row.uid;
row.icon._row=row.obj;
row.icon.onmousedown=function(e){
p.command("toggle_status",this);
};
}
if(!this.env.flagged_col&&this.env.coltypes){
var _3b;
if((_3b=find_in_array("flag",this.env.coltypes))>=0){
this.set_env("flagged_col",_3b+1);
}
}
if(this.env.flagged_col&&(row.flagged_icon=row.obj.getElementsByTagName("td")[this.env.flagged_col].getElementsByTagName("img")[0])){
var p=this;
row.flagged_icon.id="flaggedicn_"+row.uid;
row.flagged_icon._row=row.obj;
row.flagged_icon.onmousedown=function(e){
p.command("toggle_flag",this);
};
}
this.triggerEvent("insertrow",{uid:uid,row:row});
};
this.init_messageform=function(){
if(!this.gui_objects.messageform){
return false;
}
var _3d=$("[name='_from']");
var _3e=$("[name='_to']");
var _3f=$("input[name='_subject']");
var _40=$("[name='_message']").get(0);
var _41=$("input[name='_is_html']").val()=="1";
this.init_address_input_events(_3e);
this.init_address_input_events($("[name='_cc']"));
this.init_address_input_events($("[name='_bcc']"));
if(_3d.attr("type")=="select-one"&&$("input[name='_draft_saveid']").val()==""&&!_41){
this.change_identity(_3d[0]);
}
if(_3e.val()==""){
_3e.focus();
}else{
if(_3f.val()==""){
_3f.focus();
}else{
if(_40&&!_41){
_40.focus();
}
}
}
this.compose_field_hash(true);
this.auto_save_start();
};
this.init_address_input_events=function(obj){
var _43=function(e){
return _1.ksearch_keypress(e,this);
};
obj.bind((bw.safari||bw.ie?"keydown":"keypress"),_43);
obj.attr("autocomplete","off");
};
this.command=function(_45,_46,obj){
if(obj&&obj.blur){
obj.blur();
}
if(this.busy){
return false;
}
if(!this.commands[_45]){
if(this.env.framed&&parent.rcmail&&parent.rcmail.command){
parent.rcmail.command(_45,_46);
}
return false;
}
if(this.task=="mail"&&this.env.action=="compose"&&(_45=="list"||_45=="mail"||_45=="addressbook"||_45=="settings")){
if(this.cmp_hash!=this.compose_field_hash()&&!confirm(this.get_label("notsentwarning"))){
return false;
}
}
if(typeof this.command_handlers[_45]=="function"){
var ret=this.command_handlers[_45](_46,obj);
return ret!==null?ret:(obj?false:true);
}else{
if(typeof this.command_handlers[_45]=="string"){
var ret=window[this.command_handlers[_45]](_46,obj);
return ret!==null?ret:(obj?false:true);
}
}
var _49=this.triggerEvent("before"+_45,_46);
if(typeof _49!="undefined"){
if(_49===false){
return false;
}else{
_46=_49;
}
}
switch(_45){
case "login":
if(this.gui_objects.loginform){
this.gui_objects.loginform.submit();
}
break;
case "mail":
case "addressbook":
case "settings":
case "logout":
this.switch_task(_45);
break;
case "permaurl":
if(obj&&obj.href&&obj.target){
return true;
}else{
if(this.env.permaurl){
parent.location.href=this.env.permaurl;
}
}
break;
case "open":
var uid;
if(uid=this.get_single_uid()){
obj.href="?_task="+this.env.task+"&_action=show&_mbox="+urlencode(this.env.mailbox)+"&_uid="+uid;
return true;
}
break;
case "list":
if(this.task=="mail"){
if(this.env.search_request<0||(_46!=""&&(this.env.search_request&&_46!=this.env.mailbox))){
this.reset_qsearch();
}
this.list_mailbox(_46);
if(this.env.trash_mailbox){
this.set_alttext("delete",this.env.mailbox!=this.env.trash_mailbox?"movemessagetotrash":"deletemessage");
}
}else{
if(this.task=="addressbook"){
if(this.env.search_request<0||(this.env.search_request&&_46!=this.env.source)){
this.reset_qsearch();
}
this.list_contacts(_46);
this.enable_command("add",(this.env.address_sources&&!this.env.address_sources[_46].readonly));
}
}
break;
case "load-headers":
this.load_headers(obj);
break;
case "sort":
var _4b,_4c=_46;
if(this.env.sort_col==_4c){
_4b=this.env.sort_order=="ASC"?"DESC":"ASC";
}else{
_4b="ASC";
}
$("#rcm"+this.env.sort_col).removeClass("sorted"+(this.env.sort_order.toUpperCase()));
$("#rcm"+_4c).addClass("sorted"+_4b);
this.env.sort_col=_4c;
this.env.sort_order=_4b;
this.list_mailbox("","",_4c+"_"+_4b);
break;
case "nextpage":
this.list_page("next");
break;
case "lastpage":
this.list_page("last");
break;
case "previouspage":
this.list_page("prev");
break;
case "firstpage":
this.list_page("first");
break;
case "expunge":
if(this.env.messagecount){
this.expunge_mailbox(this.env.mailbox);
}
break;
case "purge":
case "empty-mailbox":
if(this.env.messagecount){
this.purge_mailbox(this.env.mailbox);
}
break;
case "show":
if(this.task=="mail"){
var uid=this.get_single_uid();
if(uid&&(!this.env.uid||uid!=this.env.uid)){
if(this.env.mailbox==this.env.drafts_mailbox){
this.goto_url("compose","_draft_uid="+uid+"&_mbox="+urlencode(this.env.mailbox),true);
}else{
this.show_message(uid);
}
}
}else{
if(this.task=="addressbook"){
var cid=_46?_46:this.get_single_cid();
if(cid&&!(this.env.action=="show"&&cid==this.env.cid)){
this.load_contact(cid,"show");
}
}
}
break;
case "add":
if(this.task=="addressbook"){
this.load_contact(0,"add");
}else{
if(this.task=="settings"){
this.identity_list.clear_selection();
this.load_identity(0,"add-identity");
}
}
break;
case "edit":
var cid;
if(this.task=="addressbook"&&(cid=this.get_single_cid())){
this.load_contact(cid,"edit");
}else{
if(this.task=="settings"&&_46){
this.load_identity(_46,"edit-identity");
}else{
if(this.task=="mail"&&(cid=this.get_single_uid())){
var url=(this.env.mailbox==this.env.drafts_mailbox)?"_draft_uid=":"_uid=";
this.goto_url("compose",url+cid+"&_mbox="+urlencode(this.env.mailbox),true);
}
}
}
break;
case "save-identity":
case "save":
if(this.gui_objects.editform){
var _4f=$("input[name='_pagesize']");
var _50=$("input[name='_name']");
var _51=$("input[name='_email']");
if(_4f.length&&isNaN(parseInt(_4f.val()))){
alert(this.get_label("nopagesizewarning"));
_4f.focus();
break;
}else{
if(_50.length&&_50.val()==""){
alert(this.get_label("nonamewarning"));
_50.focus();
break;
}else{
if(_51.length&&!rcube_check_email(_51.val())){
alert(this.get_label("noemailwarning"));
_51.focus();
break;
}
}
}
this.gui_objects.editform.submit();
}
break;
case "delete":
if(this.task=="mail"){
this.delete_messages();
}else{
if(this.task=="addressbook"){
this.delete_contacts();
}else{
if(this.task=="settings"){
this.delete_identity();
}
}
}
break;
case "move":
case "moveto":
if(this.task=="mail"){
this.move_messages(_46);
}else{
if(this.task=="addressbook"&&this.drag_active){
this.copy_contact(null,_46);
}
}
break;
case "mark":
if(_46){
this.mark_message(_46);
}
break;
case "toggle_status":
if(_46&&!_46._row){
break;
}
var uid;
var _52="read";
if(_46._row.uid){
uid=_46._row.uid;
if(this.message_list.rows[uid].deleted){
_52="undelete";
}else{
if(!this.message_list.rows[uid].unread){
_52="unread";
}
}
}
this.mark_message(_52,uid);
break;
case "toggle_flag":
if(_46&&!_46._row){
break;
}
var uid;
var _52="flagged";
if(_46._row.uid){
uid=_46._row.uid;
if(this.message_list.rows[uid].flagged){
_52="unflagged";
}
}
this.mark_message(_52,uid);
break;
case "always-load":
if(this.env.uid&&this.env.sender){
this.add_contact(urlencode(this.env.sender));
window.setTimeout(function(){
_1.command("load-images");
},300);
break;
}
case "load-images":
if(this.env.uid){
this.show_message(this.env.uid,true,this.env.action=="preview");
}
break;
case "load-attachment":
var _53="_mbox="+urlencode(this.env.mailbox)+"&_uid="+this.env.uid+"&_part="+_46.part;
if(this.env.uid&&_46.mimetype&&find_in_array(_46.mimetype,this.mimetypes)>=0){
if(_46.mimetype=="text/html"){
_53+="&_safe=1";
}
this.attachment_win=window.open(this.env.comm_path+"&_action=get&"+_53+"&_frame=1","rcubemailattachment");
if(this.attachment_win){
window.setTimeout(function(){
_1.attachment_win.focus();
},10);
break;
}
}
this.goto_url("get",_53+"&_download=1",false);
break;
case "select-all":
if(_46=="invert"){
this.message_list.invert_selection();
}else{
this.message_list.select_all(_46);
}
break;
case "select-none":
this.message_list.clear_selection();
break;
case "nextmessage":
if(this.env.next_uid){
this.show_message(this.env.next_uid,false,this.env.action=="preview");
}
break;
case "lastmessage":
if(this.env.last_uid){
this.show_message(this.env.last_uid);
}
break;
case "previousmessage":
if(this.env.prev_uid){
this.show_message(this.env.prev_uid,false,this.env.action=="preview");
}
break;
case "firstmessage":
if(this.env.first_uid){
this.show_message(this.env.first_uid);
}
break;
case "checkmail":
this.check_for_recent(true);
break;
case "compose":
var url=this.env.comm_path+"&_action=compose";
if(this.task=="mail"){
url+="&_mbox="+urlencode(this.env.mailbox);
if(this.env.mailbox==this.env.drafts_mailbox){
var uid;
if(uid=this.get_single_uid()){
url+="&_draft_uid="+uid;
}
}else{
if(_46){
url+="&_to="+urlencode(_46);
}
}
}else{
if(this.task=="addressbook"){
if(_46&&_46.indexOf("@")>0){
url=this.get_task_url("mail",url);
this.redirect(url+"&_to="+urlencode(_46));
break;
}
var _54=new Array();
if(_46){
_54[_54.length]=_46;
}else{
if(this.contact_list){
var _55=this.contact_list.get_selection();
for(var n=0;n<_55.length;n++){
_54[_54.length]=_55[n];
}
}
}
if(_54.length){
this.http_request("mailto","_cid="+urlencode(_54.join(","))+"&_source="+urlencode(this.env.source),true);
}
break;
}
}
url=url.replace(/&_framed=1/,"");
this.redirect(url);
break;
case "spellcheck":
if(window.tinyMCE&&tinyMCE.get(this.env.composebody)){
tinyMCE.execCommand("mceSpellCheck",true);
}else{
if(this.env.spellcheck&&this.env.spellcheck.spellCheck&&this.spellcheck_ready){
this.env.spellcheck.spellCheck();
this.set_spellcheck_state("checking");
}
}
break;
case "savedraft":
self.clearTimeout(this.save_timer);
if(!this.gui_objects.messageform){
break;
}
if(!this.env.drafts_mailbox||this.cmp_hash==this.compose_field_hash()){
break;
}
this.set_busy(true,"savingmessage");
var _57=this.gui_objects.messageform;
_57.target="savetarget";
_57._draft.value="1";
_57.submit();
break;
case "send":
if(!this.gui_objects.messageform){
break;
}
if(!this.check_compose_input()){
break;
}
self.clearTimeout(this.save_timer);
this.set_busy(true,"sendingmessage");
var _57=this.gui_objects.messageform;
_57.target="savetarget";
_57._draft.value="";
_57.submit();
clearTimeout(this.request_timer);
break;
case "add-attachment":
this.show_attachment_form(true);
case "send-attachment":
self.clearTimeout(this.save_timer);
this.upload_file(_46);
break;
case "remove-attachment":
this.remove_attachment(_46);
break;
case "reply-all":
case "reply":
var uid;
if(uid=this.get_single_uid()){
this.goto_url("compose","_reply_uid="+uid+"&_mbox="+urlencode(this.env.mailbox)+(_45=="reply-all"?"&_all=1":""),true);
}
break;
case "forward":
var uid;
if(uid=this.get_single_uid()){
this.goto_url("compose","_forward_uid="+uid+"&_mbox="+urlencode(this.env.mailbox),true);
}
break;
case "print":
var uid;
if(uid=this.get_single_uid()){
_1.printwin=window.open(this.env.comm_path+"&_action=print&_uid="+uid+"&_mbox="+urlencode(this.env.mailbox)+(this.env.safemode?"&_safe=1":""));
if(this.printwin){
window.setTimeout(function(){
_1.printwin.focus();
},20);
if(this.env.action!="show"){
this.mark_message("read",uid);
}
}
}
break;
case "viewsource":
var uid;
if(uid=this.get_single_uid()){
_1.sourcewin=window.open(this.env.comm_path+"&_action=viewsource&_uid="+uid+"&_mbox="+urlencode(this.env.mailbox));
if(this.sourcewin){
window.setTimeout(function(){
_1.sourcewin.focus();
},20);
}
}
break;
case "download":
var uid;
if(uid=this.get_single_uid()){
this.goto_url("viewsource","&_uid="+uid+"&_mbox="+urlencode(this.env.mailbox)+"&_save=1");
}
break;
case "add-contact":
this.add_contact(_46);
break;
case "search":
if(!_46&&this.gui_objects.qsearchbox){
_46=this.gui_objects.qsearchbox.value;
}
if(_46){
this.qsearch(_46);
break;
}
case "reset-search":
var s=this.env.search_request;
this.reset_qsearch();
if(s&&this.env.mailbox){
this.list_mailbox(this.env.mailbox);
}else{
if(s&&this.task=="addressbook"){
this.list_contacts(this.env.source);
}
}
break;
case "import":
if(this.env.action=="import"&&this.gui_objects.importform){
var _59=document.getElementById("rcmimportfile");
if(_59&&!_59.value){
alert(this.get_label("selectimportfile"));
break;
}
this.gui_objects.importform.submit();
this.set_busy(true,"importwait");
this.lock_form(this.gui_objects.importform,true);
}else{
this.goto_url("import");
}
break;
case "export":
if(this.contact_list.rowcount>0){
var _5a=(this.env.source?"_source="+urlencode(this.env.source)+"&":"");
if(this.env.search_request){
_5a+="_search="+this.env.search_request;
}
this.goto_url("export",_5a);
}
break;
case "collapse-folder":
if(_46){
this.collapse_folder(_46);
}
break;
case "preferences":
this.goto_url("");
break;
case "identities":
this.goto_url("identities");
break;
case "delete-identity":
this.delete_identity();
case "folders":
this.goto_url("folders");
break;
case "subscribe":
this.subscribe_folder(_46);
break;
case "unsubscribe":
this.unsubscribe_folder(_46);
break;
case "create-folder":
this.create_folder(_46);
break;
case "rename-folder":
this.rename_folder(_46);
break;
case "delete-folder":
this.delete_folder(_46);
break;
}
this.triggerEvent("after"+_45,_46);
return obj?false:true;
};
this.enable_command=function(){
var _5b=arguments;
if(!_5b.length){
return -1;
}
var _5c;
var _5d=_5b[_5b.length-1];
for(var n=0;n<_5b.length-1;n++){
_5c=_5b[n];
this.commands[_5c]=_5d;
this.set_button(_5c,(_5d?"act":"pas"));
}
return true;
};
this.set_busy=function(a,_60){
if(a&&_60){
var msg=this.get_label(_60);
if(msg==_60){
msg="Loading...";
}
this.display_message(msg,"loading",true);
}else{
if(!a){
this.hide_message();
}
}
this.busy=a;
if(this.gui_objects.editform){
this.lock_form(this.gui_objects.editform,a);
}
if(this.request_timer){
clearTimeout(this.request_timer);
}
if(a&&this.env.request_timeout){
this.request_timer=window.setTimeout(function(){
_1.request_timed_out();
},this.env.request_timeout*1000);
}
};
this.get_label=function(_62,_63){
if(_63&&this.labels[_63+"."+_62]){
return this.labels[_63+"."+_62];
}else{
if(this.labels[_62]){
return this.labels[_62];
}else{
return _62;
}
}
};
this.gettext=this.get_label;
this.switch_task=function(_64){
if(this.task===_64&&_64!="mail"){
return;
}
var url=this.get_task_url(_64);
if(_64=="mail"){
url+="&_mbox=INBOX";
}
this.redirect(url);
};
this.get_task_url=function(_66,url){
if(!url){
url=this.env.comm_path;
}
return url.replace(/_task=[a-z]+/,"_task="+_66);
};
this.request_timed_out=function(){
this.set_busy(false);
this.display_message("Request timed out!","error");
};
this.reload=function(_68){
if(this.env.framed&&parent.rcmail){
parent.rcmail.reload(_68);
}else{
if(_68){
window.setTimeout(function(){
rcmail.reload();
},_68);
}else{
if(window.location){
location.href=this.env.comm_path;
}
}
}
};
this.doc_mouse_up=function(e){
var _6a,_6b,li;
if(this.message_list){
if(!rcube_mouse_is_over(e,this.message_list.list)){
this.message_list.blur();
}
_6b=this.message_list;
_6a=this.env.mailboxes;
}else{
if(this.contact_list){
if(!rcube_mouse_is_over(e,this.contact_list.list)){
this.contact_list.blur();
}
_6b=this.contact_list;
_6a=this.env.address_sources;
}else{
if(this.ksearch_value){
this.ksearch_blur();
}
}
}
if(this.drag_active&&_6a&&this.env.last_folder_target){
$(this.get_folder_li(this.env.last_folder_target)).removeClass("droptarget");
this.command("moveto",_6a[this.env.last_folder_target].id);
this.env.last_folder_target=null;
_6b.draglayer.hide();
}
if(this.buttons_sel){
for(var id in this.buttons_sel){
if(typeof id!="function"){
this.button_out(this.buttons_sel[id],id);
}
}
this.buttons_sel={};
}
};
this.drag_start=function(_6e){
var _6f=this.task=="mail"?this.env.mailboxes:this.env.address_sources;
this.drag_active=true;
if(this.preview_timer){
clearTimeout(this.preview_timer);
}
if(this.gui_objects.folderlist&&_6f){
this.initialBodyScrollTop=bw.ie?0:window.pageYOffset;
this.initialListScrollTop=this.gui_objects.folderlist.parentNode.scrollTop;
var li,pos,_6e,_72;
_6e=$(this.gui_objects.folderlist);
pos=_6e.offset();
this.env.folderlist_coords={x1:pos.left,y1:pos.top,x2:pos.left+_6e.width(),y2:pos.top+_6e.height()};
this.env.folder_coords=new Array();
for(var k in _6f){
if(li=this.get_folder_li(k)){
if(_72=li.firstChild.offsetHeight){
pos=$(li.firstChild).offset();
this.env.folder_coords[k]={x1:pos.left,y1:pos.top,x2:pos.left+li.firstChild.offsetWidth,y2:pos.top+_72,on:0};
}
}
}
}
};
this.drag_end=function(e){
this.drag_active=false;
this.env.last_folder_target=null;
if(this.folder_auto_timer){
window.clearTimeout(this.folder_auto_timer);
this.folder_auto_timer=null;
this.folder_auto_expand=null;
}
if(this.gui_objects.folderlist&&this.env.folder_coords){
for(var k in this.env.folder_coords){
if(this.env.folder_coords[k].on){
$(this.get_folder_li(k)).removeClass("droptarget");
}
}
}
};
this.drag_move=function(e){
if(this.gui_objects.folderlist&&this.env.folder_coords){
var _77=bw.ie?-document.documentElement.scrollTop:this.initialBodyScrollTop;
var _78=this.initialListScrollTop-this.gui_objects.folderlist.parentNode.scrollTop;
var _79=-_78-_77;
var li,div,pos,_7d;
_7d=rcube_event.get_mouse_pos(e);
pos=this.env.folderlist_coords;
_7d.y+=_79;
if(_7d.x<pos.x1||_7d.x>=pos.x2||_7d.y<pos.y1||_7d.y>=pos.y2){
if(this.env.last_folder_target){
$(this.get_folder_li(this.env.last_folder_target)).removeClass("droptarget");
this.env.folder_coords[this.env.last_folder_target].on=0;
this.env.last_folder_target=null;
}
return;
}
for(var k in this.env.folder_coords){
pos=this.env.folder_coords[k];
if(_7d.x>=pos.x1&&_7d.x<pos.x2&&_7d.y>=pos.y1&&_7d.y<pos.y2&&this.check_droptarget(k)){
li=this.get_folder_li(k);
div=$(li.getElementsByTagName("div")[0]);
if(div.hasClass("collapsed")){
if(this.folder_auto_timer){
window.clearTimeout(this.folder_auto_timer);
}
this.folder_auto_expand=k;
this.folder_auto_timer=window.setTimeout(function(){
rcmail.command("collapse-folder",rcmail.folder_auto_expand);
rcmail.drag_start(null);
},1000);
}else{
if(this.folder_auto_timer){
window.clearTimeout(this.folder_auto_timer);
this.folder_auto_timer=null;
this.folder_auto_expand=null;
}
}
$(li).addClass("droptarget");
this.env.last_folder_target=k;
this.env.folder_coords[k].on=1;
}else{
if(pos.on){
$(this.get_folder_li(k)).removeClass("droptarget");
this.env.folder_coords[k].on=0;
}
}
}
}
};
this.collapse_folder=function(id){
var div;
if((li=this.get_folder_li(id))&&(div=$(li.getElementsByTagName("div")[0]))&&(div.hasClass("collapsed")||div.hasClass("expanded"))){
var ul=$(li.getElementsByTagName("ul")[0]);
if(div.hasClass("collapsed")){
ul.show();
div.removeClass("collapsed").addClass("expanded");
var reg=new RegExp("&"+urlencode(id)+"&");
this.set_env("collapsed_folders",this.env.collapsed_folders.replace(reg,""));
}else{
ul.hide();
div.removeClass("expanded").addClass("collapsed");
this.set_env("collapsed_folders",this.env.collapsed_folders+"&"+urlencode(id)+"&");
if(this.env.mailbox.indexOf(id+this.env.delimiter)==0){
this.command("list",id);
}
}
if((bw.ie6||bw.ie7)&&li.nextSibling&&(li.nextSibling.getElementsByTagName("ul").length>0)&&li.nextSibling.getElementsByTagName("ul")[0].style&&(li.nextSibling.getElementsByTagName("ul")[0].style.display!="none")){
li.nextSibling.getElementsByTagName("ul")[0].style.display="none";
li.nextSibling.getElementsByTagName("ul")[0].style.display="";
}
this.http_post("save-pref","_name=collapsed_folders&_value="+urlencode(this.env.collapsed_folders));
this.set_unread_count_display(id,false);
}
};
this.click_on_list=function(e){
if(this.gui_objects.qsearchbox){
this.gui_objects.qsearchbox.blur();
}
if(this.message_list){
this.message_list.focus();
}else{
if(this.contact_list){
this.contact_list.focus();
}
}
return rcube_event.get_button(e)==2?true:rcube_event.cancel(e);
};
this.msglist_select=function(_84){
if(this.preview_timer){
clearTimeout(this.preview_timer);
}
var _85=_84.selection.length==1;
if(this.env.mailbox==this.env.drafts_mailbox){
this.enable_command("reply","reply-all","forward",false);
this.enable_command("show","print","open","edit","download","viewsource",_85);
this.enable_command("delete","moveto","mark",(_84.selection.length>0?true:false));
}else{
this.enable_command("show","reply","reply-all","forward","print","edit","open","download","viewsource",_85);
this.enable_command("delete","moveto","mark",(_84.selection.length>0?true:false));
}
if(_85&&this.env.contentframe&&!_84.multi_selecting){
this.preview_timer=window.setTimeout(function(){
_1.msglist_get_preview();
},200);
}else{
if(this.env.contentframe){
this.show_contentframe(false);
}
}
};
this.msglist_dbl_click=function(_86){
if(this.preview_timer){
clearTimeout(this.preview_timer);
}
var uid=_86.get_single_selection();
if(uid&&this.env.mailbox==this.env.drafts_mailbox){
this.goto_url("compose","_draft_uid="+uid+"&_mbox="+urlencode(this.env.mailbox),true);
}else{
if(uid){
this.show_message(uid,false,false);
}
}
};
this.msglist_keypress=function(_88){
if(_88.key_pressed==_88.ENTER_KEY){
this.command("show");
}else{
if(_88.key_pressed==_88.DELETE_KEY){
this.command("delete");
}else{
if(_88.key_pressed==_88.BACKSPACE_KEY){
this.command("delete");
}else{
_88.shiftkey=false;
}
}
}
};
this.msglist_get_preview=function(){
var uid=this.get_single_uid();
if(uid&&this.env.contentframe&&!this.drag_active){
this.show_message(uid,false,true);
}else{
if(this.env.contentframe){
this.show_contentframe(false);
}
}
};
this.check_droptarget=function(id){
if(this.task=="mail"){
return (this.env.mailboxes[id]&&this.env.mailboxes[id].id!=this.env.mailbox&&!this.env.mailboxes[id].virtual);
}else{
if(this.task=="addressbook"){
return (id!=this.env.source&&this.env.address_sources[id]&&!this.env.address_sources[id].readonly);
}else{
if(this.task=="settings"){
return (id!=this.env.folder);
}
}
}
};
this.show_message=function(id,_8c,_8d){
if(!id){
return;
}
var _8e="";
var _8f=_8d?"preview":"show";
var _90=window;
if(_8d&&this.env.contentframe&&window.frames&&window.frames[this.env.contentframe]){
_90=window.frames[this.env.contentframe];
_8e="&_framed=1";
}
if(_8c){
_8e="&_safe=1";
}
if(this.env.search_request){
_8e+="&_search="+this.env.search_request;
}
var url="&_action="+_8f+"&_uid="+id+"&_mbox="+urlencode(this.env.mailbox)+_8e;
if(_8f=="preview"&&String(_90.location.href).indexOf(url)>=0){
this.show_contentframe(true);
}else{
this.set_busy(true,"loading");
_90.location.href=this.env.comm_path+url;
if(_8f=="preview"&&this.message_list&&this.message_list.rows[id]&&this.message_list.rows[id].unread){
this.set_message(id,"unread",false);
if(this.env.unread_counts[this.env.mailbox]){
this.env.unread_counts[this.env.mailbox]-=1;
this.set_unread_count(this.env.mailbox,this.env.unread_counts[this.env.mailbox],this.env.mailbox=="INBOX");
}
}
}
};
this.show_contentframe=function(_92){
var frm;
if(this.env.contentframe&&(frm=$("#"+this.env.contentframe))&&frm.length){
if(!_92&&window.frames[this.env.contentframe]){
if(window.frames[this.env.contentframe].location.href.indexOf(this.env.blankpage)<0){
window.frames[this.env.contentframe].location.href=this.env.blankpage;
}
}else{
if(!bw.safari&&!bw.konq){
frm[_92?"show":"hide"]();
}
}
}
if(!_92&&this.busy){
this.set_busy(false);
}
};
this.list_page=function(_94){
if(_94=="next"){
_94=this.env.current_page+1;
}
if(_94=="last"){
_94=this.env.pagecount;
}
if(_94=="prev"&&this.env.current_page>1){
_94=this.env.current_page-1;
}
if(_94=="first"&&this.env.current_page>1){
_94=1;
}
if(_94>0&&_94<=this.env.pagecount){
this.env.current_page=_94;
if(this.task=="mail"){
this.list_mailbox(this.env.mailbox,_94);
}else{
if(this.task=="addressbook"){
this.list_contacts(this.env.source,_94);
}
}
}
};
this.filter_mailbox=function(_95){
var _96;
if(this.gui_objects.qsearchbox){
_96=this.gui_objects.qsearchbox.value;
}
this.message_list.clear();
this.env.current_page=1;
this.set_busy(true,"searching");
this.http_request("search","_filter="+_95+(_96?"&_q="+urlencode(_96):"")+(this.env.mailbox?"&_mbox="+urlencode(this.env.mailbox):""),true);
};
this.list_mailbox=function(_97,_98,_99){
var _9a="";
var _9b=window;
if(!_97){
_97=this.env.mailbox;
}
if(_99){
_9a+="&_sort="+_99;
}
if(this.env.search_request){
_9a+="&_search="+this.env.search_request;
}
if(!_98&&this.env.mailbox!=_97){
_98=1;
this.env.current_page=_98;
this.show_contentframe(false);
}
if(_97!=this.env.mailbox||(_97==this.env.mailbox&&!_98&&!_99)){
_9a+="&_refresh=1";
}
this.last_selected=0;
if(this.message_list){
this.message_list.clear_selection();
}
this.select_folder(_97,this.env.mailbox);
this.env.mailbox=_97;
if(this.gui_objects.messagelist){
this.list_mailbox_remote(_97,_98,_9a);
return;
}
if(this.env.contentframe&&window.frames&&window.frames[this.env.contentframe]){
_9b=window.frames[this.env.contentframe];
_9a+="&_framed=1";
}
if(_97){
this.set_busy(true,"loading");
_9b.location.href=this.env.comm_path+"&_mbox="+urlencode(_97)+(_98?"&_page="+_98:"")+_9a;
}
};
this.list_mailbox_remote=function(_9c,_9d,_9e){
this.message_list.clear();
var url="_mbox="+urlencode(_9c)+(_9d?"&_page="+_9d:"");
this.set_busy(true,"loading");
this.http_request("list",url+_9e,true);
};
this.expunge_mailbox=function(_a0){
var _a1=false;
var _a2="";
if(_a0==this.env.mailbox){
_a1=true;
this.set_busy(true,"loading");
_a2="&_reload=1";
}
var url="_mbox="+urlencode(_a0);
this.http_post("expunge",url+_a2,_a1);
};
this.purge_mailbox=function(_a4){
var _a5=false;
var _a6="";
if(!confirm(this.get_label("purgefolderconfirm"))){
return false;
}
if(_a4==this.env.mailbox){
_a5=true;
this.set_busy(true,"loading");
_a6="&_reload=1";
}
var url="_mbox="+urlencode(_a4);
this.http_post("purge",url+_a6,_a5);
return true;
};
this.purge_mailbox_test=function(){
return (this.env.messagecount&&(this.env.mailbox==this.env.trash_mailbox||this.env.mailbox==this.env.junk_mailbox||this.env.mailbox.match("^"+RegExp.escape(this.env.trash_mailbox)+RegExp.escape(this.env.delimiter))||this.env.mailbox.match("^"+RegExp.escape(this.env.junk_mailbox)+RegExp.escape(this.env.delimiter))));
};
this.set_message_icon=function(uid){
var _a9;
var _aa=this.message_list.rows;
if(!_aa[uid]){
return false;
}
if(_aa[uid].deleted&&this.env.deletedicon){
_a9=this.env.deletedicon;
}else{
if(_aa[uid].replied&&this.env.repliedicon){
if(_aa[uid].forwarded&&this.env.forwardedrepliedicon){
_a9=this.env.forwardedrepliedicon;
}else{
_a9=this.env.repliedicon;
}
}else{
if(_aa[uid].forwarded&&this.env.forwardedicon){
_a9=this.env.forwardedicon;
}else{
if(_aa[uid].unread&&this.env.unreadicon){
_a9=this.env.unreadicon;
}else{
if(this.env.messageicon){
_a9=this.env.messageicon;
}
}
}
}
}
if(_a9&&_aa[uid].icon){
_aa[uid].icon.src=_a9;
}
_a9="";
if(_aa[uid].flagged&&this.env.flaggedicon){
_a9=this.env.flaggedicon;
}else{
if(!_aa[uid].flagged&&this.env.unflaggedicon){
_a9=this.env.unflaggedicon;
}
}
if(_aa[uid].flagged_icon&&_a9){
_aa[uid].flagged_icon.src=_a9;
}
};
this.set_message_status=function(uid,_ac,_ad){
var _ae=this.message_list.rows;
if(!_ae[uid]){
return false;
}
if(_ac=="unread"){
_ae[uid].unread=_ad;
}else{
if(_ac=="deleted"){
_ae[uid].deleted=_ad;
}else{
if(_ac=="replied"){
_ae[uid].replied=_ad;
}else{
if(_ac=="forwarded"){
_ae[uid].forwarded=_ad;
}else{
if(_ac=="flagged"){
_ae[uid].flagged=_ad;
}
}
}
}
}
this.env.messages[uid]=_ae[uid];
};
this.set_message=function(uid,_b0,_b1){
var _b2=this.message_list.rows;
if(!_b2[uid]){
return false;
}
if(_b0){
this.set_message_status(uid,_b0,_b1);
}
var _b3=$(_b2[uid].obj);
if(_b2[uid].unread&&_b2[uid].classname.indexOf("unread")<0){
_b2[uid].classname+=" unread";
_b3.addClass("unread");
}else{
if(!_b2[uid].unread&&_b2[uid].classname.indexOf("unread")>=0){
_b2[uid].classname=_b2[uid].classname.replace(/\s*unread/,"");
_b3.removeClass("unread");
}
}
if(_b2[uid].deleted&&_b2[uid].classname.indexOf("deleted")<0){
_b2[uid].classname+=" deleted";
_b3.addClass("deleted");
}else{
if(!_b2[uid].deleted&&_b2[uid].classname.indexOf("deleted")>=0){
_b2[uid].classname=_b2[uid].classname.replace(/\s*deleted/,"");
_b3.removeClass("deleted");
}
}
if(_b2[uid].flagged&&_b2[uid].classname.indexOf("flagged")<0){
_b2[uid].classname+=" flagged";
_b3.addClass("flagged");
}else{
if(!_b2[uid].flagged&&_b2[uid].classname.indexOf("flagged")>=0){
_b2[uid].classname=_b2[uid].classname.replace(/\s*flagged/,"");
_b3.removeClass("flagged");
}
}
this.set_message_icon(uid);
};
this.move_messages=function(_b4){
if(!_b4||_b4==this.env.mailbox||(!this.env.uid&&(!this.message_list||!this.message_list.get_selection().length))){
return;
}
var _b5=false;
var _b6="&_target_mbox="+urlencode(_b4)+"&_from="+(this.env.action?this.env.action:"");
if(this.env.action=="show"){
_b5=true;
this.set_busy(true,"movingmessage");
}else{
this.show_contentframe(false);
}
this.enable_command("reply","reply-all","forward","delete","mark","print","open","edit","viewsource","download",false);
this._with_selected_messages("moveto",_b5,_b6);
};
this.delete_messages=function(){
var _b7=this.message_list?this.message_list.get_selection():new Array();
if(!this.env.uid&&!_b7.length){
return;
}
if(this.env.flag_for_deletion){
this.mark_message("delete");
}else{
if(!this.env.trash_mailbox||String(this.env.mailbox).toLowerCase()==String(this.env.trash_mailbox).toLowerCase()){
this.permanently_remove_messages();
}else{
if(this.message_list&&this.message_list.shiftkey){
if(confirm(this.get_label("deletemessagesconfirm"))){
this.permanently_remove_messages();
}
}else{
this.move_messages(this.env.trash_mailbox);
}
}
}
};
this.permanently_remove_messages=function(){
if(!this.env.uid&&(!this.message_list||!this.message_list.get_selection().length)){
return;
}
this.show_contentframe(false);
this._with_selected_messages("delete",false,"&_from="+(this.env.action?this.env.action:""));
};
this._with_selected_messages=function(_b8,_b9,_ba,_bb){
var _bc=new Array();
if(this.env.uid){
_bc[0]=this.env.uid;
}else{
var _bd=this.message_list.get_selection();
var _be=this.message_list.rows;
var id;
for(var n=0;n<_bd.length;n++){
id=_bd[n];
_bc[_bc.length]=id;
this.message_list.remove_row(id,(this.env.display_next&&n==_bd.length-1));
}
if(!this.env.display_next){
this.message_list.clear_selection();
}
}
if(this.env.search_request){
_ba+="&_search="+this.env.search_request;
}
if(this.env.display_next&&this.env.next_uid){
_ba+="&_next_uid="+this.env.next_uid;
}
this.http_post(_b8,"_uid="+_bc.join(",")+"&_mbox="+urlencode(this.env.mailbox)+_ba,_b9);
};
this.mark_message=function(_c1,uid){
var _c3=new Array();
var _c4=new Array();
var _c5=this.message_list?this.message_list.get_selection():new Array();
if(uid){
_c3[0]=uid;
}else{
if(this.env.uid){
_c3[0]=this.env.uid;
}else{
if(this.message_list){
for(var n=0;n<_c5.length;n++){
_c3[_c3.length]=_c5[n];
}
}
}
}
if(!this.message_list){
_c4=_c3;
}else{
for(var id,n=0;n<_c3.length;n++){
id=_c3[n];
if((_c1=="read"&&this.message_list.rows[id].unread)||(_c1=="unread"&&!this.message_list.rows[id].unread)||(_c1=="delete"&&!this.message_list.rows[id].deleted)||(_c1=="undelete"&&this.message_list.rows[id].deleted)||(_c1=="flagged"&&!this.message_list.rows[id].flagged)||(_c1=="unflagged"&&this.message_list.rows[id].flagged)){
_c4[_c4.length]=id;
}
}
}
if(!_c4.length){
return;
}
switch(_c1){
case "read":
case "unread":
this.toggle_read_status(_c1,_c4);
break;
case "delete":
case "undelete":
this.toggle_delete_status(_c4);
break;
case "flagged":
case "unflagged":
this.toggle_flagged_status(_c1,_c3);
break;
}
};
this.toggle_read_status=function(_c8,_c9){
for(var i=0;i<_c9.length;i++){
this.set_message(_c9[i],"unread",(_c8=="unread"?true:false));
}
this.http_post("mark","_uid="+_c9.join(",")+"&_flag="+_c8);
};
this.toggle_flagged_status=function(_cb,_cc){
for(var i=0;i<_cc.length;i++){
this.set_message(_cc[i],"flagged",(_cb=="flagged"?true:false));
}
this.http_post("mark","_uid="+_cc.join(",")+"&_flag="+_cb);
};
this.toggle_delete_status=function(_ce){
var _cf=this.message_list?this.message_list.rows:new Array();
if(_ce.length==1){
if(!_cf.length||(_cf[_ce[0]]&&!_cf[_ce[0]].deleted)){
this.flag_as_deleted(_ce);
}else{
this.flag_as_undeleted(_ce);
}
return true;
}
var _d0=true;
for(var i=0;i<_ce.length;i++){
uid=_ce[i];
if(_cf[uid]){
if(!_cf[uid].deleted){
_d0=false;
break;
}
}
}
if(_d0){
this.flag_as_undeleted(_ce);
}else{
this.flag_as_deleted(_ce);
}
return true;
};
this.flag_as_undeleted=function(_d2){
for(var i=0;i<_d2.length;i++){
this.set_message(_d2[i],"deleted",false);
}
this.http_post("mark","_uid="+_d2.join(",")+"&_flag=undelete");
return true;
};
this.flag_as_deleted=function(_d4){
var _d5="";
var _d6=new Array();
var _d7=this.message_list?this.message_list.rows:new Array();
for(var i=0;i<_d4.length;i++){
uid=_d4[i];
if(_d7[uid]){
if(_d7[uid].unread){
_d6[_d6.length]=uid;
}
if(this.env.skip_deleted){
this.message_list.remove_row(uid,(this.env.display_next&&i==this.message_list.selection.length-1));
}else{
this.set_message(uid,"deleted",true);
}
}
}
if(this.env.skip_deleted&&!this.env.display_next&&this.message_list){
this.message_list.clear_selection();
}
_d5="&_from="+(this.env.action?this.env.action:"");
if(_d6.length){
_d5+="&_ruid="+_d6.join(",");
}
if(this.env.skip_deleted){
if(this.env.search_request){
_d5+="&_search="+this.env.search_request;
}
if(this.env.display_next&&this.env.next_uid){
_d5+="&_next_uid="+this.env.next_uid;
}
}
this.http_post("mark","_uid="+_d4.join(",")+"&_flag=delete"+_d5);
return true;
};
this.flag_deleted_as_read=function(_d9){
var _da;
var _db=this.message_list?this.message_list.rows:new Array();
var str=String(_d9);
var _dd=new Array();
_dd=str.split(",");
for(var uid,i=0;i<_dd.length;i++){
uid=_dd[i];
if(_db[uid]){
this.set_message(uid,"unread",false);
}
}
};
this.login_user_keyup=function(e){
var key=rcube_event.get_keycode(e);
var _e2=$("#rcmloginpwd");
if(key==13&&_e2.length&&!_e2.val()){
_e2.focus();
return rcube_event.cancel(e);
}
return true;
};
this.check_compose_input=function(){
var _e3=$("[name='_to']");
var _e4=$("[name='_cc']");
var _e5=$("[name='_bcc']");
var _e6=$("[name='_from']");
var _e7=$("[name='_subject']");
var _e8=$("[name='_message']");
if(_e6.attr("type")=="text"&&!rcube_check_email(_e6.val(),true)){
alert(this.get_label("nosenderwarning"));
_e6.focus();
return false;
}
var _e9=_e3.val()?_e3.val():(_e4.val()?_e4.val():_e5.val());
if(!rcube_check_email(_e9.replace(/^\s+/,"").replace(/[\s,;]+$/,""),true)){
alert(this.get_label("norecipientwarning"));
_e3.focus();
return false;
}
for(var key in this.env.attachments){
if(typeof this.env.attachments[key]=="object"&&!this.env.attachments[key].complete){
alert(this.get_label("notuploadedwarning"));
return false;
}
}
if(_e7.val()==""){
var _eb=prompt(this.get_label("nosubjectwarning"),this.get_label("nosubject"));
if(!_eb&&_eb!==""){
_e7.focus();
return false;
}else{
_e7.val((_eb?_eb:this.get_label("nosubject")));
}
}
if((!window.tinyMCE||!tinyMCE.get(this.env.composebody))&&_e8.val()==""&&!confirm(this.get_label("nobodywarning"))){
_e8.focus();
return false;
}else{
if(window.tinyMCE&&tinyMCE.get(this.env.composebody)&&!tinyMCE.get(this.env.composebody).getContent()&&!confirm(this.get_label("nobodywarning"))){
tinyMCE.get(this.env.composebody).focus();
return false;
}
}
this.stop_spellchecking();
if(window.tinyMCE&&tinyMCE.get(this.env.composebody)){
tinyMCE.triggerSave();
}
return true;
};
this.stop_spellchecking=function(){
if(this.env.spellcheck&&!this.spellcheck_ready){
$(this.env.spellcheck.spell_span).trigger("click");
this.set_spellcheck_state("ready");
}
};
this.display_spellcheck_controls=function(vis){
if(this.env.spellcheck){
if(!vis){
this.stop_spellchecking();
}
$(this.env.spellcheck.spell_container).css("visibility",vis?"visible":"hidden");
}
};
this.set_spellcheck_state=function(s){
this.spellcheck_ready=(s=="ready"||s=="no_error_found");
this.enable_command("spellcheck",this.spellcheck_ready);
};
this.set_draft_id=function(id){
$("input[name='_draft_saveid']").val(id);
};
this.auto_save_start=function(){
if(this.env.draft_autosave){
this.save_timer=self.setTimeout(function(){
_1.command("savedraft");
},this.env.draft_autosave*1000);
}
this.busy=false;
};
this.compose_field_hash=function(_ef){
var _f0=$("[name='_to']").val();
var _f1=$("[name='_cc']").val();
var _f2=$("[name='_bcc']").val();
var _f3=$("[name='_subject']").val();
var str="";
if(_f0){
str+=_f0+":";
}
if(_f1){
str+=_f1+":";
}
if(_f2){
str+=_f2+":";
}
if(_f3){
str+=_f3+":";
}
var _f5=tinyMCE.get(this.env.composebody);
if(_f5){
str+=_f5.getContent();
}else{
str+=$("[name='_message']").val();
}
if(this.env.attachments){
for(var _f6 in this.env.attachments){
str+=_f6;
}
}
if(_ef){
this.cmp_hash=str;
}
return str;
};
this.change_identity=function(obj){
if(!obj||!obj.options){
return false;
}
var id=obj.options[obj.selectedIndex].value;
var _f9=$("[name='_message']");
var _fa=_f9.val();
var _fb=($("input[name='_is_html']").val()=="1");
var sig,p,len;
if(!this.env.identity){
this.env.identity=id;
}
if(!_fb){
if(this.env.identity&&this.env.signatures&&this.env.signatures[this.env.identity]){
if(this.env.signatures[this.env.identity]["is_html"]){
sig=this.env.signatures[this.env.identity]["plain_text"];
}else{
sig=this.env.signatures[this.env.identity]["text"];
}
if(sig.indexOf("-- ")!=0){
sig="-- \n"+sig;
}
p=_fa.lastIndexOf(sig);
if(p>=0){
_fa=_fa.substring(0,p-1)+_fa.substring(p+sig.length,_fa.length);
}
}
_fa=_fa.replace(/[\r\n]+$/,"");
len=_fa.length;
if(this.env.signatures&&this.env.signatures[id]){
sig=this.env.signatures[id]["text"];
if(this.env.signatures[id]["is_html"]){
sig=this.env.signatures[id]["plain_text"];
}
if(sig.indexOf("-- ")!=0){
sig="-- \n"+sig;
}
_fa+="\n\n"+sig;
if(len){
len+=1;
}
}
}else{
var _ff=tinyMCE.get(this.env.composebody);
if(this.env.signatures){
var _100=_ff.dom.get("_rc_sig");
var _101="";
var _102=true;
if(!_100){
if(bw.ie){
_ff.getBody().appendChild(_ff.getDoc().createElement("br"));
}
_100=_ff.getDoc().createElement("div");
_100.setAttribute("id","_rc_sig");
_ff.getBody().appendChild(_100);
}
if(this.env.signatures[id]){
_101=this.env.signatures[id]["text"];
_102=this.env.signatures[id]["is_html"];
if(_101){
if(_102&&this.env.signatures[id]["plain_text"].indexOf("-- ")!=0){
_101="<p>-- </p>"+_101;
}else{
if(!_102&&_101.indexOf("-- ")!=0){
_101="-- \n"+_101;
}
}
}
}
if(_102){
_100.innerHTML=_101;
}else{
_100.innerHTML="<pre>"+_101+"</pre>";
}
}
}
_f9.val(_fa);
if(!_fb){
this.set_caret_pos(_f9.get(0),len);
}
this.env.identity=id;
return true;
};
this.show_attachment_form=function(a){
if(!this.gui_objects.uploadbox){
return false;
}
var elm,list;
if(elm=this.gui_objects.uploadbox){
if(a&&(list=this.gui_objects.attachmentlist)){
var pos=$(list).offset();
elm.style.top=(pos.top+list.offsetHeight+10)+"px";
elm.style.left=pos.left+"px";
}
elm.style.visibility=a?"visible":"hidden";
}
try{
if(!a&&this.gui_objects.attachmentform!=this.gui_objects.messageform){
this.gui_objects.attachmentform.reset();
}
}
catch(e){
}
return true;
};
this.upload_file=function(form){
if(!form){
return false;
}
var send=false;
for(var n=0;n<form.elements.length;n++){
if(form.elements[n].type=="file"&&form.elements[n].value){
send=true;
break;
}
}
if(send){
var ts=new Date().getTime();
var _10b="rcmupload"+ts;
if(document.all){
var html="<iframe name=\""+_10b+"\" src=\"program/blank.gif\" style=\"width:0;height:0;visibility:hidden;\"></iframe>";
document.body.insertAdjacentHTML("BeforeEnd",html);
}else{
var _10d=document.createElement("iframe");
_10d.name=_10b;
_10d.style.border="none";
_10d.style.width=0;
_10d.style.height=0;
_10d.style.visibility="hidden";
document.body.appendChild(_10d);
}
var fr=document.getElementsByName(_10b)[0];
$(fr).bind("load",{ts:ts},function(e){
var _110="";
try{
if(this.contentDocument){
var d=this.contentDocument;
}else{
if(this.contentWindow){
var d=this.contentWindow.document;
}
}
_110=d.childNodes[0].innerHTML;
}
catch(e){
}
if(!String(_110).match(/add2attachment/)&&(!bw.opera||(rcmail.env.uploadframe&&rcmail.env.uploadframe==e.data.ts))){
rcmail.display_message(rcmail.get_label("fileuploaderror"),"error");
rcmail.remove_from_attachment_list(e.data.ts);
}
if(bw.opera){
rcmail.env.uploadframe=e.data.ts;
}
});
form.target=_10b;
form.action=this.env.comm_path+"&_action=upload&_uploadid="+ts;
form.setAttribute("enctype","multipart/form-data");
form.submit();
this.show_attachment_form(false);
var _112=this.get_label("uploading");
if(this.env.loadingicon){
_112="<img src=\""+this.env.loadingicon+"\" alt=\"\" />"+_112;
}
if(this.env.cancelicon){
_112="<a title=\""+this.get_label("cancel")+"\" onclick=\"return rcmail.cancel_attachment_upload('"+ts+"', '"+_10b+"');\" href=\"#cancelupload\"><img src=\""+this.env.cancelicon+"\" alt=\"\" /></a>"+_112;
}
this.add2attachment_list(ts,{name:"",html:_112,complete:false});
}
this.gui_objects.attachmentform=form;
return true;
};
this.add2attachment_list=function(name,att,_115){
if(!this.gui_objects.attachmentlist){
return false;
}
var li=$("<li>").attr("id",name).html(att.html);
var _117;
if(_115&&(_117=document.getElementById(_115))){
li.replaceAll(_117);
}else{
li.appendTo(this.gui_objects.attachmentlist);
}
if(_115&&this.env.attachments[_115]){
delete this.env.attachments[_115];
}
this.env.attachments[name]=att;
return true;
};
this.remove_from_attachment_list=function(name){
if(this.env.attachments[name]){
delete this.env.attachments[name];
}
if(!this.gui_objects.attachmentlist){
return false;
}
var list=this.gui_objects.attachmentlist.getElementsByTagName("li");
for(i=0;i<list.length;i++){
if(list[i].id==name){
this.gui_objects.attachmentlist.removeChild(list[i]);
}
}
};
this.remove_attachment=function(name){
if(name&&this.env.attachments[name]){
this.http_post("remove-attachment","_file="+urlencode(name));
}
return true;
};
this.cancel_attachment_upload=function(name,_11c){
if(!name||!_11c){
return false;
}
this.remove_from_attachment_list(name);
$("iframe[name='"+_11c+"']").remove();
return false;
};
this.add_contact=function(_11d){
if(_11d){
this.http_post("addcontact","_address="+_11d);
}
return true;
};
this.qsearch=function(_11e){
if(_11e!=""){
var _11f="";
if(this.message_list){
this.message_list.clear();
if(this.env.search_mods){
var _120=new Array();
for(var n in this.env.search_mods){
_120.push(n);
}
_11f+="&_headers="+_120.join(",");
}
}else{
if(this.contact_list){
this.contact_list.clear(true);
this.show_contentframe(false);
}
}
if(this.gui_objects.search_filter){
_11f+="&_filter="+this.gui_objects.search_filter.value;
}
this.env.current_page=1;
this.set_busy(true,"searching");
this.http_request("search","_q="+urlencode(_11e)+(this.env.mailbox?"&_mbox="+urlencode(this.env.mailbox):"")+(this.env.source?"&_source="+urlencode(this.env.source):"")+(_11f?_11f:""),true);
}
return true;
};
this.reset_qsearch=function(){
if(this.gui_objects.qsearchbox){
this.gui_objects.qsearchbox.value="";
}
this.env.search_request=null;
return true;
};
this.sent_successfully=function(type,msg){
this.list_mailbox();
this.display_message(msg,type,true);
};
this.ksearch_keypress=function(e,obj){
if(this.ksearch_timer){
clearTimeout(this.ksearch_timer);
}
var _126;
var key=rcube_event.get_keycode(e);
var mod=rcube_event.get_modifier(e);
switch(key){
case 38:
case 40:
if(!this.ksearch_pane){
break;
}
var dir=key==38?1:0;
_126=document.getElementById("rcmksearchSelected");
if(!_126){
_126=this.ksearch_pane.__ul.firstChild;
}
if(_126){
this.ksearch_select(dir?_126.previousSibling:_126.nextSibling);
}
return rcube_event.cancel(e);
case 9:
if(mod==SHIFT_KEY){
break;
}
case 13:
if(this.ksearch_selected===null||!this.ksearch_input||!this.ksearch_value){
break;
}
this.insert_recipient(this.ksearch_selected);
this.ksearch_hide();
return rcube_event.cancel(e);
case 27:
this.ksearch_hide();
break;
case 37:
case 39:
if(mod!=SHIFT_KEY){
return;
}
}
this.ksearch_timer=window.setTimeout(function(){
_1.ksearch_get_results();
},200);
this.ksearch_input=obj;
return true;
};
this.ksearch_select=function(node){
var _12b=$("#rcmksearchSelected");
if(_12b[0]&&node){
_12b.removeAttr("id").removeClass("selected");
}
if(node){
$(node).attr("id","rcmksearchSelected").addClass("selected");
this.ksearch_selected=node._rcm_id;
}
};
this.insert_recipient=function(id){
if(!this.env.contacts[id]||!this.ksearch_input){
return;
}
var _12d=this.ksearch_input.value;
var cpos=this.get_caret_pos(this.ksearch_input);
var p=_12d.lastIndexOf(this.ksearch_value,cpos);
var pre=this.ksearch_input.value.substring(0,p);
var end=this.ksearch_input.value.substring(p+this.ksearch_value.length,this.ksearch_input.value.length);
var _132=this.env.contacts[id]+", ";
this.ksearch_input.value=pre+_132+end;
cpos=p+_132.length;
if(this.ksearch_input.setSelectionRange){
this.ksearch_input.setSelectionRange(cpos,cpos);
}
};
this.ksearch_get_results=function(){
var _133=this.ksearch_input?this.ksearch_input.value:null;
if(_133===null){
return;
}
if(this.ksearch_pane&&this.ksearch_pane.is(":visible")){
this.ksearch_pane.hide();
}
var cpos=this.get_caret_pos(this.ksearch_input);
var p=_133.lastIndexOf(",",cpos-1);
var q=_133.substring(p+1,cpos);
q=q.replace(/(^\s+|\s+$)/g,"");
if(q==this.ksearch_value){
return;
}
var _137=this.ksearch_value;
this.ksearch_value=q;
if(!q.length){
return;
}
if(_137&&_137.length&&this.env.contacts&&!this.env.contacts.length&&q.indexOf(_137)==0){
return;
}
this.display_message(this.get_label("searching"),"loading",true);
this.http_post("autocomplete","_search="+urlencode(q));
};
this.ksearch_query_results=function(_138,_139){
if(this.ksearch_value&&_139!=this.ksearch_value){
return;
}
this.hide_message();
this.env.contacts=_138?_138:[];
this.ksearch_display_results(this.env.contacts);
};
this.ksearch_display_results=function(_13a){
if(_13a.length&&this.ksearch_input){
var p,ul,li;
if(!this.ksearch_pane){
ul=$("<ul>");
this.ksearch_pane=$("<div>").attr("id","rcmKSearchpane").css({position:"absolute","z-index":30000}).append(ul).appendTo(document.body);
this.ksearch_pane.__ul=ul[0];
}
ul=this.ksearch_pane.__ul;
ul.innerHTML="";
for(i=0;i<_13a.length;i++){
li=document.createElement("LI");
li.innerHTML=_13a[i].replace(new RegExp("("+this.ksearch_value+")","ig"),"##$1%%").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/##([^%]+)%%/g,"<b>$1</b>");
li.onmouseover=function(){
_1.ksearch_select(this);
};
li.onmouseup=function(){
_1.ksearch_click(this);
};
li._rcm_id=i;
ul.appendChild(li);
}
$(ul.firstChild).attr("id","rcmksearchSelected").addClass("selected");
this.ksearch_selected=0;
var pos=$(this.ksearch_input).offset();
this.ksearch_pane.css({left:pos.left+"px",top:(pos.top+this.ksearch_input.offsetHeight)+"px"}).show();
}else{
this.ksearch_hide();
}
};
this.ksearch_click=function(node){
if(this.ksearch_input){
this.ksearch_input.focus();
}
this.insert_recipient(node._rcm_id);
this.ksearch_hide();
};
this.ksearch_blur=function(){
if(this.ksearch_timer){
clearTimeout(this.ksearch_timer);
}
this.ksearch_value="";
this.ksearch_input=null;
this.ksearch_hide();
};
this.ksearch_hide=function(){
this.ksearch_selected=null;
if(this.ksearch_pane){
this.ksearch_pane.hide();
}
};
this.contactlist_keypress=function(list){
if(list.key_pressed==list.DELETE_KEY){
this.command("delete");
}
};
this.contactlist_select=function(list){
if(this.preview_timer){
clearTimeout(this.preview_timer);
}
var id,_143,_1=this;
if(id=list.get_single_selection()){
this.preview_timer=window.setTimeout(function(){
_1.load_contact(id,"show");
},200);
}else{
if(this.env.contentframe){
this.show_contentframe(false);
}
}
this.enable_command("compose",list.selection.length>0);
this.enable_command("edit",(id&&this.env.address_sources&&!this.env.address_sources[this.env.source].readonly)?true:false);
this.enable_command("delete",list.selection.length&&this.env.address_sources&&!this.env.address_sources[this.env.source].readonly);
return false;
};
this.list_contacts=function(src,page){
var _146="";
var _147=window;
if(!src){
src=this.env.source;
}
if(page&&this.current_page==page&&src==this.env.source){
return false;
}
if(src!=this.env.source){
page=1;
this.env.current_page=page;
this.reset_qsearch();
}
this.select_folder(src,this.env.source);
this.env.source=src;
if(this.gui_objects.contactslist){
this.list_contacts_remote(src,page);
return;
}
if(this.env.contentframe&&window.frames&&window.frames[this.env.contentframe]){
_147=window.frames[this.env.contentframe];
_146="&_framed=1";
}
if(this.env.search_request){
_146+="&_search="+this.env.search_request;
}
this.set_busy(true,"loading");
_147.location.href=this.env.comm_path+(src?"&_source="+urlencode(src):"")+(page?"&_page="+page:"")+_146;
};
this.list_contacts_remote=function(src,page){
this.contact_list.clear(true);
this.show_contentframe(false);
this.enable_command("delete","compose",false);
var url=(src?"_source="+urlencode(src):"")+(page?(src?"&":"")+"_page="+page:"");
this.env.source=src;
if(this.env.search_request){
url+="&_search="+this.env.search_request;
}
this.set_busy(true,"loading");
this.http_request("list",url,true);
};
this.load_contact=function(cid,_14c,_14d){
var _14e="";
var _14f=window;
if(this.env.contentframe&&window.frames&&window.frames[this.env.contentframe]){
_14e="&_framed=1";
_14f=window.frames[this.env.contentframe];
this.show_contentframe(true);
}else{
if(_14d){
return false;
}
}
if(_14c&&(cid||_14c=="add")&&!this.drag_active){
this.set_busy(true);
_14f.location.href=this.env.comm_path+"&_action="+_14c+"&_source="+urlencode(this.env.source)+"&_cid="+urlencode(cid)+_14e;
}
return true;
};
this.copy_contact=function(cid,to){
if(!cid){
cid=this.contact_list.get_selection().join(",");
}
if(to!=this.env.source&&cid&&this.env.address_sources[to]&&!this.env.address_sources[to].readonly){
this.http_post("copy","_cid="+urlencode(cid)+"&_source="+urlencode(this.env.source)+"&_to="+urlencode(to));
}
};
this.delete_contacts=function(){
var _152=this.contact_list.get_selection();
if(!(_152.length||this.env.cid)||!confirm(this.get_label("deletecontactconfirm"))){
return;
}
var _153=new Array();
var qs="";
if(this.env.cid){
_153[_153.length]=this.env.cid;
}else{
var id;
for(var n=0;n<_152.length;n++){
id=_152[n];
_153[_153.length]=id;
this.contact_list.remove_row(id,(n==_152.length-1));
}
if(_152.length==1){
this.show_contentframe(false);
}
}
if(this.env.search_request){
qs+="&_search="+this.env.search_request;
}
this.http_post("delete","_cid="+urlencode(_153.join(","))+"&_source="+urlencode(this.env.source)+"&_from="+(this.env.action?this.env.action:"")+qs);
return true;
};
this.update_contact_row=function(cid,_158,_159){
var row;
if(this.contact_list.rows[cid]&&(row=this.contact_list.rows[cid].obj)){
for(var c=0;c<_158.length;c++){
if(row.cells[c]){
$(row.cells[c]).html(_158[c]);
}
}
if(_159){
row.id="rcmrow"+_159;
this.contact_list.remove_row(cid);
this.contact_list.init_row(row);
this.contact_list.selection[0]=_159;
row.style.display="";
}
return true;
}
return false;
};
this.add_contact_row=function(cid,cols,_15e){
if(!this.gui_objects.contactslist||!this.gui_objects.contactslist.tBodies[0]){
return false;
}
var _15f=this.gui_objects.contactslist.tBodies[0];
var _160=_15f.rows.length;
var even=_160%2;
var row=document.createElement("tr");
row.id="rcmrow"+cid;
row.className="contact "+(even?"even":"odd");
if(this.contact_list.in_selection(cid)){
row.className+=" selected";
}
for(var c in cols){
col=document.createElement("td");
col.className=String(c).toLowerCase();
col.innerHTML=cols[c];
row.appendChild(col);
}
this.contact_list.insert_row(row);
this.enable_command("export",(this.contact_list.rowcount>0));
};
this.init_subscription_list=function(){
var p=this;
this.subscription_list=new rcube_list_widget(this.gui_objects.subscriptionlist,{multiselect:false,draggable:true,keyboard:false,toggleselect:true});
this.subscription_list.addEventListener("select",function(o){
p.subscription_select(o);
});
this.subscription_list.addEventListener("dragstart",function(o){
p.drag_active=true;
});
this.subscription_list.addEventListener("dragend",function(o){
p.subscription_move_folder(o);
});
this.subscription_list.row_init=function(row){
var _169=row.obj.getElementsByTagName("a");
if(_169[0]){
_169[0].onclick=function(){
p.rename_folder(row.id);
return false;
};
}
if(_169[1]){
_169[1].onclick=function(){
p.delete_folder(row.id);
return false;
};
}
row.obj.onmouseover=function(){
p.focus_subscription(row.id);
};
row.obj.onmouseout=function(){
p.unfocus_subscription(row.id);
};
};
this.subscription_list.init();
};
this.section_select=function(list){
var id=list.get_single_selection();
if(id){
var _16c="";
var _16d=window;
this.set_busy(true);
if(this.env.contentframe&&window.frames&&window.frames[this.env.contentframe]){
_16c="&_framed=1";
_16d=window.frames[this.env.contentframe];
}
_16d.location.href=this.env.comm_path+"&_action=edit-prefs&_section="+id+_16c;
}
return true;
};
this.identity_select=function(list){
var id;
if(id=list.get_single_selection()){
this.load_identity(id,"edit-identity");
}
};
this.load_identity=function(id,_171){
if(_171=="edit-identity"&&(!id||id==this.env.iid)){
return false;
}
var _172="";
var _173=window;
if(this.env.contentframe&&window.frames&&window.frames[this.env.contentframe]){
_172="&_framed=1";
_173=window.frames[this.env.contentframe];
document.getElementById(this.env.contentframe).style.visibility="inherit";
}
if(_171&&(id||_171=="add-identity")){
this.set_busy(true);
_173.location.href=this.env.comm_path+"&_action="+_171+"&_iid="+id+_172;
}
return true;
};
this.delete_identity=function(id){
var _175=this.identity_list.get_selection();
if(!(_175.length||this.env.iid)){
return;
}
if(!id){
id=this.env.iid?this.env.iid:_175[0];
}
this.goto_url("delete-identity","_iid="+id+"&_token="+this.env.request_token,true);
return true;
};
this.focus_subscription=function(id){
var row,_178;
var reg=RegExp("["+RegExp.escape(this.env.delimiter)+"]?[^"+RegExp.escape(this.env.delimiter)+"]+$");
if(this.drag_active&&this.env.folder&&(row=document.getElementById(id))){
if(this.env.subscriptionrows[id]&&(_178=this.env.subscriptionrows[id][0])){
if(this.check_droptarget(_178)&&!this.env.subscriptionrows[this.get_folder_row_id(this.env.folder)][2]&&(_178!=this.env.folder.replace(reg,""))&&(!_178.match(new RegExp("^"+RegExp.escape(this.env.folder+this.env.delimiter))))){
this.set_env("dstfolder",_178);
$(row).addClass("droptarget");
}
}else{
if(this.env.folder.match(new RegExp(RegExp.escape(this.env.delimiter)))){
this.set_env("dstfolder",this.env.delimiter);
$(this.subscription_list.frame).addClass("droptarget");
}
}
}
};
this.unfocus_subscription=function(id){
var row=$("#"+id);
this.set_env("dstfolder",null);
if(this.env.subscriptionrows[id]&&row[0]){
row.removeClass("droptarget");
}else{
$(this.subscription_list.frame).removeClass("droptarget");
}
};
this.subscription_select=function(list){
var id,_17e;
if((id=list.get_single_selection())&&this.env.subscriptionrows["rcmrow"+id]&&(_17e=this.env.subscriptionrows["rcmrow"+id][0])){
this.set_env("folder",_17e);
}else{
this.set_env("folder",null);
}
if(this.gui_objects.createfolderhint){
$(this.gui_objects.createfolderhint).html(this.env.folder?this.get_label("addsubfolderhint"):"");
}
};
this.subscription_move_folder=function(list){
var reg=RegExp("["+RegExp.escape(this.env.delimiter)+"]?[^"+RegExp.escape(this.env.delimiter)+"]+$");
if(this.env.folder&&this.env.dstfolder&&(this.env.dstfolder!=this.env.folder)&&(this.env.dstfolder!=this.env.folder.replace(reg,""))){
var reg=new RegExp("[^"+RegExp.escape(this.env.delimiter)+"]*["+RegExp.escape(this.env.delimiter)+"]","g");
var _181=this.env.folder.replace(reg,"");
var _182=this.env.dstfolder==this.env.delimiter?_181:this.env.dstfolder+this.env.delimiter+_181;
this.set_busy(true,"foldermoving");
this.http_post("rename-folder","_folder_oldname="+urlencode(this.env.folder)+"&_folder_newname="+urlencode(_182),true);
}
this.drag_active=false;
this.unfocus_subscription(this.get_folder_row_id(this.env.dstfolder));
};
this.create_folder=function(name){
if(this.edit_folder){
this.reset_folder_rename();
}
var form;
if((form=this.gui_objects.editform)&&form.elements["_folder_name"]){
name=form.elements["_folder_name"].value;
if(name.indexOf(this.env.delimiter)>=0){
alert(this.get_label("forbiddencharacter")+" ("+this.env.delimiter+")");
return false;
}
if(this.env.folder&&name!=""){
name=this.env.folder+this.env.delimiter+name;
}
this.set_busy(true,"foldercreating");
this.http_post("create-folder","_name="+urlencode(name),true);
}else{
if(form.elements["_folder_name"]){
form.elements["_folder_name"].focus();
}
}
};
this.rename_folder=function(id){
var temp,row,form;
if(temp=this.edit_folder){
this.reset_folder_rename();
if(temp==id){
return;
}
}
if(id&&this.env.subscriptionrows[id]&&(row=document.getElementById(id))){
var reg=new RegExp(".*["+RegExp.escape(this.env.delimiter)+"]");
this.name_input=document.createElement("input");
this.name_input.type="text";
this.name_input.value=this.env.subscriptionrows[id][0].replace(reg,"");
reg=new RegExp("["+RegExp.escape(this.env.delimiter)+"]?[^"+RegExp.escape(this.env.delimiter)+"]+$");
this.name_input.__parent=this.env.subscriptionrows[id][0].replace(reg,"");
this.name_input.onkeypress=function(e){
rcmail.name_input_keypress(e);
};
row.cells[0].replaceChild(this.name_input,row.cells[0].firstChild);
this.edit_folder=id;
this.name_input.select();
if(form=this.gui_objects.editform){
form.onsubmit=function(){
return false;
};
}
}
};
this.reset_folder_rename=function(){
var cell=this.name_input?this.name_input.parentNode:null;
if(cell&&this.edit_folder&&this.env.subscriptionrows[this.edit_folder]){
$(cell).html(this.env.subscriptionrows[this.edit_folder][1]);
}
this.edit_folder=null;
};
this.name_input_keypress=function(e){
var key=rcube_event.get_keycode(e);
if(key==13){
var _18e=this.name_input?this.name_input.value:null;
if(this.edit_folder&&_18e){
if(_18e.indexOf(this.env.delimiter)>=0){
alert(this.get_label("forbiddencharacter")+" ("+this.env.delimiter+")");
return false;
}
if(this.name_input.__parent){
_18e=this.name_input.__parent+this.env.delimiter+_18e;
}
this.set_busy(true,"folderrenaming");
this.http_post("rename-folder","_folder_oldname="+urlencode(this.env.subscriptionrows[this.edit_folder][0])+"&_folder_newname="+urlencode(_18e),true);
}
}else{
if(key==27){
this.reset_folder_rename();
}
}
};
this.delete_folder=function(id){
var _190=this.env.subscriptionrows[id][0];
if(this.edit_folder){
this.reset_folder_rename();
}
if(_190&&confirm(this.get_label("deletefolderconfirm"))){
this.set_busy(true,"folderdeleting");
this.http_post("delete-folder","_mboxes="+urlencode(_190),true);
this.set_env("folder",null);
$(this.gui_objects.createfolderhint).html("");
}
};
this.add_folder_row=function(name,_192,_193,_194){
if(!this.gui_objects.subscriptionlist){
return false;
}
for(var _195 in this.env.subscriptionrows){
if(this.env.subscriptionrows[_195]!=null&&!this.env.subscriptionrows[_195][2]){
break;
}
}
var _196,form;
var _198=this.gui_objects.subscriptionlist.tBodies[0];
var id="rcmrow"+(_198.childNodes.length+1);
var _19a=this.subscription_list.get_single_selection();
if(_193&&_193.id){
id=_193.id;
_195=_193.id;
}
if(!id||!(_196=document.getElementById(_195))){
this.goto_url("folders");
}else{
var row=this.clone_table_row(_196);
row.id=id;
if(_194&&(_194=this.get_folder_row_id(_194))){
_198.insertBefore(row,document.getElementById(_194));
}else{
_198.appendChild(row);
}
if(_193){
_198.removeChild(_193);
}
}
this.env.subscriptionrows[row.id]=[name,_192,0];
row.cells[0].innerHTML=_192;
if(!_193){
row.cells[1].innerHTML="*";
}
if(!_193&&row.cells[2]&&row.cells[2].firstChild.tagName.toLowerCase()=="input"){
row.cells[2].firstChild.value=name;
row.cells[2].firstChild.checked=true;
}
if(!_193&&(form=this.gui_objects.editform)){
if(form.elements["_folder_oldname"]){
form.elements["_folder_oldname"].options[form.elements["_folder_oldname"].options.length]=new Option(name,name);
}
if(form.elements["_folder_name"]){
form.elements["_folder_name"].value="";
}
}
this.init_subscription_list();
if(_19a&&document.getElementById("rcmrow"+_19a)){
this.subscription_list.select_row(_19a);
}
if(document.getElementById(id).scrollIntoView){
document.getElementById(id).scrollIntoView();
}
};
this.replace_folder_row=function(_19c,_19d,_19e,_19f){
var id=this.get_folder_row_id(_19c);
var row=document.getElementById(id);
this.add_folder_row(_19d,_19e,row,_19f);
var form,elm;
if((form=this.gui_objects.editform)&&(elm=form.elements["_folder_oldname"])){
for(var i=0;i<elm.options.length;i++){
if(elm.options[i].value==_19c){
elm.options[i].text=_19e;
elm.options[i].value=_19d;
break;
}
}
form.elements["_folder_newname"].value="";
}
};
this.remove_folder_row=function(_1a5){
var row;
var id=this.get_folder_row_id(_1a5);
if(id&&(row=document.getElementById(id))){
row.style.display="none";
}
var form;
if((form=this.gui_objects.editform)&&form.elements["_folder_oldname"]){
for(var i=0;i<form.elements["_folder_oldname"].options.length;i++){
if(form.elements["_folder_oldname"].options[i].value==_1a5){
form.elements["_folder_oldname"].options[i]=null;
break;
}
}
}
if(form&&form.elements["_folder_newname"]){
form.elements["_folder_newname"].value="";
}
};
this.subscribe_folder=function(_1aa){
if(_1aa){
this.http_post("subscribe","_mbox="+urlencode(_1aa));
}
};
this.unsubscribe_folder=function(_1ab){
if(_1ab){
this.http_post("unsubscribe","_mbox="+urlencode(_1ab));
}
};
this.get_folder_row_id=function(_1ac){
for(var id in this.env.subscriptionrows){
if(this.env.subscriptionrows[id]&&this.env.subscriptionrows[id][0]==_1ac){
break;
}
}
return id;
};
this.clone_table_row=function(row){
var cell,td;
var _1b1=document.createElement("tr");
for(var n=0;n<row.cells.length;n++){
cell=row.cells[n];
td=document.createElement("td");
if(cell.className){
td.className=cell.className;
}
if(cell.align){
td.setAttribute("align",cell.align);
}
td.innerHTML=cell.innerHTML;
_1b1.appendChild(td);
}
return _1b1;
};
this.set_page_buttons=function(){
this.enable_command("nextpage",(this.env.pagecount>this.env.current_page));
this.enable_command("lastpage",(this.env.pagecount>this.env.current_page));
this.enable_command("previouspage",(this.env.current_page>1));
this.enable_command("firstpage",(this.env.current_page>1));
};
this.init_buttons=function(){
for(var cmd in this.buttons){
if(typeof cmd!="string"){
continue;
}
for(var i=0;i<this.buttons[cmd].length;i++){
var prop=this.buttons[cmd][i];
var elm=document.getElementById(prop.id);
if(!elm){
continue;
}
var _1b7=false;
if(prop.type=="image"){
elm=elm.parentNode;
_1b7=true;
}
elm._command=cmd;
elm._id=prop.id;
if(prop.sel){
elm.onmousedown=function(e){
return rcmail.button_sel(this._command,this._id);
};
elm.onmouseup=function(e){
return rcmail.button_out(this._command,this._id);
};
if(_1b7){
new Image().src=prop.sel;
}
}
if(prop.over){
elm.onmouseover=function(e){
return rcmail.button_over(this._command,this._id);
};
elm.onmouseout=function(e){
return rcmail.button_out(this._command,this._id);
};
if(_1b7){
new Image().src=prop.over;
}
}
}
}
};
this.set_button=function(_1bc,_1bd){
var _1be=this.buttons[_1bc];
var _1bf,obj;
if(!_1be||!_1be.length){
return false;
}
for(var n=0;n<_1be.length;n++){
_1bf=_1be[n];
obj=document.getElementById(_1bf.id);
if(obj&&_1bf.type=="image"&&!_1bf.status){
_1bf.pas=obj._original_src?obj._original_src:obj.src;
if(obj.runtimeStyle&&obj.runtimeStyle.filter&&obj.runtimeStyle.filter.match(/src=['"]([^'"]+)['"]/)){
_1bf.pas=RegExp.$1;
}
}else{
if(obj&&!_1bf.status){
_1bf.pas=String(obj.className);
}
}
if(obj&&_1bf.type=="image"&&_1bf[_1bd]){
_1bf.status=_1bd;
obj.src=_1bf[_1bd];
}else{
if(obj&&typeof (_1bf[_1bd])!="undefined"){
_1bf.status=_1bd;
obj.className=_1bf[_1bd];
}
}
if(obj&&_1bf.type=="input"){
_1bf.status=_1bd;
obj.disabled=!_1bd;
}
}
};
this.set_alttext=function(_1c2,_1c3){
if(!this.buttons[_1c2]||!this.buttons[_1c2].length){
return;
}
var _1c4,obj,link;
for(var n=0;n<this.buttons[_1c2].length;n++){
_1c4=this.buttons[_1c2][n];
obj=document.getElementById(_1c4.id);
if(_1c4.type=="image"&&obj){
obj.setAttribute("alt",this.get_label(_1c3));
if((link=obj.parentNode)&&link.tagName.toLowerCase()=="a"){
link.setAttribute("title",this.get_label(_1c3));
}
}else{
if(obj){
obj.setAttribute("title",this.get_label(_1c3));
}
}
}
};
this.button_over=function(_1c8,id){
var _1ca=this.buttons[_1c8];
var _1cb,elm;
if(!_1ca||!_1ca.length){
return false;
}
for(var n=0;n<_1ca.length;n++){
_1cb=_1ca[n];
if(_1cb.id==id&&_1cb.status=="act"){
elm=document.getElementById(_1cb.id);
if(elm&&_1cb.over){
if(_1cb.type=="image"){
elm.src=_1cb.over;
}else{
elm.className=_1cb.over;
}
}
}
}
};
this.button_sel=function(_1ce,id){
var _1d0=this.buttons[_1ce];
var _1d1,elm;
if(!_1d0||!_1d0.length){
return;
}
for(var n=0;n<_1d0.length;n++){
_1d1=_1d0[n];
if(_1d1.id==id&&_1d1.status=="act"){
elm=document.getElementById(_1d1.id);
if(elm&&_1d1.sel){
if(_1d1.type=="image"){
elm.src=_1d1.sel;
}else{
elm.className=_1d1.sel;
}
}
this.buttons_sel[id]=_1ce;
}
}
};
this.button_out=function(_1d4,id){
var _1d6=this.buttons[_1d4];
var _1d7,elm;
if(!_1d6||!_1d6.length){
return;
}
for(var n=0;n<_1d6.length;n++){
_1d7=_1d6[n];
if(_1d7.id==id&&_1d7.status=="act"){
elm=document.getElementById(_1d7.id);
if(elm&&_1d7.act){
if(_1d7.type=="image"){
elm.src=_1d7.act;
}else{
elm.className=_1d7.act;
}
}
}
}
};
this.set_pagetitle=function(_1da){
if(_1da&&document.title){
document.title=_1da;
}
};
this.display_message=function(msg,type,hold){
if(!this.loaded){
this.pending_message=new Array(msg,type);
return true;
}
if(this.env.framed&&parent.rcmail){
return parent.rcmail.display_message(msg,type,hold);
}
if(!this.gui_objects.message){
return false;
}
if(this.message_timer){
clearTimeout(this.message_timer);
}
var cont=msg;
if(type){
cont="<div class=\""+type+"\">"+cont+"</div>";
}
var obj=$(this.gui_objects.message).html(cont).show();
if(type!="loading"){
obj.bind("mousedown",function(){
_1.hide_message();
return true;
});
}
if(!hold){
this.message_timer=window.setTimeout(function(){
_1.hide_message(true);
},this.message_time);
}
};
this.hide_message=function(fade){
if(this.gui_objects.message){
$(this.gui_objects.message).unbind()[(fade?"fadeOut":"hide")]();
}
};
this.select_folder=function(name,old){
if(this.gui_objects.folderlist){
var _1e3,_1e4;
if((_1e3=this.get_folder_li(old))){
$(_1e3).removeClass("selected").removeClass("unfocused");
}
if((_1e4=this.get_folder_li(name))){
$(_1e4).removeClass("unfocused").addClass("selected");
}
this.triggerEvent("selectfolder",{folder:name,old:old});
}
};
this.get_folder_li=function(name){
if(this.gui_objects.folderlist){
name=String(name).replace(this.identifier_expr,"_");
return document.getElementById("rcmli"+name);
}
return null;
};
this.set_message_coltypes=function(_1e6){
this.coltypes=_1e6;
var cell,col;
var _1e9=this.gui_objects.messagelist?this.gui_objects.messagelist.tHead:null;
for(var n=0;_1e9&&n<this.coltypes.length;n++){
col=this.coltypes[n];
if((cell=_1e9.rows[0].cells[n+1])&&(col=="from"||col=="to")){
if(cell.firstChild&&cell.firstChild.tagName.toLowerCase()=="a"){
cell.firstChild.innerHTML=this.get_label(this.coltypes[n]);
cell.firstChild.onclick=function(){
return rcmail.command("sort",this.__col,this);
};
cell.firstChild.__col=col;
}else{
cell.innerHTML=this.get_label(this.coltypes[n]);
}
cell.id="rcm"+col;
}else{
if(col=="subject"&&this.message_list){
this.message_list.subject_col=n+1;
}
}
}
};
this.add_message_row=function(uid,cols,_1ed,_1ee,_1ef){
if(!this.gui_objects.messagelist||!this.message_list){
return false;
}
if(this.message_list.background){
var _1f0=this.message_list.background;
}else{
var _1f0=this.gui_objects.messagelist.tBodies[0];
}
var _1f1=_1f0.rows.length;
var even=_1f1%2;
this.env.messages[uid]={deleted:_1ed.deleted?1:0,replied:_1ed.replied?1:0,unread:_1ed.unread?1:0,forwarded:_1ed.forwarded?1:0,flagged:_1ed.flagged?1:0};
var _1f3="message"+(even?" even":" odd")+(_1ed.unread?" unread":"")+(_1ed.deleted?" deleted":"")+(_1ed.flagged?" flagged":"")+(this.message_list.in_selection(uid)?" selected":"");
var row=document.createElement("tr");
row.id="rcmrow"+uid;
row.className=_1f3;
var icon=this.env.messageicon;
if(_1ed.deleted&&this.env.deletedicon){
icon=this.env.deletedicon;
}else{
if(_1ed.replied&&this.env.repliedicon){
if(_1ed.forwarded&&this.env.forwardedrepliedicon){
icon=this.env.forwardedrepliedicon;
}else{
icon=this.env.repliedicon;
}
}else{
if(_1ed.forwarded&&this.env.forwardedicon){
icon=this.env.forwardedicon;
}else{
if(_1ed.unread&&this.env.unreadicon){
icon=this.env.unreadicon;
}
}
}
}
var col=document.createElement("td");
col.className="icon";
col.innerHTML=icon?"<img src=\""+icon+"\" alt=\"\" />":"";
row.appendChild(col);
for(var n=0;n<this.coltypes.length;n++){
var c=this.coltypes[n];
col=document.createElement("td");
col.className=String(c).toLowerCase();
if(c=="flag"){
if(_1ed.flagged&&this.env.flaggedicon){
col.innerHTML="<img src=\""+this.env.flaggedicon+"\" alt=\"\" />";
}else{
if(!_1ed.flagged&&this.env.unflaggedicon){
col.innerHTML="<img src=\""+this.env.unflaggedicon+"\" alt=\"\" />";
}
}
}else{
if(c=="attachment"){
col.innerHTML=(_1ee&&this.env.attachmenticon?"<img src=\""+this.env.attachmenticon+"\" alt=\"\" />":"&nbsp;");
}else{
col.innerHTML=cols[c];
}
}
row.appendChild(col);
}
this.message_list.insert_row(row,_1ef);
if(_1ef&&this.env.pagesize&&this.message_list.rowcount>this.env.pagesize){
var uid=this.message_list.get_last_row();
this.message_list.remove_row(uid);
this.message_list.clear_selection(uid);
}
};
this.offline_message_list=function(flag){
if(this.message_list){
this.message_list.set_background_mode(flag);
}
};
this.set_rowcount=function(text){
$(this.gui_objects.countdisplay).html(text);
this.set_page_buttons();
};
this.set_mailboxname=function(_1fb){
if(this.gui_objects.mailboxname&&_1fb){
this.gui_objects.mailboxname.innerHTML=_1fb;
}
};
this.set_quota=function(_1fc){
if(_1fc&&this.gui_objects.quotadisplay){
if(typeof (_1fc)=="object"){
this.percent_indicator(this.gui_objects.quotadisplay,_1fc);
}else{
$(this.gui_objects.quotadisplay).html(_1fc);
}
}
};
this.set_unread_count=function(mbox,_1fe,_1ff){
if(!this.gui_objects.mailboxlist){
return false;
}
this.env.unread_counts[mbox]=_1fe;
this.set_unread_count_display(mbox,_1ff);
};
this.set_unread_count_display=function(mbox,_201){
var reg,_203,item,_205,_206,div;
if(item=this.get_folder_li(mbox)){
_205=this.env.unread_counts[mbox]?this.env.unread_counts[mbox]:0;
_203=item.getElementsByTagName("a")[0];
reg=/\s+\([0-9]+\)$/i;
_206=0;
if((div=item.getElementsByTagName("div")[0])&&div.className.match(/collapsed/)){
for(var k in this.env.unread_counts){
if(k.indexOf(mbox+this.env.delimiter)==0){
_206+=this.env.unread_counts[k];
}
}
}
if(_205&&_203.innerHTML.match(reg)){
_203.innerHTML=_203.innerHTML.replace(reg," ("+_205+")");
}else{
if(_205){
_203.innerHTML+=" ("+_205+")";
}else{
_203.innerHTML=_203.innerHTML.replace(reg,"");
}
}
reg=new RegExp(RegExp.escape(this.env.delimiter)+"[^"+RegExp.escape(this.env.delimiter)+"]+$");
if(mbox.match(reg)){
this.set_unread_count_display(mbox.replace(reg,""),false);
}
if((_205+_206)>0){
$(item).addClass("unread");
}else{
$(item).removeClass("unread");
}
}
reg=/^\([0-9]+\)\s+/i;
if(_201&&document.title){
var _209=String(document.title);
var _20a="";
if(_205&&_209.match(reg)){
_20a=_209.replace(reg,"("+_205+") ");
}else{
if(_205){
_20a="("+_205+") "+_209;
}else{
_20a=_209.replace(reg,"");
}
}
this.set_pagetitle(_20a);
}
};
this.new_message_focus=function(){
if(this.env.framed&&window.parent){
window.parent.focus();
}else{
window.focus();
}
};
this.toggle_prefer_html=function(_20b){
var _20c;
if(_20c=document.getElementById("rcmfd_addrbook_show_images")){
_20c.disabled=!_20b.checked;
}
};
this.set_headers=function(_20d){
if(this.gui_objects.all_headers_row&&this.gui_objects.all_headers_box&&_20d){
$(this.gui_objects.all_headers_box).html(_20d).show();
if(this.env.framed&&parent.rcmail){
parent.rcmail.set_busy(false);
}else{
this.set_busy(false);
}
}
};
this.load_headers=function(elem){
if(!this.gui_objects.all_headers_row||!this.gui_objects.all_headers_box||!this.env.uid){
return;
}
$(elem).removeClass("show-headers").addClass("hide-headers");
$(this.gui_objects.all_headers_row).show();
elem.onclick=function(){
rcmail.hide_headers(elem);
};
if(!this.gui_objects.all_headers_box.innerHTML){
this.display_message(this.get_label("loading"),"loading",true);
this.http_post("headers","_uid="+this.env.uid);
}
};
this.hide_headers=function(elem){
if(!this.gui_objects.all_headers_row||!this.gui_objects.all_headers_box){
return;
}
$(elem).removeClass("hide-headers").addClass("show-headers");
$(this.gui_objects.all_headers_row).hide();
elem.onclick=function(){
rcmail.load_headers(elem);
};
};
this.percent_indicator=function(obj,data){
if(!data||!obj){
return false;
}
var _212=80;
var _213=55;
var _214=data.width?data.width:this.env.indicator_width?this.env.indicator_width:100;
var _215=data.height?data.height:this.env.indicator_height?this.env.indicator_height:14;
var _216=data.percent?Math.abs(parseInt(data.percent)):0;
var _217=parseInt(_216/100*_214);
var pos=$(obj).position();
this.env.indicator_width=_214;
this.env.indicator_height=_215;
if(_217>_214){
_217=_214;
_216=100;
}
var main=$("<div>");
main.css({position:"absolute",top:pos.top,left:pos.left,width:_214+"px",height:_215+"px",zIndex:100,lineHeight:_215+"px"}).attr("title",data.title).addClass("quota_text").html(_216+"%");
var bar1=$("<div>");
bar1.css({position:"absolute",top:pos.top+1,left:pos.left+1,width:_217+"px",height:_215+"px",zIndex:99});
var bar2=$("<div>");
bar2.css({position:"absolute",top:pos.top+1,left:pos.left+1,width:_214+"px",height:_215+"px",zIndex:98}).addClass("quota_bg");
if(_216>=_212){
main.addClass(" quota_text_high");
bar1.addClass("quota_high");
}else{
if(_216>=_213){
main.addClass(" quota_text_mid");
bar1.addClass("quota_mid");
}else{
main.addClass(" quota_text_normal");
bar1.addClass("quota_low");
}
}
obj.innerHTML="";
$(obj).append(bar1).append(bar2).append(main);
};
this.html2plain=function(_21c,id){
var url=this.env.bin_path+"html2text.php";
var _21f=this;
this.set_busy(true,"converting");
console.log("HTTP POST: "+url);
$.ajax({type:"POST",url:url,data:_21c,contentType:"application/octet-stream",error:function(o){
_21f.http_error(o);
},success:function(data){
_21f.set_busy(false);
$(document.getElementById(id)).val(data);
console.log(data);
}});
};
this.plain2html=function(_222,id){
this.set_busy(true,"converting");
$(document.getElementById(id)).val("<pre>"+_222+"</pre>");
this.set_busy(false);
};
this.redirect=function(url,lock){
if(lock||lock===null){
this.set_busy(true);
}
if(this.env.framed&&window.parent){
parent.location.href=url;
}else{
location.href=url;
}
};
this.goto_url=function(_226,_227,lock){
var _229=_227?"&"+_227:"";
this.redirect(this.env.comm_path+"&_action="+_226+_229,lock);
};
this.http_request=function(_22a,_22b,lock){
_22b+=(_22b?"&":"")+"_remote=1";
var url=this.env.comm_path+"&_action="+_22a+"&"+_22b;
console.log("HTTP POST: "+url);
jQuery.get(url,{_unlock:(lock?1:0)},function(data){
_1.http_response(data);
},"json");
};
this.http_post=function(_22f,_230,lock){
var url=this.env.comm_path+"&_action="+_22f;
if(_230&&typeof (_230)=="object"){
_230._remote=1;
_230._unlock=(lock?1:0);
}else{
_230+=(_230?"&":"")+"_remote=1"+(lock?"&_unlock=1":"");
}
console.log("HTTP POST: "+url);
jQuery.post(url,_230,function(data){
_1.http_response(data);
},"json");
};
this.http_response=function(_234){
var _235="";
if(_234.unlock){
this.set_busy(false);
}
if(_234.env){
this.set_env(_234.env);
}
if(typeof _234.texts=="object"){
for(var name in _234.texts){
if(typeof _234.texts[name]=="string"){
this.add_label(name,_234.texts[name]);
}
}
}
if(_234.exec){
console.log(_234.exec);
eval(_234.exec);
}
if(_234.callbacks&&_234.callbacks.length){
for(var i=0;i<_234.callbacks.length;i++){
this.triggerEvent(_234.callbacks[i][0],_234.callbacks[i][1]);
}
}
switch(_234.action){
case "delete":
if(this.task=="addressbook"){
var uid=this.contact_list.get_selection();
this.enable_command("compose",(uid&&this.contact_list.rows[uid]));
this.enable_command("delete","edit",(uid&&this.contact_list.rows[uid]&&this.env.address_sources&&!this.env.address_sources[this.env.source].readonly));
this.enable_command("export",(this.contact_list&&this.contact_list.rowcount>0));
}
case "moveto":
if(this.env.action=="show"){
this.enable_command("reply","reply-all","forward","delete","mark","print","open","edit","viewsource","download",true);
}else{
if(this.message_list){
this.message_list.init();
}
}
break;
case "purge":
case "expunge":
if(!this.env.messagecount&&this.task=="mail"){
if(this.env.contentframe){
this.show_contentframe(false);
}
this.enable_command("show","reply","reply-all","forward","moveto","delete","mark","viewsource","open","edit","download","print","load-attachment","purge","expunge","select-all","select-none","sort",false);
}
break;
case "check-recent":
case "getunread":
case "list":
if(this.task=="mail"){
if(this.message_list&&_234.action=="list"){
this.msglist_select(this.message_list);
}
this.enable_command("show","expunge","select-all","select-none","sort",(this.env.messagecount>0));
this.enable_command("purge",this.purge_mailbox_test());
if(_234.action=="list"){
this.triggerEvent("listupdate",{folder:this.env.mailbox,rowcount:this.message_list.rowcount});
}
}else{
if(this.task=="addressbook"){
this.enable_command("export",(this.contact_list&&this.contact_list.rowcount>0));
if(_234.action=="list"){
this.triggerEvent("listupdate",{folder:this.env.source,rowcount:this.contact_list.rowcount});
}
}
}
break;
}
};
this.http_error=function(_239,_23a,err){
var _23c=_239.statusText;
this.set_busy(false);
_239.abort();
if(_23c){
this.display_message(this.get_label("servererror")+" ("+_23c+")","error");
}
};
this.send_keep_alive=function(){
var d=new Date();
this.http_request("keep-alive","_t="+d.getTime());
};
this.check_for_recent=function(_23e){
if(this.busy){
return;
}
if(_23e){
this.set_busy(true,"checkingmail");
}
var _23f="_t="+(new Date().getTime());
if(this.gui_objects.messagelist){
_23f+="&_list=1";
}
if(this.gui_objects.quotadisplay){
_23f+="&_quota=1";
}
if(this.env.search_request){
_23f+="&_search="+this.env.search_request;
}
this.http_request("check-recent",_23f,true);
};
this.get_single_uid=function(){
return this.env.uid?this.env.uid:(this.message_list?this.message_list.get_single_selection():null);
};
this.get_single_cid=function(){
return this.env.cid?this.env.cid:(this.contact_list?this.contact_list.get_single_selection():null);
};
this.get_caret_pos=function(obj){
if(typeof (obj.selectionEnd)!="undefined"){
return obj.selectionEnd;
}else{
if(document.selection&&document.selection.createRange){
var _241=document.selection.createRange();
if(_241.parentElement()!=obj){
return 0;
}
var gm=_241.duplicate();
if(obj.tagName=="TEXTAREA"){
gm.moveToElementText(obj);
}else{
gm.expand("textedit");
}
gm.setEndPoint("EndToStart",_241);
var p=gm.text.length;
return p<=obj.value.length?p:-1;
}else{
return obj.value.length;
}
}
};
this.set_caret_pos=function(obj,pos){
if(obj.setSelectionRange){
obj.setSelectionRange(pos,pos);
}else{
if(obj.createTextRange){
var _246=obj.createTextRange();
_246.collapse(true);
_246.moveEnd("character",pos);
_246.moveStart("character",pos);
_246.select();
}
}
};
this.lock_form=function(form,lock){
if(!form||!form.elements){
return;
}
var type;
for(var n=0;n<form.elements.length;n++){
type=form.elements[n];
if(type=="hidden"){
continue;
}
form.elements[n].disabled=lock;
}
};
};
rcube_webmail.prototype.addEventListener=rcube_event_engine.prototype.addEventListener;
rcube_webmail.prototype.removeEventListener=rcube_event_engine.prototype.removeEventListener;
rcube_webmail.prototype.triggerEvent=rcube_event_engine.prototype.triggerEvent;

