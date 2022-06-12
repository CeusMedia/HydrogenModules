<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Helper_Content extends Hook
{
	public static function onPageApplyModules( $env, $context, $module, $payload )
	{
		$map	= array(
			'text'			=> "formatText",
			'currencies'	=> "formatCurrencies",
			'links'			=> "formatLinks",
			'links.discogs'	=> "formatDiscogsLinks",
			'links.imdb'	=> "formatImdbLinks",
			'links.map'		=> "formatMapLinks",
			'links.myspace'	=> "formatMyspaceLinks",
			'links.wiki'	=> "formatWikiLinks",
			'links.youtube'	=> "formatYoutubeLinks",
			'search.image'	=> "formatImageSearch",
			'search.map'	=> "formatMapSearch",
			'code'			=> "formatCodeBlocks",
			'breaks'		=> "formatBreaks",
			'lists'			=> "formatLists",
		);
		$plugins	= $env->getConfig()->getAll( 'module.ui_helper_content.register.' );
		foreach( $map as $key => $method ){
			if( isset( $plugins[$key] ) && $plugins[$key] ){
				View_Helper_ContentConverter::register( 'View_Helper_ContentConverter', $method );
			}
		}
	}
}
