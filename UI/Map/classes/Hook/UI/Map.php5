<?php
class Hook_UI_Map extends CMF_Hydrogen_Hook
{
	public static function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		$key	= $env->getConfig()->get( 'module.ui_map.apiKey' );
		if( $key ){
			$env->getPage()->js->addUrl( "https://maps.google.com/maps/api/js?key=".$key );
			return;
		}
		$url	= 'https://developers.google.com/maps/documentation/javascript/get-api-key';
		$msg	= 'Module <b>UI_Map</b> has no Google API key. Please <a href="'.$url.'" target="_blank">create</a> one and set in module configuration!';
		$env->getMessenger()->noteNotice( $msg );
	}
}
