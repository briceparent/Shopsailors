function changeCaptchaImage(form,image){
    uri = '/captcha/changer_image_de_captcha.php';
    post = 'form=' + form;
    new Ajax.Request(uri, {
      method: 'post', parameters : post ,
      onSuccess: function(transport) {
        $(image).src =  transport.responseText;
      }
    });
}