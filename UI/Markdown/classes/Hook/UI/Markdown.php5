<?php
class Hook_UI_Markdown extends CMF_Hydrogen_View
{
	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $payload )
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
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		$config	= $env->getConfig()->getAll( 'module.ui_markdown.', TRUE );
		if( !$config->get( 'active' ) )
			return;
		if( !class_exists( '\\CeusMedia\\Markdown\\Renderer\\Html' ) ){
			$message	= 'Markdown support is not installed. Use composer to install "ceus-media/markdown"!';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
/*		$libVersion	= \Michelf\Markdown::MARKDOWNLIB_VERSION;
		if( $config->get( 'version.min' ) ){
			if( version_compare( $libVersion, $config->get( 'version.min' ), '<=' ) ){
				$message	= 'Installed version of Markdown is invalid - must be atleast %s.';
				$env->getMessenger()->noteFailure( sprint_m( $message, $config->get( 'version.min' ) ) );
				return;
			}
		}
		if( $config->get( 'version.max' ) ){
			if( version_compare( $libVersion, $config->get( 'version.max' ), '>' ) ){
				$message	= 'Installed version of Markdown is invalid - must be atmost %s.';
				$env->getMessenger()->noteFailure( sprint_m( $message, $config->get( 'version.max' ) ) );
				return;
			}
		}*/
	}
}

