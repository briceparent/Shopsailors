/*
* Crossfade
* Version 4.1 30/03/2007
* 
* Copyright (c) 2007 Millstream Web Software http://www.millstream.com.au
* 
* Permission is hereby granted, free of charge, to any person
* obtaining a copy of this software and associated documentation
* files (the "Software"), to deal in the Software without
* restriction, including without limitation the rights to use, copy,
* modify, merge, publish, distribute, sublicense, and/or sell copies
* of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
* BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
* ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
* CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
* * 
*/

var Crossfade = Class.create();

Crossfade.prototype = {
    loaded : false,
    initialize : function(elm, options) {
        var me = this, next, prev;
        this.elm = $(elm);
        this.counter = 0;
        this.prevSlide = null;
        var t_opt = {};
        for(t in Crossfade.Transition) {
            var trans = Crossfade.Transition[t];
            if(trans.className && this.elm.hasClassName(trans.className)) {
                t_opt = {
                    transition:trans
                };
                break;
            }
        }
        this.options = Object.extend(Object.clone(Crossfade.defaults),Object.extend(options || {},t_opt));
        this.options.interval = Math.max(2,this.options.interval);
        this.elm.makePositioned();
        this.slides = this.elm.immediateDescendants();
        if(this.options.random || this.elm.hasClassName(this.options.randomClassName)){
            this.slides.sort(function(a,b){
                return me.rndm(-1,1);
            });
        }
        if(this.elm.id) {
            next = $(this.elm.id + '-next');
            prev = $(this.elm.id + '-previous');
            if(next) {
                Event.observe(next, 'click', this.next.bind(this));
            }
            if(prev) {
                Event.observe(prev, 'click', this.previous.bind(this));
            }
        }
		
        this.loadSlide(this.slides[0],function() {
            me.options.transition.prepare(me);
        });
        this.loadSlide(this.slides[1]);
		
        if(this.options.autoStart) {
            setTimeout(this.start.bind(this),this.rndm((this.options.interval-1)*1000,(this.options.interval+1)*1000));
        }
    },
    start : function() {
        this.ready = true;
        this.cycle()
        return this.timer = new PeriodicalExecuter(this.cycle.bind(this), this.options.interval);
    },
    stop : function() {
        this.options.transition.cancel(this);
        this.timer.stop();
    },
    next : function(){
        this.options.transition.cancel(this);
        this.cycle();
    },
    previous : function() {
        this.options.transition.cancel(this);
        this.cycle(-1);
    },
    cycle : function(dir) {
        if(!this.ready) {
            return;
        }
        this.ready = false;
        dir = (dir === -1) ? dir : 1;
        var me = this, prevSlide, nextSlide, opt, fade;
        prevSlide = this.slides[this.counter];
        this.counter = this.loopCount(this.counter + dir);
        if(this.counter == 0){
            this.loaded = true;
        }
        nextSlide = this.slides[this.counter];
        this.loadSlide(nextSlide, me.options.transition.cycle(prevSlide, nextSlide, me));
        if(!this.loaded) {
            this.loadSlide(this.slides[this.loopCount(this.counter+1)]);
        }
    },
    loadSlide : function(slide, onload){
        var loaders = [], me = this, img, pnode, onloadFunction;
        onload = typeof onload === 'function' ? onload : function(){};
        onloadFunction = function() {
            onload();
            me.ready = true;
        };
        slide = $(slide);
        loaders = Selector.findChildElements(slide,[this.options.imageLoadSelector]);
        if(loaders.length && loaders[0].href !== ''){
            img = document.createElement('img');
            img.className = 'loadimage';
            img.onload = onloadFunction;
            img.src = loaders[0].href;
            loaders[0].parentNode.replaceChild(img,loaders[0]);
        } else {
            loaders = [];
            loaders = Selector.findChildElements(slide, [this.options.ajaxLoadSelector]);
            if(loaders.length && loaders[0].href !== ''){
                new Ajax.Updater(slide, loaders[0].href, {
                    method:'get',
                    onComplete:onloadFunction
                });
            } else {
                onloadFunction();
            }
        }
    },
    loopCount : function(c){
        if(c >= this.slides.length){
            c = 0;
        } else if (c < 0) {
            c = this.slides.length - 1
        }
        return c;
    },
    rndm : function(min, max){
        return Math.floor(Math.random() * (max - min + 1) + min);
    },
    timer : null,
    effect : null,
    ready : false
};
Crossfade.Transition = {};
Crossfade.Transition.Switch = {
    className : 'transition-switch',
    cycle : function(prev, next, show) {
        show.slides.without(next).each(function(s){
            $(s).hide();
        })
        $(next).show();
    },
    cancel : function(show){},
    prepare : function(show){
        show.slides.each(function(s,i){
            $(s).setStyle({
                display:(i === 0 ? 'block' : 'none')
                });
        });
    }
};
Crossfade.Transition.Crossfade = {
    className : 'transition-crossfade',
    cycle : function(prev, next, show) {
        var opt = show.options;
        show.effect = new Effect.Parallel([new Effect.Fade(prev ,{
            sync:true
        }),
        new Effect.Appear(next,{
            sync:true
        })],

        {
            duration: opt.duration,
            queue : 'Crossfade',
            afterFinish:function(){
                show.slides.without(next).each(function(s){
                    $(s).setStyle({
                        opacity:0
                    });
                })
            }
            }
        );
    },
    cancel : function(show){
        if(show.effect) {
            show.effect.cancel();
        }
    },
    prepare : function(show){
        show.slides.each(function(s,i){
            $(s).setStyle({
                opacity:(i === 0 ? 1 : 0),
                visibility:'visible'
            });
        });
    }
};
Crossfade.Transition.FadeOutFadeIn = {
    className : 'transition-fadeoutfadein',
    cycle : function(prev, next, show) {
        var opt = show.options;
        show.effect = new Effect.Fade(prev ,{
            duration: opt.duration/2,
            afterFinish: function(){
                show.effect = new Effect.Appear(next,{
                    duration: opt.duration/2
                    });
                show.slides.without(next).each(function(s){
                    $(s).setStyle({
                        opacity:0
                    });
                })
            }
        });
    },
    cancel : function(show){
        if(show.effect) {
            show.effect.cancel();
        }
    },
    prepare : function(show){
        show.slides.each(function(s,i){
            $(s).setStyle({
                opacity:(i === 0 ? 1 : 0),
                visibility:'visible'
            });
        });
    }
};

