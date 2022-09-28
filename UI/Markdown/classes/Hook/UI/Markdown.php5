<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class Hook_UI_Markdown extends View
{
	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( Environment $env, object $context, $module, array & $payload )
	{
		$config	= $env->getConfig()->getAll( 'module.ui_markdown.', TRUE );
		if( !$config->get( 'active' ) )
			return;
		if( !class_exists( '\\CeusMedia\\Markdown\\Renderer\\Html' ) ){
			$message	= 'Markdown support is not installed. Use composer to install "ceus-media/markdown"!';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
	}

	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		bool
	 */
	public static function onRenderContent( Environment $env, object $context, $module, array & $payload ): bool
	{
		$config	= $env->getConfig()->getAll( 'module.ui_markdown.', TRUE );			//  get module configuration
		if( !$config->get( 'active' ) )												//  module is not active
			return FALSE;															//  skip this hook
		$payload	= (object) $payload;											//  convert given data to object
		$type		= strtolower( $payload->type );									//  convert given content type to lowercase
		if( in_array( $type, array( 'markdown', 'md' ), TRUE ) ){					//  content is Markdown
			$renderer			= new CeusMedia\Markdown\Renderer\Html();			//  create renderer
			$payload->content	= $renderer->convert( $payload->content );			//  convert to HTML
			return TRUE;															//  break hook handling chain
		}
		return FALSE;
	}
}
