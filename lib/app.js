var fuse,
  time_range;

$(document).ready(function(){
    var mapOptions = {
        zoom: 4,
        mapTypeControl: false,
        center: new google.maps.LatLng(39.50, -98.35),
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: [{featureType:"water",elementType:"geometry",stylers:[{color:"#000000"},{lightness:17}]},{featureType:"landscape",elementType:"geometry",stylers:[{color:"#000000"},{lightness:20}]},{featureType:"road.highway",elementType:"geometry.fill",stylers:[{color:"#000000"},{lightness:17}]},{featureType:"road.highway",elementType:"geometry.stroke",stylers:[{color:"#000000"},{lightness:29},{weight:.2}]},{featureType:"road.arterial",elementType:"geometry",stylers:[{color:"#000000"},{lightness:18}]},{featureType:"road.local",elementType:"geometry",stylers:[{color:"#000000"},{lightness:16}]},{featureType:"poi",elementType:"geometry",stylers:[{color:"#000000"},{lightness:21}]},{elementType:"labels.text.stroke",stylers:[{visibility:"on"},{color:"#000000"},{lightness:16}]},{elementType:"labels.text.fill",stylers:[{saturation:36},{color:"#000000"},{lightness:40}]},{elementType:"labels.icon",stylers:[{visibility:"off"}]},{featureType:"transit",elementType:"geometry",stylers:[{color:"#000000"},{lightness:19}]},{featureType:"administrative",elementType:"geometry.fill",stylers:[{color:"#000000"},{lightness:20}]},{featureType:"administrative",elementType:"geometry.stroke",stylers:[{color:"#000000"},{lightness:17},{weight:1.2}]}]
    };
    var mapDiv = document.getElementById('map');
    map = new google.maps.Map(mapDiv, mapOptions);

    points = [];
    search_terms = [];
    layer = null;

    var fuseIdx = [];
    $.getJSON("init.php", function(results){
      for (i in results){
        fuseIdx.push({
          term: results[i].slice(1)
        });
      }

      fuse = new Fuse(fuseIdx, { keys: ["term"] });
    });

    layer = new ThreejsLayer({map: map}, function(layer){});
    document.getElementById("notice").style.display = "None";

    $(".sent_input").focus();

    $(document).on("click", "#term_frequency .fa-times", function(){
        var term = $.trim($(this).parent().text());
        var index = search_terms.indexOf(term);
        search_terms.splice(index, 1);
        if(search_terms.length > 0)
        {
          update_map(search_terms);
        }
        $(this).parent().remove();
    });

    google.maps.event.addListener(map, 'click', function(event){
        var latitude = event.latLng.lat();
        var longitude = event.latLng.lng();
        $("#tweets").html('Loading...');
        $.getJSON("get_tweets.php", { lat: latitude, lng: longitude, search_terms: search_terms, search_times: time_range }, function(results){
            $("#tweets").html('');
            var tweets_text = '';
            for (i in results)
            {
                tweets_text += results[i][1] + '<hr>';
                /*$("<div>")
                    .appendTo("#tweets")
                    .addClass("tweet")
                    .text(results[i][1]);*/
            }
            if (results.length > 0)
            {
                var infowindow = new google.maps.InfoWindow({
                    content: tweets_text
                });

                var location = new google.maps.LatLng(latitude, longitude);
                var marker = new google.maps.Marker({
                    position: location,
                    map: map,
                    icon:{
                        path: ''
                    }
                });
                infowindow.open(map, marker);
                google.maps.event.addListener(infowindow, 'domready', function() {
                    $(".gm-style-iw").children(":first-child").css({'overflow': 'hidden'});
                    $(".gm-style-iw > div > div").linkify();
                });
            }
        });
    });

});

function populate_layer(layer)
{
    color = d3.scale.quantize()
        .domain([-1, 1])
        .range(colorbrewer.RdYlGn[9]);

    alpha = function(val)
    {
        return Math.max(0.1, Math.abs(val));
    }

    var colors = [];
    var alphas = [];

    var circle_image = "data:image/png;base64,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";

    /* Setting up the attributes and Shader material */
    var attributes = {
        a_color: {
            type: 'c',
            value: colors
        },
        a_alpha: {
            type: 'f',
            value: alphas
        }
    };

    var uniforms = {
        texture: {
            type: 't',
            value: 0,
            texture: circle_image
        }
    };

    var PI2 = Math.PI * 2;
    var circle_program = function ( context )
    {
        context.beginPath();
        context.arc( 0, 5, 2, 0, 5, true );
        context.closePath();
        context.fill();
    };

    var material = new THREE.ShaderMaterial({
        attributes: attributes,
        uniforms: uniforms,
        vertexShader: $("#vertexshader").text(),
        fragmentShader: $("#fragmentshader").text(),
        transparent: true,
        depthWrite: false,
    });

    var grid = new THREE.PointCloud( geometry, material );

    var geometry = new THREE.Geometry();
    index = 0;
    points.forEach(function(point){
        var location = new google.maps.LatLng(parseFloat(point[0]), parseFloat(point[1]));
        var vertex = layer.fromLatLngToVertex(location);
        geometry.vertices.push(vertex);

        var c = new THREE.Color(color(point[2]));
        attributes.a_color.value[index] = c;
        attributes.a_alpha.value[index] = 0.2;//alpha(point[2]);

        index++;
    });
    var grid = new THREE.PointCloud(geometry, material);
    layer.add(grid);
    layer.render();
}


