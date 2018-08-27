<?php
class Hook_Form/* extends CMF_Hydrogen_Hook*/{

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Object scope to apply hook within
	 *	@param		???							$module		???
	 *	@param		array|object				$data		Data array or object for hook event handler
	 *	@return		boolean|NULL				...
	 */
	static public function onViewRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$processor		= new Logic_Shortcode( $env );
		$processor->setContent( $data->content );
		$words			= $env->getLanguage()->getWords( 'info/news' );
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
		$data->content	= $processor->getContent();
	}
}
