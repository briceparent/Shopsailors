function rcube_list_widget(_1,p){
this.ENTER_KEY=13;
this.DELETE_KEY=46;
this.BACKSPACE_KEY=8;
this.list=_1?_1:null;
this.frame=null;
this.rows=[];
this.selection=[];
this.rowcount=0;
this.subject_col=-1;
this.shiftkey=false;
this.multiselect=false;
this.multi_selecting=false;
this.draggable=false;
this.keyboard=false;
this.toggleselect=false;
this.dont_select=false;
this.drag_active=false;
this.last_selected=0;
this.shift_start=0;
this.in_selection_before=false;
this.focused=false;
this.drag_mouse_start=null;
this.dblclick_time=600;
this.row_init=function(){
};
if(p&&typeof (p)=="object"){
for(var n in p){
this[n]=p[n];
}
}
};
rcube_list_widget.prototype={init:function(){
if(this.list&&this.list.tBodies[0]){
this.rows=new Array();
this.rowcount=0;
var _4;
for(var r=0;r<this.list.tBodies[0].childNodes.length;r++){
_4=this.list.tBodies[0].childNodes[r];
while(_4&&(_4.nodeType!=1||_4.style.display=="none")){
_4=_4.nextSibling;
r++;
}
this.init_row(_4);
this.rowcount++;
}
this.frame=this.list.parentNode;
if(this.keyboard){
rcube_event.add_listener({element:document,event:bw.opera?"keypress":"keydown",object:this,method:"key_press"});
rcube_event.add_listener({element:document,event:"keydown",object:this,method:"key_down"});
}
}
},init_row:function(_6){
if(_6&&String(_6.id).match(/rcmrow([a-z0-9\-_=\+\/]+)/i)){
var p=this;
var _8=RegExp.$1;
_6.uid=_8;
this.rows[_8]={uid:_8,id:_6.id,obj:_6,classname:_6.className};
_6.onmousedown=function(e){
return p.drag_row(e,this.uid);
};
_6.onmouseup=function(e){
return p.click_row(e,this.uid);
};
if(document.all){
_6.onselectstart=function(){
return false;
};
}
this.row_init(this.rows[_8]);
}
},clear:function(_b){
var _c=document.createElement("tbody");
this.list.insertBefore(_c,this.list.tBodies[0]);
this.list.removeChild(this.list.tBodies[1]);
this.rows=new Array();
this.rowcount=0;
if(_b){
this.clear_selection();
}
},remove_row:function(_d,_e){
if(this.rows[_d].obj){
this.rows[_d].obj.style.display="none";
}
if(_e){
this.select_next();
}
this.rows[_d]=null;
this.rowcount--;
},insert_row:function(_f,_10){
if(this.background){
var _11=this.background;
}else{
var _11=this.list.tBodies[0];
}
if(_10&&_11.rows.length){
_11.insertBefore(_f,_11.firstChild);
}else{
_11.appendChild(_f);
}
this.init_row(_f);
this.rowcount++;
},focus:function(e){
this.focused=true;
for(var n=0;n<this.selection.length;n++){
id=this.selection[n];
if(this.rows[id]&&this.rows[id].obj){
$(this.rows[id].obj).addClass("selected").removeClass("unfocused");
}
}
if(e||(e=window.event)){
rcube_event.cancel(e);
}
},blur:function(){
var id;
this.focused=false;
for(var n=0;n<this.selection.length;n++){
id=this.selection[n];
if(this.rows[id]&&this.rows[id].obj){
$(this.rows[id].obj).removeClass("selected").addClass("unfocused");
}
}
},drag_row:function(e,id){
var _18=rcube_event.get_target(e);
var _19=_18.tagName.toLowerCase();
if(this.dont_select||(_18&&(_19=="input"||_19=="img"))){
return true;
}
if(rcube_event.get_button(e)==2){
return true;
}
this.in_selection_before=this.in_selection(id)?id:false;
if(!this.in_selection_before){
var _1a=rcube_event.get_modifier(e);
this.select_row(id,_1a,false);
}
if(this.draggable&&this.selection.length){
this.drag_start=true;
this.drag_mouse_start=rcube_event.get_mouse_pos(e);
rcube_event.add_listener({element:document,event:"mousemove",object:this,method:"drag_mouse_move"});
rcube_event.add_listener({element:document,event:"mouseup",object:this,method:"drag_mouse_up"});
var _1b=document.getElementsByTagName("iframe");
this.iframe_events=Object();
for(var n in _1b){
var _1d=null;
if(_1b[n].contentDocument){
_1d=_1b[n].contentDocument;
}else{
if(_1b[n].contentWindow){
_1d=_1b[n].contentWindow.document;
}else{
if(_1b[n].document){
_1d=_1b[n].document;
}
}
}
if(_1d){
var _1e=this;
var pos=$("#"+_1b[n].id).offset();
this.iframe_events[n]=function(e){
e._offset=pos;
return _1e.drag_mouse_move(e);
};
if(_1d.addEventListener){
_1d.addEventListener("mousemove",this.iframe_events[n],false);
}else{
if(_1b[n].attachEvent){
_1d.attachEvent("onmousemove",this.iframe_events[n]);
}else{
_1d["onmousemove"]=this.iframe_events[n];
}
}
rcube_event.add_listener({element:_1d,event:"mouseup",object:this,method:"drag_mouse_up"});
}
}
}
return false;
},click_row:function(e,id){
var now=new Date().getTime();
var _24=rcube_event.get_modifier(e);
var _25=rcube_event.get_target(e);
var _26=_25.tagName.toLowerCase();
if((_25&&(_26=="input"||_26=="img"))){
return true;
}
if(this.dont_select){
this.dont_select=false;
return false;
}
var _27=now-this.rows[id].clicked<this.dblclick_time;
if(!this.drag_active&&this.in_selection_before==id&&!_27){
this.select_row(id,_24,false);
}
this.drag_start=false;
this.in_selection_before=false;
if(this.rows&&_27&&this.in_selection(id)){
this.triggerEvent("dblclick");
}else{
this.triggerEvent("click");
}
if(!this.drag_active){
rcube_event.cancel(e);
}
this.rows[id].clicked=now;
return false;
},get_next_row:function(){
if(!this.rows){
return false;
}
var _28=this.rows[this.last_selected];
var _29=_28?_28.obj.nextSibling:null;
while(_29&&(_29.nodeType!=1||_29.style.display=="none")){
_29=_29.nextSibling;
}
return _29;
},get_prev_row:function(){
if(!this.rows){
return false;
}
var _2a=this.rows[this.last_selected];
var _2b=_2a?_2a.obj.previousSibling:null;
while(_2b&&(_2b.nodeType!=1||_2b.style.display=="none")){
_2b=_2b.previousSibling;
}
return _2b;
},get_first_row:function(){
if(this.rowcount){
var _2c=this.list.tBodies[0].rows;
for(var i=0;i<_2c.length-1;i++){
if(_2c[i].id&&String(_2c[i].id).match(/rcmrow([a-z0-9\-_=\+\/]+)/i)&&this.rows[RegExp.$1]!=null){
return RegExp.$1;
}
}
}
return null;
},get_last_row:function(){
if(this.rowcount){
var _2e=this.list.tBodies[0].rows;
for(var i=_2e.length-1;i>=0;i--){
if(_2e[i].id&&String(_2e[i].id).match(/rcmrow([a-z0-9\-_=\+\/]+)/i)&&this.rows[RegExp.$1]!=null){
return RegExp.$1;
}
}
}
return null;
},select_row:function(id,_31,_32){
var _33=this.selection.join(",");
if(!this.multiselect){
_31=0;
}
if(!this.shift_start){
this.shift_start=id;
}
if(!_31){
this.shift_start=id;
this.highlight_row(id,false);
this.multi_selecting=false;
}else{
switch(_31){
case SHIFT_KEY:
this.shift_select(id,false);
break;
case CONTROL_KEY:
if(!_32){
this.highlight_row(id,true);
}
break;
case CONTROL_SHIFT_KEY:
this.shift_select(id,true);
break;
default:
this.highlight_row(id,false);
break;
}
this.multi_selecting=true;
}
if(this.selection.join(",")!=_33){
this.triggerEvent("select");
}
if(this.last_selected!=0&&this.rows[this.last_selected]){
$(this.rows[this.last_selected].obj).removeClass("focused");
}
if(this.toggleselect&&this.last_selected==id){
this.clear_selection();
id=null;
}else{
$(this.rows[id].obj).addClass("focused");
}
if(!this.selection.length){
this.shift_start=null;
}
this.last_selected=id;
},select:function(id){
this.select_row(id,false);
this.scrollto(id);
},select_next:function(){
var _35=this.get_next_row();
var _36=this.get_prev_row();
var _37=(_35)?_35:_36;
if(_37){
this.select_row(_37.uid,false,false);
}
},select_first:function(){
var _38=this.get_first_row();
if(_38){
this.select_row(_38,false,false);
}
},shift_select:function(id,_3a){
if(!this.rows[this.shift_start]||!this.selection.length){
this.shift_start=id;
}
var _3b=this.rows[this.shift_start].obj.rowIndex;
var _3c=this.rows[id].obj.rowIndex;
var i=((_3b<_3c)?_3b:_3c);
var j=((_3b>_3c)?_3b:_3c);
for(var n in this.rows){
if((this.rows[n].obj.rowIndex>=i)&&(this.rows[n].obj.rowIndex<=j)){
if(!this.in_selection(n)){
this.highlight_row(n,true);
}
}else{
if(this.in_selection(n)&&!_3a){
this.highlight_row(n,true);
}
}
}
},in_selection:function(id){
for(var n in this.selection){
if(this.selection[n]==id){
return true;
}
}
return false;
},select_all:function(_42){
if(!this.rows||!this.rows.length){
return false;
}
var _43=this.selection.join(",");
this.selection=new Array();
for(var n in this.rows){
if(!_42||(this.rows[n]&&this.rows[n][_42]==true)){
this.last_selected=n;
this.highlight_row(n,true);
}else{
if(this.rows[n]){
$(this.rows[n].obj).removeClass("selected").removeClass("unfocused");
}
}
}
if(this.selection.join(",")!=_43){
this.triggerEvent("select");
}
this.focus();
return true;
},invert_selection:function(){
if(!this.rows||!this.rows.length){
return false;
}
var _45=this.selection.join(",");
for(var n in this.rows){
this.highlight_row(n,true);
}
if(this.selection.join(",")!=_45){
this.triggerEvent("select");
}
this.focus();
return true;
},clear_selection:function(id){
var _48=this.selection.length;
if(id){
for(var n=0;n<this.selection.length;n++){
if(this.selection[n]==id){
this.selection.splice(n,1);
break;
}
}
}else{
for(var n=0;n<this.selection.length;n++){
if(this.rows[this.selection[n]]){
$(this.rows[this.selection[n]].obj).removeClass("selected").removeClass("unfocused");
}
}
this.selection=new Array();
}
if(_48&&!this.selection.length){
this.triggerEvent("select");
}
},get_selection:function(){
return this.selection;
},get_single_selection:function(){
if(this.selection.length==1){
return this.selection[0];
}else{
return null;
}
},highlight_row:function(id,_4b){
if(this.rows[id]&&!_4b){
if(this.selection.length>1||!this.in_selection(id)){
this.clear_selection();
this.selection[0]=id;
$(this.rows[id].obj).addClass("selected");
}
}else{
if(this.rows[id]){
if(!this.in_selection(id)){
this.selection[this.selection.length]=id;
$(this.rows[id].obj).addClass("selected");
}else{
var p=find_in_array(id,this.selection);
var _4d=this.selection.slice(0,p);
var _4e=this.selection.slice(p+1,this.selection.length);
this.selection=_4d.concat(_4e);
$(this.rows[id].obj).removeClass("selected").removeClass("unfocused");
}
}
}
},key_press:function(e){
if(this.focused!=true){
return true;
}
var _50=rcube_event.get_keycode(e);
var _51=rcube_event.get_modifier(e);
switch(_50){
case 40:
case 38:
case 63233:
case 63232:
rcube_event.cancel(e);
return this.use_arrow_key(_50,_51);
default:
this.shiftkey=e.shiftKey;
this.key_pressed=_50;
this.triggerEvent("keypress");
if(this.key_pressed==this.BACKSPACE_KEY){
return rcube_event.cancel(e);
}
}
return true;
},key_down:function(e){
switch(rcube_event.get_keycode(e)){
case 27:
if(this.drag_active){
return this.drag_mouse_up(e);
}
case 40:
case 38:
case 63233:
case 63232:
if(!rcube_event.get_modifier(e)&&this.focused){
return rcube_event.cancel(e);
}
default:
}
return true;
},use_arrow_key:function(_53,_54){
var _55;
if(_53==40||_53==63233){
_55=this.get_next_row();
}else{
if(_53==38||_53==63232){
_55=this.get_prev_row();
}
}
if(_55){
this.select_row(_55.uid,_54,true);
this.scrollto(_55.uid);
}
return false;
},scrollto:function(id){
var row=this.rows[id].obj;
if(row&&this.frame){
var _58=Number(row.offsetTop);
if(_58<Number(this.frame.scrollTop)){
this.frame.scrollTop=_58;
}else{
if(_58+Number(row.offsetHeight)>Number(this.frame.scrollTop)+Number(this.frame.offsetHeight)){
this.frame.scrollTop=(_58+Number(row.offsetHeight))-Number(this.frame.offsetHeight);
}
}
}
},drag_mouse_move:function(e){
if(this.drag_start){
var m=rcube_event.get_mouse_pos(e);
if(!this.drag_mouse_start||(Math.abs(m.x-this.drag_mouse_start.x)<3&&Math.abs(m.y-this.drag_mouse_start.y)<3)){
return false;
}
if(!this.draglayer){
this.draglayer=$("<div>").attr("id","rcmdraglayer").css({position:"absolute",display:"none","z-index":2000}).appendTo(document.body);
}
var _5b="";
var c,i,_5e,_5f,obj;
for(var n=0;n<this.selection.length;n++){
if(n>12){
_5b+="...";
break;
}
if(this.rows[this.selection[n]].obj){
obj=this.rows[this.selection[n]].obj;
_5f="";
for(c=0,i=0;i<obj.childNodes.length;i++){
if(obj.childNodes[i].nodeName=="TD"){
if(((_5e=obj.childNodes[i].firstChild)&&(_5e.nodeType==3||_5e.nodeName=="A"))&&(this.subject_col<0||(this.subject_col>=0&&this.subject_col==c))){
if(n==0){
if(_5e.nodeType==3){
this.drag_start_pos=$(obj.childNodes[i]).offset();
}else{
this.drag_start_pos=$(_5e).offset();
}
}
_5f=_5e.nodeType==3?_5e.data:_5e.innerHTML;
_5f=_5f.replace(/^\s+/i,"");
_5b+=(_5f.length>50?_5f.substring(0,50)+"...":_5f)+"<br />";
break;
}
c++;
}
}
}
}
this.draglayer.html(_5b);
this.draglayer.show();
this.drag_active=true;
this.triggerEvent("dragstart");
}
if(this.drag_active&&this.draglayer){
var pos=rcube_event.get_mouse_pos(e);
this.draglayer.css({left:(pos.x+20)+"px",top:(pos.y-5+(bw.ie?document.documentElement.scrollTop:0))+"px"});
this.triggerEvent("dragmove",e?e:window.event);
}
this.drag_start=false;
return false;
},drag_mouse_up:function(e){
document.onmousemove=null;
if(this.draglayer&&this.draglayer.is(":visible")){
if(this.drag_start_pos){
this.draglayer.animate(this.drag_start_pos,300,"swing").hide(20);
}else{
this.draglayer.hide();
}
}
this.drag_active=false;
this.triggerEvent("dragend");
rcube_event.remove_listener({element:document,event:"mousemove",object:this,method:"drag_mouse_move"});
rcube_event.remove_listener({element:document,event:"mouseup",object:this,method:"drag_mouse_up"});
var _64=document.getElementsByTagName("iframe");
for(var n in _64){
var _66;
if(_64[n].contentDocument){
_66=_64[n].contentDocument;
}else{
if(_64[n].contentWindow){
_66=_64[n].contentWindow.document;
}else{
if(_64[n].document){
_66=_64[n].document;
}
}
}
if(_66){
if(this.iframe_events[n]){
if(_66.removeEventListener){
_66.removeEventListener("mousemove",this.iframe_events[n],false);
}else{
if(_66.detachEvent){
_66.detachEvent("onmousemove",this.iframe_events[n]);
}else{
_66["onmousemove"]=null;
}
}
}
rcube_event.remove_listener({element:_66,event:"mouseup",object:this,method:"drag_mouse_up"});
}
}
return rcube_event.cancel(e);
},set_background_mode:function(_67){
if(_67){
this.background=document.createElement("tbody");
}else{
if(this.background){
this.list.replaceChild(this.background,this.list.tBodies[0]);
this.background=null;
}
}
}};
rcube_list_widget.prototype.addEventListener=rcube_event_engine.prototype.addEventListener;
rcube_list_widget.prototype.removeEventListener=rcube_event_engine.prototype.removeEventListener;
rcube_list_widget.prototype.triggerEvent=rcube_event_engine.prototype.triggerEvent;

