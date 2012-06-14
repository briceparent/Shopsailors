function sh_shop_toggleVariants(){
    var productHasVariants = $('hasVariants').checked;

    if(productHasVariants && $('variantsChangeStock').checked){
        $$('.variant_stock').each(function(el){
            el.disabled = false;
        });
        $('stock').disabled = true;
    }else{
        $$('.variant_stock').each(function(el){
            el.disabled = true;
        });
        $('stock').disabled = false;
    }

    if(productHasVariants && $('variantsChangePrice').checked){
        $$('.variant_price').each(function(el){
            el.disabled = false;
        });
        $('price').disabled = true;
    }else{
        $$('.variant_price').each(function(el){
            el.disabled = true;
        });
        $('price').disabled = false;
    }

    if(productHasVariants && $('variantsChangeRef').checked){
        $$('.variant_ref').each(function(el){
            el.disabled = false;
        });
        $('ref').disabled = true;
    }else{
        $$('.variant_ref').each(function(el){
            el.disabled = true;
        });
        $('ref').disabled = false;
    }
    if(!productHasVariants){
        $('productsVariants').hide();
    }else{
        $('productsVariants').show();
    }
}

var clickedElement = null;
function add_variant(){
    var li = document.createElement('li');
    var html = '<table>';
    $$('.allowedCustomPropertiesForVariants').each(function(el){
        if(el.checked){
            html += $('model_'+el.id).innerHTML;
        }
    });
    html += $('model_ref').innerHTML;
    html += $('model_price').innerHTML;
    html += $('model_stock').innerHTML;
    html += '</table>';
    li.innerHTML = html;
    $('variants').insert(li);
}

function variants_enable_change(){
    if($('variants').innerHTML == ''){
        $('allowModifyProps').style.display = 'none';
        $$('.allowedCustomPropertiesForVariants').each(function(el){
            el.disabled = false;
            $('variants').innerHTML = '';
        });
        return true;
    }
    sh_popup.confirm(
        'Le fait de modifier les propriétés personnalisées utilisables dans les variantes supprimera les variantes actuellement créées.',
        {
            title:'Attention!',
            onconfirmok:function(){
                $('allowModifyProps').style.display = 'none';
                $$('.allowedCustomPropertiesForVariants').each(function(el){
                    el.disabled = false;
                    $('variants').innerHTML = '';
                });
            },
            onconfirmcancel:function(){
                if(clickedElement != null){
                    clickedElement.checked = !clickedElement.checked;
                }
                return false;
            }
        }
    );
    return true;
}

function beforeSave(){
    return true;
}

Event.observe(window,'load',function(){
    sh_shop_toggleVariants();
    Event.observe('productEditor', 'submit', function(event){
        var ok = false;
        $$('.shop_categories').each(function(el){
            if(el.checked){
                ok = true;
                return true;
            }
            return true;
        });
        if(!ok){
            Event.stop(event);
            sh_popup.alert(msg_shop_shouldSelectACategory_content,{
                title:msg_shop_shouldSelectACategory_title
            });
            return false;
        }
        $$('input').each(function(el){
            el.disabled = false;
        });
        return true;
    });
});
