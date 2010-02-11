var browser_imgEdited= new Array();
var browser_imgEditedId= new Array();

function browser_changeImg(image,id,folder){
    popupBrowser.clearParameters();
    popupBrowser.parameters.set('folder',folder);
    popupBrowser.parameters.set('types',"images");
    popupBrowser.replaceImage(image, id);
    popupBrowser.open();
    return false;
}

function browser_replaceImage(image,cpt){
    browser_imgEdited[cpt].src = image;
    browser_imgEditedId[cpt].value = image;
    return false;
}


function browser_show(session){
    var link = '/browser/show.php?type=session&session=' + session;
    window.open(
        link,
        'sh_browser',
        config='height=410, width=750, toolbar=no, menubar=no'
    );
}


function browser_return(method,image,id){
    var functionToCall = eval(method);
    functionToCall(image,id)
}

var popupBrowser = {
  url: '/browser/show.php',
  width: 750,
  height: 410,
  name:"shopsailors_browser",
  location:"no",
  menubar:"no",
  toolbar:"no",
  status:"no",
  scrollbars:"no",
  resizable:"no",
  left:"",
  top:"",
  normal:false,
  parameters: new Hash({type: 'url'}),
  open: function(){
    /*Resizing if the screen isn't big enough*/
    if(this.width < screen.availWidth){
        this.width = this.width;
    }else{
        this.width = screen.availWidth;
    }
    if(this.height < screen.availHeight){
        this.height = this.height;
    }else{
        this.height = screen.availHeight;
    }
    /*Setting the options*/
    var openOptions = 'width='+this.width+',height='+this.height;
    if(this.top!=""){
        openOptions+=",top="+this.top;
    }
    if(this.left!=""){
        openOptions+=",left="+this.left;
    }
    openOptions += ',location='+this.location+',menubar='+this.menubar;
    openOptions += ',toolbar='+this.toolbar+',scrollbars='+this.scrollbars;
    openOptions += ',resizable='+this.resizable+',status='+this.status;
    /*Opening the browser*/
    window.open(this.url + '?' + this.parameters.toQueryString(), this.name,openOptions );
    return false;
  },
  clearParameters: function(){
      this.parameters = new Hash({type: 'url'});
      return false;
  },
  replaceImage: function(imageId,inputId){
      this.parameters.set('action','browser_replaceImage');
      cpt = browser_imgEdited.length;
      browser_imgEdited[cpt] = imageId;
      browser_imgEditedId[cpt] = $(inputId);
      this.parameters.set('type','url');
      this.parameters.set('element',cpt);
      return false;
  }
}

/*
function oneReturn(param,image){
    alert('Pour #'+ param + ', on place l\'image '+image);
    return false;
}

popupBrowser.parameters.set('type','url');
popupBrowser.parameters.set('folder','shop');
popupBrowser.parameters.set('action','oneReturn');
popupBrowser.parameters.set('element','5');
popupBrowser.open();
*/

function changeImg(img,id,folder){
    /* Old version; Present for back compatibility but could be removed later */
    browser_changeImg(img, id, folder);
    return false;
}
/* Old stuffs */
/*var imgEdited= new Array();
var imgEditedId= new Array();

function browser_changeImg(img,id,folder){
    cpt = imgEdited.length;
    imgEdited[cpt] = img;
    imgEditedId[cpt] = $(id);
    var link = '/browser/show.php?type=folder&folder=' + folder;
    link += '&returnAction=browser_replaceImg&replaceImgId=' + cpt;
    window.open(
        link,
        'sh_browser',
        config='height=410, width=750, toolbar=no, menubar=no'
    );
}

function browser_replaceImg(imgFile,cpt){
    imgEdited[cpt].src = imgFile;
    imgEditedId[cpt].value = imgFile;
}
/* End of the old stuffs */