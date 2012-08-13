/**
 *	jQuery Plugin for creating a zooming lense for an image with a linked larger image.
 *	@name		cmImagnifier
 *	@type		jQuery
 *	@cat		Plugins/UI
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	2012 Christian Würker <christian.wuerker@ceusmedia.de> (http://ceusmedia.de)
 *	@license	LGPL/CC
 *	@option		string		autoEnable				Setup image on init
 *	@option		string		classContainer			CSS class(es) set to image container
 *	@option		string		classImage				CSS class(es) set if image is magnifyable
 *	@option		string		classLense				CSS class(es) set to lense
 *	@option		string		classMagnified			CSS class(es) set if image is magnified
 *	@option		string		classPosition			CSS class(es) set to lense position
 *	@option		string		classRatio				CSS class(es) set to lense ratio
 *	@option		string		easeIn					Easing of showing Animation
 *	@option		string		easeOut					Easing of hiding Animation
 *	@option		boolean		showPosition			Show position in lense
 *	@option		boolean		showRatio				Show ratio in lense
 *	@option		integer		speedIn					Speed of showing Animation
 *	@option		integer		speedOut				Speed of hiding Animation
 */
(function($){
	jQuery.fn.cmImagnifier = function(method){

		var methods = {																				//  methods callable using constructor
			disable: function(){
				return teardownImage($(this));														//  tear down image and return result
			},
			enable: function(){
				return setupImage($(this));															//  setup image and return result
			},
			getOption: function(key){
				var settings = $(this).data('settings');											//  shortcut settings
				if(typeof(settings[key]) !== 'undefined')											//  option key is defined
					return settings[key];															//  return option value
				return null;
			},
			hideLense: function(){
				return hideLense($(this));															//  hide lense and return result
			},
			init: function(options){
				var settings = jQuery.extend({														//  options and defaults
					autoEnable: true,																//  setup image on init
					classContainer: 'cmImagnifier-container',										//  CSS class(es) set to image container
					classLense: 'cmImagnifier-lense',												//  CSS class(es) set to lense
					classImage: 'cmImagnifier-image',												//  CSS class(es) set if image is magnifyable
					classMagnified: 'cmImagnifier-image-magnified',									//  CSS class(es) set if image is magnified
					classPosition: 'cmImagnifier-lense-position',									//  CSS class(es) set to lense position
					classRatio: 'cmImagnifier-lense-ratio',											//  CSS class(es) set to lense ratio
					easeIn: 'linear',																//  easing of showing Animation
					easeOut: 'linear',																//  easing of hiding Animation
					onLoad: function(){},															//  function to call after zoomed image has been loaded
					showRatio: false,																//  show ratio in lense
					showPosition: false,															//  show position in lense
					speedIn: 0,																		//  speed of showing Animation
					speedOut: 0																		//  speed of hiding Animation
				}, options);
				return this.each(function(){														//  iterate found images
					var image = $(this);															//  shortcut current image
					image.data('status', 1);														//  note that image was found and handled
					if(image.data('original')){														//  image has zoomed image information
						if(!image.data('settings')){												//  magnifier is not prepared for this image
							image.data('status', 2);												//  note that loading succeeded
							image.data('settings', settings);										//  store settings for image
							image.wrap($('<div></div>').addClass(settings.classContainer));			//  wrap image into a new created container
							image.bind('load', function(){											//  when image is loaded
								if(image.data('settings').autoEnable)								//  automatic setup on init is enabled
									setupImage(image);												//  setup lense for image
							});
						}
					}
				});
			},
			setOption: function(key, value){
				var image = $(this);																//  shortcut image
				var settings = $(this).data('settings');											//  shortcut settings
				if(typeof(settings[key]) == 'undefined')											//  option key is not defined
					return false;
				settings[key] = value;																//  set new option value
				$(this).data('settings', settings);													//  store changed settings
				teardownImage(image);																//  tear down and
				return setupImage(image);															//  setup again to realize changes
			},
			showLense: function(){
				return showLense($(this));															//  show lense and return result
			},
			showLenseAt: function(x, y){
				var image = $(this);																//  shortcut image
				var mouseX = parseInt(x) + image.data('imageOffsetX');								//  calucalute mouse position on x axis
				var mouseY = parseInt(y) + image.data('imageOffsetY');								//  calucalute mouse position on y axis
				moveLense(image, mouseX, mouseY);													//  move lense to calculated position
				showLense($(this));																	//  show lense
			},
			toggle: function(){
				var image = $(this);																//  shortcut image
				if(image.data('status') == 2 && setupImage(image))									//  image was not setup (but loaded) and setup succeeded
					return 1;																		//  indicate that setup succeeded
				else if(image.data('status') >= 3 && teardownImage(image))							//  image is setup and tear down succeeded
					return 2;																		//  indicate that tear down succeeded
				return 0;																			//  indicate that nothing happened
			}
		}

		// Method calling logic
		if(methods[method])																			//  called method is defined
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));			//  call method with arguments
		else if(typeof(method) === 'object' || ! method)											//  options object or nothing given
			return methods.init.apply(this, arguments);												//  call init method with arguments
		$.error( 'Method ' +  method + ' does not exist on cmImagnifier' );							//  report invalid method call

		function hideLense(image){
			if(image.data('status') == 4){															//  image is setup and lense is open
				image.data('status', 3);															//  set to not open
				image.removeClass(image.data('settings').classMagnified);							//  unmark image as magnified
				var lense	= image.parent().children('div');										//  get lense
				lense.stop(true).animate({opacity: 0}, {											//  stop lense animations and start fading in
					duration: image.data('settings').speedOut,										//  duration of animation
					easing: image.data('settings').easeOut,											//  easing function for animation
					complete: function(){															//  callback when finished
						$(this).css('display', 'none');												//  hide lense completely
					}
				});
				return true;
			}
			return false;
		}

		function moveLense(image, mouseX, mouseY){
			if(image.data('status') < 3)															//  image is not setup
				return false;																		//  do nothing
			var lense	= image.parent().children('div');											//  get lense
			var posX = mouseX - image.data('imageOffsetX');											//  calculate mouse position on x axis within image
			var posY = mouseY - image.data('imageOffsetY');											//  calculate mouse position on y axis within image
			var zoomX	= posX * image.data('ratioX') - (image.data('lenseWidth') / 2);				//  calculate position on x axis in zoomed image
			var zoomY	= posY * image.data('ratioY') - (image.data('lenseHeight') / 2);			//  calculate position on y axis in zoomed image
//			zoomX = Math.min(Math.max(zoomX, 0), image.data('zoomMaxX'));							//  avoid lense to leave zoomed image on x axis
//			zoomY = Math.min(Math.max(zoomY, 0), image.data('zoomMaxY'));							//  avoid lense to leave zoomed image on y axis
			var imageWidth = image.data('imageWidth');												//  shortcut image width
			var imageHeight = image.data('imageHeight');											//  shortcut image height
			var isInside = posX >= 0 && posY >= 0 && posX < imageWidth && posY < imageHeight;		//  calculate if mouse is inside image
			if(!isInside && image.data('status') == 4){												//  mouse is outside image but lense is still open
				image.parent().trigger('mouseleave');												//  hide lense by triggering the leave event on image container
				return false;
			}
			if(isInside && image.data('status') == 3)												//  mouse is inside image but lense is not open
				showLense(image);																	//  show lense
			if(image.data('status') == 4){															//  lense is open
				lense.css('background-position', (zoomX * -1)+'px '+(zoomY * -1)+'px');				//  set position of zoomed image in lense
				var lensePosX = posX - image.data('lenseWidth') / 2;								//  calculate new lense position on x axis
				var lensePosY = posY - image.data('lenseHeight') / 2;								//  calculate new lense position on y axis
				lense.css({left: lensePosX, top: lensePosY});										//  move lense to calculated position
				var selector = '.'+image.data('settings').classPosition.replace(/ /, '.');			//  get selector of position
				lense.find(selector).html(posX+'/'+posY);											//	update position	
				return true;
			}
			return false;
		}
	
		function showLense(image){
			if(image.data('status') == 3){															//  image is setup and lense is not open
				image.addClass(image.data('settings').classMagnified);								//  mark image as magnified
				image.data('status', 4);															//  set to open
				var lense	= image.parent().children('div');										//  get lense
				lense.stop(true).css('display','block').animate({opacity: 1}, {						//  stop lense animations and start fading out
					duration: image.data('settings').speedIn,										//  duration of animation
					easing: image.data('settings').easeIn											//  easing function for animation
				});
				return true;
			}
			return false;
		}

		function setupImage(image){
			if(image.data('status') != 2)															//  image must be found and initialised
				return false;																		//  else no setup
			image.data({imageWidth: image.width(), imageHeight: image.height()});					//  store image dimensions
			image.data({imageOffsetX: image.offset().left, imageOffsetY: image.offset().top});		//  store image offset for position calculations

			//  --  CREATE LENSE  --  //
			var lense = $('<div></div>').appendTo(image.parent());									//  create lense in image container
			lense.addClass(image.data('settings').classLense).css('opacity', 0);					//  set lense CSS class and opacity for first animation
			image.data({lenseWidth: lense.width(), lenseHeight: lense.height()});					//  store lense dimensions for position calculations

			//  --  LOAD ZOOMED IMAGE  --  //
			var imageZoom	= $("<img/>").appendTo(lense);											//  create image container for zoomed image
			$(imageZoom).bind('load ready', {image: image}, function(event){						//  when zoomed image is loaded
				var zoom = $(this);																	//  shortcut zoomed image
				var image = event.data.image;														//  shortcut normal image

				//  --  CALCULATIONS  --  //
				zoom.hide().width();																//  hide original image and calculate dimensions
				image.data('zoomWidth', this.width);												//  store width of zoomed image
				image.data('zoomHeight', this.height);												//  store height of zoomed image
				var ratioX	= image.data('zoomWidth') / image.data('imageWidth');					//  calculate width ratio between normal and zoomed image
				var ratioY	= image.data('zoomHeight') / image.data('imageHeight');					//  calculate height ratio between normal and zoomed image
				var maxX	= image.data('imageWidth') * ratioX - image.data('lenseWidth');			//  calculate greated mouse position on x axis to stay in image
				var maxY	= image.data('imageHeight') * ratioY - image.data('lenseHeight');		//  calculate greated mouse position on y axis to stay in image
				image.data({zoomMaxX: maxX, zoomMaxY: maxY, ratioX: ratioX, ratioY: ratioY});		//  store calculated information
				lense.css('background-image', 'url('+encodeURI(zoom.attr('src'))+')');		//  copy zoomed image into lense background
				zoom.remove();																		//  remove zoomed image from DOM

				//  --  CREATE RATIO & POSITION  --  //
				if(image.data('settings').showRatio){												//  showing ratio is enabled
					var ratio = $('<span></span>').appendTo(lense);									//  create ratio in lense
					ratio.addClass(image.data('settings').classRatio);								//  set lense CSS class
					ratio.html('x'+Math.round(image.data('ratioX'), 1));							//  store lense dimensions for position calculations
				}
				if(image.data('settings').showPosition){											//  showing position is enabled
					var position = $('<span></span>').appendTo(lense);								//  create position in lense
					position.addClass(image.data('settings').classPosition);						//  set lense CSS class
				}

				//  --  EVENTS  --  //
				image.parent().bind('mouseenter.cmImagnifier', {image: image}, function(event){		//  mouse enters image container
					moveLense(event.data.image, event.pageX, event.pageY);							//  position lense of image in container and show (done by move automatically)
				});
				image.parent().bind('mouseleave.cmImagnifier', {image: image}, function(event){		//  mouse leaves image container
					hideLense(event.data.image);													//  hide lense of image in container
				});
				image.parent().bind('mousemove.cmImagnifier', {image: image}, function(event){		//  mouse moves inside image container (or fading lense)
					moveLense(event.data.image, event.pageX, event.pageY);							//  position lense of image in container
				});
				image.addClass(image.data('settings').classImage);									//  mark image as magnifyable

				if(image.data('settings').onLoad)													//  callback on load is defined
					image.data('settings').onLoad(image);											//  call function
			});
			$(imageZoom).attr('src', image.data('original'));										//  load zoomed image
			image.data('status', 3);																//  note that setup succeeded
			return true;																			//  indicate that setup succeeded
		}

		function teardownImage(image){
			if(image.data('status') >= 3){															//  image is atleast setup
				image.parent().children('div').remove();											//  remove lense and zoomed image
				image.data('status', 2);															//  note missing lense
				image.removeClass(image.data('settings').classImage);								//  remove CSS class of magnifyable image
				image.parent().unbind('.cmImagnifier');												//  unbind events on image container
				return true;
			}
			return false;
		}
	};
})( jQuery );
