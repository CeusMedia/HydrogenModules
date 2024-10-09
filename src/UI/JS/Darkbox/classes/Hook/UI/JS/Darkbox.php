<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_JS_Darkbox extends Hook
{
	public static function onPageApplyModules( $env, $context, $module, $payload )
	{
		$config	= $env->getConfig()->getAll( 'module.ui_js_darkbox.', TRUE );
		if( $config->get( 'auto' ) ){
			$options	= json_encode( [
				'durationFadeIn'	=> $config->get( 'auto.duration.fade.in' ),
				'durationFadeOut'	=> $config->get( 'auto.duration.fade.out' ),
				'prefix'			=> $config->get( 'auto.prefix' ),
				'btnCloseLabel'		=> $config->get( 'auto.close.label' ),
				'btnCloseTitle'		=> $config->get( 'auto.close.title' ),
			], JSON_THROW_ON_ERROR );
			$selector	= '.'.$config->get( 'auto.class' );
			$script		= '$(document).ready(function(){$("'.$selector.'").darkbox('.$options.');});';
			$context->js->addScript( $script );
		}
	}
}
