
var Module_UI_Map = {
	maps: [],
	addMarker: function(map, lat, lng, title, options){
		var options = jQuery.extend({}, options, {
			position: new google.maps.LatLng(lat,lng),
			map: map
		});
		if(typeof title !== "undefined")
			options.title = title;
		return marker = new google.maps.Marker(options);
	},
	getMap: function(id){
		if(typeof this.maps[id] !== "undefined")
			return this.maps[id];
	},
	loadMap: function(id){
		var container = $("#"+id);
		if(!container.size())
			return;
		if(container.hasClass("UI_Map"))
			return this.getMap(id);
		var lng = container.data('longitude');
		var lat = container.data('latitude');
		if(lat == 0 && lng == 0)
			return;
		var latlng = new google.maps.LatLng(lat, lng);
		var zoom = container.data('zoom') ? container.data('zoom') : 14;
		var myOptions = {
			zoom: zoom,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map(document.getElementById(id), myOptions);

		if(container.data('marker-title')){
			var marker = new google.maps.Marker({
				position: latlng,
				map: map,
				title: container.data('marker-title')
			});
		}
		container.addClass("UI_Map").data('map', map);
		this.maps[id] = map;
		return map;
	}
};

function loadMap(id){
	return Module_UI_Map.loadMap(id);
}

function addMarker(map, lat, lng, title, options){
	return Module_UI_Map.addMarker(map, lat, lng, title, options);
}
