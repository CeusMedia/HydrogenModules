<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info_Blog extends Hook
{
	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@throws		Exception
	 */
	public function onViewRenderContent(): void
	{
		$content	= $this->payload['content'];

		/** @todo remove this legacy support */
		$pattern	= "/\[blog:(\S+)\]/sU";															//  old syntax
		if( preg_match( $pattern, $content ) )														//  found instance of old syntax
			$content	= preg_replace( $pattern, '[blog id="\\1"]', $content );			//  replace by new syntax

		$processor	= new Logic_Shortcode( $this->env );
		$processor->setContent( $content );
		while( $attr = $processor->find( 'blog' ) ){
			$panel	= View_Info_Blog::renderPostAbstractPanelStatic( $this->env, $attr['id'] );
			$processor->replaceNext( 'blog', $panel );
		}
		$this->payload['content']	= $processor->getContent();
	}
}