temp_results = {};
function update_map(terms, time)
{
    points = [];
    var times = [];

    time_range = "";
    if(time !== undefined)
    {
      time_range = [
        time + " 00:00:00",
        time + " 23:59:59"
      ];
    }

    /* clear current table */
    $("#term_frequency_wrapper").css("display", "none");
    $("#search").css("display", "none");
    document.getElementById("notice").style.display = "block";
    $("#term_frequency > thead").empty();
    $("#term_frequency > tbody").empty();
    $.getJSON("get_positions.php", { search_terms: terms, search_times: time_range }, function(results){
        $.getJSON("get_heatmap.php", { search_terms: terms, search_times: time_range }, function(heatmap){
            /* get all times from search terms */
            for (term in results)
            {
                for (time in results[term])
                {
                    if(times.indexOf(time) == -1)
                    {
                        times.push(time);
                    }
                }
            }
            times.sort(function(a, b) {
                return new Date(a) - new Date(b);
            });

            /* add table headers */
            var headerHtmlStr = "<tr><th></th>";
            for(var t = 0; t < times.length; t++)
            {
                headerHtmlStr += "<th><div><span onclick='update_map(search_terms, this.innerHTML);'>" + times[t] + "</div></span></th>";
            }
            if(time_range !== "")
            {
                headerHtmlStr += "<th><div><span onclick='update_map(search_terms, undefined);'>RESET TIME</div></span></th>";
            }
            headerHtmlStr += "</tr>";
            $("#term_frequency > thead").append(headerHtmlStr);

            /* add table rows */
            for(term in results)
            {
                var bodyHtmlStr = "<tr><td><i class='fa fa-times'/>&nbsp;" + term + "</td>";
                for(time in times)
                {
                    bodyHtmlStr += "<td></td>";
                }
                bodyHtmlStr += "</tr>";
                $("#term_frequency > tbody").append(bodyHtmlStr);
            }

            var freq_color = d3.scale.quantize()
                .domain([0, 1])
                .range(colorbrewer.YlOrRd[9]);

            /* add points for layer and set up table contents */
            var termIdx = 0;
            temp_results = heatmap;
            for (term in results)
            {
                for (time in results[term])
                {
                    var timeIdx = times.indexOf(time);
                    if(timeIdx !== -1)
                    {
                        $("#term_frequency > tbody").children().eq(termIdx).children().eq(timeIdx+1).css("background-color", freq_color(heatmap[term][time]));
                    }

                    for (idx in results[term][time])
                    {
                        points.push(results[term][time][idx]);
                    }
                }

                termIdx++;
            }

            /* show table if there were search terms */
            if(terms.length > 0)
            {
                $("#term_frequency_wrapper").css("display", "block");
                $("#search").css("display", "block");
                document.getElementById("notice").style.display = "none";
            }

            // Let's remove all from the scene
            for (var i = layer.scene.children.length - 1; i >= 0; i--)
            {
                var obj = layer.scene.children[i];
                layer.scene.remove(obj);
            }
            $("#search").css("top", $("#term_frequency_wrapper").outerHeight());
            populate_layer(layer);
        });
    });
}

function add_term(term)
{
    search_terms.push(term);
    $("<div>")
        .appendTo(".selected-terms")
        .addClass("term")
        .text(term)
        .append("<i class='fa fa-times'></i>");
}

function fuseSearch(val)
{
  if (fuse !== undefined)
  {
    $(".sent_ul").empty();

    var results = fuse.search(val);
    for (i in results)
    {
      var li = document.createElement("li");

      li.innerHTML = "#" + results[i].term;
      li.onclick = function()
      {
        var term = this.innerHTML;
        add_term(term);
        update_map(search_terms, undefined);

        $(".sent_input").val("");
        $(".sent_ul").empty();
        $("#search").stop();
        $(".sent_ul").stop();
        $("#search").animate({"height":29});
        $(".sent_ul").animate({"height":0});
        $(".sent_input").css("border-radius", "0px 0px 5px 5px");
      }

      $(".sent_ul").append($(li));
    }

    $("#search").stop();
    $(".sent_ul").stop();
    $("#search").animate({"height":results.length*30+29});
    if(results.length > 0)
    {
      $(".sent_input").css("border-radius", "0px");
      $(".sent_ul").animate({"height":results.length*30});
    }
    else
    {
      $(".sent_ul").animate({"height":results.length*30}, function() {
        $(".sent_input").css("border-radius", "0px 0px 5px 5px");
      });
    }
  }
}
