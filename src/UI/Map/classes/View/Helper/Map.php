<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Map
{
	protected Environment $env;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function build( $latitude, $longitude, string $title = NULL, string $class = NULL, $zoom = NULL ): string
	{
		return $this->render( $latitude, $longitude, $title, $class, $zoom );
	}

	public function render( float|string $latitude, float|string $longitude, ?string $title = NULL, ?string $class = NULL, $zoom = NULL ): string
	{
		$id		= self::getMapId( $latitude, $longitude, $title, $zoom );
		$html	= $this->renderHtml( $latitude, $longitude, $title, $class, $zoom );
		$script	= $this->renderScript( $latitude, $longitude, $title, $zoom );
		$this->env->getPage()->js->addScriptOnReady( $script, 2 );
		return $html;
	}

	public function renderHtml( float|string $latitude, float|string $longitude, ?string $title = NULL, ?string $class = NULL, $zoom = NULL ): string
	{
		$id		= self::getMapId( $latitude, $longitude, $title, $zoom );
		return HtmlTag::create( 'div', '', [
			'id'				=> $id,
			'class'				=> $class,
			'data-latitude'		=> (float) $latitude,
			'data-longitude'	=> (float) $longitude,
			'data-marker-title'	=> $title,
			'data-zoom'			=> $zoom,
		] );
	}

	public function renderScript( float|string $latitude, float|string $longitude, ?string $title = NULL, $zoom = NULL ): string
	{
		return sprintf( 'loadMap("%s");', self::getMapId( $latitude, $longitude, $title, $zoom ) );
	}

	protected static function getMapId( float|string $latitude, float|string $longitude, ?string $title = NULL, $zoom = NULL ): string
	{
		$hash	= md5( json_encode( [(float) $latitude, (float) $longitude, $title, $zoom] ) );
		return 'map-'.$hash;
	}
}
