<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Blog extends Hook
{
	public function onViewRenderContent(): void
	{
		$content	= $this->payload['content'];
		$pattern	= "/^(.*)(\[blog:(.+)\])(.*)$/sU";
		while( preg_match( $pattern, $content ) ){
			$id				= trim( preg_replace( $pattern, "\\3", $content ) );
			$content		= View_Info_Blog::renderPostAbstractPanelStatic( $this->env, $id );
			$replacement	= "\\1".$content."\\4";													//  insert content of nested page...
			$content		= preg_replace( $pattern, $replacement, $content );						//  ...into page content
		}
		$this->payload['content']	= $content;
	}
}
