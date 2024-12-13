<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Image_Slider extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onRenderContent(): void
	{
		/** @var WebEnvironment $env */
		$env		= $this->env;
		$processor	= $env->getLogic()->get( 'Shortcode' );
		$shortCodes	= ['slider'	=> ['id' => 0]];

		/** @todo remove this legacy support */
		$pattern	= "/\[slider:([0-9]+)\]/sU";													//  old syntax
		if( preg_match( $pattern, $this->payload['content'] ) )												//  found instance of old syntax
			$this->payload['content']	= preg_replace( $pattern, '[slider id="\\1"]', $this->payload['content'] );	//  replace by new syntax

		$processor->setContent( $this->payload['content'] );
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			$helper		= new View_Helper_Image_Slider( $env );
			while( is_array( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				try{
//					$helper->setAttr( 'attr', $attr['attr'] );
					$replacement	= $helper->render( $attr['id'] );
					$processor->replaceNext( $shortCode, $replacement );
				}
				catch( Exception $e ){
					$env->getLog()->logException( $e );
					$env->getMessenger()->noteFailure( 'Rendering of slider failed: '.$e->getMessage() );
					$processor->removeNext( $shortCode );
				}
			}
		}
		$this->payload['content']	= $processor->getContent();
	}
}