Effect.DoNothing = Class.create();
Object.extend(Object.extend(Effect.DoNothing.prototype, Effect.Base.prototype), {
    initialize: function() {
        this.start({
            duration: 0
        });
    },
    update: Prototype.emptyFunction
});
Crossfade.Transition.FadeOutResizeFadeIn = {
    className : 'transition-fadeoutresizefadein',
    cycle : function(prev, next, show) {
        var opt = show.options;
        show.effect = new Effect.Fade(prev ,{
            duration: (opt.duration-1)/2,
            afterFinish: function(){
                show.slides.without(next).each(function(s){
                    $(s).setStyle({
                        opacity:0
                    });
                })
                var slideDims = [next.getWidth(),next.getHeight()];
                var loadimg = Selector.findChildElements(next,['img.loadimage']);
                if(loadimg.length && loadimg[0].offsetWidth && loadimg[0].offsetHeight){
                    slideDims[0] += slideDims[0] < loadimg[0].offsetWidth ? loadimg[0].offsetWidth : 0;
                    slideDims[1] += slideDims[1] < loadimg[0].offsetHeight ? loadimg[0].offsetHeight : 0;
                }
                var showDims = [show.elm.getWidth(),show.elm.getHeight()];
                var scale = [(showDims[0] > 0 && slideDims[0] > 0 ? slideDims[0]/showDims[0] : 1)*100,(showDims[1] > 0 && slideDims[1] > 0 ? slideDims[1]/showDims[1] : 1)*100];
                show.effect = new Effect.Parallel([
                    (scale[0] === 100 ? new Effect.DoNothing() : new Effect.Scale(show.elm,scale[0],{
                        sync:true,
                        scaleY:false,
                        scaleContent:false
                    })),
                    (scale[1] === 100 ? new Effect.DoNothing() : new Effect.Scale(show.elm,scale[1],{
                        sync:true,
                        scaleX:false,
                        scaleContent:false
                    }))
                    ],
                    {
                        duration: 1,
                        queue : 'FadeOutResizeFadeIn',
                        afterFinish: function(){
                            show.effect = new Effect.Appear(next,{
                                duration: (opt.duration-1)/2
                                });
                        }
                    }
                    );
            }
        });
    },
    cancel : function(show){
        if(show.effect) {
            show.effect.cancel();
        }
    },
    prepare : function(show){
        var slideDims = [$(show.slides[0]).getWidth(),$(show.slides[0]).getHeight()];
        show.elm.setStyle({
            width:slideDims[0]+'px',
            height:slideDims[1]+'px'
            });
        show.slides.each(function(s,i){
            $(s).setStyle({
                opacity:(i === 0 ? 1 : 0),
                visibility:'visible'
            });
        });
    }
};
Crossfade.defaults = {
    autoLoad : false,
    autoStart : false,
    random : false,
    randomClassName : 'random',
    selectors : ['.crossfade'],
    imageLoadSelector : 'a.loadimage',
    ajaxLoadSelector : 'a.load',
    interval : 6,
    duration : 1,
    transition : Crossfade.Transition.Crossfade
};
Crossfade.setup = function(options) {
    Object.extend(Crossfade.defaults,options);
};
Crossfade.load = function() {
    if(Crossfade.defaults.autoLoad) {
        Crossfade.defaults.selectors.each(function(s){
            $$(s).each(function(c){
                return new Crossfade(c);
            });
        });
    }
};

if(window.FastInit) {
    FastInit.addOnLoad(Crossfade.load);
} else {
    Event.observe(window, 'load', Crossfade.load);
}
