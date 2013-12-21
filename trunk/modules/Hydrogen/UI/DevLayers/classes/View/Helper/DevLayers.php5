<?php
class View_Helper_DevLayers{

	static protected $layers	= array();

	public function __construct( $env ){
		$this->env		= $env;
	}

	static public function add( $id, $label, $content, $measure = NULL ){
		self::$layers[]	= (object) array(
			'id'		=> $id,
			'label'		=> $label,
			'content'	=> $content,
			'measure'	=> $measure
		);
	}

	public function render(){
		if( !count( self::$layers ) )
			return "";
		$layers		= array();
		$buttons	= array();
		foreach( self::$layers as $layer ){
			$attributes	= array( 'class' => 'dev-layer', 'id' => 'dev-layer-'.$layer->id );
			$layers[]	= UI_HTML_Tag::create( 'div', $layer->content, $attributes );
			$attributes	= array(
				'type'		=> 'button',
				'class'		=> 'dev-layer-trigger',
				'id'		=> 'dev-layer-'.$layer->id.'-trigger',
				'onclick'	=> "UI.DevLayers.show('".$layer->id."');"
			);
			$buttons[]	= UI_HTML_Tag::create( 'button', $layer->label, $attributes );
		}
		$layers		= UI_HTML_Tag::create( 'div', $layers, array( 'id' => 'dev-layers' ) );
		$buttons	= UI_HTML_Tag::create( 'div', $buttons, array( 'id' => 'dev-layer-buttons' ) );
		return $layers.$buttons;
	}
}
?>
