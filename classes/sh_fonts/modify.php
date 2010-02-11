<JAVASCRIPT>
    function changeColor(element,color){
        $(element + '_preview').style.backgroundColor = "#" + color;
        $(element).value = "#" + color;
    }
    function directChangeColor(element,color){
        $(element + '_preview').style.backgroundColor = color;
    }
</JAVASCRIPT>

<form action="" method="post">
    <table>
        <tr>
            <td>
                variations disponibles : 
            </td>
            <td>
                <ul>
                    <LOOP-VARIATIONS query="VARIATION">
                        <li>
                            Variation "<VARIATION-NAME/>"<br/>
                            - Passive : <input id="<VARIATION-NAME/>_passiveColor" name="<VARIATION-NAME/>_passiveColor" value="#<VARIATION-COLOR/>" onchange="directChangeColor('passiveColor',this.value);"/>
                            <span onclick="window.open('/include/colorPicker/color.php?element=<VARIATION-NAME/>_passiveColor&color=<VARIATION-COLOR/>', 'colorPicker', config='height=450, width=500, toolbar=no, menubar=no');"
                                        id="<VARIATION-NAME/>_passiveColor_preview" style="border:1px solid black;background-color:#<VARIATION-COLOR/>">&nbsp; &nbsp;</span><br />
                            - Active : <input id="<VARIATION-NAME/>_activeColor" name="<VARIATION-NAME/>_activeColor" value="#<VARIATION-ACTIVECOLOR/>" onchange="directChangeColor('activeColor',this.value);"/>
                            <span onclick="window.open('/include/colorPicker/color.php?element=<VARIATION-NAME/>_activeColor&color=<VARIATION-ACTIVECOLOR/>', 'colorPicker', config='height=450, width=500, toolbar=no, menubar=no');"
                                        id="<VARIATION-NAME/>_activeColor_preview" style="border:1px solid black;background-color:#<VARIATION-ACTIVECOLOR/>">&nbsp; &nbsp;</span><br />
                            - Selected : <input id="<VARIATION-NAME/>_selectedColor" name="<VARIATION-NAME/>_selectedColor" value="#<VARIATION-SELECTEDCOLOR/>" onchange="directChangeColor('activeColor',this.value);"/>
                            <span onclick="window.open('/include/colorPicker/color.php?element=<VARIATION-NAME/>_selectedColor&color=<VARIATION-SELECTEDCOLOR/>', 'colorPicker', config='height=450, width=500, toolbar=no, menubar=no');"
                                        id="<VARIATION-NAME/>_selectedColor_preview" style="border:1px solid black;background-color:#<VARIATION-SELECTEDCOLOR/>">&nbsp; &nbsp;</span>
                        </li>
                    </LOOP-VARIATIONS>
                </ul>
            </td>
        </tr>
        <tr>
            <td>
                Polices autorisées : 
            </td>
            <td>
                <LOOP-FONTS query="FONT">
                    <ul>
                        <li><input type="checkbox" name="fonts[]" value="<FONT-NAME/>"/><FONT-NAME/></li>
                    </ul>
                </LOOP-FONTS>
            </td>
        </tr>

    </table>
</form>
