if(typeof UI === "undefined")
	UI = {};
UI.DevCenter = {
	init: function(){
		$("#DevCenterHandleTop").bind("selectstart", function(event){
			event.stopPropagation();
			event.preventDefault();
			return false;
		});
		$("#DevCenterHandleTop").bind("mousedown", function(event){
			$(document).data("dragging", "#DevCenterHandleTop");
			$("#DevCenterHandleTop").data("offset", event.pageY);
			event.stopPropagation();
			event.preventDefault();
		});
		$(document).bind("mousemove", function(event){
			if($(this).data("dragging")){
				var diff = $("#DevCenterHandleTop").data("offset") - event.pageY;
				var height = ($("#DevCenter").height() + diff) / $(window).height() * 100;
				heightNormal = Math.min(75, Math.max( 20, height));
				$("#DevCenterHandleTop").data("offset", event.pageY);
				$("#DevCenterContent").height($("#DevCenter").height() + diff - 54);
				$("#DevCenter").height(heightNormal+"%");
				if(height !== heightNormal)
					$(document).trigger("mouseup");
				event.stopPropagation();
				event.preventDefault();
			}
		});
		$(document).bind("mouseup", function(event){
				return;
			if($(document).data("dragging") !== "#DevCenterHandleTop")
			$.ajax({
				url: "./DevCenter/ajaxSetHeight",
				data: {height: $("#DevCenter").height() / $(window).height() * 100},
				type: "POST",
				success: function(){}
			});
			$(this).data("dragging", null);
			event.stopPropagation();
			event.preventDefault();
		});
		$(window).bind("keyup", function(event){
			if(event.keyCode == 120){
				if($("#DevCenter").is(":visible"))
					UI.DevCenter.hide();
				else
					UI.DevCenter.show();
			}
		});
		$("#DevCenter #DevCenterContent .tabbable .navbar .nav-collapse a").bind("click", function(){
			$.ajax({
				url: "./?action=ajaxSetTab",
				data: {tab: $(this).attr("href").substring(1)},
				type: "POST",
				success: function(){}
			});
		});
	},
	hide: function(){
		$("#DevCenter").hide();
		$.ajax("./DevCenter/ajaxSetState?open=0");
	},
	show: function(){
		$("#DevCenter").show();
		$.ajax("./DevCenter/ajaxSetState?open=1");
	}
};
