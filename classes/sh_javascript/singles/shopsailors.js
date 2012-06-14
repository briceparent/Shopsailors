/* POPUPS */
var sh_mouse_overEvent = null;

Event.observe(window, "load", function(){
    $$('body')[0].addClassName('withJS');
    
    $$('.toggle_next_element').each(function(el){
        el.next().style.display = 'none';
        Event.observe(el,'click',function(){
            Effect.toggle(el.next(),'blind');
        });
        el.next().removeClassName('toggle_keep_hidden');
    });
});
Event.observe(window, "mousemove", function(event){
   sh_mouse_overEvent = event;
});
 
function debug(text){
    $('sh_debug').innerHTML = text+'<br />'+$('sh_debug').innerHTML;
}

var sh_popupClass = new Class.create({
    content: '',
    title: null,
    ret:null,
    type:'',
    width:300,
    position:'top',
    textarea:false,
    cursorChanged:false,
    onconfirmok: function(){},
    onconfirmcancel: function(){},
    onpromptok:function(){},
    onpromptcancel:function(){},
    onalertshown:function(){},
    initialize: function() {
    },
    _setParams: function(content,params){
        this.content = content;
        if(params != undefined){
            if(params.width != undefined && params.width > 20){
                this.width = params.width;
            }
            if(params.title != undefined){
                this.title = params.title;
            }
            if(params.onconfirmok != undefined){
                this.onconfirmok = params.onconfirmok;
            }
            if(params.onconfirmcancel != undefined){
                this.onconfirmcancel = params.onconfirmcancel;
            }
            if(params.onpromptok != undefined){
                this.onpromptok = params.onpromptok;
            }
            if(params.onpromptcancel != undefined){
                this.onpromptcancel = params.onpromptcancel;
            }
            if(params.onalertshown != undefined){
                this.onalertshown = params.onalertshown;
            }
            if(params.onafterhide != undefined){
                this.onafterhide = params.onafterhide;
            }
            if(params.icon != undefined){
                this.icon = params.icon;
            }
            if(params.textarea != undefined){
                this.textarea = params.textarea;
            }
        }
    },
    resizeToContent: function(){
        Modalbox.resizeToContent();
    },
    wait: function(content, params) {
        this.type = 'wait';
        this._setParams(content, params);
        this.cursorChanged = true;
        document.body.style.cursor = 'wait';
        if(this.title == null){
            this.title = document.title;
        }
        if(this.icon != undefined){
            this.title = '<img src="'+this.icon+'"/>'+this.title;
        }
        content = '<div>'+this.content+'</div>';
        Modalbox.show(content, {
            title: this.title,
            width: this.width,
            closeValue: '',
            overlayClose: false
        });
    },
    message: function(div, params){
        this.type = 'message';
        var content = '';
        if($(div) == '[object HTMLDivElement]'){
            content = $(div).innerHTML;
        }else{
            content = div;
        }
        this._setParams('<div>'+content+'</div>', params);
        if(this.title == null){
            this.title = document.title;
        }
        if(this.icon != undefined){
            this.title = '<img src="'+this.icon+'"/>'+this.title;
        }

        Modalbox.show(this.content, {
            title: this.title,
            width: this.width,
            closeValue: '',
            overlayClose: true
        });
    },
    hide: function() {
        if(this.cursorChanged){
            document.body.style.cursor = 'auto';
            this.cursorChanged = false;
        }
        Modalbox.hide();
    },
    alert: function(content, params) {
        this.type = 'alert';
        this._setParams(content, params);
        if(this.title == null){
            this.title = document.title;
        }
        if(this.icon != undefined){
            this.title = '<img src="'+this.icon+'"/>'+this.title;
        }
        content = '<div>'+this.content+'</div><div class="popup_buttons"><input class="popup_button popup_button_ok" type="button" onclick="sh_popup.setRet(true);" value="OK"/></div>';
        Modalbox.show(content, {
            title: this.title,
            width: this.width,
            overlayClose: false
        });
    },
    confirm: function(content, params){
        this.type='confirm';
        this._setParams(content, params);
        if(this.title == null){
            this.title = document.title;
        }
        if(this.icon != undefined){
            this.title = '<img src="'+this.icon+'"/>'+this.title;
        }else{
            this.title = '<img src="/images/shared/icons/help.png"/>'+this.title;
        }
        content = '<div>'+this.content+'</div><br /><br />';
        content += '<div class="popup_buttons"><input onclick="sh_popup.setRet(true);" type="button" class="popup_button popup_button_ok" value="OK"/>&#160;<input type="button" value="Annuler" class="popup_button popup_button_cancel" onclick="sh_popup.setRet(false);"/></div>';
        
        Modalbox.show(content, {
            title: this.title,
            width: this.width,
            overlayClose: false,
            closeValue: '',
            onShow:function(){
            },
            afterHide:function(){
            }
        });
    },
    prompt: function(content, value, params){
        this.type='prompt';
        this._setParams(content, params);
        if(this.title == null){
            this.title = document.title;
        }
        if(this.icon != undefined){
            this.title = '<img src="'+this.icon+'"/>'+this.title;
        }
        content = '<div>'+this.content+'</div>';
        if(this.textarea){
            content += '<div class="popup_buttons"><textarea id="sh_popup_promptInput" style="width:100%;height:80px;">'+value+'</textarea><br /><br />\
<input onclick="sh_popup.setRet($(\'sh_popup_promptInput\').value);" type="button" class="popup_button popup_button_ok" value="OK"/>&#160;\
<input type="button" value="Annuler" class="popup_button popup_button_cancel" onclick="sh_popup.setRet(false);"/></div>';
            
        }else{
            content += '<div class="popup_buttons"><input id="sh_popup_promptInput" value="'+value+'"/><br /><br />\
<input onclick="sh_popup.setRet($(\'sh_popup_promptInput\').value);" type="button" class="popup_button popup_button_ok" value="OK"/>&#160;\
<input type="button" value="Annuler" class="popup_button popup_button_cancel" onclick="sh_popup.setRet(false);"/></div>';
        }
        Modalbox.show(content, {
            title: this.title,
            width: this.width,
            overlayClose: false,
            closeValue: '',
            onShow:function(){
            },
            afterHide:function(){
            }
        });
    },
    setPromptValue: function(value){
        $('sh_popup_promptInput').value = value;
    },
    setRet: function(ret){
        this.ret = ret;
        if(this.type == 'alert' && this.onalertshown != undefined){
            this.onalertshown();
        }else if(this.type == 'confirm'){
            if(this.ret && this.onconfirmok != undefined){
                this.onconfirmok();
            }else if(this.onconfirmcancel != undefined){
                this.onconfirmcancel();
            }
        }else if(this.type == 'prompt'){
            if(this.ret != false && this.onpromptok != undefined){
                this.onpromptok(this.ret);
            }else if(this.onpromptcancel != undefined){
                this.onpromptcancel();
            }
        }
        Modalbox.hide();
    }
});

