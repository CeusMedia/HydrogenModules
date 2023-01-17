<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_NewsList extends Hook
{
	public static function onViewRenderContent( Environment $env, $context, $module, $payload = [] )
	{
		$processor		= new Logic_Shortcode( $env );
		$processor->setContent( $payload->content );
		$words			= $env->getLanguage()->getWords( 'info/newslist' );
		$shortCodes		= array(
			'newslist'	=> [
				'resource'				=> 'Info_NewsList',
				'action'				=> 'collectNews',
				'panel'					=> FALSE,
				'panel-heading'			=> $words['panel']['heading'],
				'panel-heading-level'	=> 3,
				'limit'					=> '5',
			]
		);
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			$helper		= new View_Helper_NewsList( $env );
			while( ( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				try{
				/*	$options	= ...; */
					$helper->collect( $attr['resource'], $attr['action']/*, $options */);		//  @todo add options
					$helper->setLimit( $attr['limit'] );
					$replacement	= $helper->render();											//  get newslist content
					if( $attr['panel'] ){
						$heading	= '';
						if( strlen( trim( $attr['panel-heading'] ) ) )
							$heading	= HtmlTag::create(
								'h'.$attr['panel-heading-level'],
								$attr['panel-heading']
							);
						$replacement	= HtmlTag::create( 'div', array(
							$heading,
							HtmlTag::create( 'div', $replacement, ['class' => 'content-panel-inner'] ),
						), ['class' => 'content-panel'] );
					}
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
		$payload->content	= $processor->getContent();
	}
}
