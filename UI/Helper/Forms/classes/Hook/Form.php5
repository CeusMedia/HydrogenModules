<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Form extends Hook
{
	/**
	 *	...
	 *	@access		public
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Object scope to apply hook within
	 *	@param		???				$module		???
	 *	@param		object			$payload	Data array or object for hook event handler
	 */
	public static function onViewRenderContent( Environment $env, $context, $module, $payload )
	{
		$processor		= new Logic_Shortcode( $env );
		$processor->setContent( $payload->content );
//		$words			= $env->getLanguage()->getWords( 'info/news' );
		$shortCodes		= array(
			'form'		=> array(
				'id'		=> 0,
			)
		);
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			$helper		= new View_Helper_Form( $env );
			while( ( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				try{
					$helper->setId( $attr['id'] );
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
		$payload->content	= $processor->getContent();
	}
}
