$(document).ready(function(){
	var map;
	var context;
    radius = 3;

    // initialize the map
    var mapOptions = {
         zoom: 4,
         center: new google.maps.LatLng(39.3, -95.8),
         mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: [{featureType:"water",elementType:"geometry",stylers:[{color:"#000000"},{lightness:17}]},{featureType:"landscape",elementType:"geometry",stylers:[{color:"#000000"},{lightness:20}]},{featureType:"road.highway",elementType:"geometry.fill",stylers:[{color:"#000000"},{lightness:17}]},{featureType:"road.highway",elementType:"geometry.stroke",stylers:[{color:"#000000"},{lightness:29},{weight:.2}]},{featureType:"road.arterial",elementType:"geometry",stylers:[{color:"#000000"},{lightness:18}]},{featureType:"road.local",elementType:"geometry",stylers:[{color:"#000000"},{lightness:16}]},{featureType:"poi",elementType:"geometry",stylers:[{color:"#000000"},{lightness:21}]},{elementType:"labels.text.stroke",stylers:[{visibility:"on"},{color:"#000000"},{lightness:16}]},{elementType:"labels.text.fill",stylers:[{saturation:36},{color:"#000000"},{lightness:40}]},{elementType:"labels.icon",stylers:[{visibility:"off"}]},{featureType:"transit",elementType:"geometry",stylers:[{color:"#000000"},{lightness:19}]},{featureType:"administrative",elementType:"geometry.fill",stylers:[{color:"#000000"},{lightness:20}]},{featureType:"administrative",elementType:"geometry.stroke",stylers:[{color:"#000000"},{lightness:17},{weight:1.2}]}]
    };
    var mapDiv = document.getElementById('map');
    map = new google.maps.Map(mapDiv, mapOptions);
    var overlay = new google.maps.OverlayView();


    points = [[37.800228, -122.436116], [35.960638, -83.920739]];

    overlay.onAdd = function()
    {
        var layer = d3.select(this.getPanes().overlayLayer).append("div").attr("class", "pins");
        overlay.draw = function()
        {
            var projection = this.getProjection();
            var marker = layer.selectAll("svg")
                .data(points)
                .each(transform)
                .enter()
                .append("svg:svg")
                .each(transform)
                .attr("class", "marker");

            marker.append("svg:circle")
                .attr("r", radius)
                .attr("cx", radius)
                .attr("cy", radius)
                .attr("fill", function(d){ return 'yellow'; })
                .attr("class", "canvas_pin");

            function transform(d)
            {
                d = new google.maps.LatLng(d[0], d[1]);
                d = projection.fromLatLngToDivPixel(d);
                return d3.select(this)
                    .style("left", (d.x - radius)  + "px")
                    .style("top", (d.y - radius) + "px");
            }
        }
    }
    overlay.setMap(map);
    document.getElementById("notice").style.display = "None";
});
