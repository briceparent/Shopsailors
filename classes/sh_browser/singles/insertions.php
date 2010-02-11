<?php
function inserer($quoi){
	$tab=explode('+',$quoi);
	$nb=count($tab);
	for ($num=0;$num<$nb;$num++){
		if ($tab[$num]=='menu'){
			echo '<div id="contenucentral">';
		}elseif ($tab[$num]=='bas'){
			echo '</div>';
		}
		include('pages/'.$tab[$num].'.php');
	}
}

function pub($ou,$nompage){//$ou contient hautgauche ou hautdroit
	echo '<span id='.$ou.'>';
	echo '<img src="images/pub/'.$nompage.'" width="90px" height="90px">';
	echo '</span>';
}

function injectercss(){
	echo '<link rel="stylesheet" href="css/page.css" type="text/css">';
	if (isset($_GET['voir'])){
		echo '<link rel="stylesheet" href="css/temp.css" type="text/css">';
	}else{
		echo '<link rel="stylesheet" href="css/variables_css.css" type="text/css">';
	}
}
