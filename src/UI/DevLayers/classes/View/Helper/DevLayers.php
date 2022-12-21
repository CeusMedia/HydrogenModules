<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/**
 *	@todo		apply module config main switch
 */
class View_Helper_DevLayers
{
	protected $env;
	protected static $layers	= [];

	public function __construct( Environment $env )
	{
		$this->env		= $env;
	}

	public static function add( $id, string $label, $content, $measure = NULL )
	{
		self::$layers[]	= (object) array(
			'id'		=> $id,
			'label'		=> $label,
			'content'	=> $content,
			'measure'	=> $measure
		);
	}

	public function render(): string
	{
		if( !count( self::$layers ) )
			return "";
		$layers		= [];
		$buttons	= [];
		foreach( self::$layers as $layer ){
			$attributes	= ['class' => 'dev-layer', 'id' => 'dev-layer-'.$layer->id];
			$layers[]	= HtmlTag::create( 'div', $layer->content, $attributes );
			$attributes	= array(
				'type'		=> 'button',
				'class'		=> 'dev-layer-trigger',
				'id'		=> 'dev-layer-'.$layer->id.'-trigger',
				'onclick'	=> "UI.DevLayers.show('".$layer->id."');"
			);
			$buttons[]	= HtmlTag::create( 'button', $layer->label, $attributes );
		}
		$layers		= HtmlTag::create( 'div', $layers, ['id' => 'dev-layers'] );
		$buttons	= HtmlTag::create( 'div', $buttons, ['id' => 'dev-layer-buttons'] );
		return $layers.$buttons;
	}
}
