<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_UI_Shortcode_Example extends CMF_Hydrogen_Hook
{
	static public function onViewRenderContent( Environment $env, $context, $module, $payload )
	{
		$processor		= new Logic_Shortcode( $env );
		$processor->setContent( $payload->content );
//		$words			= $env->getLanguage()->getWords( '...module/id...' );
		$shortCodes		= array(
			'example'	=> array(
				'type'			=> 'default',
			)
		);
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			while( ( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				try{
					if( $shortCode === 'example' ){
//						$helper		= new View_Helper_Module_Id_Helper( $env );
//						$helper->setLabel( $words['...section...']['...key...'] );
//						$helper->setType( $attr['type'] );
//						$replacement	= $helper->render();										//  get replacement content

						$replacement	= UI_HTML_Tag::create( 'div', array(
							'This is an example.',
						), array( 'class' => 'example-type-'.$attr['type'] ) );
						$processor->replaceNext( $shortCode, $replacement );
					}
				}
				catch( Exception $e ){
					$env->getLog()->logException( $e );
					$env->getMessenger()->noteFailure( 'Short code failed: '.$e->getMessage() );
					$processor->removeNext( $shortCode );
				}
			}
		}
		$payload->content	= $processor->getContent();
	}
}
