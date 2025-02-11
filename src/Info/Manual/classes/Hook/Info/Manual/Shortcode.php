<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Manual_Shortcode extends Hook
{
	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function onViewRenderContent(): void
	{
		$data			= (object) $this->payload;
		$processor		= new Logic_Shortcode( $this->env );
		$processor->setContent( $data->content );
//		$words			= $env->getLanguage()->getWords( '...module/id...' );
		$shortCodes		= [
			'manual:page'	=> [
				'id'		=> 0,
			]
		];

		$modelPage		= new Model_Manual_Page( $this->env );
		$modelCategory	= new Model_Manual_Category( $this->env );
		$helperUrl		= new View_Helper_Info_Manual_Url( $this->env );
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			while( ( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				$replacement	= '';
				try{
					if( 'manual:category' === $shortCode ){
						$category		= $modelCategory->get( (int) $attr['id'] );
						if( $category ){
							$helperUrl->setCategory( $category );
							$replacement	= HtmlTag::create( 'a', $category->title, [
								'href' 		=> $helperUrl->render(),
								'class'		=> 'link-manual-category',
							] );
						}
					}
					if( 'manual:page' === $shortCode ){
						$page		= $modelPage->get( (int) $attr['id'] );
						if( $page ){
							$helperUrl->setPage( $page );
							$replacement	= HtmlTag::create( 'a', $page->title, [
								'href' 		=> $helperUrl->render(),
								'class'		=> 'link-manual-page',
							] );
						}
					}
				}
				catch( Exception $e ){
					$this->env->getMessenger()->noteFailure( 'Short code failed: '.$e->getMessage() );
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
