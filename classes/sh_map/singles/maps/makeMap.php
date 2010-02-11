<?php $mapKey='ABQIAAAAOJKcs20BkQS9aZeedSl6qxRhb-1VHJxMYlx-cGJFbnflSc_Y6BSWVP91DuWn2XUnURdTaST5umyE_g';?>
    <script src="http://maps.google.com/maps?file=api&v=2&key=<?php echo $mapKey; ?>" ype="text/javascript"></script>
    <script type="text/javascript">
    //<![CDATA[

    var iconBlue = new GIcon(); 
    iconBlue.image = 'http://labs.google.com/ridefinder/images/mm_20_blue.png';
    iconBlue.shadow = 'http://labs.google.com/ridefinder/images/mm_20_shadow.png';
    iconBlue.iconSize = new GSize(12, 20);
    iconBlue.shadowSize = new GSize(22, 20);
    iconBlue.iconAnchor = new GPoint(6, 20);
    iconBlue.infoWindowAnchor = new GPoint(5, 1);

    var iconRed = new GIcon(); 
    iconRed.image = 'http://labs.google.com/ridefinder/images/mm_20_red.png';
    iconRed.shadow = 'http://labs.google.com/ridefinder/images/mm_20_shadow.png';
    iconRed.iconSize = new GSize(12, 20);
    iconRed.shadowSize = new GSize(22, 20);
    iconRed.iconAnchor = new GPoint(6, 20);
    iconRed.infoWindowAnchor = new GPoint(5, 1);
//	var centerPoint=GLatLng(47.614495, -122.341861);

    function load() {
		if (GBrowserIsCompatible()) {
			var map = new GMap2(document.getElementById("map"));
			map.addControl(new GSmallMapControl());
			map.addControl(new GMapTypeControl());
			map.setCenter(new GLatLng(48., 2.0), 5);
			GDownloadUrl("<?php echo $_SERVER['document_root']; ?>global_map2.xml", function(data) {
							var xml = GXml.parse(data);
							var markers = xml.documentElement.getElementsByTagName("marker");
							for (var i = 0; i < markers.length; i++) {
								var name = markers[i].getAttribute("name");
								var address = markers[i].getAttribute("address");
								var type = markers[i].getAttribute("type");
								var onMapId = markers[i].getAttribute("id");
								var point = new GLatLng(parseFloat(markers[i].getAttribute("lat")), parseFloat(markers[i].getAttribute("lng")));
								var marker = createMarker(point, name, address, type, onMapId);
								map.addOverlay(marker);
								var myOnMap = "<?php echo MD5($_GET['id']); ?>";
								if (myOnMap==onMapId)
									map.setCenter(point, 15);
							}
						});
		}
    }

    function createMarker(point, name, address, type, onMapId) {
		var myOnMap = "<?php echo MD5($_GET['id']); ?>";
		if (myOnMap==onMapId){
			var markerOptions = {
			  icon: iconBlue,
			  title: name,
			  zIndexProcess: function(){ return 65535; }
			};
		}else{
			var markerOptions = {
			  icon: iconRed,
			  title: name
			};
		}
		var marker = new GMarker(point, markerOptions);
		var html = name + "<table><tr><td>" +'<img src="http://www.wsparent.info/images/miniatures_clients/'+onMapId+'.png"/></td><td>' + address+'</td></tr></table>';
		GEvent.addListener(marker, 'click', function() {
			marker.openInfoWindowHtml(html);
		});
		return marker;
    }
    //]]>
  </script>


  <body onload="load()" onunload="GUnload()">
    <div id="map" style="width: 400px; height: 500px"></div>
	<div>
	<?php
/*	for($a=0;$a<20;$a++)
		echo $a.'  = '.MD5($a).'<br />';
*/	?>
	</div><img src="http://www.websailors.fr/images/miniatures_clients/<?php echo MD5($_GET['id']); ?>.png"/><br />
  </body>
</html>