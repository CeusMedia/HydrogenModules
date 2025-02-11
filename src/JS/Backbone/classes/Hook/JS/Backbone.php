<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Backbone extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$this->env->getLanguage()->load( 'gallery' );											//  load gallery language file
		$words		= $this->env->getLanguage()->getWords( 'gallery' );						//  get gallery feed words
		$this->context->addHead( HtmlTag::create( 'link', NULL, [						//  create link with attributes
			'rel'	=> "alternate",																	//
			'type'	=> "application/rss+xml",														//  MIME type of RSS
			'href'	=> $this->env->getConfig()->get( 'app.base.url' )."gallery/feed",			//  URL to RSS feed
			'title'	=> $this->env->getConfig()->get( 'app.name' ).': '.$words['feed']['title']	//  link title
		] ) );																						//  and add link to page head
		View_Helper_ContentConverter::register( "View_Helper_Gallery", "formatGalleryLinks" );
	}
}
