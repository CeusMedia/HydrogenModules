<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_News extends Hook
{
	public static function onViewRenderContent( Environment $env, object $context, $module, array & $payload = [] )
	{
		$processor		= new Logic_Shortcode( $env );
		$processor->setContent( $payload['content'] );
		$words			= $env->getLanguage()->getWords( 'info/news' );
		$shortCodes		= array(
			'news'	=> array(
				'panel'					=> FALSE,
				'panel-heading'			=> $words['panel']['heading'],
				'panel-heading-level'	=> 3,
				'limit'					=> '5',
			)
		);
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			$helper		= new View_Helper_News( $env );
			while( ( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				try{
					$helper->setLimit( $attr['limit'] );
					$replacement	= $helper->render();											//  get newslist content
					if( strlen( $replacement ) ){
						if( $attr['panel'] ){
							$heading	= '';
							if( strlen( trim( $attr['panel-heading'] ) ) )
								$heading	= UI_HTML_Tag::create(
									'h'.$attr['panel-heading-level'],
									$attr['panel-heading']
								);
							$replacement	= UI_HTML_Tag::create( 'div', array(
								$heading,
								UI_HTML_Tag::create( 'div', $replacement, array( 'class' => 'content-panel-inner' ) ),
							), array( 'class' => 'content-panel' ) );
						}
					}
					$processor->replaceNext(
						$shortCode,
						$replacement
					);
				}
				catch( Exception $e ){
					$env->getMessenger()->noteFailure( 'Short code failed: '.$e->getMessage() );
					$processor->removeNext( $shortCode );
					break;
				}
			}
		}
		$payload['content']	= $processor->getContent();
	}
}
