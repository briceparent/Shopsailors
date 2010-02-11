<style type="text/css">
  div.slider { width:150px; margin:10px 0; background-color:#ccc; height:5px; position: relative;display:inline-block;}
  div.slider div.handle { background-color:#000;height:10px;width:5px; cursor:move; position: absolute; color:#FFF;text-align:center; }
  div.slider div.hue_handle { background-color:#FF0; color: #00F;}
  div.slider div.saturation_handle { background-color:#0FF; color: #F00; }
  div.slider div.value_handle { background-color:#F0F; color: #0F0; }
  table.buttonStates {border:1px solid black;}
  table.buttonStates td {border:1px solid black;}
</style>
  <form action="" method="post">
    <table>
        <tr>
            <td>
                variations disponibles : 
            </td>
            <td>
            <script type="text/javascript">
                var hueSliders = new Array();
                var saturationSliders = new Array();
                var valueSliders = new Array();
            </script>
                <ul><LOOP-VARIATIONS query="VARIATION" >
                    <li>
                        Variation "<VARIATION-NAME/>"<br />
    <ul>
        <li>Etat "Passive" :
            <table class="buttonStates">
                <tr>
                    <td>Texte : </td>
                    <td>Teinte : </td>
                    <td>Saturation :</td>
                    <td>Valeur :</td>
                </tr>
                <tr>
                    <td><COLORPICKER-variations[<VARIATION-NAME/>][passive][color] default="<VARIATION-PASSIVECOLOR/>"/></td>
                    <td>
                        <div id="hue_slider_<VARIATION-PASSIVEUID/>" class="slider">
                            <div id="hue_handle_<VARIATION-PASSIVEUID/>" class="handle hue_handle"></div>
                            <div id="hue_value_<VARIATION-PASSIVEUID/>"></div>
                        </div>
                        <input type="hidden" name="variations[<VARIATION-NAME/>][passive][hue]" id="variations_<VARIATION-NAME/>_passive_hue" value=""/>
                    </td>
                    <td>
                        <div id="saturation_slider_<VARIATION-PASSIVEUID/>" class="slider">
                            <div id="saturation_handle_<VARIATION-PASSIVEUID/>" class="handle saturation_handle"></div>
                            <div id="saturation_value_<VARIATION-PASSIVEUID/>"></div>
                        </div>
                        <input type="hidden" name="variations[<VARIATION-NAME/>][passive][saturation]" id="variations_<VARIATION-NAME/>_passive_saturation" value=""/>
                    </td>
                    <td>
                        <div id="value_slider_<VARIATION-PASSIVEUID/>" class="slider">
                            <div id="value_handle_<VARIATION-PASSIVEUID/>" class="handle value_handle"></div>
                            <div id="value_value_<VARIATION-PASSIVEUID/>"></div>
                        </div>
                        <input type="hidden" name="variations[<VARIATION-NAME/>][passive][value]" id="variations_<VARIATION-NAME/>_passive_value" value=""/>
                    </td>
                </tr>
            </table>
        </li>
        <li>Etat "Active" : 
            <table class="buttonStates">
                <tr>
                    <td>Texte : </td>
                    <td>Teinte : </td>
                    <td>Saturation :</td>
                    <td>Valeur :</td>
                </tr>
                <tr>
                    <td><COLORPICKER-variations[<VARIATION-NAME/>][active][color] default="<VARIATION-ACTIVECOLOR/>"/></td>
                    <td>
                        <div id="hue_slider_<VARIATION-ACTIVEUID/>" class="slider">
                            <div id="hue_handle_<VARIATION-ACTIVEUID/>" class="handle hue_handle"></div>
                            <div id="hue_value_<VARIATION-ACTIVEUID/>"></div>
                        </div>
                        <input type="hidden" name="variations[<VARIATION-NAME/>][active][hue]" id="variations_<VARIATION-NAME/>_active_hue"  value=""/>
                    </td>
                    <td>
                        <div id="saturation_slider_<VARIATION-ACTIVEUID/>" class="slider">
                            <div id="saturation_handle_<VARIATION-ACTIVEUID/>" class="handle saturation_handle"></div>
                            <div id="saturation_value_<VARIATION-ACTIVEUID/>"></div>
                        </div>
                        <input type="hidden" name="variations[<VARIATION-NAME/>][active][saturation]" id="variations_<VARIATION-NAME/>_active_saturation"  value=""/>
                    </td>
                    <td>
                        <div id="value_slider_<VARIATION-ACTIVEUID/>" class="slider">
                            <div id="value_handle_<VARIATION-ACTIVEUID/>" class="handle value_handle"></div>
                            <div id="value_value_<VARIATION-ACTIVEUID/>"></div>
                        </div>
                        <input type="hidden" name="variations[<VARIATION-NAME/>][active][value]" id="variations_<VARIATION-NAME/>_active_value"  value=""/>
                    </td>
                </tr>
            </table>
        </li>
        <li>Etat "Selected" : 
            <table class="buttonStates">
                <tr>
                    <td>Texte : </td>
                    <td>Teinte : </td>
                    <td>Saturation :</td>
                    <td>Valeur :</td>
                </tr>
                <tr>
                    <td><COLORPICKER-variations[<VARIATION-NAME/>][selected][color] default="<VARIATION-SELECTEDCOLOR/>"/></td>
                    <td>
                        <div id="hue_slider_<VARIATION-SELECTEDUID/>" class="slider">
                            <div id="hue_handle_<VARIATION-SELECTEDUID/>" class="handle hue_handle"></div>
                            <div id="hue_value_<VARIATION-SELECTEDUID/>"></div>
                        </div>
                        <input type="hidden" name="variations[<VARIATION-NAME/>][selected][hue]" id="variations_<VARIATION-NAME/>_selected_hue" value=""/>
                    </td>
                    <td>
                        <div id="saturation_slider_<VARIATION-SELECTEDUID/>" class="slider">
                            <div id="saturation_handle_<VARIATION-SELECTEDUID/>" class="handle saturation_handle"></div>
                            <div id="saturation_value_<VARIATION-SELECTEDUID/>"></div>
                        </div>
                        <input type="hidden" name="variations[<VARIATION-NAME/>][selected][saturation]" id="variations_<VARIATION-NAME/>_selected_saturation" value=""/>
                    </td>
                    <td>
                        <div id="value_slider_<VARIATION-SELECTEDUID/>" class="slider">
                            <div id="value_handle_<VARIATION-SELECTEDUID/>" class="handle value_handle"></div>
                            <div id="value_value_<VARIATION-SELECTEDUID/>"></div>
                        </div>
                        <input type="hidden" name="variations[<VARIATION-NAME/>][selected][value]" id="variations_<VARIATION-NAME/>_selected_value" value=""/>
                    </td>
                </tr>
            </table>
        </li>
    </ul>
    <div id="message"></div>
            <script type="text/javascript">
                hueSliders.push(Array('<VARIATION-PASSIVEUID/>','<VARIATION-NAME/>_passive','<VARIATION-PASSIVEHUE/>'));
                saturationSliders.push(Array('<VARIATION-PASSIVEUID/>','<VARIATION-NAME/>_passive','<VARIATION-PASSIVESATURATION/>'));
                valueSliders.push(Array('<VARIATION-PASSIVEUID/>','<VARIATION-NAME/>_passive','<VARIATION-PASSIVEVALUE/>'));
                hueSliders.push(Array('<VARIATION-ACTIVEUID/>','<VARIATION-NAME/>_active','<VARIATION-PASSIVEHUE/>'));
                saturationSliders.push(Array('<VARIATION-ACTIVEUID/>','<VARIATION-NAME/>_active','<VARIATION-PASSIVESATURATION/>'));
                valueSliders.push(Array('<VARIATION-ACTIVEUID/>','<VARIATION-NAME/>_active','<VARIATION-PASSIVEVALUE/>'));
                hueSliders.push(Array('<VARIATION-SELECTEDUID/>','<VARIATION-NAME/>_selected','<VARIATION-PASSIVEHUE/>'));
                saturationSliders.push(Array('<VARIATION-SELECTEDUID/>','<VARIATION-NAME/>_selected','<VARIATION-PASSIVESATURATION/>'));
                valueSliders.push(Array('<VARIATION-SELECTEDUID/>','<VARIATION-NAME/>_selected','<VARIATION-PASSIVEVALUE/>'));
            </script>
                    </li>
                </LOOP-VARIATIONS></ul>
                <script type="text/javascript">
function hue_update(element,value,input){
    $('hue_value_' + element).innerHTML = value + '°';
    $('variations_' + input + '_hue').value = value;
};
hueSliders.each(function(uid) {
    var slider = new Control.Slider('hue_handle_' + uid[0], 'hue_slider_' + uid[0],{
        onSlide:function(v){hue_update(uid[0],Math.round(v),uid[1]);},
        onChange:function(v){hue_update(uid[0],Math.round(v),uid[1]);},
        range:$R(0,360),
        sliderValue:uid[2]
        });
        hue_update(uid[0], Math.round(uid[2]),uid[1]);
});

function saturation_update(element,value,input){
    $('saturation_value_' + element).innerHTML = value + '%';
    $('variations_' + input + '_saturation').value = value;
};
saturationSliders.each(function(uid) {
    var slider = new Control.Slider('saturation_handle_' + uid[0], 'saturation_slider_' + uid[0],{
        onSlide:function(v){saturation_update(uid[0], Math.round(v),uid[1]);},
        onChange:function(v){saturation_update(uid[0], Math.round(v),uid[1]);},
        range:$R(0,100),
        sliderValue:uid[2]
        });
        saturation_update(uid[0], Math.round(uid[2]),uid[1]);
});

function value_update(element, value, input){
    $('value_value_' + element).innerHTML = value + '%';
    $('variations_' + input + '_value').value = value;
};
valueSliders.each(function(uid) {
    var slider = new Control.Slider('value_handle_' + uid[0], 'value_slider_' + uid[0],{
        onSlide:function(v){value_update(uid[0], Math.round(v),uid[1]);},
        onChange:function(v){value_update(uid[0], Math.round(v),uid[1]);},
        range:$R(0,100),
        sliderValue:uid[2]
        });
        value_update(uid[0], Math.round(uid[2]),uid[1]);
});
                </script>
                </td>
        </tr>
        <tr>
            <td>
                Polices autorisées : 
            </td>
            <td>
                <LOOP-FONTS query="FONT">
                    <ul style="margin:0;">
                        <li><input type="checkbox" name="fonts[]" value="<FONT-NAME/>"<FONT-CHECKED/>/><FONT-NAME/></li>
                    </ul>
                </LOOP-FONTS>
            </td>
        </tr>
    </table>
    <input type="submit" value="Enregistrer"/>
    <FORM-VERIFIER name="buttonModify"/>
</form>
