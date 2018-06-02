Gallery = {
	pathImages: 'images/gallery/',
	setupIndex: function(isMobile){
		var galleryItemInfoButton = $("#gallery-item-info-button");
		if(!isMobile)
			$("div.thumbnail").on("mouseenter",function(){
				galleryItemInfoButton.unbind("click").on("click",function(){
					var url = $(this).parent().children("a").data("original");
					document.location.href = "./gallery/info/"+url;
				});
				$(this).append(galleryItemInfoButton.show());
			}).on("mouseleave",function(){
				galleryItemInfoButton.hide();
			});
	},
	setupInfo: function(viewModes){
		var viewModes = viewModes || [];
		if(viewModes.length)
			$("#hint-"+viewModes[0]).show();

		if($("img.zoomable").length){
			var config = settings.JS_cmImagnifier;
			$("img.zoomable").cmImagnifier({
				autoEnable: false,
				classContainer: config.classContainer,
				classLense: config.classLense,
				classImage: config.classImage,
				classMagnified: config.classMagnified,
				classPosition: config.classPosition,
				classRatio: config.classRatio,
				easeIn: config.easeIn,
				easeOut: config.easeOut,
				showRatio: config.showRatio,
				showPosition: config.showPosition,
				speedIn: config.speedIn,
				speedOut: config.speedOut
			});
			$("#button-fullscreen").on("click",function(){
				$("img.zoomable").addClass("fullscreenable").cmImagnifier("toggle");				//  @todo handle: module cmMagnifier not installed
				$("#button-magnifier").removeAttr("disabled");
				$("#hint-magnifier").hide();
				$("#hint-fullscreen").fadeIn(200);
				$(this).attr("disabled","disabled");
			}).attr("disabled","disabled");
			$("#button-magnifier").on("click",function(){
				$("#hint-fullscreen").hide();
				$("#hint-magnifier").fadeIn(200);
				$("img.zoomable").cmImagnifier("toggle");
				$(this).attr("disabled","disabled");
				$("img.fullscreenable").removeClass("fullscreenable");
				$("#button-fullscreen").removeAttr("disabled");
			});
		}
		var layer = $("<div></div>").prependTo($("body"));
		layer.attr("id","gallery-image-fullscreen").addClass("loading");
		layer.on("click",function(){
			$(this).fadeOut(200,function(){
				$("body").css("overflow-y", "auto");
				$(this).children("img").remove();
			});
		});

		$("img.fullscreenable").on("click", function(){
			var source = $(".gallery-image-info").data("original");
			$("#gallery-image-fullscreen")/*.addClass("loading")*/.show();
			var image =  $("<img/>").appendTo(layer);
			image.on("load",function(){
				$("#button-fullscreen").removeClass("loading");
				var ratioBody = $("body").width() / $("body").height();
				var ratioImage = $(this).get(0).width / $(this).get(0).height;
				$(this).css((ratioBody > ratioImage ? "width" : "height"), "100%");
//				$(this).parent().removeClass("loading");
				$("body").css("overflow-y", "hidden");
				$(this).fadeIn(300,function(){});
			});
			image.attr("src",Gallery.pathImages+source);
		});
		$("#button-download").on("click",function(){
			var source = $(".gallery-image-info").data("original");
			document.location.href = "./gallery/download/"+source;
		});
		$("#button-gallery").on("click",function(){
			var path	= $("#gallery").data("original").split(/\//).slice(0, -1).join("/");
			document.location.href = "./gallery/index/"+encodeURI(path);
		});
		$("#button-wallpaper").on("click",function(){
			var source = $(".gallery-image-info").data("original");
			$.ajax({
				url: "./background/set?source="+source,
				dataType: "json",
				success: function(response){
					Background.images = response.images;
					Background.change(response.id);
				}
			})
		});
	}
}
