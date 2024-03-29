/**
 *	@todo		apply module config main switch
 */
if(typeof UI === "undefined")
	UI = {};
UI.DevCenter = {
	init: function(){
		$("#DevCenterHandleTop").on("selectstart", function(event){
			event.stopPropagation();
			event.preventDefault();
			return false;
		});
		$("#DevCenterHandleTop").on("mousedown", function(event){
			$(document).data("dragging", "#DevCenterHandleTop");
			$("#DevCenterHandleTop").data("offset", event.pageY);
			event.stopPropagation();
			event.preventDefault();
		});
		$(document).on("mousemove", function(event){
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
		$(document).on("mouseup", function(event){
			if($(document).data("dragging") !== "#DevCenterHandleTop")
			$.ajax({
				url: "./ajax/DevCenter/setHeight",
				data: {height: $("#DevCenter").height() / $(window).height() * 100},
				type: "POST",
				success: function(){}
			});
			$(this).data("dragging", null);
			event.stopPropagation();
			event.preventDefault();
		});
		$(window).on("keyup", function(event){
			if(event.keyCode == 120){
				if($("#DevCenter").is(":visible"))
					UI.DevCenter.hide();
				else
					UI.DevCenter.show();
			}
		});
		$("#DevCenter #DevCenterContent .tabbable .navbar .nav-collapse a").on("click", function(){
			$.ajax({
				url: "./ajax/DevCenter/setTab",
				data: {tab: $(this).attr("href").substring(1)},
				type: "POST",
				success: function(){}
			});
		});
	},
	hide: function(){
		$("#DevCenter").hide();
		$.ajax("./ajax/DevCenter/setState?open=0");
	},
	show: function(){
		$("#DevCenter").show();
		$.ajax("./ajax/DevCenter/setState?open=1");
	}
};
