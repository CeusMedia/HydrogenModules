function loadMap(id){
	var m = $("#"+id);
	if(!m.size())
		return;
	if(m.data('latitude') == 0 && m.data('longitude') == 0)
		return;
	var latlng = new google.maps.LatLng(m.data('latitude'),m.data('longitude'));
	var zoom = m.data('zoom') ? m.data('zoom') : 14;
	var myOptions = {
		zoom: zoom,
		center: latlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	var map = new google.maps.Map(document.getElementById(id), myOptions);
	if(m.data('marker-title')){
		var marker = new google.maps.Marker({
			position: latlng, 
			map: map,
			title: m.data('marker-title')
		});
	}
}
