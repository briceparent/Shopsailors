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
function add_all_variants(){
    var itsok = false;
    sh_popup.confirm(
        'Cette fonctionnalité supprimera toutes les variantes actuellement créées.<br />Etes-vous sûr de vouloir continuer ?',
        {
            title:'Attention!',
            onconfirmok:function(){
                $('allowModifyProps').style.display = 'none';
                $$('.allowedCustomPropertiesForVariants').each(function(el){
                    itsok = true;
                    el.checked = false;
                    $$('.oneVariant').each(function(el2){
                        Element.remove(el2);
                    });
                });
            },
            afterHide:function(){
                if(itsok){
                    sh_popup.message(
                        $('variants_create_all').innerHTML,
                        {'title':'Création des variantes'}
                    );
                    itsok = false;
                }
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
function variants_auto_check_cp(checkbox){
    $(checkbox.name).checked = checkbox.checked;
    var cssclass = checkbox.id+'_tr';
    var visible = checkbox.checked;
    $$('.'+cssclass).each(function(el){
        el.style.display = visible?'block':'none';
    });
    $$('.'+cssclass+' input[type=checkbox]').each(function(el){
        el.checked = false;
    });
    sh_popup.resizeToContent();
}
function lazyProduct(sets) {
    var setLength = sets.length;
    function helper(array_current, set_index) {
        if (++set_index >= setLength) {
            ret[ret.length] = array_current;
        } else {
            var array_next = sets[set_index];
            array_next.each(function(el){
                helper(array_current.concat(el), set_index);
            });
        }
    }
    var ret = [];
    helper([], -1);
    return ret;
}


function create_all_variants(){
    $$('.allowedCustomPropertiesForVariants').each(function(el){
        el.checked = false;
    });
    var allProps = [];
    var allPropsIds = [];
    var allPropsIdsRev = [];
    var cpt = -1;
    $$('.cp_creator').each(function(el){
        if(el.checked){
            var sub = el.name.split('_');
            $(sub[0]).checked = true;
            if(typeof(allPropsIdsRev[sub[0]]) == 'undefined'){
                cpt++;
                allPropsIds[cpt] = sub[0];
                allPropsIdsRev[sub[0]] = cpt;
                allProps[cpt] = [];
            }
            allProps[cpt][sub[1]] = sub[1];
        }
    });
    
    var ret = lazyProduct(allProps);
    
    var newContent = '<tr>';
    var props = [];
    $$('.allowedCustomPropertiesForVariants').each(function(el){
        if(el.checked){
            var name = $$('label[for='+el.id+']')[0].innerHTML;
            newContent += '<th>'+name+'</th>';
            props[props.length] = el.id;
        }
    });
    
    newContent += '<th>Stock</th><th>Ref</th><th>Prix</th>';
    newContent += '</tr>';
    var var_counter = 0;
    
    ret.each(function(el){
        newContent += '<tr>';
        var propCounter = 0;
        var ref = $('ref').value;
        var price = parseFloat($('price').value.replace(',','.'));
        el.each(function(el2){
            newContent += '<td>';
            //alert('label[for=cp_m_'+props[propCounter]+'_'+el2);
            var name=$$('label[for=cp_m_'+props[propCounter]+'_'+el2+']')[0].innerHTML;
            //alert(name);
            
            newContent += name;
            newContent += '<input type="hidden" name="variants[variant_'+props[propCounter]+'][]" value="'+el2+'"/>';
            newContent += '</td>';
            ref += '_'+el2;
            price += parseFloat($('cp_m_'+props[propCounter]+'_'+el2+'_priceToAdd').value.replace(',','.'));
            propCounter++;
        });
        newContent += '<th><input value="'+$('stock').value+'" name="variants[variant_stock][]" class="variant_stock" style="width:40px;"/></th>';
        newContent += '<th><input value="'+ref+'" name="variants[variant_ref][]" class="variant_ref" style="width:140px;"/></th>';
        newContent += '<th><input value="'+price+'" name="variants[variant_price][]" class="variant_price" style="width:60px;"/></th>';
        
        newContent += '</tr>';
        var_counter++;
    });
    
    $('variants').innerHTML = newContent;
    
    sh_popup.hide();
}
function add_variant(){
    var li = document.createElement('tr');
    var html = $('model_customProperties').innerHTML;
    li.innerHTML = html;
    li.addClassName('oneVariant');
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
                    $$('.oneVariant').each(function(el){
                        Element.remove(el);
                    });
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
