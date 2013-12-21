if(typeof UI === "undefined")
	UI = {};
UI.DevLayers = {
	init: function(){ 
		$("#dev-layers .dev-layer").each(function(){
			$(this).bind("click",function(){$(this).parent().fadeOut(function(){$(this).hide()});});
			var id = $(this).attr("id");
			$("#dev-layer-"+id+"-trigger").bind("click", {id: id}, function(event){
				UI.DevLayers.show(event.data.id);
			});
		});
	},
	show: function(id){
		$("#dev-layers .dev-layer").hide();
		$("#dev-layers #dev-layer-"+id).show().parent().fadeIn();
	}
};
$(document).ready(function(){
	UI.DevLayers.init();
});
