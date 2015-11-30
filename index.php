<html>
	<head>
		<title>Sentimentweet</title>
        <link href="styles/style.css" rel="stylesheet">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
		<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r69/three.js"></script>
		<script src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
		<script src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/canvaslayer/src/CanvasLayer.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.9/d3.min.js"></script>
		<script src="./lib/colorbrewer.js" charset="utf-8"></script>
		<script src="./lib/detector.js"></script>
		<script src="./lib/dat.gui.js"></script>
		<script src="./lib/threejs-layer.js"></script>
		<script src="./lib/app.js"></script>
		<script src="./lib/fuse.min.js"></script>
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
	</head>
	<body>
		<div id="map"></div>
		<div id="notice">
			<div class="loading">
				Loading...
			</div>
		</div>
		<div id="search">
			<input type="text" class="sent_input" placeholder="Enter a term..." oninput="fuseSearch(this.value);">
			<ul class="sent_ul"></ul>
            <ul class="selected-terms">

            </ul>
		</div>
		<table id="term_frequency">
			<thead></thead>
			<tbody></tbody>
		</table>
	</body>
</html>
