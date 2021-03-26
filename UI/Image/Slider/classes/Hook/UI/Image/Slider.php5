<?php
class Hook_UI_Image_Slider extends CMF_Hydrogen_Hook
{
	public static function onRenderContent( CMF_Hydrogen_Environment $env, $context, $modules, $payload = array() )
	{
		$payload		= (object) $payload;
		$processor		= $env->getLogic()->get( 'Shortcode' );
		$shortCodes		= array(
			'slider'	=> array(
				'id'		=> 0,
			)
		);

		/** @todo remove this legacy support */
		$pattern	= "/\[slider:([0-9]+)\]/sU";													//  old syntax
		if( preg_match( $pattern, $payload->content ) )												//  found instance of old syntax
			$payload->content	= preg_replace( $pattern, '[slider id="\\1"]', $payload->content );	//  replace by new syntax

		$processor->setContent( $payload->content );
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
		$payload->content	= $processor->getContent();
	}
}
