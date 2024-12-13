if(typeof UI === "undefined")													//  no UI object defined yet
	let UI = {};																//  define empty UI object
if(typeof UI.Image === "undefined")												//  no UI image object defined yet
	UI.Image = {};																//  define empty UI image object
UI.Image.Slider = {
	options: {
		durationSlide: 2500,
		durationShow: 2500,
		rollback: false,														//  rollback after last slide
		animation: 'slide',
		animations: ['slide', 'fade'],
		selectorPrefix: 'imageSlider-',
		strict: false,
		showDots: false
	},
	events: {
		onMoveDoneByClick: function(instance){},
		onMoveDoneBySlide: function(instance){},
		onMoveStartByClick: function(instance){},
		onMoveStartBySlide: function(instance){},
		onSliderPause: function(instance){},
		onSliderResume: function(instance){}
	},
	animateFade: function(instance, nr, slide1, slide2, callback) {
		instance.current = nr;
		slide2.css({opacity: 0, left: 0}).animate({opacity: 1},{
			duration: parseInt(instance.options.durationSlide, 10),
			easing: instance.options.easing,
			complete: function(){
				slide1.removeAttr("style");
				callback();
			}
	});
	},
	animateSlide: function(instance, nr, slide1, slide2, callback) {
		let target1 = "-100%";
		let source2 = "100%";
		if((1 || instance.options.rollback) && nr < instance.current){
			slide2.css({left: target1});
			target1 = "100%";
			source2 = "-100%";
		}
		instance.current = nr;
		slide2.css({left: source2});
		slide2.animate({left: "0"},{
			duration: parseInt(instance.options.durationSlide, 10),
			easing: instance.options.easing,
			complete: callback
		});
		slide1.animate({left: target1},{
			duration: parseInt(instance.options.durationSlide, 10),
			easing: instance.options.easing
		});
	},
	getOption: function(instance, key){
		return instance.options[key];
	},
	getSlideTitle: function(instance, nr){
		if(nr < instance.slides.length)
			return $(instance.slides[nr]).find("img").prop("title");
	},
	init: function(sliderId, options, events){
		options = $.extend({}, this.options, options);
		let prefix = options.selectorPrefix;
		let selector = "#"+prefix+sliderId;
		let instance = {
			sliderId: sliderId,
			options: options,
			events: $.extend({}, this.events, events),
			container: $(selector),
			dots: null,
			current: 0,
			timeout: null,
			paused: false
		};
		if(!$(selector).length && this.strict)
			throw 'UI.Image.Slider: Container with ID "' + selector + '" not found';
		instance.slides = $("div."+prefix+"slide", instance.container);
		$("."+prefix+"button-prev", instance.container).on("click", function(){
			instance.events.onMoveStartByClick(instance);
			UI.Image.Slider.moveBy(instance, "-1", function(){
				instance.events.onMoveDoneByClick(instance);
			});
		});
		$("."+prefix+"button-next", instance.container).on("click", function(){
			instance.events.onMoveStartByClick(instance);
			UI.Image.Slider.moveBy(instance, "+1", function(){
				instance.events.onMoveDoneByClick(instance);
			});
		});

		instance.container.on("mouseenter", {instance: instance}, function(event){
			UI.Image.Slider.pauseAutoSlide(event.data.instance);
		});
		instance.container.on("mouseleave", {instance: instance}, function(event){
			UI.Image.Slider.resumeAutoSlide(event.data.instance);
		});
		let dots = $("."+prefix+"dot", instance.container);
		dots.on("click", {instance: instance}, function(event){
			let instance = event.data.instance;
			instance.events.onMoveStartByClick(instance);
			UI.Image.Slider.moveTo(instance, $(this).data('nr'), function(){
				instance.events.onMoveDoneByClick(instance);
			});
		});
		this.startAutoSlide(instance);
		if(instance.options.showDots){
			instance.dots = $("."+prefix+"dot", instance.container);
		}
		$(window).on("resize", {instance: instance}, function(event){
			UI.Image.Slider.onSliderWidthChange(event.data.instance);
		});
		if(instance.options.scaleToFit)
			this.setWidth(instance, "100%");
		instance.container.data('slider', instance);
	},
	move: function(instance, toOrBy, callback){
		if(("" + toOrBy).match(/^(\+|-)/))
			this.moveBy(instance, toOrBy, callback);
		else
			this.moveTo(instance, toOrBy, callback);
	},
	moveBy: function(instance, by, callback){
		let total = instance.slides.length;
		let number = instance.current + parseInt(by);
		if(number >= total)
		   number -= total;
		else if(number < 0)
		   number = total - 1;
		parseInt(by, 10) > 0 ? 1 : 0;
		this.moveTo(instance, number, callback);
	},
	moveTo: function(instance, nr, callback){
		callback = callback || function(){};
		if(instance.slides.filter(":animated").length)
			return;
		nr = parseInt(nr);
		if(!(nr >= 0 && nr < instance.slides.length))
			return;
		let slide1 = instance.slides.eq(instance.current).css("z-index", 1003);
		let slide2 = instance.slides.eq(nr).css("z-index", 1004);
		slide1.stop(true, true);
		slide2.stop(true, true);
		let animation = instance.options.animation;
		if(animation === 'random'){
			let index = Math.floor(Math.random() * instance.options.animations.length);
			animation = instance.options.animations[index];
		}
		if(animation === 'slide')
			this.animateSlide(instance, nr, slide1, slide2, callback);
		else if(animation === 'fade')
			this.animateFade(instance, nr, slide1, slide2, callback);
		if(instance.options.showDots){
			instance.dots.removeClass('active');
			instance.dots.eq(nr).addClass('active');
		}
		let label = instance.container.find("."+instance.options.selectorPrefix+"label");
		let duration = instance.options.durationSlide / 2;
		let title = slide2.find("img").prop("title");
		let link = slide2.find("img").data("link");
		if(link)
			title = $("<a></a>").html(title).attr({href: link});
		label.fadeOut(duration, function(){
			label.html(title).fadeIn(duration);
		});
	},
	onSliderWidthChange: function(instance){
		let width = instance.container.width();
		let ratio = instance.container.data("ratio");
		let height = Math.floor(width * ratio);
		instance.container.height(height);
		instance.slides.each(function(){
			let content = $(this).children("."+instance.options.selectorPrefix+"slide-content");
			if(content.length){
				content.css("top", (height - content.height()) / 2);
			}
		});
	},
	pauseAutoSlide: function(instance){
		if(!instance.timeout)
			return;
		window.clearTimeout(instance.timeout);
		instance.timeout = null;
		instance.paused = true;
		instance.events.onSliderPause(instance);
	},
	resumeAutoSlide: function(instance){
		if(instance.timeout)
			return;
		instance.paused = false;
		UI.Image.Slider.startAutoSlide(instance);
		UI.Image.Slider.events.onSliderResume(instance);
	},
	setOption: function(instance, key, value){
		instance.options[key] = value;
	},
	setWidth: function(instance, width){
		instance.container.width(width);
		UI.Image.Slider.onSliderWidthChange(instance);
	},
	startAutoSlide: function(instance){
		if(instance.timeout)
			return;
		if(instance.paused)
			return true;
		if(instance.slides.length < 2)
			return true;
		instance.timeout = window.setTimeout(function(){
			instance.events.onMoveStartBySlide(instance);
			UI.Image.Slider.moveBy(instance, "+1", function(){
				instance.events.onMoveDoneBySlide(instance);
				instance.timeout = null;
				UI.Image.Slider.startAutoSlide(instance);
			});
		}, instance.options.durationShow);
	}
};
