<?php
class Hook_Captcha /*extends CMF_Hydrogen_Hook*/{

	static public function onViewRenderContent( $env, $context, $module, $data = array() ){
		$config	= $env->getConfig()->getAll( 'module.ui_captcha.', TRUE );

		$length		= $config->get( 'length' ) > 2 ? $config->get( 'length' ) : 4;
		$strength	= $config->get( 'strength' ) ? $config->get( 'strength' ) : 'soft';
		$width		= $config->get( 'width' ) > 0 ? $config->get( 'width' ) : 100;
		$height		= $config->get( 'height' ) > 0 ? $config->get( 'height' ) : 40;

		$processor		= new Logic_Shortcode( $env );
		$shortCodes		= array(
			'captcha'	=> array(
				'length'	=> $length,
				'strength'	=> $strength,
				'width'		=> $width,
				'height'	=> $height,
			)
		);
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $data->content, $shortCode ) )
				continue;
			$helper		= new View_Helper_Captcha( $env );
			while( is_array( $attr = $processor->find( $data->content, $shortCode, $defaultAttributes ) ) ){
				try{
					$helper->setLength( $attr['length'] );
					$helper->setStrength( $attr['strength'] );
					$helper->setWidth( $attr['width'] );
					$helper->setHeight( $attr['height'] );
					$replacement	= $helper->render();											//  get newslist content
					$data->content	= $processor->replaceNext(
						$data->content,
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
	}
}
