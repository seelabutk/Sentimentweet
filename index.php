<html>
	<head>
		<title>3Maps</title>
		<style>
			body { margin: 0; }
			canvas { width: 100%; height: 100% }
			#map
			{
				position: absolute;
				width: 100%;
				height: 100%;
			}
			#notice
			{
				width: 100%;
				height: 100%;
				z-index: 1000;
				padding: 10px;
				position: absolute;
				background-color: rgba(255, 255, 255, 0.5);
				border-radius: 5px;
				font-family: helvetica;
				text-align: center;
				vertical-align: middle;
			}
			.loading
			{
				background-image: url('coffee.png');
				background-position: -45 -45;
				top: 50%;
				width: 95px;
				height: 97px;
				left: 50%;
				margin-top: -50px;
				margin-left: -50px;
				position: absolute;
				padding-left: 20px;
				padding-top: 15px;
			}
		</style>
		<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r69/three.js"></script>
		<script src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
		<script src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/canvaslayer/src/CanvasLayer.js"></script>
		<script src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/canvaslayer/src/CanvasLayer.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.9/d3.min.js"></script>
		<script src="./lib/colorbrewer.js" charset="utf-8"></script>
		<script src="./lib/detector.js"></script>
		<script src="./lib/dat.gui.js"></script>
		<script src="./lib/threejs-layer.js"></script>
		<script src="./accs.js"></script>
		<script id="vertexshader" type="x-shader/x-vertex">
	        attribute vec3 a_color;
	        attribute float a_alpha;
	        varying vec3 v_color;
	        varying float v_alpha;
	        void main()
	        {
	            v_color = a_color;
	            v_alpha = a_alpha;
	            gl_PointSize = 10.0;
	            gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
	        }
	    </script>
	    <script id="fragmentshader" type="x-shader/x-fragment">
	        varying vec3 v_color;
	        varying float v_alpha;
	        uniform sampler2D texture;
	        void main()
	        {
	            float centerDist = length(gl_PointCoord - 0.5);
	            float radius = 0.5;
	            gl_FragColor = vec4(v_color.r, v_color.g, v_color.b, v_alpha * step(centerDist, radius));
	        }
	    </script>
		<script>
			$(document).ready(function(){
				var mapOptions = {
					zoom: 4,
					center: new google.maps.LatLng(39.50, -98.35),
					mapTypeId: google.maps.MapTypeId.ROADMAP,
					styles: [{featureType:"water",elementType:"geometry",stylers:[{color:"#000000"},{lightness:17}]},{featureType:"landscape",elementType:"geometry",stylers:[{color:"#000000"},{lightness:20}]},{featureType:"road.highway",elementType:"geometry.fill",stylers:[{color:"#000000"},{lightness:17}]},{featureType:"road.highway",elementType:"geometry.stroke",stylers:[{color:"#000000"},{lightness:29},{weight:.2}]},{featureType:"road.arterial",elementType:"geometry",stylers:[{color:"#000000"},{lightness:18}]},{featureType:"road.local",elementType:"geometry",stylers:[{color:"#000000"},{lightness:16}]},{featureType:"poi",elementType:"geometry",stylers:[{color:"#000000"},{lightness:21}]},{elementType:"labels.text.stroke",stylers:[{visibility:"on"},{color:"#000000"},{lightness:16}]},{elementType:"labels.text.fill",stylers:[{saturation:36},{color:"#000000"},{lightness:40}]},{elementType:"labels.icon",stylers:[{visibility:"off"}]},{featureType:"transit",elementType:"geometry",stylers:[{color:"#000000"},{lightness:19}]},{featureType:"administrative",elementType:"geometry.fill",stylers:[{color:"#000000"},{lightness:20}]},{featureType:"administrative",elementType:"geometry.stroke",stylers:[{color:"#000000"},{lightness:17},{weight:1.2}]}]
				};
				var mapDiv = document.getElementById('map');
				map = new google.maps.Map(mapDiv, mapOptions);

                points = [];

                $.getJSON("get_positions.php", function(results){
                    for (i in results){
                        points.push(results[i]);
                    }
                    //google.maps.event.addListenerOnce(map, 'idle', function(){
                        init();
                    //});
                });
				function init()
				{
					color = d3.scale.quantize()
			        	.domain([-1, 1])
			        	.range(colorbrewer.RdYlGn[9]);

					var colors = [];
			        var alphas = [];

                    console.log(points[0]);
					new ThreejsLayer({map: map}, function(layer){
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
                            attributes.a_alpha.value[index] = 0.2;

							index++;
						});
						var grid = new THREE.PointCloud(geometry, material);
						layer.add(grid);

						function update()
						{
							layer.render();
							//requestAnimationFrame( update );
						}
						//requestAnimationFrame( update );

						//setInterval(update, 10);
						document.getElementById("notice").style.display = "None";
						update();
					});
				}
            
			});
		</script>
	</head>
	<body>
		<div id="map"></div>
		<div id="notice">
			<div class="loading">
				Loading
			</div>
		</div>
	</body>
</html>
