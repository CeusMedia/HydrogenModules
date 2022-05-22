<?php
use CeusMedia\HydrogenFramework\Environment;

class Hook_Gallery_Compact extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( Environment $env, $context, $module, $payload = [] ){
		$env->getLanguage()->load( 'gallery' );														//  load gallery language file
		$words		= $env->getLanguage()->getWords( 'gallery' );									//  get gallery feed words
		$context->addHead( UI_HTML_Tag::create( 'link', NULL, array(								//  create link with attributes
			'rel'	=> "alternate",																	//
			'type'	=> "application/rss+xml",														//  MIME type of RSS
			'href'	=> $env->getConfig()->get( 'app.base.url' )."gallery/feed",						//  URL to RSS feed
			'title'	=> $env->getConfig()->get( 'app.name' ).': '.$words['feed']['title']			//  link title
		) ) );																						//  and add link to page head
		View_Helper_ContentConverter::register( "View_Helper_Gallery", "formatGalleryLinks" );
	}
}
