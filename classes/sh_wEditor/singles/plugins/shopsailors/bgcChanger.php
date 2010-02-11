<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>bgcChanger</title>
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="js/bcgChanger.js"></script>
	<base target="_self" />
    <style>
    .bgcChanger{
    /*loat:left;*/
    border:1px solid black;
    width: 20px;
    height: 20px;
    }
    body{
    background: transparent url(img/bcg_color.png) no-repeat center 0;
    text-align:center;
     }
    #bcg_img{
        width:100%;
        height:250px;
        margin-left:1px;
    }
    table{
        width:100%;
        text-align:center;
    margin-top: 30px;
        
    }
    tr{
        
    }
    td{
        
    }
    </style>
</head>
<body>
<div id="bcg_img">
    <table>
<?php
$colors =  array('000000','993300','333300','003300','003366','000080','333399','333333','800000','FF6600',
'808000','008000','008080','0000FF','666699','808080','FF0000','FF9900','99CC00','339966',
'33CCCC','3366FF','800080','999999','FF00FF','FFCC00','FFFF00','00FF00','00FFFF','00CCFF',
'993366','C0C0C0','FF99CC','FFCC99','FFFF99','CCFFCC','CCFFFF','99CCFF','CC99FF','FFFFFF');
for($a = 0;$a<5;$a++){
    echo '      <tr>'."\n";
    for ($b=0;$b<8;$b++){
        echo '        <td><div class="bgcChanger" style="background-color: #'.$colors[$a*5+$b].'" onclick="javascript:bgcChangerDialog.insert(\'#'.$colors[$a*5+$b].'\');"></div></td>'."\n";
    }
    echo '      </tr>'."\n";
}
?>
    </table>
</div>
	
</body>
</html>