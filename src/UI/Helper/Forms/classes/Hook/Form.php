<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Form extends Hook
{
	/**
	 *	Injects forms for shortcodes.
	 *	@access		public
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onViewRenderContent(): void
	{
		$processor	= new Logic_Shortcode( $this->env );
		$processor->setContent( $this->payload['content'] );
//		$words		= $env->getLanguage()->getWords( 'info/news' );
		$shortCodes	= [
			'form'	=> [
				'id'	=> 0,
			]
		];
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			$helper		= new View_Helper_Form( $this->env );
			while( ( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				try{
					$helper->setId( $attr['id'] );
					$replacement	= $helper->render();
					$processor->replaceNext(
						$shortCode,
						$replacement
					);
				}
				catch( Exception $e ){
					$this->env->getMessenger()->noteFailure( 'Short code failed: '.$e->getMessage() );
					break;
				}
			}
		}
		$this->payload['content']	= $processor->getContent();
	}
}
