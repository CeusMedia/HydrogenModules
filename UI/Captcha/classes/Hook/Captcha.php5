<?php
class Hook_Captcha /*extends CMF_Hydrogen_Hook*/{

	static public function onViewRenderContent( $env, $context, $module, $data = array() ){
		$processor		= new Logic_Shortcode( $env );
		$shortCodes		= array(
			'captcha'	=> array(
			)
		);
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $data->content, $shortCode ) )
				continue;
			$helper		= new View_Helper_Captcha( $env );
			while( is_array( $attr = $processor->find( $data->content, $shortCode, $defaultAttributes ) ) ){
				try{
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
