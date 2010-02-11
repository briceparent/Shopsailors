var CONTROL_KEY=1;
var SHIFT_KEY=2;
var CONTROL_SHIFT_KEY=3;
function roundcube_browser(){
this.ver=parseFloat(navigator.appVersion);
this.appver=navigator.appVersion;
this.agent=navigator.userAgent;
this.name=navigator.appName;
this.vendor=navigator.vendor?navigator.vendor:"";
this.vendver=navigator.vendorSub?parseFloat(navigator.vendorSub):0;
this.product=navigator.product?navigator.product:"";
this.platform=String(navigator.platform).toLowerCase();
this.lang=(navigator.language)?navigator.language.substring(0,2):(navigator.browserLanguage)?navigator.browserLanguage.substring(0,2):(navigator.systemLanguage)?navigator.systemLanguage.substring(0,2):"en";
this.win=(this.platform.indexOf("win")>=0)?true:false;
this.mac=(this.platform.indexOf("mac")>=0)?true:false;
this.linux=(this.platform.indexOf("linux")>=0)?true:false;
this.unix=(this.platform.indexOf("unix")>=0)?true:false;
this.dom=document.getElementById?true:false;
this.dom2=(document.addEventListener&&document.removeEventListener);
this.ie=(document.all)?true:false;
this.ie4=(this.ie&&!this.dom);
this.ie5=(this.dom&&this.appver.indexOf("MSIE 5")>0);
this.ie8=(this.dom&&this.appver.indexOf("MSIE 8")>0);
this.ie7=(this.dom&&this.appver.indexOf("MSIE 7")>0);
this.ie6=(this.dom&&!this.ie8&&!this.ie7&&this.appver.indexOf("MSIE 6")>0);
this.mz=(this.dom&&this.ver>=5);
this.ns=((this.ver<5&&this.name=="Netscape")||(this.ver>=5&&this.vendor.indexOf("Netscape")>=0));
this.ns6=(this.ns&&parseInt(this.vendver)==6);
this.ns7=(this.ns&&parseInt(this.vendver)==7);
this.safari=(this.agent.toLowerCase().indexOf("safari")>0||this.agent.toLowerCase().indexOf("applewebkit")>0);
this.konq=(this.agent.toLowerCase().indexOf("konqueror")>0);
this.opera=(window.opera)?true:false;
if(this.opera&&window.RegExp){
this.vendver=(/opera(\s|\/)([0-9\.]+)/i.test(navigator.userAgent))?parseFloat(RegExp.$2):-1;
}else{
if(!this.vendver&&this.safari){
this.vendver=(/(safari|applewebkit)\/([0-9]+)/i.test(this.agent))?parseInt(RegExp.$2):0;
}else{
if((!this.vendver&&this.mz)||this.agent.indexOf("Camino")>0){
this.vendver=(/rv:([0-9\.]+)/.test(this.agent))?parseFloat(RegExp.$1):0;
}else{
if(this.ie&&window.RegExp){
this.vendver=(/msie\s+([0-9\.]+)/i.test(this.agent))?parseFloat(RegExp.$1):0;
}else{
if(this.konq&&window.RegExp){
this.vendver=(/khtml\/([0-9\.]+)/i.test(this.agent))?parseFloat(RegExp.$1):0;
}
}
}
}
}
if(this.safari&&(/;\s+([a-z]{2})-[a-z]{2}\)/i.test(this.agent))){
this.lang=RegExp.$1;
}
this.dhtml=((this.ie4&&this.win)||this.ie5||this.ie6||this.ns4||this.mz);
this.vml=(this.win&&this.ie&&this.dom&&!this.opera);
this.pngalpha=(this.mz||(this.opera&&this.vendver>=6)||(this.ie&&this.mac&&this.vendver>=5)||(this.ie&&this.win&&this.vendver>=5.5)||this.safari);
this.opacity=(this.mz||(this.ie&&this.vendver>=5.5&&!this.opera)||(this.safari&&this.vendver>=100));
this.cookies=navigator.cookieEnabled;
this.xmlhttp_test=function(){
var _1=new Function("try{var o=new ActiveXObject('Microsoft.XMLHTTP');return true;}catch(err){return false;}");
this.xmlhttp=(window.XMLHttpRequest||(window.ActiveXObject&&_1()))?true:false;
return this.xmlhttp;
};
};
var rcube_event={get_target:function(e){
e=e||window.event;
return e&&e.target?e.target:e.srcElement;
},get_keycode:function(e){
e=e||window.event;
return e&&e.keyCode?e.keyCode:(e&&e.which?e.which:0);
},get_button:function(e){
e=e||window.event;
return e&&(typeof e.button!="undefined")?e.button:(e&&e.which?e.which:0);
},get_modifier:function(e){
var _6=0;
e=e||window.event;
if(bw.mac&&e){
_6+=(e.metaKey&&CONTROL_KEY)+(e.shiftKey&&SHIFT_KEY);
return _6;
}
if(e){
_6+=(e.ctrlKey&&CONTROL_KEY)+(e.shiftKey&&SHIFT_KEY);
return _6;
}
},get_mouse_pos:function(e){
if(!e){
e=window.event;
}
var mX=(e.pageX)?e.pageX:e.clientX;
var mY=(e.pageY)?e.pageY:e.clientY;
if(document.body&&document.all){
mX+=document.body.scrollLeft;
mY+=document.body.scrollTop;
}
if(e._offset){
mX+=e._offset.left;
mY+=e._offset.top;
}
return {x:mX,y:mY};
},add_listener:function(p){
if(!p.object||!p.method){
return;
}
if(!p.element){
p.element=document;
}
if(!p.object._rc_events){
p.object._rc_events=[];
}
var _b=p.event+"*"+p.method;
if(!p.object._rc_events[_b]){
p.object._rc_events[_b]=function(e){
return p.object[p.method](e);
};
}
if(p.element.addEventListener){
p.element.addEventListener(p.event,p.object._rc_events[_b],false);
}else{
if(p.element.attachEvent){
p.element.detachEvent("on"+p.event,p.object._rc_events[_b]);
p.element.attachEvent("on"+p.event,p.object._rc_events[_b]);
}else{
p.element["on"+p.event]=p.object._rc_events[_b];
}
}
},remove_listener:function(p){
if(!p.element){
p.element=document;
}
var _e=p.event+"*"+p.method;
if(p.object&&p.object._rc_events&&p.object._rc_events[_e]){
if(p.element.removeEventListener){
p.element.removeEventListener(p.event,p.object._rc_events[_e],false);
}else{
if(p.element.detachEvent){
p.element.detachEvent("on"+p.event,p.object._rc_events[_e]);
}else{
p.element["on"+p.event]=null;
}
}
}
},cancel:function(_f){
var e=_f?_f:window.event;
if(e.preventDefault){
e.preventDefault();
}
if(e.stopPropagation){
e.stopPropagation();
}
e.cancelBubble=true;
e.returnValue=false;
return false;
}};
function rcube_event_engine(){
this._events={};
};
rcube_event_engine.prototype={addEventListener:function(evt,_12,obj){
if(!this._events){
this._events={};
}
if(!this._events[evt]){
this._events[evt]=[];
}
var e={func:_12,obj:obj?obj:window};
this._events[evt][this._events[evt].length]=e;
},removeEventListener:function(evt,_16,obj){
if(typeof obj=="undefined"){
obj=window;
}
for(var h,i=0;this._events&&this._events[evt]&&i<this._events[evt].length;i++){
if((h=this._events[evt][i])&&h.func==_16&&h.obj==obj){
this._events[evt][i]=null;
}
}
},triggerEvent:function(evt,e){
var ret,h;
if(typeof e=="undefined"){
e=this;
}else{
if(typeof e=="object"){
e.event=evt;
}
}
if(this._events&&this._events[evt]&&!this._event_exec){
this._event_exec=true;
for(var i=0;i<this._events[evt].length;i++){
if((h=this._events[evt][i])){
if(typeof h.func=="function"){
ret=h.func.call?h.func.call(h.obj,e):h.func(e);
}else{
if(typeof h.obj[h.func]=="function"){
ret=h.obj[h.func](e);
}
}
if(typeof ret!="undefined"&&!ret){
break;
}
}
}
}
this._event_exec=false;
return ret;
}};
function rcube_layer(id,_20){
this.name=id;
this.create=function(arg){
var l=(arg.x)?arg.x:0;
var t=(arg.y)?arg.y:0;
var w=arg.width;
var h=arg.height;
var z=arg.zindex;
var vis=arg.vis;
var _28=arg.parent;
var obj;
obj=document.createElement("DIV");
with(obj){
id=this.name;
with(style){
position="absolute";
visibility=(vis)?(vis==2)?"inherit":"visible":"hidden";
left=l+"px";
top=t+"px";
if(w){
width=w.toString().match(/\%$/)?w:w+"px";
}
if(h){
height=h.toString().match(/\%$/)?h:h+"px";
}
if(z){
zIndex=z;
}
}
}
if(_28){
_28.appendChild(obj);
}else{
document.body.appendChild(obj);
}
this.elm=obj;
};
if(_20!=null){
this.create(_20);
this.name=this.elm.id;
}else{
this.elm=document.getElementById(id);
}
if(!this.elm){
return false;
}
this.css=this.elm.style;
this.event=this.elm;
this.width=this.elm.offsetWidth;
this.height=this.elm.offsetHeight;
this.x=parseInt(this.elm.offsetLeft);
this.y=parseInt(this.elm.offsetTop);
this.visible=(this.css.visibility=="visible"||this.css.visibility=="show"||this.css.visibility=="inherit")?true:false;
this.move=function(x,y){
this.x=x;
this.y=y;
this.css.left=Math.round(this.x)+"px";
this.css.top=Math.round(this.y)+"px";
};
this.resize=function(w,h){
this.css.width=w+"px";
this.css.height=h+"px";
this.width=w;
this.height=h;
};
this.show=function(a){
if(a==1){
this.css.visibility="visible";
this.visible=true;
}else{
if(a==2){
this.css.visibility="inherit";
this.visible=true;
}else{
this.css.visibility="hidden";
this.visible=false;
}
}
};
this.write=function(_2f){
this.elm.innerHTML=_2f;
};
};
function rcube_check_email(_30,_31){
if(_30&&window.RegExp){
var _32="[^\\x0d\\x22\\x5c\\x80-\\xff]";
var _33="[^\\x0d\\x5b-\\x5d\\x80-\\xff]";
var _34="[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+";
var _35="\\x5c[\\x00-\\x7f]";
var _36="\\x5b("+_33+"|"+_35+")*\\x5d";
var _37="\\x22("+_32+"|"+_35+")*\\x22";
var _38="("+_34+"|"+_36+")";
var _39="("+_34+"|"+_37+")";
var _3a=_38+"(\\x2e"+_38+")*";
var _3b=_39+"(\\x2e"+_39+")*";
var _3c=_3b+"\\x40"+_3a;
var _3d="[,;s\n]";
var _3e=_31?new RegExp("(^|<|"+_3d+")"+_3c+"($|>|"+_3d+")","i"):new RegExp("^"+_3c+"$","i");
return _3e.test(_30)?true:false;
}
return false;
};
function find_in_array(){
var _3f=find_in_array.arguments;
if(!_3f.length){
return -1;
}
var _40=typeof (_3f[0])=="object"?_3f[0]:_3f.length>1&&typeof (_3f[1])=="object"?_3f[1]:new Array();
var _41=typeof (_3f[0])!="object"?_3f[0]:_3f.length>1&&typeof (_3f[1])!="object"?_3f[1]:"";
var _42=_3f.length==3?_3f[2]:false;
if(!_40.length){
return -1;
}
for(var i=0;i<_40.length;i++){
if(_42&&_40[i].toLowerCase()==_41.toLowerCase()){
return i;
}else{
if(_40[i]==_41){
return i;
}
}
}
return -1;
};
function urlencode(str){
return window.encodeURIComponent?encodeURIComponent(str):escape(str);
};
function rcube_find_object(id,d){
var n,f,obj,e;
if(!d){
d=document;
}
if(d.getElementsByName&&(e=d.getElementsByName(id))){
obj=e[0];
}
if(!obj&&d.getElementById){
obj=d.getElementById(id);
}
if(!obj&&d.all){
obj=d.all[id];
}
if(!obj&&d.images.length){
obj=d.images[id];
}
if(!obj&&d.forms.length){
for(f=0;f<d.forms.length;f++){
if(d.forms[f].name==id){
obj=d.forms[f];
}else{
if(d.forms[f].elements[id]){
obj=d.forms[f].elements[id];
}
}
}
}
if(!obj&&d.layers){
if(d.layers[id]){
obj=d.layers[id];
}
for(n=0;!obj&&n<d.layers.length;n++){
obj=rcube_find_object(id,d.layers[n].document);
}
}
return obj;
};
function rcube_mouse_is_over(ev,obj){
var _4d=rcube_event.get_mouse_pos(ev);
var pos=$(obj).offset();
return ((_4d.x>=pos.left)&&(_4d.x<(pos.left+obj.offsetWidth))&&(_4d.y>=pos.top)&&(_4d.y<(pos.top+obj.offsetHeight)));
};
function setCookie(_4f,_50,_51,_52,_53,_54){
var _55=_4f+"="+escape(_50)+(_51?"; expires="+_51.toGMTString():"")+(_52?"; path="+_52:"")+(_53?"; domain="+_53:"")+(_54?"; secure":"");
document.cookie=_55;
};
roundcube_browser.prototype.set_cookie=setCookie;
function getCookie(_56){
var dc=document.cookie;
var _58=_56+"=";
var _59=dc.indexOf("; "+_58);
if(_59==-1){
_59=dc.indexOf(_58);
if(_59!=0){
return null;
}
}else{
_59+=2;
}
var end=document.cookie.indexOf(";",_59);
if(end==-1){
end=dc.length;
}
return unescape(dc.substring(_59+_58.length,end));
};
roundcube_browser.prototype.get_cookie=getCookie;
function rcube_console(){
this.log=function(msg){
var box=rcube_find_object("dbgconsole");
if(box){
if(msg.charAt(msg.length-1)=="\n"){
msg+="--------------------------------------\n";
}else{
msg+="\n--------------------------------------\n";
}
if(bw.konq){
box.innerText+=msg;
box.value=box.innerText;
}else{
box.value+=msg;
}
}
};
this.reset=function(){
var box=rcube_find_object("dbgconsole");
if(box){
box.innerText=box.value="";
}
};
};
var bw=new roundcube_browser();
if(!window.console){
console=new rcube_console();
}
RegExp.escape=function(str){
return String(str).replace(/([.*+?^=!:${}()|[\]\/\\])/g,"\\$1");
};
if(bw.ie){
document._getElementById=document.getElementById;
document.getElementById=function(id){
var i=0;
var o=document._getElementById(id);
if(!o||o.id!=id){
while((o=document.all[i])&&o.id!=id){
i++;
}
}
return o;
};
}

