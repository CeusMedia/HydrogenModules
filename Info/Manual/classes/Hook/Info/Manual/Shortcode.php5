<?php
class Hook_Info_Manual_Shortcode extends CMF_Hydrogen_Hook
{
	public static function onViewRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] )
	{
		$data			= (object) $payload;
		$processor		= new Logic_Shortcode( $env );
		$processor->setContent( $data->content );
//		$words			= $env->getLanguage()->getWords( '...module/id...' );
		$shortCodes		= array(
			'manual:page'	=> array(
				'id'		=> 0,
			)
		);

		$modelPage		= new Model_Manual_Page( $env );
		$modelCategory	= new Model_Manual_Category( $env );

		$helperUrl		= new View_Helper_Info_Manual_Url( $env );
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			while( ( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				$replacement	= '';
				try{
					if( $shortCode === 'manual:category' ){
						$category		= $modelCategory->get( (int) $attr['id'] );
						if( $category ){
							$helperUrl->setCategory( $category );
							$replacement	= UI_HTML_Tag::create( 'a', $category->title, array(
								'href' 		=> $helperUrl->render(),
								'class'		=> 'link-manual-category',
							) );
						}
					}
					if( $shortCode === 'manual:page' ){
						$page		= $modelPage->get( (int) $attr['id'] );
						if( $page ){
							$helperUrl->setPage( $page );
							$replacement	= UI_HTML_Tag::create( 'a', $page->title, array(
								'href' 		=> $helperUrl->render(),
								'class'		=> 'link-manual-page',
							) );
						}
					}
				}
				catch( Exception $e ){
					$env->getMessenger()->noteFailure( 'Short code failed: '.$e->getMessage() );
				}
				$processor->replaceNext(
					$shortCode,
					$replacement
				);
			}
		}
		$data->content	= $processor->getContent();
	}
}
