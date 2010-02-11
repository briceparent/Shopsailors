<?php
/**
 * Params file 
 * 
 * Params file version : 0.2
 * Licensed under LGPL
 */

if(!defined('SH_MARKER')){
	header('location: directCallForbidden.php');
}

$this->version = '0.2';

$this->values = array (
'baseColors' => 
array (
'passive' => '123438',
'active' => '132000',
'selected' => '824556',
),
'variations' => 
array (
'Navy' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Blue' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'DarkGreen' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Teal' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'DeepSkyBlue' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'SpringGreen' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'DarkSlateGray' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'SteelBlue' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Indigo' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'MediumAquaMarine' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'DimGray' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Chartreuse' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Purple' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Olive' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'DarkRed' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'SaddleBrown' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'DarkViolet' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'YellowGreen' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'DarkGray' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'LightBlue' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'FireBrick' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'DarkGoldenRod' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'RosyBrown' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'DarkKhaki' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'IndianRed' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Peru' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Chocolate' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Tan' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'LightGrey' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'GoldenRod' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'BurlyWood' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Violet' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Khaki' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'SandyBrown' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Wheat' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'EF5FEA',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Salmon' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Red' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Magenta' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'DeepPink' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'OrangeRed' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Tomato' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'HotPink' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Orange' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Gold' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Yellow' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'White' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Green' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'Aquamarine' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
'WhiteSmoke' =>array ('passive' => array ('text' => '132456','base' => '994410','transparency' => '008319',),'active' => array ('text' => '132000','base' => '448100','transparency' => 'D974E0',),'selected' => array ('text' => '824556','base' => '334230','transparency' => '008319',),),
),
'fonts' => 
    array (
        0 => 'Aarvark_Cafe.ttf',
        1 => 'Hall_Fetica_Decompose.ttf',
        2 => 'Hall_Fetica_Decompose_Italic.ttf',
        3 => 'FreeFontBold.ttf',
        4 => 'VeraBold.ttf',
        5 => 'VeraSeBd.ttf',
    ),
);
