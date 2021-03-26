<?php
class Hook_UI_Markdown extends CMF_Hydrogen_View
{
	/**
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@static
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	public static function onRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		$config	= $env->getConfig()->getAll( 'module.ui_markdown.', TRUE );			//  get module configuration
		if( !$config->get( 'active' ) )												//  module is not active
			return;																	//  skip this hook
		$payload	= (object) $payload;											//  convert given data to object
		$type		= strtolower( $payload->type );									//  convert given content type to lowercase
		if( in_array( $type, array( 'markdown', 'md' ), TRUE ) ){					//  content is Markdown
			$renderer			= new CeusMedia\Markdown\Renderer\Html();			//  create renderer
			$payload->content	= $renderer->convert( $payload->content );			//  convert to HTML
			return TRUE;															//  break hook handling chain
		}
		return;
	}

	/**
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@static
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	public static function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload )
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
}
