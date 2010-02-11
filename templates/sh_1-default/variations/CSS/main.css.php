<?php
include('../colors/'.$_GET['variation'].'.php');
?>
/* CSS for the <?php echo $_GET['variation']; ?> variation */

body{
    color: <?php echo $textColor; ?>;
}

#bg_middle{
	background-color: <?php echo $backgroundColor; ?>;
}


h1{
    color: <?php echo $templateColor; ?>
}
h2{
    color: <?php echo $templateColor; ?>
}
a:link, a:visited, a:hover, a:active {
    color: <?php echo $templateColor; ?>;
}
.falseLink{
    color: <?php echo $templateColor; ?>;
}
.templateColor{
    color: <?php echo $templateColor; ?>
}

div.sh_autocomplete {
  background-color: <?php echo $backgroundColor; ?>;
  border: 1px solid #888;
  color: <?php echo $textColor; ?>;
}
