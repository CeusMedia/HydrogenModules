<?php
class Hook_Info_NewsList/* extends CMF_Hydrogen_Hook*/{

	static public function onViewRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$processor		= new Logic_Shortcode( $env );
		$words			= $env->getLanguage()->getWords( 'info/newslist' );
		$shortCodes		= array(
			'newslist'	=> array(
				'resource'				=> 'Info_NewsList',
				'action'				=> 'collectNews',
				'panel'					=> FALSE,
				'panel-heading'			=> $words['panel']['heading'],
				'panel-heading-level'	=> 3,
				'limit'					=> '5',
			)
		);
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $data->content, $shortCode ) )
				continue;
			$helper		= new View_Helper_NewsList( $env );
			while( ( $attr = $processor->find( $data->content, $shortCode, $defaultAttributes ) ) ){
				try{
				/*	$options	= ...; */
					$helper->collect( $attr['resource'], $attr['action']/*, $options */);		//  @todo add options
					$helper->setLimit( $attr['limit'] );
					$replacement	= $helper->render();											//  get newslist content
					if( $attr['panel'] ){
						$heading	= '';
						if( strlen( trim( $attr['panel-heading'] ) ) )
							$heading	= UI_HTML_Tag::create(
								'h'.$attr['panel-heading-level'],
								$attr['panel-heading']
							);
						$replacement	= UI_HTML_Tag::create( 'div', array(
							$heading,
							UI_HTML_Tag::create( 'div', $replacement, array( 'class' => 'content-panel-inner' ) ),
						), array( 'class' => 'content-panel' ) );
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
