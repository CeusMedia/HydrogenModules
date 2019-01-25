var ModuleInfoWorkshop = {

	applyImage: function($container){
console.log($container);
		if($container.data('url')){
			var positionH = $container.data('align-h') ? $container.data('align-h') : 'center';
			var positionV = $container.data('align-v') ? $container.data('align-v') : 'center';
			$container.css({
				backgroundImage: 'url("'+$container.data('url')+'")',
				backgroundPosition: positionH + ' ' + positionV
			});
		}
	},
	init: function(){
		this.initList();
		this.initView();
	},
	initList: function(){
		console.log('ModuleInfoWorkshop::init!');
		var that = this;
		var $list = jQuery('.workshop-list');
		if(!$list.length)
			return;
		$list.find('.workshop-item').each(function(){
			var $item = jQuery(this);				
			$item.on('click', function(){
				var url = $(this).data('url');
				if(url.length)
					document.location = $(this).data('url');
			});
		});
		$images	= $list.find('.workshop-item-image');
		$images.each(function(){that.applyImage(jQuery(this));});
	},
	initView: function(){
		this.applyImage(jQuery('.workshop-view .workshop-image'));
	}
};
