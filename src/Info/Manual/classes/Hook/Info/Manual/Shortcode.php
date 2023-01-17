<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Manual_Shortcode extends Hook
{
	public static function onViewRenderContent( Environment $env, $context, $module, $payload = [] )
	{
		$data			= (object) $payload;
		$processor		= new Logic_Shortcode( $env );
		$processor->setContent( $data->content );
//		$words			= $env->getLanguage()->getWords( '...module/id...' );
		$shortCodes		= [
			'manual:page'	=> [
				'id'		=> 0,
			]
		];

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
							$replacement	= HtmlTag::create( 'a', $category->title, array(
								'href' 		=> $helperUrl->render(),
								'class'		=> 'link-manual-category',
							) );
						}
					}
					if( $shortCode === 'manual:page' ){
						$page		= $modelPage->get( (int) $attr['id'] );
						if( $page ){
							$helperUrl->setPage( $page );
							$replacement	= HtmlTag::create( 'a', $page->title, array(
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
