<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_MailEncryption extends Hook
{
	public static function onPageApplyModules( Environment $env, $context, $module, array & $payload )
	{
		$options	=  $env->getConfig()->getAll( 'module.ui_js_mailencryption.', TRUE );
		if( !$options->get( 'active' ) )
			return;
		$context->js->addScriptOnReady( 'UI.MailEncryption.decrypt();', 8 );
	}

	public static function onPageBuild( Environment $env, $context, $module, array & $payload )
	{
		$options	=  $env->getConfig()->getAll( 'module.ui_js_mailencryption.', TRUE );
		if( !$options->get( 'active' ) )
			return;
		$matches		= [];
		$pattern	= '@^(.*)<a[^>]+href="mailto:(\S+\@\S+)".*>(.+)</a>(.*)$@siU';					//  pattern to match mail shortcode
		while( preg_match( $pattern, $payload['content'] ) ){											//  while mail links in content
			preg_match_all( $pattern, $payload['content'], $matches );								//  get all match parts
			list( $partName, $partHost )	= explode( '@', $matches[2][0] );						//  extract mail parts
			$replacement	= UI_HTML_Tag::create( 'span', $matches[3][0], array(					//  build replacement ...
				'class'			=> 'encrypted-mail',												//  ... set identifier class for JS decryption
				'data-name'		=> $partName,														//  ... set name part
				'data-host'		=> $partHost,														//  ... set host part
			) );
			$replacement	= "\\1".$replacement."\\4";												//  build preg replacement
			$payload['content']	= preg_replace( $pattern, $replacement, $payload['content'], 1 );		//  and replace in page content
		}

		$pattern	= '@^(.*)(\[\[mailto:(.+)\@(.*)(\|(.+))?\]\])(.*)$@siU';						//  pattern to match mail shortcode
		while( preg_match( $pattern, $payload['content'] ) ){											//  while mail shortcodes in content
			preg_match_all( $pattern, $payload['content'], $matches );								//  get all match parts
			$label			= $matches[3][0].'@'.$matches[4][0];									//  glue label from name and host part
			if( !empty( $matches[6][0] ) )															//  a link label has been set
				$label	= $matches[6][0];															//  replace glued label by set label
			$replacement	= UI_HTML_Tag::create( 'span', $label, array(							//  build replacement ...
				'class'			=> 'encrypted-mail',												//  ... set identifier class for JS decryption
				'data-name'		=> $matches[3][0],													//  ... set name part
				'data-host'		=> $matches[4][0],													//  ... set host part
			) );
			$replacement	= "\\1".$replacement."\\7";												//  build preg replacement
			$payload['content']	= preg_replace( $pattern, $replacement, $payload['content'], 1 );		//  and replace in page content
		}
	}
}
