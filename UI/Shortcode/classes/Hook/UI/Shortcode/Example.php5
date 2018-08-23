<?php
class Hook_UI_Shortcode_Example extends CMF_Hydrogen_Hook{

	static public function onViewRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$processor		= new Logic_Shortcode( $env );
//		$words			= $env->getLanguage()->getWords( '...module/id...' );
		$shortCodes		= array(
			'example'	=> array(
				'type'			=> 'default',
			)
		);
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $data->content, $shortCode ) )
				continue;
//			$helper		= new View_Helper_Module_Id_Helper( $env );
			while( ( $attr = $processor->find( $data->content, $shortCode, $defaultAttributes ) ) ){
				try{
//					$helper->setType( $attr['type'] );
					$replacement	= $helper->render();											//  get newslist content
					if( strlen( $replacement ) ){
//						$label			= $words['...section...']['...key...'];
						$replacement	= UI_HTML_Tag::create( 'div', array(
							'This is an example.',//$label,
						), array( 'class' => 'example-type-'.$attr['type'] ) );
					}
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
