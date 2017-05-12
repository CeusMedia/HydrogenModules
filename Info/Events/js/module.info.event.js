var mapMarkerStar = {
	path: "M 125,5 155,90 245,90 175,145 200,230 125,180 50,230 75,145 5,90 95,90 z",
	fillColor: "white",
	fillOpacity: 0.8,
	scale: 0.08,
	strokeColor: "blue",
	strokeWeight: 2
};

function clickItem(elem, panTo){
	var item = $(elem);
	var list = $(item.data("parent"));
	list.find(".accordion-toggle").addClass("collapsed");
//	item.trigger("click");
//	item.parent().children("li").removeClass("active");
//	item.addClass("active");
	if(panTo){
		var marker = item.data("map-marker");
		marker.map.panTo(marker.position);
	}
}

function applyMapMarkers(selectorMap, selectorData){
	var mapCanvas = $(selectorMap);
	if(!mapCanvas.size())
		throw "Map "+selectorMap+" not found";
	$(selectorData).each(function(nr){
		var lat = $(this).data("latitude");
		var lng = $(this).data("longitude");
		var item = $(this);
		var map = mapCanvas.data("map");
		if(map && lat && lng){
			var link = $(this).find("a");
			var title = $(this).data('marker-title');
			var marker = addMarker(map, lat, lng, title);
			$(this).data("map-marker", marker);
			google.maps.event.addListener(marker, "click", function(){
//				map.panTo(marker.getPosition());
//				clickItem(item);
				item.trigger("click", true);
			});
		}
	});
	$(selectorData).eq(0).trigger("click");
}

var Module_Info_Event = {
	initFilterLocationTypeahead: function(){
		$("#input_location").prop("autocomplete", "off").typeahead({
			minLength: 1,
			matcher: function(){return true;},
			source: function(query, process){
				var url = "./info/event/ajaxTypeaheadCities";
				return $.getJSON(url, {query: query}, function(data){
					return process(data.options);
				});
			}
		});
	}
}
