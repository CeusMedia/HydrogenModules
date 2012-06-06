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
			if(event.keyCode == 27){
				if(Layer.current)
					Layer.hide();
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
		var imageIndex	= 0
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

		var buttonDownload = "";
		var image = new Image();
		$(image).click(Layer.hide);
		if(elem.data('original')){
			buttonDownload = $('<button class="button download"><span>download</button>').click(function(){
				document.location.href = './gallery/download/'+elem.data('original');
			});
			buttonInfo = $('<button class="button info"><span>info</button>').click(function(){
				document.location.href = './gallery/download/'+elem.data('original');
			});
		}
		image.onload = function(){
			$(this).hide();
			Layer.scaleImage($(this));
		};

		Layer.current.html('').append($('<div></div>').addClass('layer-image').html(image));
		image.src = elem.attr('href')+ ( $.browser.msie ? "#"+new Date().getMilliseconds() : '' );

		var buttonPrev = $('<button class="button no-to-prev"><span>&laquo;</span></button>').click(function(){
			Layer.showImage($(imageGroup[imageIndex-1]));
		});
		var buttonNext = $('<button class="button no-to-next"><span>&raquo;</button>').click(function(){
			Layer.showImage($(imageGroup[imageIndex+1]));
		});
		if(imageIndex == 0)
			buttonPrev.attr('disabled','disabled');
		if(imageIndex == imageGroup.length - 1)
			buttonNext.attr('disabled','disabled');
		var infoNavi = $('<div></div>').addClass('layer-info-navi buttons').append(buttonPrev).append(buttonNext).append(buttonDownload);
		var infoTitle = $('<div></div>').addClass('layer-info-title').html(elem.attr('title'));
		var info = $('<div></div>').addClass('layer-info').append(infoNavi).append(infoTitle);
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
