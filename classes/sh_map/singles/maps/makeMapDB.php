<?php
	define('ANGIE_GENERAL_DB','angie_general');
	define('ANGIE_GENERAL_HOST','localhost');
	define('ANGIE_GENERAL_USER','www-data');
	define('ANGIE_GENERAL_PASSWORD','aqwzsxedc');
	define('ANGIE_GENERAL_GMAPS_KEY','ABQIAAAAOJKcs20BkQS9aZeedSl6qxQ_SjxQSxiudrSNA_pUeqPudjHXABQJFkuxQiKfRny6AT_SboMv0vxxug');
	define("ANGIE_GENERAL_GMAPS_HOST", "maps.google.fr");
	
function connectToMysql(){
	// Opens a connection to a MySQL server
	$connexion=mysql_connect (ANGIE_GENERAL_HOST, ANGIE_GENERAL_USER, ANGIE_GENERAL_PASSWORD);
	if (!$connexion) {
	  die('Not connected : ' . mysql_error());
	}
	
	// Set the active MySQL database
	$db_selected = mysql_select_db(ANGIE_GENERAL_DB, $connexion);
	if (!$db_selected) {
	  die ('Can\'t use db : ' . mysql_error());
	}
	return $connexion;
}

function buildXML(){
	// Start XML file, create parent node
	$dom = new DOMDocument("1.0");
	$node = $dom->createElement("markers");
	$parnode = $dom->appendChild($node); 
	
	$connexion=connectToMysql();
	// Select all the rows in the markers table
	$query = "SELECT * FROM `gmarkers` WHERE 1";
	$result = mysql_query($query,$connexion);
	if (!$result) {
	  die('Invalid query: ' . mysql_error());
	}
	
	
	while ($row = @mysql_fetch_assoc($result)){  
	  // ADD TO XML DOCUMENT NODE  
	  $node = $dom->createElement("marker");  
	  $newnode = $parnode->appendChild($node);   
	  $newnode->setAttribute("name",$row['name']);
	  $newnode->setAttribute("address", $row['address']);  
	  $newnode->setAttribute("lat", $row['lat']);  
	  $newnode->setAttribute("lng", $row['lng']);  
	  $newnode->setAttribute("type", $row['type']);
	  $newnode->setAttribute("id",MD5( $row['id']));
	} 
	
	$f=fopen('global_map2.xml','w+');
	fputs($f,$dom->saveXML());
	fclose($f);
?>
Le fichier a &eacute;t&eacute; cr&eacute;&eacute; avec succ&egrave;s.
<?php
}

function addElementInDb($address){
	$name=$_POST['name'];
	$base_url = "http://" . ANGIE_GENERAL_GMAPS_HOST . "/maps/geo?output=csv" . "&key=" . ANGIE_GENERAL_GMAPS_KEY;
    $request_url = $base_url . "&q=" . urlencode($address);
	echo $request_url.'<br />';
    $csv = file_get_contents($request_url) or die("url not loading");
print_r($csv);
    $csvSplit = split(",", $csv);
    $status = $csvSplit[0];
    $lat = $csvSplit[2];
    $lng = $csvSplit[3];

    if (strcmp($status, "200") == 0) {
		// Successful geocode
		$connexion=connectToMysql();
		$query = "INSERT INTO `gmarkers` (`name`, `address`, `lat`, `lng`, `type`) VALUES ('".htmlentities($name)."','".htmlentities($address)."', '".$lat."','".$lng."', 'client');";
		$result = mysql_query($query,$connexion);
		if (!$result) {
		  die('Invalid query: ' . mysql_error());
		}
		echo 'Insertion effectu&eacute;e avec succ&egrave;s!<br />';
	}else{
		echo 'Non troiuv&eacute;';
	}
	
}
if(isset($_POST['address']) && strlen($_POST['address'])>2){
	addElementInDb($_POST['address']);
}elseif(isset($_POST['update']))
	buildXML();
?>
<form action="<?php echo $_SERVER['SERVER_REQUEST']; ?>" method="POST" >
<input type="hidden" name="update" /><br />
<input type="submit" value="Mettre &aacute; jour"/>
</form><br />
<form action="<?php echo $_SERVER['SERVER_REQUEST']; ?>" method="POST" >
Nom: <input name="name" /><br />
Adresse: <input name="address" /><br />
<input type="submit" value="Envoyer"/>
</form>