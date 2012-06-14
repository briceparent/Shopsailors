/* 
 * This files allows submenus to unroll instead of appearing, when javascript is active
 */

Event.observe(window, "load", function(){
    $$('.with_submenus').each(function(menu){
        var id= menu.id;
        var block = $$('#'+id+' .submenus')[0];
        var destHeight = block.offsetHeight;
        block.style.overflow = 'hidden';
        block.style.height = '0';
        block.style.visibility = 'visible';
        var menuIsDevelopped = false;
        Event.observe(menu,'mouseover',function(){
            if(!menuIsDevelopped){
                menuIsDevelopped = true;
                var activeHeight = 0;
                var forceHide = false;
                new PeriodicalExecuter(function(pe) {
                    if(forceHide){
                        pe.stop();
                    }
                    activeHeight += 2;
                    var realHeight = destHeight * activeHeight / 100;
                    block.style.height = realHeight+'px';
                    if (activeHeight >= 100){
                        block.style.height = destHeight+'px';
                        pe.stop();
                    }
                }, 0.001);
                new PeriodicalExecuter(function(pe) {
                    if(sh_mouse_overEvent.element().up('#'+id) == undefined){
                        forceHide = true;
                        closeMenu(id);
                        menuIsDevelopped = false;
                        pe.stop();
                    }
                }, 0.05);
            }
        });
    });
    
    function closeMenu(id){
        var block = $$('#'+id+' .submenus')[0];
        var initHeight = block.offsetHeight;
        var activeHeight = 100;
        new PeriodicalExecuter(function(pe) {
            activeHeight -= 2;
            var realHeight = initHeight * activeHeight / 100;
            block.style.height = realHeight+'px';
            if (activeHeight <= 0){
                block.style.height = '0';
                pe.stop();
            }
        }, 0.001);
    }
});

