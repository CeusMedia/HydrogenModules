<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Layout_Footer extends Hook
{
	public static function onPageBuild( Environment $env, object $context, $module, array & $payload )
	{
		$pattern	= "/^(.*)(\[footer\])(.*)$/sU";
		if( preg_match( $pattern, $payload['content'] ) ){
			$links	= [];
			if( isset( $scopes->footer ) )
				foreach( $scopes->footer as $pageId => $page )
					$links[$page->path]	= $page->label;

			$footer	= [['id' => 'timestamp', 'label' => 'Date & Time: <b>'.date( "d.m.Y H:i:s" ).'</b>']];
			if( $env->getDatabase() )
				$footer[]	= ['id' => 'database', 'label' => 'Database Queries: <b>'.$env->getDatabase()->numberStatements.'</b>'];
			if( $env->getModules()->has( 'Server_System_Load' ) )
				$footer[]	= ['id' => 'system-load', 'title' => 'System Load'];
			foreach( $links as $path => $label )
				$footer[]	= ['label' => HtmlTag::create( 'a', $label, ['href' => $path] )];

			$list	= [];
			foreach( $footer as $entry ){
				$label	= "";
				if( isset( $entry['label'] ) ){
					$label	= $entry['label'];
					unset( $entry['label'] );
				}
				$list[]	= HtmlTag::create( 'li', $label, $entry );
			}
			$footer		= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );
			$footer		= HtmlTag::create( 'div', $footer, ['class' => 'container'] );
			$content	= HtmlTag::create( 'div', $footer, ['id' => 'layout-footer'] );
			$payload['content']  = preg_replace( $pattern, "\\1".$content."\\4", $payload['content'] );
		}
	}
}
