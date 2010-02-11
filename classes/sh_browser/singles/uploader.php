<?php 
//On param�tre la largeur, la hauteur et le poids maxi � ne pas d�passer. 
$taille_maxi ="30720000";//bytes 
$fichier = $_FILES['fichier']['name']; 
$tab = array(' ', '&',"'",'%','$');
$fichier = str_replace($tab, '_', $fichier);
$speciaux="�����������������������������������������������������";
$normaux="AAAAAAaaaaaaCcOOOOOOooooooEEEEeeeeIIIIiiiiUUUUuuuuyNn";
$fichier = strtr($fichier, $speciaux, $normaux);
$taille=$_FILES['fichier']['size']; 
$tmp = $_FILES['fichier']['tmp_name']; 
$size_tmp=getimagesize ($tmp); 
if ($fichier !="none")
	{
	if ($taille < $taille_maxi)
		{
		$point=strrpos($fichier, "."); 
		if ($point) 
			$extension=substr ($fichier, $point); 
		else 
			$extension =""; 
		//indiquer l'url relative vers le dossier d'upload 
		$chemin=$_POST['dossier']; 
		//un nouveau nom qui prend en compte la date, l'heure, les minutes  
		//et secondes est cr�e ; ainsi, aucune image ne peut �tre �cras�e sur le serveur 
		$nouveau_nom = $chemin.'/'.$fichier; 
		move_uploaded_file($tmp, $nouveau_nom);
		}
		else 
		{ 
		$retour='Le fichier est trop lourd; Retenter avec une image plus petite.';
		}
	}
?> 
<html>
<head>
<script>
function envoimessage()
	{
	window.opener.message("<?php echo $retour ?>","<?php echo $chemin ?>");
	window.close ();
	}
</script>
</head>
<body onload="envoimessage();">
</body>
</html>