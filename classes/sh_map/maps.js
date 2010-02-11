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
var map;
function centerOn(lat,lng,marker){
    alert(lat + ',' + lng)
    var point=new GLatLng(lat,lng);
    map.setCenter(point,15);
    GEvent.trigger(marker,'click');
}

function loadMap(myOnMap) {
    if (GBrowserIsCompatible()) {
            map = new GMap2(document.getElementById("map"));
            map.addControl(new GSmallMapControl());
            map.addControl(new GMapTypeControl());
            map.setCenter(new GLatLng(48., 2.0), 5);
            map.enableScrollWheelZoom();
            GDownloadUrl("/include/global_map.xml", function(data) {
                    var xml = GXml.parse(data);
                    var markers = xml.documentElement.getElementsByTagName("marker");
                    for (var i = 0; i < markers.length; i++) {
                        var name = markers[i].getAttribute("name");
                        var address = markers[i].getAttribute("address");
                        var type = markers[i].getAttribute("type");
                        var onMapId = markers[i].getAttribute("id");
                        lat = parseFloat(markers[i].getAttribute("lat"));
                        lng = parseFloat(markers[i].getAttribute("lng"));
                        var marker = createMarker(lat,lng, name, address, type, onMapId,myOnMap);
                        map.addOverlay(marker);
                        if (myOnMap == onMapId){
                            GEvent.trigger(marker, 'click');
                        }
                    }
                });
    }
}

function createMarker(lat,lng, name, address, type, onMapId,myOnMap) {
    var point = new GLatLng(lat,lng );
    if (myOnMap == onMapId){
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
    var html = name + "<table><tr><td>" +'<img src="/images/'+onMapId+'.png"/></td><td>' + address+'</td></tr></table>';

    var sidebarEntry = createSidebarEntry(marker, name, address);
    $('mapSidebar').appendChild(sidebarEntry);

    GEvent.addListener(marker, 'click', function() {
        marker.openInfoWindowHtml(html);
    });
    if (myOnMap == onMapId){
        map.setCenter(point, 15);
    }
    return marker;
}

function createSidebarEntry(marker, name, address) {
  var div = document.createElement('div');
  var html = '' + name + '<br />' + address;
  div.innerHTML = html;
  div.style.cursor = 'pointer';
  div.style.marginBottom = '5px';
  GEvent.addDomListener(div, 'click', function() {
    GEvent.trigger(marker, 'click');
  });
  GEvent.addDomListener(div, 'mouseover', function() {
    div.style.backgroundColor = '#eee';
  });
  GEvent.addDomListener(div, 'mouseout', function() {
    div.style.backgroundColor = 'transparent';
  });
  return div;
}
