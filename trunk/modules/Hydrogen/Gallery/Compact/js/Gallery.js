Gallery = {
	pathImages: 'images/gallery/',
	setupIndex: function(isMobile){
		var galleryItemInfoButton = $("#gallery-item-info-button");
		if(!isMobile)
			$("div.thumbnail").bind("mouseenter",function(){
				galleryItemInfoButton.unbind("click").bind("click",function(){
					var url = $(this).parent().children("a").data("original");
					document.location.href = "./gallery/info/"+url;
				});
				$(this).append(galleryItemInfoButton.show());
			}).bind("mouseleave",function(){
				galleryItemInfoButton.hide();
			});
	},
	setupInfo: function(){
		if($("img.zoomable").size()){
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
			$("#button-fullscreen").bind("click",function(){
				$("img.zoomable").addClass("fullscreenable").cmImagnifier("toggle");				//  @todo handle: module cmMagnifier not installed
				$("#button-magnifier").removeAttr("disabled");
				$("#hint-magnifier").hide();
				$("#hint-fullscreen").fadeIn(200);
				$(this).attr("disabled","disabled");
			}).attr("disabled","disabled");
			$("#button-magnifier").bind("click",function(){
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
		layer.bind("click",function(){
			$(this).fadeOut(200,function(){
				$(this).children("img").remove();
			});
		});
		
		$("img.fullscreenable").bind("click", function(){
			var source = $(".gallery-image-info").data("original");
			$("#gallery-image-fullscreen")/*.addClass("loading")*/.show();
			var image =  $("<img/>").appendTo(layer);
			image.bind("load",function(){
				$("#button-fullscreen").removeClass("loading");
				var ratioBody = $("body").width() / $("body").height();
				var ratioImage = $(this).get(0).width / $(this).get(0).height;
				$(this).css((ratioBody > ratioImage ? "width" : "height"), "100%");
//				$(this).parent().removeClass("loading");
				$(this).fadeIn(300,function(){});
			});
			image.attr("src",Gallery.pathImages+source);
		});
		$("#button-download").bind("click",function(){
			var source = $(".gallery-image-info").data("original");
			document.location.href = "./gallery/download/"+source;
		});
		$("#button-gallery").bind("click",function(){
			var path	= $("#gallery").data("original").split(/\//).slice(0, -1).join("/");
			document.location.href = "./gallery/index/"+encodeURI(path);
		});
		$("#button-wallpaper").bind("click",function(){
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