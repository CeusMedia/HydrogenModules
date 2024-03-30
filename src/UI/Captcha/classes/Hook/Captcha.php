<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Captcha extends Hook
{
	public static function onViewRenderContent( Environment $env, $context, $module, array & $payload )
	{
		$config	= $env->getConfig()->getAll( 'module.ui_captcha.', TRUE );

		$default	= $config->getAll( 'default.', TRUE );
		$length		= $default->get( 'length' ) > 2 ? $default->get( 'length' ) : 4;
		$strength	= $default->get( 'strength' ) ?: 'soft';
		$width		= $default->get( 'width' ) > 0 ? $default->get( 'width' ) : 100;
		$height		= $default->get( 'height' ) > 0 ? $default->get( 'height' ) : 40;

		$processor		= new Logic_Shortcode( $env );
		$processor->setContent( $payload['content'] );
		$shortCodes		= array(
			'captcha'	=> array(
				'mode'		=> $config->get( 'mode' ),
				'length'	=> $length,
				'strength'	=> $strength,
				'width'		=> $width,
				'height'	=> $height,
			)
		);
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			$helper		= new View_Helper_Captcha( $env );
			while( is_array( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				try{
					$helper->setMode( $attr['mode'] );
					$helper->setLength( $attr['length'] );
					$helper->setStrength( $attr['strength'] );
					$helper->setWidth( $attr['width'] );
					$helper->setHeight( $attr['height'] );
					$replacement	= $helper->render();											//  get newslist content
					$processor->replaceNext(
						$shortCode,
						$replacement
					);
				}
				catch( Exception $e ){
					$env->getMessenger()->noteFailure( 'Short code failed: '.$e->getMessage() );
					break;
				}
			}
		}
		$payload['content']	= $processor->getContent();
	}
}
