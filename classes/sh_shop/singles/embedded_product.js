                    
function rSP_product_buy(product){
    var title = $$('#rSP_popup_'+product+' .rSP_productName')[0].innerHTML;
    
    var text = $('rSP_popup_'+product).innerHTML.replace(/_origin/g,'');
    var realTitle = addToCartEmbedded_i18n.replace('[PRODUCT]',title);

    sh_popup.message(
        text,
        {
            title:realTitle,
            width:900,
            onShow:function(){
                window.setTimeout(function(){
                    rSP_product_getNewPrices(product);
                },1500);

            }
        }
        );
}

function rSP_product_getNewPrices(product){
    if($('product_quantity_'+product).value == ''){
        return true;
    }
    var quantity = Math.abs(parseInt($('product_quantity_'+product).value));
    if(quantity == 0){
        quantity = 1;
    }

    var variant = 'splitted';
    var parameters = {
        quantity: quantity,
        ajax:'ajax',
        variant:variant,
        quantityElement:'product_quantity_'+product,
        variantElement:'variant_'+product
    };

    $$('.variant_element_'+product).each(function(el){
        parameters[el.name] = el.value;
    });

    var page = embeddedProduct_productPages[product];

    new Ajax.Updater('product_price_complete_'+product, 
        page+'?submitted=submitted', {
            parameters: parameters,
            evalScripts: true,
            onComplete:function(){
                sh_popup.resizeToContent();
            }
        });
}

function rSP_addToCart(product,goToCart){
    var quantity = Math.abs(parseInt($('product_quantity_'+product).value));
    if(quantity == 0){
        quantity = 1;
    }

    var variant = $('variant_'+product).value;

    new Ajax.Request(
        '/shop/add_to_cart_ajax.php', {
            parameters: {
                product:product,
                variant:variant,
                quantity: quantity
            },
            onSuccess: function(transport) {
                if(goToCart){
                    location.href="/shop/cart_show.php";
                }else{
                    sh_popup.hide();
                }
            },
            evalScripts: true
        });
    return true;
}