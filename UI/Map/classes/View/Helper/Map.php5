<?php
class View_Helper_Map {

	public function __construct( $env ){
		$this->env	= $env;
	}

	static public function ___onPageApplyModules( $env, $context, $module, $data = array() ){
		$key	= $env->getConfig()->get( 'module.ui_map.apiKey' );
		if( $key ){
			$env->getPage()->js->addUrl( "https://maps.google.com/maps/api/js?key=".$key );
			return;
		}
		$url	= 'https://developers.google.com/maps/documentation/javascript/get-api-key';
		$msg	= 'Module <b>UI_Map</b> has no Google API key. Please <a href="'.$url.'" target="_blank">create</a> one and set in module configuration!';
		$env->getMessenger()->noteFailure( $msg );
	}

	public function build( $latitude, $longitude, $title = NULL, $class = NULL, $zoom = NULL ){
		return $this->render( $latitude, $longitude, $title, $class, $zoom );
	}

	static protected function getMapId( $latitude, $longitude, $title = NULL, $zoom = NULL ){
		$hash	= md5( json_encode( array( $latitude, $longitude, $title, $zoom ) ) );
		return 'map-'.$hash;
	}

	public function render( $latitude, $longitude, $title = NULL, $class = NULL, $zoom = NULL ){
		$id		= self::getMapId( $latitude, $longitude, $title, $zoom );
		$html	= $this->renderHtml( $latitude, $longitude, $title, $class, $zoom );
		$script	= $this->renderScript( $latitude, $longitude, $title, $zoom );
		$this->env->getPage()->js->addScriptOnReady( $script, 2 );
		return $html;
	}

	public function renderHtml( $latitude, $longitude, $title = NULL, $class = NULL, $zoom = NULL ){
		$id		= self::getMapId( $latitude, $longitude, $title, $zoom );
		$map	= UI_HTML_Tag::create( 'div', '', array(
			'id'				=> $id,
			'class'				=> $class,
			'data-latitude'		=> (float) $latitude,
			'data-longitude'	=> (float) $longitude,
			'data-marker-title'	=> $title,
			'data-zoom'			=> $zoom,
		) );
		return $map;
	}

	public function renderScript( $latitude, $longitude, $title = NULL, $zoom = NULL ){
		$id		= self::getMapId( $latitude, $longitude, $title, $zoom );
		$script	= sprintf( 'loadMap("%s");', $id );
		return $script;
	}
}
