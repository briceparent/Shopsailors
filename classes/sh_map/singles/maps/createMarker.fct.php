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
    var html = name + "<table><tr><td>" +'<img src="http://www.websailors.fr/images/miniatures_clients/'+onMapId+'.png"/></td><td>' + address+'</td></tr></table>';
    GEvent.addListener(marker, 'click', function() {
            marker.openInfoWindowHtml(html);
    });
    return marker;
}