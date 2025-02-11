<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Shortcode_Example extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onViewRenderContent(): void
	{
		$processor		= new Logic_Shortcode( $this->env );
		$processor->setContent( $this->payload['content'] );
//		$words			= $env->getLanguage()->getWords( '...module/id...' );
		$shortCodes		= [
			'example'	=> [
				'type'			=> 'default',
			]
		];
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

						$replacement	= HtmlTag::create( 'div', [
							'This is an example.',
						], ['class' => 'example-type-'.$attr['type']] );
						$processor->replaceNext( $shortCode, $replacement );
					}
				}
				catch( Exception $e ){
					$this->env->getLog()->logException( $e );
					$this->env->getMessenger()->noteFailure( 'Short code failed: '.$e->getMessage() );
					$processor->removeNext( $shortCode );
				}
			}
		}
		$this->payload['content']	= $processor->getContent();
	}
}