var sh_popup = new sh_popupClass();

/* POPUPS END */

/* POPINS */
var sh_popinClass = new Class.create({
    url:'',
    className:"alphacube",
    width:500,
    okLabel:'Close',
    _setParams: function(params){
        if(params != undefined){
            if(params.width != undefined && params.width > 20){
                this.width = params.width;
            }
            if(params.url != undefined){
                this.url = params.url;
            }
            if(params.okLabel != undefined){
                this.okLabel = params.okLabel;
            }
            if(params.className != undefined){
                this.className = params.className;
            }
        }
    },
    show: function(content, params) {
        this._setParams(params);

        Dialog.alert(
        {
            url: this.url
            },

            {
            className: this.className,
            width:this.width,
            okLabel: this.okLabel
            }
        );
    }
});

var sh_popin = new sh_popinClass();

/* END OF POPINS */

var sh_loaded_css = new Array();
function sh_load_css(cssFile){
    if(sh_loaded_css[cssFile] == undefined){
        Scriptaculous.load('../'+cssFile);
        sh_loaded_css[cssFile] = true;
    }
}

sh_load_css('window/themes/alert.css');

/* Tabbed arrays */
Event.observe(window, 'load', function() {

    /* TAB GROUPS */
    $$('.tabGroup_containerTitle').each(function(el){
        Event.observe(el, 'click', function(clicked){
            sh_switchTab(el);
        });
    });
    /* selected tab */
    var selectedTab = get_url_parameter( 'selectedTab' );
    if(selectedTab != ''){
        if($(selectedTab) != 'null'){
            sh_switchTab($(selectedTab));
        }else{
            alert('Couldn\'t find #'+selectedTab);
        }
    }
    
    function sh_switchTab(el){
        var theClass = el.ancestors()[0].id;
        $$('.'+theClass).each(function(el){
            el.removeClassName('selected');
        });
        $$('.'+theClass+'_titles').each(function(el){
            el.removeClassName('selected');
        });
        $(el.id).addClassName('selected');
        $(el.id+'_content').addClassName('selected');
    }
    var activeTab = window.location.hash;
    if(activeTab != ''){
        if(activeTab.substring(0,6) == '#show_'){
            activeTab = activeTab.substring(6);
        }else{
            activeTab = activeTab.substring(1);
        }
        sh_switchTab($(activeTab));
    }
});

function get_url_parameter( name ){
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
    return "";
  else
    return results[1];
}

function greaterThan(a,b){
    return a>b;
}
function lessThan(a,b){
    return a<b;
}


/** 
 * "number" should be a foating number between 0 and 1
 * "type" should be either 
 * - is (increasing slowering)
 * - ia (increasing accelation)
 * - ds (decreasing slowing)
 * - da (decreasing acceleration)
 * "from" (optionnal, 0 by default) : the minimum number
 * "to" (optionnal, 1 by default) : the maximum number
 */
Math.distribute = function(number,type){
	var from = (typeof arguments[2] == 'undefined') ? 0 : arguments[2];
	var to = (typeof arguments[3] == 'undefined') ? 1 : arguments[3];
    type = type.toLowerCase();
	
    if(type == 'is'){
        number =  Math.sqrt(1 - number * number);
    }else if(type == 'ia'){
        number = 1 - Math.sqrt(1 - number * number);
    }else if(type == 'da'){
        number = Math.sqrt(1 - Math.pow(number - 1, 2));
    }else{ //type = ds
        number = 1 - Math.sqrt(1 - Math.pow(number - 1, 2));
    }
	
	return number * (to - from) + from;
}
