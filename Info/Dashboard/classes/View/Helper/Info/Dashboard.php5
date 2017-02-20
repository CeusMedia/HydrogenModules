<?php
class View_Helper_Info_Dashboard{

	protected $panels;

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function render( $columns = 3 ){
//		print_m( $this->dashboard );
//		print_m( $this->panels );
//		die;
		$list	= array();
		foreach( explode( ',', $this->dashboard->panels ) as $panelId ){
			if( !array_key_exists( $panelId, $this->panels ) )
				continue;
			$panel	= $this->panels[$panelId];

			$iconMove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrows' ) );
			$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

			$handle		= UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'a', $iconRemove, array(
					'class'		=> 'btn btn-mini btn-inverse handle-icon',
					'href'		=> './info/dashboard/removePanel/'.$panel->id,
					'onclick'	=> 'if(!confirm(\'Wirklich ?\')) return false;'
				) ),
				UI_HTML_Tag::create( 'a', $iconMove, array(
					'class'		=> 'btn btn-mini handle-icon handle-button-move',
				) ),
				UI_HTML_Tag::create( 'h4', $panel->heading ),
			), array( 'class' => 'dashboard-panel-handle' ) );
			$container	= UI_HTML_Tag::create( 'div', '', array(
				'class'	=> 'dashboard-panel-container',
				'id'	=> NULL,
			) );

			$list[]	= UI_HTML_Tag::create( 'li', array(
				UI_HTML_Tag::create( 'div', $handle.$container, array(
					'class'		=> 'thumbnail',
				) )
			), array(
				'class'			=> 'dashboard-panel span'.( 12 * $panel->cols / $columns ),
				'data-panel-id'	=> $panel->id,
				'id'			=> 'dashboard-panel-'.$panel->id,
			) );
			$script	= 'jQuery("#dashboard-panel-'.$panel->id.' .dashboard-panel-container").load("./'.$panel->url.'/'.$panel->id.'");';
			$this->env->getPage()->js->addScript( $script );
			if( $panel->refresh > 0 ){
				$script	= 'window.setInterval(function(){'.$script.'}, '.( $panel->refresh * 1000 ).');';
				$this->env->getPage()->js->addScriptOnReady( $script );
			}
		}
		$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'thumbnails sortable' ) );
		$board	= UI_HTML_Tag::create( 'div', $list, array( 'class' => '', 'id' => 'dashboard' ) );
		return $board;

		$this->env->getCaptain()->callHook( 'Dashboard', 'renderPanels', $this );
		$listSmall		= array();
		$listFull		= array();

		foreach( $this->panels as $panel ){
			$heading		= UI_HTML_Tag::create( 'h4', $panel->title );
			if( $panel->size == '3col-flex' ){
				$thumbnail		= UI_HTML_Tag::create( 'div', $heading.$panel->content, array( 'class' => 'thumbnail' ) );
				$listSmall[]	= UI_HTML_Tag::create( 'li', $thumbnail, array(
					'class'		=> 'span'.( 24 / $columns ),
					'data-key'	=> $panel->key,
				) );
//				$listFull[]	= $panel->content;
			}
			else if( $panel->size == '1col-fixed' || 1 ){
				$thumbnail		= UI_HTML_Tag::create( 'div', $heading.$panel->content, array( 'class' => 'thumbnail' ) );
				$listSmall[]	= UI_HTML_Tag::create( 'li', $thumbnail, array(
					'class'		=> 'span'.( 12 / $columns ),
					'data-key'	=> $panel->key,
				) );
			}
		}
		$listSmall	= UI_HTML_Tag::create( 'ul', $listSmall, array( 'class' =>'thumbnails sortable' ) );
		$script	= '<script>
jQuery(document).ready(function(){
	jQuery(".thumbnails.sortable").sortable({
		containment: "parent",
		stop: function( event, ui ) {
			var list = [];
			$("ul.thumbnails>li").each(function(){
				list.push($(this).data("key"))
				console.log(list);
			});
		}
	});
});</script>';
		$style	= '<style>
ul.thumbnails div.thumbnail {
	text-align: left;
	height: 300px;
	}
</style>';
		return $listSmall.join( '', $listFull ).$script.$style;
	}

	public function setDashboard( $dashboard ){
		$this->dashboard	= $dashboard;
	}

	public function setPanels( $panels ){
		$this->panels	= $panels;
	}
/*
	public function unregisterPanel( $key ){
		if( !isset( $this->panels[$key] ) )
			return FALSE;
		unset( $this->panels[$key] );
		return TRUE;
	}*/
}
?>
