Layer = {
	current		: null,
	isOpen		: false,
	speedIn		: 0,
	speedOut	: 0,
	width		: 800,
	height		: 600,
	animationShow: {
		'opacity': 'show'
	},
	animationHide: {
		'opacity': 'hide'
	},
	speedShow: 0,
	speedHide: 0,
	buttonImageWallpaper: false,
	buttonImageDownload: false,
	buttonImageInfo: false,
	labelButtonPrev: '<span>&laquo;</span>',
	labelButtonNext: '<span>&raquo;</span>',
	labelButtonDown: '<span>download</span>',
	labelButtonInfo: '<span>info</span>',

	init: function(){
		$("a.layer-image").click(function(event){
			Layer.showImage($(this),event)
			return false;
		});
		$("a.layer-html").click(function(event){
			Layer.showContent($(this),event);
			return false;
		});
		if($("#layer-back").length)
			return;
		window.onerror = function(e){
			alert(e);
		}
		var back = $('<div></div>').attr('id','layer-back').click(Layer.hide);
		$("body").prepend(back);

		$(document).keydown(function(event){
			if(Layer.current){
				if(event.keyCode == 27)
					Layer.hide();
				if(event.keyCode == 37)
					$(".layer-info .buttons button.button.prev").not(':disabled').trigger("click");
				if(event.keyCode == 39)
					$(".layer-info .buttons button.button.next").not(':disabled').trigger("click");
			}
		});
	},
	scaleImage: function(image){
		var imageMaxWidth = Layer.width;
		var imageMaxHeight = Layer.height;
//		devLog("imageMaxX: "+imageMaxWidth+" | imageMaxY: "+imageMaxHeight);
//		devLog("imageX: "+image.width()+" | imageY: "+image.height());
		var ratioImage = image.width() / image.height();
		var ratioMax = imageMaxWidth / imageMaxHeight;
		if(ratioImage > ratioMax){
			if(image.width() > imageMaxWidth){
				image.height(image.height()*imageMaxWidth/image.width());
				image.width(imageMaxWidth);
			}
		}else if(image.height() > imageMaxHeight){
			image.width(image.width()*imageMaxHeight/image.height());
			image.height(imageMaxHeight);
		}
//		devLog("imageX: "+image.width()+" | imageY: "+image.height());
		$("div.layer").removeClass('loading');
		image.css('z-index', 1).fadeIn(150);
	},
	showContent: function(elem, width, height){
		if(!Layer.current)
			this.create();
		Layer.current.html(null);
		if(elem.attr('title')){
			var title = $('<div></div>').addClass('layer-head-title').html(elem.attr('title'));
		}
		var close = $('<button></button>').addClass('layer-head-close').append('X').click(Layer.hide);
		var head = $('<div></div>').addClass('layer-head').append(close).append(title);
		var content = $('<div></div>').addClass('layer-content');
		if( width != undefined )
			content.width(width);
		if( height != undefined )
			content.height(height);
		var iframe = '';
		if($.browser.msie)
			iframe = $('<iframe></iframe>').attr({
				'src': elem.attr('href'),
				'frameborder': 0
			});
		else
			iframe = $('<object></object>').attr({
				'data': elem.attr('href'),
				'type': 'text/html',
				'border': 0
			});
		Layer.current.append(head).append(content.html(iframe));
		Layer.show();
	},
	showImage: function(elem){
		var imageIndex	= 0;
		var image, label, buttonPrev, buttonNext, infoNavi, infoTitle, info;
		var imageGroup	= []
		if(elem.attr('rel')){
			$("a[rel='"+elem.attr('rel')+"']").each(function(i){
				imageGroup.push($(this));
				if($(this).attr('href') == elem.attr('href'))
					imageIndex = parseInt(i);
			});
		}
		else
			imageGroup.push(elem);
		if(!Layer.current)
			this.create();

		image = new Image();
		$(image).click(Layer.hide);
		image.onload = function(){Layer.scaleImage($(this).hide());};

		Layer.current.html('').append($('<div></div>').addClass('layer-image').html(image));
		image.src = elem.attr('href')+ ( $.browser.msie ? "#"+new Date().getMilliseconds() : '' );
			
		label = '<span class="label">'+Layer.labelButtonPrev+'</span>';
		buttonPrev = $('<button></button>').click(function(){
			Layer.showImage($(imageGroup[imageIndex-1]));
		}).addClass('button prev').html('<span>'+label+'</span>');
		label = '<span class="label">'+Layer.labelButtonNext+'</span>';
		buttonNext = $('<button></button>').click(function(){
			Layer.showImage($(imageGroup[imageIndex+1]));
		}).addClass('button next').html('<span>'+label+'</span>');
		if(imageIndex == 0)
			buttonPrev.attr('disabled','disabled');
		if(imageIndex == imageGroup.length - 1)
			buttonNext.attr('disabled','disabled');
		infoNavi = $('<div></div>').addClass('layer-info-navi buttons').append(buttonPrev).append(buttonNext);
		if(elem.data('original')){
			if(Layer.buttonImageDownload){
				label = '<span class="label">'+Layer.labelButtonLoad+'</span>';
				infoNavi.append($('<button></button>').click(function(){
					document.location.href = './gallery/download/'+elem.data('original');
				}).addClass('button download').html('<span>'+label+'</span>'));
			}
			if(Layer.buttonImageInfo){
				label = '<span class="label">'+Layer.labelButtonInfo+'</span>';
				infoNavi.append($('<button></button>').click(function(){
					document.location.href = './gallery/info/'+elem.data('original');
				}).addClass('button info').html('<span>'+label+'</span>'));
			}
		}
		infoTitle = $('<div></div>').addClass('layer-info-title').html(elem.attr('title'));
		info = $('<div></div>').addClass('layer-info').append(infoNavi).append(infoTitle);
		Layer.current.append(info);
		Layer.show();
	},
	create: function(){
		Layer.current = $('<div></div>').addClass('layer').addClass('loading');
		$("body").append(Layer.current);
	},
	show: function(){
		var left = Math.round(($(window).width()-Layer.current.width())/2);
		var top = Math.round(($(window).height()-Layer.current.height())/2);
//		devLog('width: '+Layer.current.width()+' | height: '+Layer.current.height());
//		devLog('top: '+top+' | left: '+left);
		Layer.current.css('top',top).css('left',left);
		if(!Layer.isOpen){
			Layer.isOpen = true;
			$("#layer-back").fadeIn(this.speedShow);
			Layer.current.animate(this.animationShow,this.speedShow);
		}
	},
	hide: function(){
		if(!Layer.isOpen)
			return;
		Layer.isOpen = false;
		$("#layer-back").fadeOut(Layer.speedHide);
		Layer.current.animate(Layer.animationHide,Layer.speedHide,function(){
			Layer.current.remove();
			Layer.current = null;
		});
	}
};
