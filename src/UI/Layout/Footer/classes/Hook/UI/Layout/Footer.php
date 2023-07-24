<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Hook;

class Hook_UI_Layout_Footer extends Hook
{
	public function onPageBuild(): void
	{
		$pattern	= "/^(.*)(\[footer\])(.*)$/sU";
		if( preg_match( $pattern, $this->payload['content'] ) ){
			$links	= [];
			if( isset( $scopes->footer ) )
				foreach( $scopes->footer as $page )
					$links[$page->path]	= $page->label;

			$footer	= [['id' => 'timestamp', 'label' => 'Date & Time: <b>'.date( "d.m.Y H:i:s" ).'</b>']];
			if( $this->env->getDatabase() )
				$footer[]	= ['id' => 'database', 'label' => 'Database Queries: <b>'.$this->env->getDatabase()->numberStatements.'</b>'];
			if( $this->env->getModules()->has( 'Server_System_Load' ) )
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
			$this->payload['content']  = preg_replace( $pattern, "\\1".$content."\\4", $this->payload['content'] );
		}
	}
}
